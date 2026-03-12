<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\Api\ProductControllerApi;
use App\Http\Controllers\Api\CategoryControllerApi;
use App\Http\Controllers\Api\CartControllerApi;

// Public Routes
Route::post('/register', [AuthControllerApi::class, 'register']);
Route::post('/login', [AuthControllerApi::class, 'login']);

Route::get('/products', [ProductControllerApi::class, 'index']);
Route::get('/products/{id}', [ProductControllerApi::class, 'show']);
Route::get('/categories', [CategoryControllerApi::class, 'index']);
Route::get('/payment-methods', [\App\Http\Controllers\Api\PaymentMethodControllerApi::class, 'index']);
Route::get('/settings', [\App\Http\Controllers\Api\SystemSettingControllerApi::class, 'index']);
Route::get('/settings/{key}', [\App\Http\Controllers\Api\SystemSettingControllerApi::class, 'show']);

// Protected Routes (Require Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthControllerApi::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/counts', [AuthControllerApi::class, 'getCounts']);

    // Products
    Route::post('/products', [ProductControllerApi::class, 'store']);
    Route::get('/my-products', [ProductControllerApi::class, 'myProducts']);
    Route::put('/products/{id}', [ProductControllerApi::class, 'update']);
    Route::delete('/products/{id}', [ProductControllerApi::class, 'destroy']);

    // Cart
    Route::get('/cart', [CartControllerApi::class, 'index']);
    Route::post('/cart', [CartControllerApi::class, 'store']);
    Route::put('/cart/{cart}', [CartControllerApi::class, 'update']);
    Route::delete('/cart/{cart}', [CartControllerApi::class, 'destroy']);

    // Transactions
    Route::get('/transactions', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'index']);
    Route::get('/transactions/{id}', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'show']);
    Route::post('/transactions/preview', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'preview']);
    Route::post('/transactions/confirm', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'confirm']);
    Route::post('/transactions/direct', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'store']); // Legacy
    Route::post('/transactions/cart', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'storeCart']); // Legacy
    Route::post('/transactions/{transaction}/proof', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'uploadProof']);

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'update']);
    Route::put('/profile/password', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'updatePassword']);
    Route::post('/profile/avatar', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'updateAvatar']);
    Route::get('/profile/addresses', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'addresses']);
    Route::post('/profile/addresses', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'storeAddress']);
    Route::put('/profile/addresses/{id}', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'updateAddress']);
    Route::delete('/profile/addresses/{id}', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'destroyAddress']);
    Route::put('/profile/addresses/{id}/default', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'setDefaultAddress']);
    Route::get('/profile/wishlists', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'wishlists']);
    Route::post('/products/{id}/wishlist', [\App\Http\Controllers\Api\ProfileControllerApi::class, 'toggleWishlist']);

    // Order Management
    Route::post('/transactions/{id}/ship', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'shipOrder']);
    Route::post('/transactions/{id}/status', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'updateOrderStatus']);
    Route::post('/transactions/{id}/confirm', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'confirmReceived']);
    Route::post('/transactions/{id}/cancel', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'cancel']);
    Route::post('/transactions/mark-seen', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'markAsSeen']);
    Route::delete('/transactions/{id}', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'destroy']);

    // Seller Dashboard
    Route::get('/seller/transactions', [\App\Http\Controllers\Api\SellerControllerApi::class, 'transactions']);
    Route::get('/seller/transactions/{id}', [\App\Http\Controllers\Api\SellerControllerApi::class, 'show']);
    Route::delete('/seller/transactions/{id}', [\App\Http\Controllers\Api\SellerControllerApi::class, 'destroy']);
    Route::get('/seller/balance', [\App\Http\Controllers\Api\SellerControllerApi::class, 'balance']);
    Route::post('/seller/transactions/mark-seen', [\App\Http\Controllers\Api\SellerControllerApi::class, 'markAsSeen']);
    Route::post('/seller/withdraw', [\App\Http\Controllers\Api\SellerControllerApi::class, 'withdraw']);

    // Reviews
    Route::post('/reviews/{transaction}', [\App\Http\Controllers\Api\ReviewControllerApi::class, 'store']);

    // Vouchers
    Route::get('/vouchers', [\App\Http\Controllers\Api\VoucherControllerApi::class, 'index']);
    Route::post('/vouchers/check', [\App\Http\Controllers\Api\VoucherControllerApi::class, 'check']);
});
