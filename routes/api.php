<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'data' => [
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String()
        ]
    ]);
});

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/request-email-code', [AuthController::class, 'requestEmailCode']);
    Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes (require Sanctum authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // User profile
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_code' => $user->phone_code,
                    'phone' => $user->phone,
                    'full_phone' => $user->full_phone,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified' => $user->is_email_verified,
                    'phone_verified' => $user->is_phone_verified,
                    'avatar' => $user->avatar,
                    'bio' => $user->bio,
                    'country' => $user->country,
                    'state' => $user->state,
                    'city' => $user->city,
                    'full_location' => $user->full_location,
                    'timezone' => $user->timezone,
                    'last_login' => $user->last_login,
                    'created_at' => $user->created_at->toIso8601String()
                ]
            ]
        ]);
    });
    
    // Vendor routes
    Route::prefix('vendor')->group(function () {
        Route::get('/me', [VendorController::class, 'me']);
        Route::post('/apply', [VendorController::class, 'apply']);
        Route::put('/profile', [VendorController::class, 'updateProfile']);
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
    });
});