<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CartItemRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ComplaintMessageRepositoryInterface;
use App\Contracts\Repositories\ComplaintRepositoryInterface;
use App\Contracts\Repositories\EscrowRepositoryInterface;
use App\Contracts\Repositories\OperatingSystemRepositoryInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\RegionRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\ReviewResponseRepositoryInterface;
use App\Contracts\Repositories\SellerRepositoryInterface;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Contracts\Repositories\SystemConfigRepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawRepositoryInterface;
use App\Repositories\CartItemRepository;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ComplaintMessageRepository;
use App\Repositories\ComplaintRepository;
use App\Repositories\EscrowRepository;
use App\Repositories\OperatingSystemRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PlatformRepository;
use App\Repositories\ProductKeyRepository;
use App\Repositories\ProductListingRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantRepository;
use App\Repositories\RegionRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ReviewResponseRepository;
use App\Repositories\SellerRepository;
use App\Repositories\SettingRepository;
use App\Repositories\SystemConfigRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Repositories\WithdrawRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $bindings = [
            SystemConfigRepositoryInterface::class => SystemConfigRepository::class,
            ReviewRepositoryInterface::class => ReviewRepository::class,
            WalletRepositoryInterface::class => WalletRepository::class,
            EscrowRepositoryInterface::class => EscrowRepository::class,
            ProductKeyRepositoryInterface::class => ProductKeyRepository::class,
            RegionRepositoryInterface::class => RegionRepository::class,
            ComplaintRepositoryInterface::class => ComplaintRepository::class,
            WithdrawRepositoryInterface::class => WithdrawRepository::class,
            ProductVariantRepositoryInterface::class => ProductVariantRepository::class,
            UserRepositoryInterface::class => UserRepository::class,
            CategoryRepositoryInterface::class => CategoryRepository::class,
            OrderRepositoryInterface::class => OrderRepository::class,
            PlatformRepositoryInterface::class => PlatformRepository::class,
            SettingRepositoryInterface::class => SettingRepository::class,
            OperatingSystemRepositoryInterface::class => OperatingSystemRepository::class,
            CartRepositoryInterface::class => CartRepository::class,
            CartItemRepositoryInterface::class => CartItemRepository::class,
            ReviewResponseRepositoryInterface::class => ReviewResponseRepository::class,
            ProductListingRepositoryInterface::class => ProductListingRepository::class,
            TransactionRepositoryInterface::class => TransactionRepository::class,
            ComplaintMessageRepositoryInterface::class => ComplaintMessageRepository::class,
            ProductRepositoryInterface::class => ProductRepository::class,
            OrderItemRepositoryInterface::class => OrderItemRepository::class,
            UserProfileRepositoryInterface::class => UserProfileRepository::class,
            SellerRepositoryInterface::class => SellerRepository::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
