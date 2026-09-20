<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegistrationVerificationCodeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display Page 1: Identity & Email Verification.
     */
    public function create(): View|RedirectResponse
    {
        $adminExists = User::where('role', 'admin')->exists();

        if (! auth()->check()) {
            if ($adminExists) {
                return redirect()->route('login')->with('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');
            }

            return view('auth.register', [
                'pageTitle' => 'Create Admin Account',
                'pageSubtitle' => 'Step 1: Enter your identity and verify your email.',
                'showRoleSelect' => false,
                'registerRole' => 'admin',
                'prefill' => session('registration_data', []),
            ]);
        }

        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        return view('auth.register', [
            'pageTitle' => 'Create Staff Account',
            'pageSubtitle' => 'Step 1: Enter staff identity and verify their email.',
            'showRoleSelect' => false,
            'registerRole' => 'staff',
            'prefill' => session('registration_data', []),
        ]);
    }

    /**
     * Send a 6-digit verification code to the prospective registrant's email.
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $adminExists = User::where('role', 'admin')->exists();

        if (! auth()->check() && $adminExists) {
            return response()->json([
                'success' => false,
                'message' => 'Public registration is closed. Please contact your shelter administrator.',
            ], 403);
        }

        if (auth()->check() && auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'name'  => ['nullable', 'string', 'max:255'],
        ]);

        $email = strtolower(trim($request->email));
        $otp = (string) random_int(100000, 999999);

        // Cache code for 15 minutes
        Cache::put('reg_otp_' . $email, $otp, now()->addMinutes(15));
        Cache::forget('reg_verified_' . $email);

        // Send email notification
        Notification::route('mail', $email)
            ->notify(new RegistrationVerificationCodeNotification($otp, $request->name ?: 'Prospective Staff'));

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your email address.',
        ]);
    }

    /**
     * Verify the 6-digit verification code submitted in Step 1.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email'],
            'code'  => ['required', 'string', 'digits:6'],
        ]);

        $email = strtolower(trim($request->email));
        $cachedOtp = Cache::get('reg_otp_' . $email);

        if (! $cachedOtp || trim($request->code) !== (string) $cachedOtp) {
            Cache::forget('reg_verified_' . $email);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code. Please check your inbox or request a new code.',
            ], 422);
        }

        // Cache pre-verified state for 30 minutes
        Cache::put('reg_verified_' . $email, true, now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully. You can now proceed to set your password.',
        ]);
    }

    /**
     * Process Step 1 submission and advance to Page 2 (Set Password).
     *
     * @throws ValidationException
     */
    public function processStep1(Request $request): RedirectResponse
    {
        $adminExists = User::where('role', 'admin')->exists();

        if (! auth()->check()) {
            if ($adminExists) {
                return redirect()->route('login')->with('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');
            }
            $role = 'admin';
        } else {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            $role = 'staff';
        }

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'code'  => ['required', 'string', 'digits:6'],
        ]);

        $email = strtolower(trim($request->email));
        $cachedOtp = Cache::get('reg_otp_' . $email);

        if (! $cachedOtp || trim($request->code) !== (string) $cachedOtp) {
            Cache::forget('reg_verified_' . $email);
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired verification code. Please enter the valid 6-digit code sent to your email.',
            ]);
        }

        // Mark verified in cache
        Cache::put('reg_verified_' . $email, true, now()->addMinutes(30));

        // Store step 1 state in session
        session([
            'registration_data' => [
                'name'  => trim($request->name),
                'email' => $email,
                'role'  => $role,
            ],
        ]);

        return redirect()->route('register.set-password');
    }

    /**
     * Display Page 2: Set Password.
     */
    public function createPassword(): View|RedirectResponse
    {
        $data = session('registration_data');

        if (! $data || empty($data['email'])) {
            return redirect()->route('register')->with('info', 'Please complete identity and email verification first.');
        }

        $adminExists = User::where('role', 'admin')->exists();

        if (! auth()->check()) {
            if ($adminExists) {
                session()->forget('registration_data');
                return redirect()->route('login')->with('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');
            }
        }

        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        return view('auth.set-password', [
            'pageTitle' => $data['role'] === 'admin' ? 'Set Admin Password' : 'Set Staff Password',
            'pageSubtitle' => 'Step 2: Choose a secure password to complete account creation.',
            'registrationData' => $data,
        ]);
    }

    /**
     * Process Page 2 submission: Finalize registration with password.
     *
     * @throws ValidationException
     */
    public function storePassword(Request $request): RedirectResponse
    {
        $data = session('registration_data');

        if (! $data || empty($data['email'])) {
            return redirect()->route('register')->with('info', 'Please verify your email address before setting a password.');
        }

        $adminExists = User::where('role', 'admin')->exists();

        if (! auth()->check()) {
            if ($adminExists) {
                session()->forget('registration_data');
                return redirect()->route('login')->with('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');
            }
        }

        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = strtolower(trim($data['email']));
        $role = $data['role'];

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $email,
            'password' => Hash::make($request->password),
            'role'     => $role,
        ]);

        $user->markEmailAsVerified();

        // Clear session and cached codes
        session()->forget('registration_data');
        Cache::forget('reg_otp_' . $email);
        Cache::forget('reg_verified_' . $email);

        $code = \App\Models\StaffProfile::generateStaffCode($role);
        $title = $role === 'admin'
            ? 'Shelter Director / Head Administrator'
            : 'CAWS Adoption & Care Staff';

        \App\Models\StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'staff_code' => $code,
                'full_name' => $user->name,
                'position_title' => $title,
                'status' => 'active',
            ]
        );

        if (! auth()->check()) {
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Admin account created and verified successfully.');
        }

        return redirect()->route('users.index')->with('success', "Staff account for {$user->name} created and verified successfully.");
    }

    /**
     * Handle single-payload registration request (used by API or direct submit).
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $adminExists = User::where('role', 'admin')->exists();

        if (! auth()->check()) {
            if ($adminExists) {
                return redirect()->route('login')->with('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');
            }
            $role = 'admin';
        } else {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            $role = 'staff';
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'verification_code' => ['nullable', 'string'],
        ]);

        $email = strtolower(trim($request->email));
        $isPreVerified = Cache::get('reg_verified_' . $email) === true;
        $cachedOtp = Cache::get('reg_otp_' . $email);
        $submittedCode = trim($request->verification_code ?? '');

        if ($submittedCode !== '') {
            if (! $cachedOtp || $submittedCode !== (string) $cachedOtp) {
                Cache::forget('reg_verified_' . $email);
                throw ValidationException::withMessages([
                    'verification_code' => 'Invalid or expired verification code. Please enter the valid 6-digit code sent to your email.',
                ]);
            }
        } elseif (! $isPreVerified) {
            throw ValidationException::withMessages([
                'verification_code' => 'Please verify your email with the 6-digit code before completing registration.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        $user->markEmailAsVerified();

        // Cleanup registration cache
        Cache::forget('reg_otp_' . $email);
        Cache::forget('reg_verified_' . $email);
        session()->forget('registration_data');

        $code = \App\Models\StaffProfile::generateStaffCode($role);
        $title = $role === 'admin'
            ? 'Shelter Director / Head Administrator'
            : 'CAWS Adoption & Care Staff';

        \App\Models\StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'staff_code' => $code,
                'full_name' => $user->name,
                'position_title' => $title,
                'status' => 'active',
            ]
        );

        if (! auth()->check()) {
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Admin account registered and verified successfully.');
        }

        return redirect()->route('users.index')->with('success', "Staff account for {$user->name} created and verified successfully.");
    }
}
