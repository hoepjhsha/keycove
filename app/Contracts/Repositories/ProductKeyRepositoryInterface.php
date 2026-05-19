<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\ProductKey;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface ProductKeyRepositoryInterface extends RepositoryInterface
{
    public function countAvailableForSeller(int $sellerId): int;

    public function countAvailableForListing(int $listingId): int;

    /**
     * @return EloquentCollection<int, ProductKey>
     */
    public function getListingKeys(int $listingId): EloquentCollection;

    /**
     * @return EloquentCollection<int, ProductKey>
     */
    public function getForAdminListing(int $listingId): EloquentCollection;

    public function findOwnedBySellerOrFail(int $sellerId, int $keyId): ProductKey;

    public function existsDuplicateForListing(int $listingId, string $keyHash): bool;
}
