<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information and photo.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Handle Avatar Upload for Staff/Admin
        $staffProfile = \App\Models\StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'staff_code' => \App\Models\StaffProfile::generateStaffCode($user->role),
                'full_name'  => $user->name,
                'status'     => 'active',
            ]
        );

        if ($request->hasFile('avatar')) {
            if ($staffProfile->avatar && !filter_var($staffProfile->avatar, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($staffProfile->avatar)) {
                Storage::disk('public')->delete($staffProfile->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $staffProfile->avatar = $path;
            $staffProfile->save();
        } elseif ($request->boolean('remove_avatar')) {
            if ($staffProfile->avatar && !filter_var($staffProfile->avatar, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($staffProfile->avatar)) {
                Storage::disk('public')->delete($staffProfile->avatar);
            }
            $staffProfile->avatar = null;
            $staffProfile->save();
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the staff/admin user's digital signature on record.
     */
    public function updateSignature(Request $request): RedirectResponse
    {
        $request->validate([
            'signature_data' => ['required', 'string'],
        ]);

        $user = $request->user();
        $sigData = $request->signature_data;

        if (preg_match('/^data:image\/(\w+);base64,/', $sigData, $type)) {
            $sigData = substr($sigData, strpos($sigData, ',') + 1);
        }

        $decoded = base64_decode($sigData);
        if (!$decoded) {
            return back()->withErrors(['signature' => 'Invalid signature image data.']);
        }

        $fileName = 'signatures/staff_sig_' . $user->id . '_' . time() . '.png';
        Storage::disk('public')->put($fileName, $decoded);

        $staffProfile = \App\Models\StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'staff_code' => \App\Models\StaffProfile::generateStaffCode($user->role),
                'full_name'  => $user->name,
                'status'     => 'active',
            ]
        );

        if ($staffProfile->digital_signature_path && Storage::disk('public')->exists($staffProfile->digital_signature_path)) {
            Storage::disk('public')->delete($staffProfile->digital_signature_path);
        }

        $staffProfile->digital_signature_path = $fileName;
        $staffProfile->save();

        \App\Models\AdoptionApplication::where('staff_id', $user->id)
            ->update([
                'staff_signature_path' => $fileName,
                'staff_name'           => $user->name,
            ]);

        return Redirect::route('profile.edit')->with('status', 'signature-updated');
    }

    /**
     * Remove the user's saved digital signature.
     */
    public function destroySignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->staffProfile && $user->staffProfile->digital_signature_path) {
            if (Storage::disk('public')->exists($user->staffProfile->digital_signature_path)) {
                Storage::disk('public')->delete($user->staffProfile->digital_signature_path);
            }
            $user->staffProfile->digital_signature_path = null;
            $user->staffProfile->save();
        }

        if ($user->adoptersProfile && $user->adoptersProfile->digital_signature_path) {
            if (Storage::disk('public')->exists($user->adoptersProfile->digital_signature_path)) {
                Storage::disk('public')->delete($user->adoptersProfile->digital_signature_path);
            }
            $user->adoptersProfile->digital_signature_path = null;
            $user->adoptersProfile->save();
        }

        return Redirect::route('profile.edit')->with('status', 'signature-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // If admin tries to delete, ensure there is at least one other admin
        if ($user->role === 'admin') {
            $adminCount = \App\Models\User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['userDeletion' => 'Cannot delete the only administrator account.']);
            }
        }

        if ($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}