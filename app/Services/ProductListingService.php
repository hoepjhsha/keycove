<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Contracts\Repositories\ProductListingRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductListingService
{
    public function __construct(
        private ProductListingRepositoryInterface $listings,
        private ProductKeyRepositoryInterface $keys,
    ) {}

    public function defaultStatusForProduct(Product|ProductVariant $model): ProductListingStatus
    {
        $productStatus = $model instanceof ProductVariant
            ? $model->product?->status
            : $model->status;

        return $productStatus === GeneralStatus::Active
            ? ProductListingStatus::Active
            : ProductListingStatus::Draft;
    }

    public function restoredStatusForProduct(Product|ProductVariant $model): ProductListingStatus
    {
        $productStatus = $model instanceof ProductVariant
            ? $model->product?->status
            : $model->status;

        return $productStatus === GeneralStatus::Active
            ? ProductListingStatus::Hidden
            : ProductListingStatus::Draft;
    }

    /**
     * @param  array{variant_id: int, seller_id?: ?int, display_name?: ?string, price: float|int|string, status: int}  $data
     */
    public function create(array $data, string $statusErrorKey = 'status'): ProductListing
    {
        $status = ProductListingStatus::from((int) $data['status']);

        if ($status === ProductListingStatus::Deleted) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_create'),
            ]);
        }

        /** @var ProductListing $listing */
        $listing = $this->listings->create([
            'variant_id'   => $data['variant_id'],
            'seller_id'    => $data['seller_id'] ?? null,
            'display_name' => $this->normalizeDisplayName($data['display_name'] ?? null),
            'price'        => $data['price'],
            'status'       => $status,
        ]);

        return $listing;
    }

    /**
     * @param  array{variant_id?: int, seller_id?: ?int, display_name?: ?string, price: float|int|string, status: int}  $data
     */
    public function update(ProductListing $listing, array $data): bool
    {
        return $listing->update([
            'display_name' => $this->normalizeDisplayName($data['display_name'] ?? null),
            'seller_id'    => $data['seller_id'] ?? null,
            'price'        => $data['price'],
            'status'       => ProductListingStatus::from((int) $data['status']),
        ]);
    }

    public function bulkChangeStatus(array $ids, int $status, string $statusErrorKey = 'status'): bool
    {
        if (! in_array($status, array_column(ProductListingStatus::cases(), 'value'), true)) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.invalid_status'),
            ]);
        }

        $hasSoldKeys = ProductListing::query()
            ->whereIn('id', $ids)
            ->whereHas('keys', function ($query): void {
                $query->where('status', ProductKeyStatus::Sold->value);
            });

        if (
            $hasSoldKeys->exists()
            && in_array($status, [ProductListingStatus::Closed->value, ProductListingStatus::Deleted->value], true)
        ) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.listing_cannot_close_sold_keys'),
            ]);
        }

        return (bool) ProductListing::query()->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function delete(ProductListing $listing, string $listingErrorKey = 'general'): bool
    {
        if ($listing->keys()->where('status', ProductKeyStatus::Sold->value)->exists()) {
            throw ValidationException::withMessages([
                $listingErrorKey => __('admin.validation.listing_delete_sold_keys'),
            ]);
        }

        return DB::transaction(function () use ($listing): bool {
            $listing->status = ProductListingStatus::Deleted;
            $listing->save();

            return $listing->delete();
        });
    }

    public function restore(
        ProductListing $listing,
        ProductListingStatus $restoredStatus = ProductListingStatus::Draft,
    ): bool {
        return DB::transaction(function () use ($listing, $restoredStatus): bool {
            $listing->restore();
            $listing->status = $restoredStatus;
            $listing->save();

            return true;
        });
    }

    public function toggleVisibility(ProductListing $listing): bool
    {
        $listing->loadMissing('variant.product');

        if ($listing->variant?->product?->status !== GeneralStatus::Active) {
            return false;
        }

        if (! in_array($listing->status, [ProductListingStatus::Active, ProductListingStatus::Hidden], true)) {
            return false;
        }

        $listing->status = match ($listing->status) {
            ProductListingStatus::Active => ProductListingStatus::Hidden,
            ProductListingStatus::Hidden => ProductListingStatus::Active,
            default                      => $listing->status,
        };

        $listing->save();

        return true;
    }

    public function toggleAdminStatus(ProductListing $listing): bool
    {
        $listing->status = match ($listing->status) {
            ProductListingStatus::Draft   => ProductListingStatus::Active,
            ProductListingStatus::Active  => ProductListingStatus::Hidden,
            ProductListingStatus::Hidden  => ProductListingStatus::Draft,
            ProductListingStatus::Pending => ProductListingStatus::Active,
            default                       => ProductListingStatus::Draft,
        };

        return $listing->save();
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            $listings = ProductListing::query()->whereIn('id', $ids)->get();

            foreach ($listings as $listing) {
                if ($listing->keys()->where('status', ProductKeyStatus::Sold->value)->exists()) {
                    continue;
                }

                $listing->status = ProductListingStatus::Deleted;
                $listing->save();
                $listing->delete();
            }
        });
    }

    public function syncStockCount(ProductListing $listing): void
    {
        $listing->forceFill([
            'stock_count' => $this->keys->countAvailableForListing($listing->id),
        ])->save();
    }

    private function normalizeDisplayName(?string $displayName): ?string
    {
        if ($displayName === null) {
            return null;
        }

        $normalizedDisplayName = trim($displayName);

        return $normalizedDisplayName === '' ? null : $normalizedDisplayName;
    }
}
