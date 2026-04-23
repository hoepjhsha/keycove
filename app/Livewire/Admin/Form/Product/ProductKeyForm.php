<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\ProductKeyStatus;
use App\Models\ProductKey;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
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

    public function store(): ProductKey
    {
        $this->validate();

        return ProductKey::create([
            'listing_id'    => $this->listing_id,
            'key_code'      => $this->key_code,
            'status'        => ProductKeyStatus::Available->value,
            'order_item_id' => null,
        ]);
    }

    public function deleteKey(int $id): bool
    {
        $key = ProductKey::findOrFail($id);

        if ($key->status !== ProductKeyStatus::Available) {
            throw ValidationException::withMessages([
                'general' => 'Cannot delete a key that is not available.',
            ]);
        }

        if ($key->order_item_id !== null) {
            throw ValidationException::withMessages([
                'general' => 'Cannot delete a key that has been assigned to an order.',
            ]);
        }

        return (bool) $key->delete();
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
