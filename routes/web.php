<?php

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Http\Controllers\CartCheckoutController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\PaymentController;
use App\Livewire\Shop\Checkout\CheckoutReview;
use App\Livewire\Shop\Home;
use App\Livewire\Shop\Product\ProductIndex;
use App\Livewire\Shop\Product\ProductShow;
use App\Livewire\Shop\Seller\Apply;
use App\Livewire\Shop\SellerBrowse\SellerIndex;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

require_once __DIR__.'/auth.php';
require_once __DIR__.'/admin.php';

Route::prefix('payment')->group(function () {
    Route::get('vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
    Route::get('vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payment.vnpay.ipn')->withoutMiddleware([VerifyCsrfToken::class]);
});

Route::livewire('/', Home::class)->name('app.shop.index');

Route::livewire('/products', ProductIndex::class)->name('app.products.index');

Route::livewire('/products/{product:slug}/{listing:slug}', ProductShow::class)
    ->scopeBindings()
    ->name('app.products.show');

Route::livewire('/sellers', SellerIndex::class)->name('app.sellers.index');

Route::middleware('auth')->group(function () {
    Route::livewire('/checkout', CheckoutReview::class)->name('app.checkout.review');
    Route::post('/cart/checkout', CartCheckoutController::class)->name('app.cart.checkout');
    Route::redirect('/orders', '/my-library')->name('app.orders.index');
    Route::get('/orders/{order:order_code}', fn () => redirect()->route('app.library.show'))->name('app.orders.show');
});

Route::middleware('auth')
    ->prefix('cart/items')
    ->name('app.cart.items.')
    ->group(function () {
        Route::patch('/{cartItem}', [CartItemController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartItemController::class, 'destroy'])->name('destroy');
    });

Route::livewire('/seller/apply', Apply::class)
    ->middleware(['auth', 'seller.email.verified'])
    ->name('seller.apply');

Route::middleware(['auth', 'seller.email.verified'])
    ->get('/seller/dashboard', function () {
        $user = auth()->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->seller === null || $user->role !== UserRole::Seller || $user->seller->kyc_status !== KycStatus::Approved) {
            return redirect()
                ->route('seller.apply')
                ->with('seller-status', 'Complete and submit your seller application first.');
        }

        return view('pages.landing.seller-dashboard');
    })
    ->name('seller.dashboard.index');

Route::get('/sample', function () {
    return view('sample');
});
