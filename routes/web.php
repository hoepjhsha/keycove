<?php

use Illuminate\Support\Facades\Route;

require_once __DIR__.'/auth.php';
require_once __DIR__.'/admin.php';

Route::get('/', function () {
    return view('pages.landing.shop');
})->name('app.shop.index');

Route::get('/seller/dashboard', function () {
    return view('pages.landing.seller-dashboard');
})->name('seller.dashboard.index');

Route::get('/sample', function () {
    return view('sample');
});
