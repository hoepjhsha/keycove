<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Enums\ProductListingStatus;
use App\Models\ProductListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface ProductListingRepositoryInterface extends RepositoryInterface
{
    public function getSellerTableQuery(int $sellerId): Builder;

    public function getAdminTableQuery(int $variantId): Builder;

    /**
     * @return EloquentCollection<int, ProductListing>
     */
    public function getForAdminProductDetail(int $productId, bool $withTrashed = false): EloquentCollection;

    /**
     * @return EloquentCollection<int, ProductListing>
     */
    public function getForAdminVariantDetail(int $variantId, bool $withTrashed = false): EloquentCollection;

    public function countOwnedBySeller(int $sellerId, ?ProductListingStatus $status = null): int;

    public function findForAdminDetailOrFail(int $listingId, bool $withTrashed = false): ProductListing;

    public function findOwnedBySellerOrFail(int $sellerId, int $listingId, bool $withTrashed = false): ProductListing;

    public function findOwnedBySellerForView(int $sellerId, int $listingId): ?ProductListing;
}
