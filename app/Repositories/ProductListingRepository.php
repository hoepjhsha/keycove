<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Models\ProductListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductListingRepository extends Repository implements ProductListingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ProductListing);
    }

    /**
     * {@inheritDoc}
     */
    public function getSellerTableQuery(int $sellerId): Builder
    {
        return $this->newQuery()
            ->select('product_listings.*')
            ->withTrashed()
            ->where('seller_id', $sellerId)
            ->with([
                'variant.product.submittedBySeller.user',
                'variant.region',
                'variant.platform',
                'variant.operatingSystem',
            ])
            ->withCount([
                'keys as available_keys_count' => function (Builder $query): void {
                    $query->where('status', ProductKeyStatus::Available->value);
                },
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminTableQuery(int $variantId): Builder
    {
        return $this->newQuery()
            ->where('variant_id', $variantId)
            ->with(['seller.user'])
            ->withCount(['keys' => function ($query) {
                $query->where('status', ProductKeyStatus::Available->value);
            }]);
    }

    /**
     * {@inheritDoc}
     */
    public function getForAdminProductDetail(int $productId, bool $withTrashed = false): EloquentCollection
    {
        return $this->buildAdminDetailQuery($withTrashed)
            ->whereHas('variant', function (Builder $query) use ($productId): void {
                $query->where('product_id', $productId);
            })
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getForAdminVariantDetail(int $variantId, bool $withTrashed = false): EloquentCollection
    {
        return $this->buildAdminDetailQuery($withTrashed)
            ->where('variant_id', $variantId)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function countOwnedBySeller(int $sellerId, ?ProductListingStatus $status = null): int
    {
        $query = $this->newQuery()
            ->where('seller_id', $sellerId)
            ->withoutTrashed();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminDetailOrFail(int $listingId, bool $withTrashed = false): ProductListing
    {
        $query = $this->newQuery();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($listingId);
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedBySellerOrFail(int $sellerId, int $listingId, bool $withTrashed = false): ProductListing
    {
        $query = $this->newQuery()->where('seller_id', $sellerId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($listingId);
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedBySellerForView(int $sellerId, int $listingId): ?ProductListing
    {
        return $this->newQuery()
            ->where('seller_id', $sellerId)
            ->with(['variant.product'])
            ->find($listingId);
    }

    private function buildAdminDetailQuery(bool $withTrashed): Builder
    {
        $query = $this->newQuery()
            ->with(['seller.user', 'variant'])
            ->withCount(['keys' => function (Builder $query): void {
                $query->where('status', ProductKeyStatus::Available->value);
            }]);

        if ($withTrashed) {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        return $query;
    }
}
