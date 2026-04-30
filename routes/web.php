<?php

use App\Http\Controllers\CartCheckoutController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\PaymentController;
use App\Livewire\Shop\Checkout\CheckoutReview;
use App\Livewire\Shop\Complaint\Thread as ComplaintThread;
use App\Livewire\Shop\Home;
use App\Livewire\Shop\Product\ProductIndex;
use App\Livewire\Shop\Product\ProductShow;
use App\Livewire\Shop\Seller\Apply;
use App\Livewire\Shop\Seller\ComplaintIndex as SellerComplaintIndex;
use App\Livewire\Shop\Seller\Dashboard;
use App\Livewire\Shop\Seller\SellerListings;
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
    Route::livewire('/my-library/complaints/{complaint}', ComplaintThread::class)->name('app.library.complaints.show');
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

Route::livewire('/seller/dashboard', Dashboard::class)
    ->middleware(['auth', 'seller.email.verified', 'seller.portal.approved'])
    ->name('seller.dashboard.index');

Route::livewire('/seller/complaints', SellerComplaintIndex::class)
    ->middleware(['auth', 'seller.email.verified', 'seller.portal.approved'])
    ->name('seller.complaints.index');

Route::livewire('/seller/complaints/{complaint}', ComplaintThread::class)
    ->middleware(['auth', 'seller.email.verified', 'seller.portal.approved'])
    ->name('seller.complaints.show');

Route::livewire('/seller/listings', SellerListings::class)
    ->middleware(['auth', 'seller.email.verified', 'seller.portal.approved'])
    ->name('seller.listings.index');

Route::get('/sample', function () {
    return view('sample');
});
