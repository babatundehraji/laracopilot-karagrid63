<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\Disputes\CustomerDisputeController;
use App\Http\Controllers\Api\Disputes\VendorDisputeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderConversationController;
use App\Http\Controllers\Api\Orders\CustomerOrderController;
use App\Http\Controllers\Api\Orders\VendorOrderController;
use App\Http\Controllers\Api\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Api\ServiceReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserTransactionController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\Vendor\ServiceController as VendorServiceController;
use App\Http\Controllers\Api\Vendor\WalletController;
use App\Http\Controllers\Api\WishlistController;
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
    
    // Public reviews (no auth required)
    Route::get('/{service}/reviews', [ServiceReviewController::class, 'index']);
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
    
    // Cart management
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::delete('/{service}', [CartController::class, 'destroy']);
    });
    
    // Wishlist management
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{service}', [WishlistController::class, 'destroy']);
    });
    
    // Checkout
    Route::prefix('checkout')->group(function () {
        Route::post('/from-cart', [CheckoutController::class, 'fromCart']);
        Route::post('/direct', [CheckoutController::class, 'direct']);
    });
    
    // Customer orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index']);
        Route::get('/{id}', [CustomerOrderController::class, 'show']);
        Route::post('/', [CustomerOrderController::class, 'store']);
        Route::post('/{id}/respond-edit', [CustomerOrderController::class, 'respondEdit']);
        Route::post('/{id}/complete', [CustomerOrderController::class, 'complete']);
        
        // Order conversations and messages
        Route::get('/{order}/conversation', [OrderConversationController::class, 'conversation']);
        Route::get('/{order}/messages', [OrderConversationController::class, 'messages']);
        Route::post('/{order}/messages', [OrderConversationController::class, 'sendMessage']);
        
        // Order disputes (customer)
        Route::post('/{order}/disputes', [CustomerDisputeController::class, 'openDispute']);
    });
    
    // Customer disputes
    Route::prefix('disputes')->group(function () {
        Route::get('/', [CustomerDisputeController::class, 'index']);
        Route::get('/{id}', [CustomerDisputeController::class, 'show']);
    });
    
    // User transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/', [UserTransactionController::class, 'index']);
        Route::get('/summary', [UserTransactionController::class, 'summary']);
        Route::get('/{id}', [UserTransactionController::class, 'show']);
    });
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
    
    // Service reviews (authenticated)
    Route::prefix('services/{service}/reviews')->group(function () {
        Route::post('/', [ServiceReviewController::class, 'store']);
        Route::put('/{id}', [ServiceReviewController::class, 'update']);
        Route::delete('/{id}', [ServiceReviewController::class, 'destroy']);
    });
    
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
        
        // Vendor orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [VendorOrderController::class, 'index']);
            Route::get('/{id}', [VendorOrderController::class, 'show']);
            Route::post('/{id}/accept', [VendorOrderController::class, 'accept']);
            Route::post('/{id}/propose-edit', [VendorOrderController::class, 'proposeEdit']);
        });
        
        // Vendor disputes
        Route::prefix('disputes')->group(function () {
            Route::get('/', [VendorDisputeController::class, 'index']);
            Route::get('/{id}', [VendorDisputeController::class, 'show']);
        });
        
        // Vendor wallet & transactions
        Route::prefix('wallet')->group(function () {
            Route::get('/transactions', [WalletController::class, 'transactions']);
            Route::get('/transactions/{id}', [WalletController::class, 'show']);
            Route::get('/summary', [WalletController::class, 'summary']);
        });
    });
});