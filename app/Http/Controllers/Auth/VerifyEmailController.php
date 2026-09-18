<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified via signed link.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if (! $user) {
            abort(404, 'User not found.');
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification signature.');
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
            Cache::forget('email_verification_otp_' . $user->id);
        }

        // If authenticated on this browser as this user
        if (Auth::check() && Auth::id() === $user->id) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        // Auto-authenticate verified user so mobile browser opens straight into portal
        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }

    /**
     * Mark the user's email address as verified via 6-digit OTP.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        $cachedOtp = Cache::get('email_verification_otp_' . $user->id);

        if (! $cachedOtp || trim($request->otp) !== (string) $cachedOtp) {
            return back()->withErrors([
                'otp' => 'The verification code provided is invalid or has expired. Please check your email or request a fresh code.',
            ]);
        }

        Cache::forget('email_verification_otp_' . $user->id);

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
