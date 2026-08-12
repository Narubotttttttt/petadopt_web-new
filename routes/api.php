<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AdoptionApiController;
use App\Http\Controllers\Api\PetApiController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('google-login', [AuthController::class, 'googleLogin']);
    Route::post('send-email-otp', [AuthController::class, 'sendEmailOtp']);
    Route::post('verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
});

Route::get('/pets', [PetApiController::class, 'index']);
Route::get('/pets/{id}', [PetApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
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
    Route::post('/save-fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);
        $user = $request->user();
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['success' => true, 'message' => 'FCM token saved successfully.']);
    });
});
