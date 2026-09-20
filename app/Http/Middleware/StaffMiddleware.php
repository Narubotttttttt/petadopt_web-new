<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'staff'], true)) {
            $user = Auth::user();
            if ($user->role === 'staff' && $user->staffProfile && in_array($user->staffProfile->status, ['deactivated', 'inactive'], true)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your staff account has been deactivated by the shelter administration.',
                ]);
            }

            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
