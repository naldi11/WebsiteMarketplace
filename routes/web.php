<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;

// Public Routes
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/home', function () {
    return redirect()->route('products.index');
})->name('home');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// TEMPORARY: Test route for reviews without auth - DEBUG 403
Route::get('/reviews/{id}', function ($id) {
    try {
        $transaction = \App\Models\Transaction::findOrFail($id);
        return "Transaction found! ID: {$transaction->id}, Status: {$transaction->status}";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Password Reset Routes
Route::get('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'showForgot'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'sendOtp'])->name('password.email');
Route::get('/verify-otp', [App\Http\Controllers\PasswordResetController::class, 'showOtp'])->name('password.otp');
Route::post('/verify-otp', [App\Http\Controllers\PasswordResetController::class, 'verifyOtp'])->name('password.verify');
Route::get('/reset-password', [App\Http\Controllers\PasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Products (Seller)
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.my');
    Route::get('/products/create/new', [ProductController::class, 'create'])->name('products.create'); // Specific URL to avoid conflict
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Cart
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
    Route::get('/cart/{product}', function () {
        return redirect()->route('cart.index');
    }); // Fallback for refresh
    Route::put('/cart/{cart}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');

    // Transactions
    Route::get('/checkout/cart', [TransactionController::class, 'checkoutCart'])->name('checkout.cart');
    Route::post('/checkout/review', [TransactionController::class, 'checkoutCart'])->name('checkout.review');
    Route::post('/checkout/cart', [TransactionController::class, 'storeCart'])->name('transactions.store_cart');
    Route::post('/checkout/voucher', [TransactionController::class, 'applyVoucher'])->name('checkout.voucher');
    Route::post('/checkout/voucher/remove', [TransactionController::class, 'removeVoucher'])->name('checkout.voucher.remove');
    Route::get('/checkout/{product}', [TransactionController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/{product}', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions', [TransactionController::class, 'history'])->name('transactions.history');
    Route::post('/vouchers/check', [\App\Http\Controllers\VoucherController::class, 'check'])->name('vouchers.check');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/transactions/{transaction}/proof', [TransactionController::class, 'uploadProof'])->name('transactions.upload_proof');

    // Admin Routes
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // User Management
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
        Route::post('/users/{id}/suspend', [App\Http\Controllers\AdminController::class, 'toggleSuspendUser'])->name('admin.users.suspend');
        Route::delete('/users/{id}/delete', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');

        Route::get('/transactions', [App\Http\Controllers\AdminController::class, 'transactions'])->name('admin.transactions');
        Route::get('/transactions/{transaction}', [App\Http\Controllers\AdminController::class, 'showTransaction'])->name('admin.transactions.show');
        Route::get('/transactions/{transaction}/invoice', [App\Http\Controllers\AdminController::class, 'printInvoice'])->name('admin.transactions.invoice');
        Route::post('/verify/{transaction}', [App\Http\Controllers\AdminController::class, 'verifyPayment'])->name('admin.verify');
        Route::post('/reject/{transaction}', [App\Http\Controllers\AdminController::class, 'rejectPayment'])->name('admin.reject');
        Route::post('/release/{transaction}', [App\Http\Controllers\AdminController::class, 'releaseFunds'])->name('admin.release');
        Route::get('/vouchers', [App\Http\Controllers\AdminController::class, 'vouchers'])->name('admin.vouchers');
        Route::post('/vouchers', [App\Http\Controllers\AdminController::class, 'storeVoucher'])->name('admin.vouchers.store');
        Route::put('/vouchers/{voucher}', [App\Http\Controllers\AdminController::class, 'updateVoucher'])->name('admin.vouchers.update');
        Route::delete('/vouchers/{voucher}', [App\Http\Controllers\AdminController::class, 'destroyVoucher'])->name('admin.vouchers.destroy');

        // Payment Methods
        Route::get('/payment-methods', [App\Http\Controllers\AdminController::class, 'paymentMethods'])->name('admin.payment_methods');
        Route::post('/payment-methods', [App\Http\Controllers\AdminController::class, 'storePaymentMethod'])->name('admin.payment_methods.store');
        Route::put('/payment-methods/{paymentMethod}', [App\Http\Controllers\AdminController::class, 'updatePaymentMethod'])->name('admin.payment_methods.update');
        Route::delete('/payment-methods/{paymentMethod}', [App\Http\Controllers\AdminController::class, 'destroyPaymentMethod'])->name('admin.payment_methods.destroy');

        // Categories
        Route::get('/categories', [App\Http\Controllers\AdminController::class, 'categories'])->name('admin.categories');
        Route::get('/categories/create', [App\Http\Controllers\AdminController::class, 'createCategory'])->name('admin.categories.create');
        Route::post('/categories', [App\Http\Controllers\AdminController::class, 'storeCategory'])->name('admin.categories.store');
        Route::get('/categories/{category}/edit', [App\Http\Controllers\AdminController::class, 'editCategory'])->name('admin.categories.edit');
        Route::put('/categories/{category}', [App\Http\Controllers\AdminController::class, 'updateCategory'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [App\Http\Controllers\AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');

        // Reports
        Route::get('/reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('admin.reports');
        Route::get('/reports/{report}', [App\Http\Controllers\AdminController::class, 'showReport'])->name('admin.reports.show');
        Route::post('/reports/{report}/update', [App\Http\Controllers\AdminController::class, 'updateReport'])->name('admin.reports.update');

        // Balances
        Route::get('/balances', [App\Http\Controllers\AdminController::class, 'balances'])->name('admin.balances');
        Route::get('/wallet-logs', [App\Http\Controllers\AdminController::class, 'walletLogs'])->name('admin.wallet_logs');
        // Settings
        Route::get('/settings', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [App\Http\Controllers\AdminController::class, 'updateSettings'])->name('admin.settings.update');

        // Ad Banners
        Route::get('/ad-banners', [App\Http\Controllers\AdminController::class, 'adBanners'])->name('admin.ad_banners');
        Route::post('/ad-banners', [App\Http\Controllers\AdminController::class, 'storeAdBanner'])->name('admin.ad_banners.store');
        Route::put('/ad-banners/{adBanner}', [App\Http\Controllers\AdminController::class, 'updateAdBanner'])->name('admin.ad_banners.update');
        Route::delete('/ad-banners/{adBanner}', [App\Http\Controllers\AdminController::class, 'destroyAdBanner'])->name('admin.ad_banners.destroy');

        // ── Dispute Management ────────────────────────────────────
        Route::get('/disputes', [App\Http\Controllers\AdminDisputeController::class, 'index'])->name('admin.disputes.index');
        Route::get('/disputes/{id}', [App\Http\Controllers\AdminDisputeController::class, 'show'])->name('admin.disputes.show');
        Route::post('/disputes/{id}/resolve', [App\Http\Controllers\AdminDisputeController::class, 'resolve'])->name('admin.disputes.resolve');
        Route::post('/disputes/{id}/force-refund', [App\Http\Controllers\AdminDisputeController::class, 'forceRefund'])->name('admin.disputes.forceRefund');
        Route::post('/disputes/{id}/reviewing', [App\Http\Controllers\AdminDisputeController::class, 'markReviewing'])->name('admin.disputes.reviewing');
        Route::post('/disputes/{id}/confirm-received', [App\Http\Controllers\AdminDisputeController::class, 'adminConfirmReceived'])->name('admin.disputes.confirmReceived');
        Route::get('/disputes/{id}/chat', [App\Http\Controllers\AdminDisputeController::class, 'viewChat'])->name('admin.disputes.chat');
        Route::post('/disputes/{id}/chat/send', [App\Http\Controllers\AdminDisputeController::class, 'sendAdminChat'])->name('admin.disputes.chat.send');
    });
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/ship', [TransactionController::class, 'shipOrder'])->name('transactions.ship');
    Route::post('/transactions/{transaction}/status', [TransactionController::class, 'updateOrderStatus'])->name('transactions.updateStatus');
    Route::post('/transactions/{transaction}/confirm', [TransactionController::class, 'confirmReceived'])->name('transactions.confirm');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    Route::post('/reviews/{transaction}', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reports', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');

    // Profile & Wishlist
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/wishlist', [ProfileController::class, 'wishlist'])->name('wishlist.index');
    Route::post('/wishlist/{product}/toggle', [ProfileController::class, 'toggleWishlist'])->name('wishlist.toggle');

    // Addresses
    Route::get('/addresses', [App\Http\Controllers\AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [App\Http\Controllers\AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [App\Http\Controllers\AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [App\Http\Controllers\AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [App\Http\Controllers\AddressController::class, 'setDefault'])->name('addresses.default');

    // Seller Dashboard
    Route::get('/seller/balance', [App\Http\Controllers\SellerController::class, 'balance'])->name('seller.balance');
    Route::post('/seller/withdraw', [App\Http\Controllers\SellerController::class, 'withdrawRequest'])->name('seller.withdraw');
    Route::get('/seller/transactions', [App\Http\Controllers\SellerController::class, 'transactions'])->name('seller.transactions');
});
