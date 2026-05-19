<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductVariantService
{
    public function __construct(
        private ProductVariantRepositoryInterface $variants,
    ) {}

    public function defaultStatusForProduct(Product $product): ProductVariantStatus
    {
        return $product->status === GeneralStatus::Active
            ? ProductVariantStatus::Active
            : ProductVariantStatus::Draft;
    }

    public function editableStatusForSeller(Product $product, ProductVariant $variant): ProductVariantStatus
    {
        if (
            $product->status === GeneralStatus::Active
            && in_array($variant->status, [ProductVariantStatus::Active, ProductVariantStatus::Hidden], true)
        ) {
            return $variant->status;
        }

        return ProductVariantStatus::Draft;
    }

    public function restoredStatusForProduct(Product $product): ProductVariantStatus
    {
        return $product->status === GeneralStatus::Active
            ? ProductVariantStatus::Hidden
            : ProductVariantStatus::Draft;
    }

    /**
     * @param  array{product_id: int, region_id: int, platform_id: int, os_id: int, edition?: ?string, status: int}  $data
     */
    public function create(array $data, string $duplicateErrorKey = 'general'): ProductVariant
    {
        $edition = $this->normalizeEdition($data['edition'] ?? null);

        $this->ensureUniqueVariant(
            productId: $data['product_id'],
            regionId: $data['region_id'],
            platformId: $data['platform_id'],
            osId: $data['os_id'],
            edition: $edition,
            ignoreVariantId: null,
            errorKey: $duplicateErrorKey,
        );

        /** @var ProductVariant $variant */
        $variant = $this->variants->create([
            'product_id'  => $data['product_id'],
            'region_id'   => $data['region_id'],
            'platform_id' => $data['platform_id'],
            'os_id'       => $data['os_id'],
            'edition'     => $edition,
            'status'      => $data['status'],
        ]);

        return $variant;
    }

    /**
     * @param  array{product_id: int, region_id: int, platform_id: int, os_id: int, edition?: ?string, status: int}  $data
     */
    public function update(
        ProductVariant $variant,
        array $data,
        string $duplicateErrorKey = 'general',
        string $statusErrorKey = 'status',
    ): bool {
        $status = ProductVariantStatus::from((int) $data['status']);

        if ($status === ProductVariantStatus::Deleted) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_update'),
            ]);
        }

        $edition = $this->normalizeEdition($data['edition'] ?? null);

        $this->ensureUniqueVariant(
            productId: $data['product_id'],
            regionId: $data['region_id'],
            platformId: $data['platform_id'],
            osId: $data['os_id'],
            edition: $edition,
            ignoreVariantId: $variant->id,
            errorKey: $duplicateErrorKey,
        );

        return $variant->update([
            'region_id'   => $data['region_id'],
            'platform_id' => $data['platform_id'],
            'os_id'       => $data['os_id'],
            'edition'     => $edition,
            'status'      => $status,
        ]);
    }

    public function bulkChangeStatus(array $ids, int $status, string $statusErrorKey = 'status'): bool
    {
        if (! in_array($status, array_column(ProductVariantStatus::cases(), 'value'), true)) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.invalid_status'),
            ]);
        }

        $hasActiveListings = ProductVariant::query()
            ->whereIn('id', $ids)
            ->where('status', ProductVariantStatus::Active)
            ->whereHas('listings', function ($query): void {
                $query->where('status', '!=', ProductListingStatus::Deleted);
            })
            ->exists();

        if ($hasActiveListings && $status === ProductVariantStatus::Discontinued->value) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.variant_discontinue_active_listings'),
            ]);
        }

        return (bool) ProductVariant::query()->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function delete(
        ProductVariant $variant,
        bool $blockWhenHasNonDeletedListings = false,
        string $listingErrorKey = 'general',
    ): bool {
        $hasBlockedListings = $blockWhenHasNonDeletedListings
            ? $variant->listings()->where('status', '!=', ProductListingStatus::Deleted->value)->exists()
            : $variant->listings()->where('status', ProductListingStatus::Active->value)->exists();

        if ($hasBlockedListings) {
            throw ValidationException::withMessages([
                $listingErrorKey => $blockWhenHasNonDeletedListings
                    ? 'Không thể xóa variant có listing chưa bị xóa.'
                    : __('admin.validation.variant_delete_active_listings'),
            ]);
        }

        return DB::transaction(function () use ($variant): bool {
            $variant->status = ProductVariantStatus::Deleted;
            $variant->save();

            return $variant->delete();
        });
    }

    public function restore(
        ProductVariant $variant,
        ProductVariantStatus $restoredStatus = ProductVariantStatus::Draft,
    ): bool {
        return DB::transaction(function () use ($variant, $restoredStatus): bool {
            $variant->restore();
            $variant->status = $restoredStatus;
            $variant->save();

            return true;
        });
    }

    public function toggleVisibility(ProductVariant $variant): bool
    {
        $variant->loadMissing('product');

        if ($variant->product?->status !== GeneralStatus::Active) {
            return false;
        }

        if (! in_array($variant->status, [ProductVariantStatus::Active, ProductVariantStatus::Hidden], true)) {
            return false;
        }

        $variant->status = match ($variant->status) {
            ProductVariantStatus::Active => ProductVariantStatus::Hidden,
            ProductVariantStatus::Hidden => ProductVariantStatus::Active,
            default                      => $variant->status,
        };

        $variant->save();

        return true;
    }

    public function toggleAdminStatus(ProductVariant $variant): bool
    {
        $variant->status = match ($variant->status) {
            ProductVariantStatus::Draft  => ProductVariantStatus::Active,
            ProductVariantStatus::Active => ProductVariantStatus::Hidden,
            ProductVariantStatus::Hidden => ProductVariantStatus::Draft,
            default                      => ProductVariantStatus::Draft,
        };

        return $variant->save();
    }

    public function bulkDelete(array $ids, bool $blockWhenHasNonDeletedListings = false): void
    {
        DB::transaction(function () use ($ids, $blockWhenHasNonDeletedListings): void {
            $variants = ProductVariant::query()->whereIn('id', $ids)->get();

            foreach ($variants as $variant) {
                $hasBlockedListings = $blockWhenHasNonDeletedListings
                    ? $variant->listings()->where('status', '!=', ProductListingStatus::Deleted->value)->exists()
                    : $variant->listings()->where('status', ProductListingStatus::Active->value)->exists();

                if ($hasBlockedListings) {
                    continue;
                }

                $variant->status = ProductVariantStatus::Deleted;
                $variant->save();
                $variant->delete();
            }
        });
    }

    private function ensureUniqueVariant(
        int $productId,
        int $regionId,
        int $platformId,
        int $osId,
        ?string $edition,
        ?int $ignoreVariantId,
        string $errorKey,
    ): void {
        if (! $this->variants->duplicateExists($productId, $regionId, $platformId, $osId, $edition, $ignoreVariantId)) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => __('admin.validation.variant_duplicate'),
        ]);
    }

    private function normalizeEdition(?string $edition): ?string
    {
        if ($edition === null) {
            return null;
        }

        $normalizedEdition = trim($edition);

        return $normalizedEdition === '' ? null : $normalizedEdition;
    }
}
