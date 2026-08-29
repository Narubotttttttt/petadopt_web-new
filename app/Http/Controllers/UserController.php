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

        $users = User::whereIn('role', ['admin', 'staff'])
            ->orderByRaw("FIELD(role, 'admin', 'staff')")
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'role' => ['required', 'in:admin,staff'],
        ]);

        if ($user->id === $admin->id && $request->role !== 'admin') {
            return back()->withErrors(['role' => 'You cannot demote your own administrator account.']);
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', "Role for {$user->name} updated to " . ucfirst($request->role) . '.');
    }
}
