<?php

use App\Http\Controllers\Admin\Auth\Logout;
use App\Http\Controllers\Admin\SellerKycImageController;
use App\Livewire\Admin\Action\Category\CategoryIndex;
use App\Livewire\Admin\Action\Complaint\ComplaintIndex;
use App\Livewire\Admin\Action\Escrow\EscrowIndex;
use App\Livewire\Admin\Action\InternalWallet\InternalWalletIndex;
use App\Livewire\Admin\Action\OperatingSystem\OperatingSystemIndex;
use App\Livewire\Admin\Action\Order\OrderDetail;
use App\Livewire\Admin\Action\Order\OrderIndex;
use App\Livewire\Admin\Action\Platform\PlatformIndex;
use App\Livewire\Admin\Action\Product\ProductDetail;
use App\Livewire\Admin\Action\Product\ProductIndex;
use App\Livewire\Admin\Action\ProductKey\BulkImportIndex;
use App\Livewire\Admin\Action\Region\RegionIndex;
use App\Livewire\Admin\Action\SellerKyc\SellerKycIndex;
use App\Livewire\Admin\Action\SystemConfig\SystemConfigIndex;
use App\Livewire\Admin\Action\Transaction\TransactionIndex;
use App\Livewire\Admin\Action\User\UserIndex;
use App\Livewire\Admin\Action\Withdraw\WithdrawalRequestIndex;
use App\Livewire\Admin\Auth\Action\Login;
use App\Livewire\Shop\Complaint\Thread as ComplaintThread;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')
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

        Route::prefix('/system-settings')
            ->name('system_settings.')
            ->group(function () {
                Route::get('/', SystemConfigIndex::class)->name('index');
            });

        Route::prefix('/categories')
            ->name('categories.')
            ->group(function () {
                Route::get('/', CategoryIndex::class)->name('index');
            });

        Route::prefix('/products')
            ->name('products.')
            ->group(function () {
                Route::get('/', ProductIndex::class)->name('index');
                Route::get('/{id}', ProductDetail::class)->name('detail');
                Route::get('/keys/import', BulkImportIndex::class)->name('keys.import');
            });

        Route::prefix('/seller-verifications')
            ->name('seller_verifications.')
            ->group(function () {
                Route::get('/', SellerKycIndex::class)->name('index');

                Route::get('/{seller}/image/{type}', SellerKycImageController::class)->name('image');
            });

        Route::prefix('/attributes')
            ->name('attributes.')
            ->group(function () {
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
            });

        Route::prefix('/users')
            ->name('users.')
            ->group(function () {
                Route::get('/', UserIndex::class)->name('index');
            });

        Route::prefix('/transactions')
            ->name('transactions.')
            ->group(function () {
                Route::get('/', TransactionIndex::class)->name('index');
            });

        Route::prefix('/internal-wallet')
            ->name('internal_wallet.')
            ->group(function () {
                Route::get('/', InternalWalletIndex::class)->name('index');
            });

        Route::prefix('/withdrawal-requests')
            ->name('withdrawals.')
            ->group(function () {
                Route::get('/', WithdrawalRequestIndex::class)->name('index');
            });

        Route::prefix('/orders')
            ->name('orders.')
            ->group(function () {
                Route::get('/', OrderIndex::class)->name('index');
                Route::get('/{id}', OrderDetail::class)->name('detail');
            });

        Route::prefix('/complaints')
            ->name('complaints.')
            ->group(function () {
                Route::get('/', ComplaintIndex::class)->name('index');
                Route::livewire('/{complaint}', ComplaintThread::class)->name('show');
            });

        Route::prefix('/escrows')
            ->name('escrows.')
            ->group(function () {
                Route::get('/', EscrowIndex::class)->name('index');
            });
    });
