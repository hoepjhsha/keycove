<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\ProductListingStatus;
use App\Enums\ProductVariantStatus;
use App\Models\ProductVariant;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductVariantForm extends Form
{
    public ?ProductVariant $variant = null;

    #[Validate(['required', 'int', 'exists:products,id'])]
    public int $product_id = 0;

    #[Validate(['required', 'int', 'exists:regions,id'])]
    public int $region_id = 0;

    #[Validate(['required', 'int', 'exists:platforms,id'])]
    public int $platform_id = 0;

    #[Validate(['required', 'int', 'exists:operating_systems,id'])]
    public int $os_id = 0;

    #[Validate(['nullable', 'string', 'max:25'])]
    public ?string $edition = null;

    #[Validate(['required', 'int', new Enum(ProductVariantStatus::class)])]
    public int $status = ProductVariantStatus::Draft->value;

    public function setVariant(ProductVariant $variant): void
    {
        $this->variant = $variant;
        $this->product_id = $variant->product_id;
        $this->region_id = $variant->region_id;
        $this->platform_id = $variant->platform_id;
        $this->os_id = $variant->os_id;
        $this->edition = $variant->edition;
        $this->status = $variant->status->value;
    }

    public function store(): ProductVariant
    {
        $this->validate();

        return ProductVariant::create([
            'product_id'  => $this->product_id,
            'region_id'   => $this->region_id,
            'platform_id' => $this->platform_id,
            'os_id'       => $this->os_id,
            'edition'     => $this->edition,
            'status'      => $this->status,
        ]);
    }

    public function update(): bool
    {
        $this->validate();

        if ($this->status === ProductVariantStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'status' => 'Cannot set status to Deleted via update.',
            ]);
        }

        return $this->variant->update([
            'region_id'   => $this->region_id,
            'platform_id' => $this->platform_id,
            'os_id'       => $this->os_id,
            'edition'     => $this->edition,
            'status'      => $this->status,
        ]);
    }

    public function bulkChangeStatus(array $ids, int $status): bool
    {
        if (! in_array($status, array_column(ProductVariantStatus::cases(), 'value'))) {
            throw ValidationException::withMessages([
                'status' => 'Invalid status value.',
            ]);
        }

        $hasActiveListings = ProductVariant::whereIn('id', $ids)
            ->where('status', ProductVariantStatus::Active)
            ->whereHas('listings', function ($query) {
                $query->where('status', '!=', ProductListingStatus::Deleted);
            })
            ->exists();

        if ($hasActiveListings && $status === ProductVariantStatus::Discontinued->value) {
            throw ValidationException::withMessages([
                'status' => 'Cannot set variant to Discontinued while it has active listings.',
            ]);
        }

        return (bool) ProductVariant::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function deleteVariant(int $id): bool
    {
        $variant = ProductVariant::findOrFail($id);

        if ($variant->listings()->where('status', '==', ProductListingStatus::Active)->exists()) {
            throw ValidationException::withMessages([
                'general' => 'Cannot delete variant with active listings.',
            ]);
        }

        $variant->status = ProductVariantStatus::Deleted;
        $variant->save();

        return $variant->delete();
    }

    public function restoreVariant(int $id): bool
    {
        $variant = ProductVariant::withTrashed()->findOrFail($id);

        $variant->restore();
        $variant->status = ProductVariantStatus::Draft;
        $variant->save();

        return true;
    }

    public function resetForm(): void
    {
        $this->variant = null;
        $this->product_id = 0;
        $this->region_id = 0;
        $this->platform_id = 0;
        $this->os_id = 0;
        $this->edition = null;
        $this->status = ProductVariantStatus::Draft->value;
        $this->resetValidation();
    }
}
