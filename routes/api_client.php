<?php

use App\Http\Controllers\Api\Client\CreditController;
use App\Http\Controllers\Api\Client\NotificationController;
use App\Http\Controllers\Api\Client\OrderController;
use App\Http\Controllers\Api\Client\PaymentController;
use App\Http\Controllers\Api\Client\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Client Portal
|--------------------------------------------------------------------------
|
| These routes are for the client portal API
| Authentication: Sanctum (sanctum guard)
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Products API
    Route::get('/companies/{company}/products', [ProductController::class, 'index']);
    Route::get('/companies/{company}/products/{product}', [ProductController::class, 'show']);
    Route::get('/companies/{company}/categories', [ProductController::class, 'categories']);

    // Orders API
    Route::get('/companies/{company}/orders', [OrderController::class, 'index']);
    Route::get('/companies/{company}/orders/{order}', [OrderController::class, 'show']);
    Route::post('/companies/{company}/orders', [OrderController::class, 'store']);

    // Credit/Fiados API
    Route::get('/companies/{company}/client/credit-balance', [CreditController::class, 'balance']);
    Route::get('/companies/{company}/client/transaction-history', [CreditController::class, 'history']);
    Route::get('/companies/{company}/client/upcoming-payments', [CreditController::class, 'upcomingPayments']);

    // Payments API (Stripe Integration)
    Route::post('/companies/{company}/payments/create-intent', [PaymentController::class, 'createIntent']);
    Route::post('/companies/{company}/payments/confirm', [PaymentController::class, 'confirm']);
    Route::get('/companies/{company}/payments', [PaymentController::class, 'history']);

    // Notifications API
    Route::get('/client/notifications', [NotificationController::class, 'index']);
    Route::get('/client/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/client/notifications/{notification}', [NotificationController::class, 'show']);
    Route::post('/client/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/client/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
