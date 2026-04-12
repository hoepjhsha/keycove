<?php

use App\Http\Controllers\Admin\Auth\Logout;
use App\Livewire\Admin\Action\Category\CategoryIndex;
use App\Livewire\Admin\Action\OperatingSystem\OperatingSystemIndex;
use App\Livewire\Admin\Action\Platform\PlatformIndex;
use App\Livewire\Admin\Action\Region\RegionIndex;
use App\Livewire\Admin\Action\User\UserIndex;
use App\Livewire\Admin\Auth\Action\Login;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->prefix('/admin/auth')
    ->name('admin.auth.')
    ->group(function () {
        Route::get('/login', Login::class)->name('login');
    });

Route::middleware('auth:admin')
    ->prefix('/admin/auth')
    ->name('admin.auth.')
    ->group(function () {
        Route::get('/logout', [Logout::class, 'logout'])->name('logout');
    });

Route::middleware('auth:admin')
    ->prefix('/admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('pages.landing.admin-dashboard');
        })->name('dashboard.index');

        Route::prefix('/categories')
            ->name('categories.')
            ->group(function () {
                Route::get('/', CategoryIndex::class)->name('index');
            });

        Route::prefix('/regions')
            ->name('regions.')
            ->group(function () {
                Route::get('/', RegionIndex::class)->name('index');
            });

        Route::prefix('/platforms')
            ->name('platforms.')
            ->group(function () {
                Route::get('/', PlatformIndex::class)->name('index');
            });

        Route::prefix('/operating-systems')
            ->name('operating_systems.')
            ->group(function () {
                Route::get('/', OperatingSystemIndex::class)->name('index');
            });

        Route::prefix('/users')
            ->name('users.')
            ->group(function () {
                Route::get('/', UserIndex::class)->name('index');
            });
    });
