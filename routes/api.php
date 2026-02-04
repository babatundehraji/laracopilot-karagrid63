<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\Vendor\ServiceController as VendorServiceController;
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

// Public services (no auth required)
Route::prefix('services')->group(function () {
    Route::get('/', [PublicServiceController::class, 'index']);
    Route::get('/{id}', [PublicServiceController::class, 'show']);
});

// Protected routes (require Sanctum authentication + reject admin users)
Route::middleware(['auth:sanctum', 'reject.admin'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // User profile
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    
    // User password change
    Route::post('/user/change-password/request-code', [UserController::class, 'requestPasswordChangeCode']);
    Route::post('/user/change-password/confirm', [UserController::class, 'confirmPasswordChange']);
    
    // User email change
    Route::post('/user/change-email/request-code', [UserController::class, 'requestEmailChangeCode']);
    Route::post('/user/change-email/confirm', [UserController::class, 'confirmEmailChange']);
    
    // Vendor profile routes
    Route::prefix('vendor')->group(function () {
        Route::get('/me', [VendorController::class, 'me']);
        Route::post('/apply', [VendorController::class, 'apply']);
        Route::put('/profile', [VendorController::class, 'updateProfile']);
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
        
        // Vendor services management
        Route::prefix('services')->group(function () {
            Route::get('/', [VendorServiceController::class, 'index']);
            Route::get('/{id}', [VendorServiceController::class, 'show']);
            Route::post('/', [VendorServiceController::class, 'store']);
            Route::put('/{id}', [VendorServiceController::class, 'update']);
            Route::patch('/{id}/status', [VendorServiceController::class, 'updateStatus']);
            Route::delete('/{id}', [VendorServiceController::class, 'destroy']);
        });
    });
});