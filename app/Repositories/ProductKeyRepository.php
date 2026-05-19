<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductKeyRepository extends Repository implements ProductKeyRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ProductKey);
    }

    /**
     * {@inheritDoc}
     */
    public function countAvailableForSeller(int $sellerId): int
    {
        return $this->newQuery()
            ->where('status', ProductKeyStatus::Available->value)
            ->whereHas('listing', function (Builder $query) use ($sellerId): void {
                $query->where('seller_id', $sellerId);
            })
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function countAvailableForListing(int $listingId): int
    {
        return $this->newQuery()
            ->where('listing_id', $listingId)
            ->where('status', ProductKeyStatus::Available->value)
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getListingKeys(int $listingId): EloquentCollection
    {
        return $this->newQuery()
            ->where('listing_id', $listingId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getForAdminListing(int $listingId): EloquentCollection
    {
        return $this->getListingKeys($listingId);
    }

    /**
     * {@inheritDoc}
     */
    public function findOwnedBySellerOrFail(int $sellerId, int $keyId): ProductKey
    {
        return $this->newQuery()
            ->whereKey($keyId)
            ->whereHas('listing', function (Builder $query) use ($sellerId): void {
                $query->where('seller_id', $sellerId);
            })
            ->firstOrFail();
    }

    /**
     * {@inheritDoc}
     */
    public function existsDuplicateForListing(int $listingId, string $keyHash): bool
    {
        return $this->newQuery()
            ->where('listing_id', $listingId)
            ->where('key_hash', $keyHash)
            ->exists();
    }
}
