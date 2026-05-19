<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\ProductListingStatus;
use App\Models\ProductListing;
use App\Services\ProductListingService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductListingForm extends Form
{
    public ?ProductListing $listing = null;

    #[Validate(['nullable', 'string', 'max:255'])]
    public ?string $display_name = null;

    #[Validate(['required', 'int', 'exists:product_variants,id'])]
    public int $variant_id = 0;

    #[Validate(['nullable', 'int', 'exists:sellers,id'])]
    public ?int $seller_id = null;

    #[Validate(['required', 'numeric', 'min:0', 'max:999999999.99'])]
    public float $price = 0.00;

    #[Validate(['required', 'int', new Enum(ProductListingStatus::class)])]
    public int $status = ProductListingStatus::Draft->value;

    public function setListing(ProductListing $listing): void
    {
        $this->listing = $listing;
        $this->display_name = $listing->display_name;
        $this->variant_id = $listing->variant_id;
        $this->seller_id = $listing->seller_id;
        $this->price = (float) $listing->price;
        $this->status = $listing->status->value;
    }

    /**
     * @return array{display_name: ?string, variant_id: int, seller_id: ?int, price: float, status: int}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'display_name' => $this->normalizeDisplayName(),
            'variant_id'   => $this->variant_id,
            'seller_id'    => $this->seller_id,
            'price'        => $this->price,
            'status'       => $this->status,
        ];
    }

    public function store(): ProductListing
    {
        return app(ProductListingService::class)->create($this->validatedData(), 'listingForm.status');
    }

    public function update(): bool
    {
        if (! $this->listing instanceof ProductListing) {
            throw ValidationException::withMessages([
                'listing' => 'Listing không hợp lệ.',
            ]);
        }

        return app(ProductListingService::class)->update($this->listing, $this->validatedData());
    }

    public function bulkChangeStatus(array $ids, int $status): bool
    {
        return app(ProductListingService::class)->bulkChangeStatus($ids, $status);
    }

    public function deleteListing(int $id): bool
    {
        return app(ProductListingService::class)->delete(ProductListing::findOrFail($id));
    }

    public function restoreListing(int $id): bool
    {
        return app(ProductListingService::class)->restore(ProductListing::withTrashed()->findOrFail($id));
    }

    public function resetForm(): void
    {
        $this->listing = null;
        $this->display_name = null;
        $this->variant_id = 0;
        $this->seller_id = null;
        $this->price = 0.00;
        $this->status = ProductListingStatus::Draft->value;
        $this->resetValidation();
    }

    private function normalizeDisplayName(): ?string
    {
        if ($this->display_name === null) {
            return null;
        }

        $displayName = trim($this->display_name);

        return $displayName === '' ? null : $displayName;
    }
}
