<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface ProductVariantRepositoryInterface extends RepositoryInterface
{
    /**
     * @return EloquentCollection<int, ProductVariant>
     */
    public function getAccessibleForSeller(int $sellerId): EloquentCollection;

    public function getAdminTableQuery(int $productId): Builder;

    /**
     * @return EloquentCollection<int, ProductVariant>
     */
    public function getForAdminProductDetail(int $productId, bool $withTrashed = false): EloquentCollection;

    public function findForAdminDetailOrFail(int $variantId, bool $withTrashed = false): ProductVariant;

    public function findOwnedBySellerOrFail(int $sellerId, int $variantId, bool $withTrashed = false): ProductVariant;

    public function findAccessibleForSellerOrFail(int $sellerId, int $variantId): ProductVariant;

    public function countOwnedBySeller(int $sellerId): int;

    public function duplicateExists(
        int $productId,
        int $regionId,
        int $platformId,
        int $osId,
        ?string $edition,
        ?int $ignoreVariantId = null,
    ): bool;
}
