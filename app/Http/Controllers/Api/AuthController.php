<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'adopter',
        ]);

        // Auto-create AdoptersProfile for registered adopter
        if ($user->role === 'adopter') {
            \App\Models\AdoptersProfile::firstOrCreate(
                ['email' => $user->email],
                [
                    'user_id'      => $user->id,
                    'adopter_code' => sprintf('ADP-%04d', $user->id),
                    'full_name'    => $user->name,
                    'status'       => 'active',
                ]
            );
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if ($user->role !== 'adopter') {
            return response()->json([
                'message' => 'Admin accounts cannot log in to the mobile app. Please use the Web Admin Portal.',
            ], 403);
        }

        // Auto-create AdoptersProfile for registered adopter
        $adopterProfile = \App\Models\AdoptersProfile::where('email', $user->email)->first();
        if ($user->role === 'adopter' && !$adopterProfile) {
            $adopterProfile = \App\Models\AdoptersProfile::create([
                'user_id'      => $user->id,
                'adopter_code' => sprintf('ADP-%04d', $user->id),
                'full_name'    => $user->name,
                'email'        => $user->email,
                'status'       => 'active',
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'phone'       => $adopterProfile?->phone ?: $user->phone,
                'address'     => $adopterProfile?->address,
                'city'        => $adopterProfile?->city,
                'province'    => $adopterProfile?->province,
                'status'      => $adopterProfile?->status ?? 'active',
                'admin_notes' => $adopterProfile?->admin_notes,
                'digital_signature_url' => $user->digital_signature_url ?: $adopterProfile?->digital_signature_url,
            ],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this email address.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password reset successfully! You can now log in.',
        ]);
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->email));
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'exists'  => $exists,
            'message' => $exists ? 'This email address is already registered. Please log in or use a different email.' : 'Email is available.',
        ]);
    }

    public function sendEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->email));

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email address is already registered. Please log in or use a different email.',
            ], 422);
        }

        $code = (string) random_int(100000, 999999);
        \Illuminate\Support\Facades\Cache::put('email_otp_' . $email, $code, now()->addMinutes(10));

        try {
            \Illuminate\Support\Facades\Mail::html("
                <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 24px; border: 1px solid #e0e0e0; border-radius: 16px; background-color: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #0A6B72; margin: 0;'>🐾 CAWS Pet Adoption</h2>
                        <p style='color: #666; font-size: 13px; margin: 4px 0 0 0;'>CDO Animal Welfare Society Inc.</p>
                    </div>
                    <p style='color: #333; font-size: 15px;'>Hello!</p>
                    <p style='color: #555; font-size: 14px; line-height: 1.5;'>Your 6-digit verification code is:</p>
                    <div style='background-color: #E6F4F5; border-radius: 12px; padding: 16px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #0A6B72;'>{$code}</span>
                    </div>
                    <p style='color: #888; font-size: 12px; text-align: center;'>This code will expire in 10 minutes. If you did not request this verification, please ignore this email.</p>
                </div>
            ", function ($m) use ($email) {
                $m->to($email)->subject('🐾 Your CAWS Verification Code');
            });

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string'],
        ]);

        $email = strtolower(trim($request->email));

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email address is already registered. Please log in or use a different email.',
            ], 422);
        }

        $cachedCode = \Illuminate\Support\Facades\Cache::get('email_otp_' . $email);

        if (! $cachedCode || $cachedCode !== trim($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        \Illuminate\Support\Facades\Cache::forget('email_otp_' . $email);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
        ]);
    }
}