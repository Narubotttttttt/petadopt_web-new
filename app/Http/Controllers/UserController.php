<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $users = User::with('staffProfile')
            ->whereIn('role', ['admin', 'staff'])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        foreach ($users as $u) {
            if (!$u->staffProfile) {
                $code = \App\Models\StaffProfile::generateStaffCode($u->role);
                $title = $u->role === 'admin'
                    ? 'Shelter Director / Head Administrator'
                    : 'CAWS Adoption & Care Staff';
                \App\Models\StaffProfile::create([
                    'user_id' => $u->id,
                    'staff_code' => $code,
                    'full_name' => $u->name,
                    'position_title' => $title,
                    'status' => 'active',
                ]);
                $u->load('staffProfile');
            }
        }

        return view('users.index', compact('users'));
    }

    public function updateStaffProfile(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'position_title' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = $user->staffProfile ?: new \App\Models\StaffProfile(['user_id' => $user->id]);
        $profile->full_name = $user->name;
        $profile->position_title = $validated['position_title'];
        $profile->phone = $validated['phone'] ?? null;
        $profile->specialization = $validated['specialization'] ?? null;
        if (empty($profile->status)) {
            $profile->status = 'active';
        }
        if (empty($profile->staff_code)) {
            $profile->staff_code = \App\Models\StaffProfile::generateStaffCode($user->role);
        }
        $profile->save();

        return back()->with('success', "Staff details for {$user->name} updated successfully.");
    }

    public function toggleStaffStatus(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'You cannot deactivate your own administrator account.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Administrator accounts cannot be deactivated from this action.');
        }

        $profile = $user->staffProfile ?: new \App\Models\StaffProfile(['user_id' => $user->id]);
        $currentStatus = $profile->status ?? 'active';
        $newStatus = in_array($currentStatus, ['deactivated', 'inactive']) ? 'active' : 'deactivated';

        $profile->status = $newStatus;
        if (empty($profile->full_name)) {
            $profile->full_name = $user->name;
        }
        if (empty($profile->staff_code)) {
            $profile->staff_code = \App\Models\StaffProfile::generateStaffCode($user->role);
        }
        $profile->save();

        $actionWord = $newStatus === 'active' ? 'reactivated' : 'deactivated';
        return back()->with('success', "Staff account for {$user->name} has been {$actionWord} successfully.");
    }

    public function resendVerification(User $user): RedirectResponse
    {
        $admin = auth()->user();
        if (! $admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('info', "{$user->name} has already verified their email address.");
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "A new verification email has been sent to {$user->email}.");
    }
}
