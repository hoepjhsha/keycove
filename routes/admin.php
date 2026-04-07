<?php

use App\Http\Controllers\Admin\Auth\Logout;
use App\Livewire\Admin\Auth\Action\Login;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->prefix('/admin')
    ->name('admin.auth.')
    ->group(function () {
        Route::get('/login', Login::class)->name('login');
    });

Route::middleware('auth')
    ->prefix('/admin/auth')
    ->name('admin.auth.')
    ->group(function () {
        Route::get('/logout', [Logout::class, 'logout'])->name('logout');
    });
