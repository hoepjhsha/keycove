<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.landing.shop');
});

Route::get('/admin/dashboard', function () {
    return view('pages.landing.admin-dashboard');
});

Route::get('/seller/dashboard', function () {
    return view('pages.landing.seller-dashboard');
});

Route::get('/sample', function () {
    return view('sample');
});
