<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\ProductVariantStatus;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Validation\Rules\Enum;
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

    /**
     * @return array{product_id: int, region_id: int, platform_id: int, os_id: int, edition: ?string, status: int}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'product_id'  => $this->product_id,
            'region_id'   => $this->region_id,
            'platform_id' => $this->platform_id,
            'os_id'       => $this->os_id,
            'edition'     => $this->normalizeEdition(),
            'status'      => $this->status,
        ];
    }

    public function store(): ProductVariant
    {
        return app(ProductVariantService::class)->create($this->validatedData(), 'general');
    }

    public function update(): bool
    {
        if (! $this->variant instanceof ProductVariant) {
            throw new \LogicException('Variant has not been set for editing.');
        }

        return app(ProductVariantService::class)->update($this->variant, $this->validatedData(), 'general', 'status');
    }

    public function bulkChangeStatus(array $ids, int $status): bool
    {
        return app(ProductVariantService::class)->bulkChangeStatus($ids, $status, 'status');
    }

    public function deleteVariant(int $id): bool
    {
        return app(ProductVariantService::class)->delete(ProductVariant::query()->findOrFail($id), false, 'general');
    }

    public function restoreVariant(int $id): bool
    {
        return app(ProductVariantService::class)->restore(
            ProductVariant::query()->withTrashed()->findOrFail($id),
            ProductVariantStatus::Draft,
        );
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

    private function normalizeEdition(): ?string
    {
        if ($this->edition === null) {
            return null;
        }

        $edition = trim($this->edition);

        return $edition === '' ? null : $edition;
    }
}
