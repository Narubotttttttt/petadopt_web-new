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
});

Route::get('/pets', [PetApiController::class, 'index']);
Route::get('/pets/{id}', [PetApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/adoption-applications', [AdoptionApiController::class, 'store']);
    Route::get('/my-applications', [AdoptionApiController::class, 'myApplications']);
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
