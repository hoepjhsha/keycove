<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\ProductKeyStatus;
use App\Enums\ProductListingStatus;
use App\Models\ProductListing;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductListingForm extends Form
{
    public ?ProductListing $listing = null;

    #[Validate(['required', 'int', 'exists:product_variants,id'])]
    public int $variant_id = 0;

    #[Validate(['required', 'int', 'exists:sellers,id'])]
    public int $seller_id = 0;

    #[Validate(['required', 'numeric', 'min:0', 'max:999999999.99'])]
    public float $price = 0.00;

    #[Validate(['required', 'int', new Enum(ProductListingStatus::class)])]
    public int $status = ProductListingStatus::Draft->value;

    public function setListing(ProductListing $listing): void
    {
        $this->listing = $listing;
        $this->variant_id = $listing->variant_id;
        $this->seller_id = $listing->seller_id;
        $this->price = (float) $listing->price;
        $this->status = $listing->status->value;
    }

    public function store(): ProductListing
    {
        $this->validate();

        if ($this->status === ProductListingStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'listingForm.status' => 'Cannot create listing with deleted status.',
            ]);
        }

        return ProductListing::create([
            'variant_id' => $this->variant_id,
            'seller_id'  => $this->seller_id,
            'price'      => $this->price,
            'status'     => $this->status,
        ]);
    }

    public function update(): bool
    {
        $this->validate();

        return $this->listing->update([
            'seller_id' => $this->seller_id,
            'price'     => $this->price,
            'status'    => $this->status,
        ]);
    }

    public function bulkChangeStatus(array $ids, int $status): bool
    {
        if (! in_array($status, array_column(ProductListingStatus::cases(), 'value'))) {
            throw ValidationException::withMessages([
                'status' => 'Invalid status value.',
            ]);
        }

        $hasSoldKeys = ProductListing::whereIn('id', $ids)
            ->whereHas('keys', function ($query) {
                $query->where('status', ProductKeyStatus::Sold->value);
            })
            ->exists();

        if ($hasSoldKeys && in_array($status, [ProductListingStatus::Closed->value, ProductListingStatus::Deleted->value])) {
            throw ValidationException::withMessages([
                'status' => 'Cannot close or delete listings with sold keys.',
            ]);
        }

        return ProductListing::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function deleteListing(int $id): bool
    {
        $listing = ProductListing::findOrFail($id);

        $hasSoldKeys = $listing->keys()->where('status', ProductKeyStatus::Sold->value)->exists();

        if ($hasSoldKeys) {
            throw ValidationException::withMessages([
                'general' => 'Cannot delete listing with sold keys.',
            ]);
        }

        $listing->status = ProductListingStatus::Deleted;
        $listing->save();

        return $listing->delete();
    }

    public function restoreListing(int $id): bool
    {
        $listing = ProductListing::withTrashed()->findOrFail($id);

        $listing->restore();
        $listing->status = ProductListingStatus::Draft;
        $listing->save();

        return true;
    }

    public function resetForm(): void
    {
        $this->listing = null;
        $this->variant_id = 0;
        $this->seller_id = 0;
        $this->price = 0.00;
        $this->status = ProductListingStatus::Draft->value;
        $this->resetValidation();
    }
}
