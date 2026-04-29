<?php

use App\Http\Controllers\CartItemController;
use App\Http\Controllers\PaymentController;
use App\Livewire\Shop\Home;
use App\Livewire\Shop\Product\ProductIndex;
use App\Livewire\Shop\Product\ProductShow;
use App\Livewire\Shop\SellerBrowse\SellerIndex;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

require_once __DIR__.'/auth.php';
require_once __DIR__.'/admin.php';

Route::prefix('payment')->group(function () {
    Route::get('pay', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::get('vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
    Route::get('vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payment.vnpay.ipn')->withoutMiddleware([VerifyCsrfToken::class]);
});

Route::livewire('/', Home::class)->name('app.shop.index');

Route::livewire('/products', ProductIndex::class)->name('app.products.index');

Route::livewire('/products/{product:slug}/{listing:slug}', ProductShow::class)
    ->scopeBindings()
    ->name('app.products.show');

Route::livewire('/sellers', SellerIndex::class)->name('app.sellers.index');

Route::middleware('auth')
    ->prefix('cart/items')
    ->name('app.cart.items.')
    ->group(function () {
        Route::patch('/{cartItem}', [CartItemController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartItemController::class, 'destroy'])->name('destroy');
    });

Route::get('/seller/dashboard', function () {
    return view('pages.landing.seller-dashboard');
})->name('seller.dashboard.index');

Route::get('/sample', function () {
    return view('sample');
});
