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
            ->orderByRaw("FIELD(role, 'admin', 'staff')")
            ->orderBy('name')
            ->get();

        foreach ($users as $u) {
            if (!$u->staffProfile) {
                $code = $u->role === 'admin'
                    ? 'ADM-' . str_pad($u->id, 4, '0', STR_PAD_LEFT)
                    : 'STF-' . str_pad($u->id, 4, '0', STR_PAD_LEFT);
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
            'status' => ['required', 'in:active,on_leave,inactive'],
        ]);

        $profile = $user->staffProfile ?: new \App\Models\StaffProfile(['user_id' => $user->id]);
        $profile->full_name = $user->name;
        $profile->position_title = $validated['position_title'];
        $profile->phone = $validated['phone'] ?? null;
        $profile->specialization = $validated['specialization'] ?? null;
        $profile->status = $validated['status'];
        if (empty($profile->staff_code)) {
            $profile->staff_code = ($user->role === 'admin' ? 'ADM-' : 'STF-') . str_pad($user->id, 4, '0', STR_PAD_LEFT);
        }
        $profile->save();

        return back()->with('success', "Staff profile for {$user->name} updated successfully.");
    }
}
