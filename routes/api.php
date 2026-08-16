<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AdoptionApiController;
use App\Http\Controllers\Api\PetApiController;
use App\Http\Controllers\Api\RecommendationApiController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('send-email-otp', [AuthController::class, 'sendEmailOtp']);
    Route::post('verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
});

Route::get('/pets', [PetApiController::class, 'index']);
Route::get('/pets/{id}', [PetApiController::class, 'show']);
Route::post('/recommendations/match', [RecommendationApiController::class, 'getRecommendations']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/recommendations/my-preferences', [RecommendationApiController::class, 'getPreferences']);
    Route::post('/user/update-profile', function (Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        $user = $request->user();
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['name' => trim($request->name)]);
        $updatedUser = \App\Models\User::find($user->id);
        return response()->json(['success' => true, 'user' => $updatedUser]);
    });
    Route::post('/user/update-avatar', function (Request $request) {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        $user = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');
        $avatarUrl = asset('storage/' . $path);
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['avatar' => $avatarUrl]);
        $updatedUser = \App\Models\User::find($user->id);
        return response()->json([
            'success' => true,
            'avatar_url' => $avatarUrl,
            'user' => $updatedUser,
        ]);
    });
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $profile = \App\Models\AdoptersProfile::where('email', $user->email)
            ->orWhere('user_id', $user->id)
            ->first();

        $userData = $user->toArray();
        if ($profile) {
            $userData['adopter_code'] = $profile->adopter_code;
            $userData['phone'] = $profile->phone;
            $userData['address'] = $profile->address;
            $userData['city'] = $profile->city;
            $userData['province'] = $profile->province;
            $userData['status'] = $profile->status;
        }

        return response()->json($userData);
    });

    Route::post('/user/update-address', function (Request $request) {
        $request->validate([
            'address'  => 'required|string|max:500',
            'city'     => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'phone'    => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $code = sprintf('ADP-%04d', $user->id);

        $profile = \App\Models\AdoptersProfile::updateOrCreate(
            ['email' => $user->email],
            [
                'user_id'      => $user->id,
                'adopter_code' => $code,
                'full_name'    => $user->name,
                'phone'        => $request->phone ?: $user->phone ?? null,
                'address'      => $request->address,
                'city'         => $request->city ?: 'Cagayan de Oro City',
                'province'     => $request->province ?: 'Misamis Oriental',
                'status'       => 'active',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'profile' => $profile,
        ]);
    });
    Route::post('/adoption-applications', [AdoptionApiController::class, 'store']);
    Route::get('/my-applications', [AdoptionApiController::class, 'myApplications']);
    Route::get('/adoption-applications/{id}/contract', function (Request $request, $id) {
        $app = \App\Models\AdoptionApplication::findOrFail($id);
        // Check ownership via applicant_email since there is no user_id column
        if ($app->applicant_email !== auth()->user()->email) abort(403);
        \Illuminate\Support\Facades\URL::forceRootUrl($request->root());
        return response()->json([
            'url' => \Illuminate\Support\Facades\URL::temporarySignedRoute('contract.download', now()->addMinutes(60), ['id' => $id])
        ]);
    });
    Route::get('/vaccine-reminders', [AdoptionApiController::class, 'vaccineReminders']);
    Route::post('/health-updates', [AdoptionApiController::class, 'storeHealthUpdate']);
    Route::get('/my-health-updates', [AdoptionApiController::class, 'getHealthUpdates']);
    Route::post('/save-fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);
        $user = $request->user();
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['success' => true, 'message' => 'FCM token saved successfully.']);
    });
});
