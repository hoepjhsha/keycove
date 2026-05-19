<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * @return EloquentCollection<int, Product>
     */
    public function getSellerProductsWithVariants(int $sellerId): EloquentCollection;

    /**
     * @return EloquentCollection<int, Product>
     */
    public function getAccessibleForSeller(int $sellerId): EloquentCollection;

    public function findForAdminDetailOrFail(int $productId): Product;

    public function findOwnedBySellerOrFail(int $sellerId, int $productId, bool $withTrashed = false): Product;

    public function findAccessibleForSellerOrFail(int $sellerId, int $productId): Product;

    public function countOwnedBySeller(int $sellerId, ?GeneralStatus $status = null): int;

    public function slugExists(string $slug, ?int $ignoreProductId = null): bool;
}
