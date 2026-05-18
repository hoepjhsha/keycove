<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Product);
    }

    /**
     * {@inheritDoc}
     */
    public function getSellerProductsWithVariants(int $sellerId): EloquentCollection
    {
        return $this->newQuery()
            ->withTrashed()
            ->where('submitted_by_seller_id', $sellerId)
            ->with([
                'variants' => function ($query): void {
                    $query
                        ->withTrashed()
                        ->with([
                            'region:id,name',
                            'platform:id,name',
                            'operatingSystem:id,name',
                        ])
                        ->withCount('listings')
                        ->orderBy('region_id')
                        ->orderBy('platform_id')
                        ->orderBy('os_id');
                },
            ])
            ->withCount(['variants', 'listings'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleForSeller(int $sellerId): EloquentCollection
    {
        return $this->applyAccessibleForSeller($this->newQuery(), $sellerId)
            ->select(['id', 'name', 'submitted_by_seller_id', 'status'])
            ->orderBy('name')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminDetailOrFail(int $productId): Product
    {
        /** @var Product $product */
        $product = $this->newQuery()
            ->with(['categories', 'submittedBySeller.user'])
            ->findOrFail($productId);

        return $product;
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedBySellerOrFail(int $sellerId, int $productId, bool $withTrashed = false): Product
    {
        $query = $this->newQuery()->where('submitted_by_seller_id', $sellerId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($productId);
    }

    /**
     * {@inheritDoc}
     */
    public function findAccessibleForSellerOrFail(int $sellerId, int $productId): Product
    {
        return $this->applyAccessibleForSeller($this->newQuery(), $sellerId)
            ->whereKey($productId)
            ->firstOrFail();
    }

    /**
     * {@inheritDoc}
     */
    public function countOwnedBySeller(int $sellerId, ?GeneralStatus $status = null): int
    {
        $query = $this->newQuery()
            ->where('submitted_by_seller_id', $sellerId)
            ->withoutTrashed();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    /**
     * {@inheritDoc}
     */
    public function slugExists(string $slug, ?int $ignoreProductId = null): bool
    {
        return $this->newQuery()
            ->where('slug', $slug)
            ->when(
                $ignoreProductId !== null,
                fn ($query) => $query->whereKeyNot($ignoreProductId),
            )
            ->exists();
    }

    private function applyAccessibleForSeller(Builder $query, int $sellerId): Builder
    {
        return $query
            ->withoutTrashed()
            ->where(function (Builder $builder) use ($sellerId): void {
                $builder
                    ->where(function (Builder $adminQuery): void {
                        $adminQuery->whereNull('submitted_by_seller_id')
                            ->where('status', GeneralStatus::Active);
                    })
                    ->orWhere(function (Builder $sellerQuery) use ($sellerId): void {
                        $sellerQuery->where('submitted_by_seller_id', $sellerId)
                            ->where('status', '!=', GeneralStatus::Deleted->value);
                    });
            });
    }
}
