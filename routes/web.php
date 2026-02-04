<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentTransactionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Admin Authentication Routes (Public)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// Admin Protected Routes (Require admin.web middleware)
Route::prefix('admin')->middleware('admin.web')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    
    // Users Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::post('/{user}/block', [UserController::class, 'block'])->name('admin.users.block');
        Route::post('/{user}/unblock', [UserController::class, 'unblock'])->name('admin.users.unblock');
    });
    
    // Orders Management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    });
    
    // Disputes Management
    Route::prefix('disputes')->group(function () {
        Route::get('/', [DisputeController::class, 'index'])->name('admin.disputes.index');
        Route::get('/{dispute}', [DisputeController::class, 'show'])->name('admin.disputes.show');
        Route::post('/{dispute}/resolve', [DisputeController::class, 'resolve'])->name('admin.disputes.resolve');
    });
    
    // Transactions Management
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('admin.transactions.index');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('admin.transactions.show');
        Route::post('/{transaction}/add-note', [TransactionController::class, 'addNote'])->name('admin.transactions.add-note');
        Route::post('/{transaction}/update-status', [TransactionController::class, 'updateStatus'])->name('admin.transactions.update-status');
    });
    
    // Payment Transactions (Gateway Logs)
    Route::prefix('payment-transactions')->group(function () {
        Route::get('/', [PaymentTransactionController::class, 'index'])->name('admin.payment-transactions.index');
        Route::get('/{payment}', [PaymentTransactionController::class, 'show'])->name('admin.payment-transactions.show');
    });
    
    // Future admin routes will go here:
    // Route::resource('vendors', VendorController::class);
    // Route::resource('categories', CategoryController::class);
});