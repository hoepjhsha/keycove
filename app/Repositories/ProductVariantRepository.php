<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductVariantRepository extends Repository implements ProductVariantRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ProductVariant);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleForSeller(int $sellerId): EloquentCollection
    {
        return $this->applyAccessibleForSeller($this->newQuery(), $sellerId)
            ->select(['id', 'product_id', 'region_id', 'platform_id', 'os_id', 'edition', 'status'])
            ->with([
                'product:id,name,submitted_by_seller_id,status',
                'region:id,name',
                'platform:id,name',
                'operatingSystem:id,name',
            ])
            ->orderBy('product_id')
            ->orderBy('region_id')
            ->orderBy('platform_id')
            ->orderBy('os_id')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminTableQuery(int $productId): Builder
    {
        return $this->newQuery()
            ->where('product_id', $productId)
            ->with(['region', 'platform', 'operatingSystem'])
            ->withCount(['listings' => function ($query) {
                $query->where('status', '!=', ProductListingStatus::Deleted);
            }]);
    }

    /**
     * {@inheritDoc}
     */
    public function getForAdminProductDetail(int $productId, bool $withTrashed = false): EloquentCollection
    {
        $query = $this->newQuery()
            ->where('product_id', $productId)
            ->with(['region', 'platform', 'operatingSystem'])
            ->withCount(['listings' => function (Builder $query): void {
                $query->where('status', '!=', ProductListingStatus::Deleted->value);
            }]);

        if ($withTrashed) {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminDetailOrFail(int $variantId, bool $withTrashed = false): ProductVariant
    {
        $query = $this->newQuery();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($variantId);
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedBySellerOrFail(int $sellerId, int $variantId, bool $withTrashed = false): ProductVariant
    {
        $query = $this->newQuery()->whereHas('product', function (Builder $builder) use ($sellerId): void {
            $builder->withTrashed()->where('submitted_by_seller_id', $sellerId);
        });

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($variantId);
    }

    /**
     * {@inheritDoc}
     */
    public function findAccessibleForSellerOrFail(int $sellerId, int $variantId): ProductVariant
    {
        return $this->applyAccessibleForSeller($this->newQuery(), $sellerId)
            ->with(['product'])
            ->whereKey($variantId)
            ->firstOrFail();
    }

    /**
     * {@inheritDoc}
     */
    public function countOwnedBySeller(int $sellerId): int
    {
        return $this->newQuery()
            ->whereHas('product', function (Builder $builder) use ($sellerId): void {
                $builder->where('submitted_by_seller_id', $sellerId);
            })
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function duplicateExists(
        int $productId,
        int $regionId,
        int $platformId,
        int $osId,
        ?string $edition,
        ?int $ignoreVariantId = null,
    ): bool {
        return $this->newQuery()
            ->where('product_id', $productId)
            ->where('region_id', $regionId)
            ->where('platform_id', $platformId)
            ->where('os_id', $osId)
            ->where(function (Builder $query) use ($edition): void {
                if ($edition === null) {
                    $query->whereNull('edition');

                    return;
                }

                $query->where('edition', $edition);
            })
            ->when(
                $ignoreVariantId !== null,
                fn (Builder $query): Builder => $query->whereKeyNot($ignoreVariantId),
            )
            ->exists();
    }

    private function applyAccessibleForSeller(Builder $query, int $sellerId): Builder
    {
        return $query
            ->withoutTrashed()
            ->where('status', '!=', ProductVariantStatus::Deleted->value)
            ->whereHas('product', function (Builder $builder) use ($sellerId): void {
                $builder->where(function (Builder $productQuery) use ($sellerId): void {
                    $productQuery
                        ->where(function (Builder $adminQuery): void {
                            $adminQuery->whereNull('submitted_by_seller_id')
                                ->where('status', GeneralStatus::Active);
                        })
                        ->orWhere(function (Builder $sellerQuery) use ($sellerId): void {
                            $sellerQuery->where('submitted_by_seller_id', $sellerId)
                                ->where('status', '!=', GeneralStatus::Deleted->value);
                        });
                });
            });
    }
}
