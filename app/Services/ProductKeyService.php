<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductKeyRepositoryInterface;
use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use App\Models\ProductListing;
use Illuminate\Validation\ValidationException;

class ProductKeyService
{
    public function __construct(
        private ProductKeyRepositoryInterface $keys,
        private ProductListingService $listings,
    ) {}

    public function create(ProductListing $listing, string $keyCode, string $duplicateErrorKey = 'keyCode'): ProductKey
    {
        $normalizedKeyCode = trim($keyCode);
        $keyHash = hash('sha256', $normalizedKeyCode);

        if ($this->keys->existsDuplicateForListing($listing->id, $keyHash)) {
            throw ValidationException::withMessages([
                $duplicateErrorKey => 'Key này đã tồn tại trong listing đã chọn.',
            ]);
        }

        /** @var ProductKey $key */
        $key = $this->keys->create([
            'listing_id'    => $listing->id,
            'key_code'      => $normalizedKeyCode,
            'key_hash'      => $keyHash,
            'status'        => ProductKeyStatus::Available->value,
            'order_item_id' => null,
        ]);

        $this->listings->syncStockCount($listing);

        return $key;
    }

    public function delete(ProductKey $key, string $errorKey = 'general'): bool
    {
        if ($key->status !== ProductKeyStatus::Available) {
            throw ValidationException::withMessages([
                $errorKey => __('admin.validation.product_key_delete_available'),
            ]);
        }

        if ($key->order_item_id !== null) {
            throw ValidationException::withMessages([
                $errorKey => __('admin.validation.product_key_delete_assigned'),
            ]);
        }

        $deleted = (bool) $key->delete();

        if ($deleted) {
            $key->loadMissing('listing');

            if ($key->listing instanceof ProductListing) {
                $this->listings->syncStockCount($key->listing);
            }
        }

        return $deleted;
    }
}
