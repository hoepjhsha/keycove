<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use App\Models\ProductListing;
use App\Services\ProductKeyService;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductKeyForm extends Form
{
    public ?ProductKey $key = null;

    #[Validate(['required', 'int', 'exists:product_listings,id'])]
    public int $listing_id = 0;

    #[Validate(['required', 'string', 'max:500'])]
    public string $key_code = '';

    #[Validate(['required', 'int', new Enum(ProductKeyStatus::class)])]
    public int $status = ProductKeyStatus::Available->value;

    public function setKey(ProductKey $key): void
    {
        $this->key = $key;
        $this->listing_id = $key->listing_id;
        $this->key_code = $key->key_code;
        $this->status = $key->status->value;
    }

    /**
     * @return array{listing_id: int, key_code: string, status: int}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'listing_id' => $this->listing_id,
            'key_code'   => $this->key_code,
            'status'     => $this->status,
        ];
    }

    public function store(): ProductKey
    {
        $data = $this->validatedData();

        return app(ProductKeyService::class)->create(
            ProductListing::findOrFail($data['listing_id']),
            $data['key_code'],
            'keyForm.key_code',
        );
    }

    public function deleteKey(int $id): bool
    {
        return app(ProductKeyService::class)->delete(ProductKey::findOrFail($id));
    }

    public function resetForm(): void
    {
        $this->key = null;
        $this->listing_id = 0;
        $this->key_code = '';
        $this->status = ProductKeyStatus::Available->value;
        $this->resetValidation();
    }
}
