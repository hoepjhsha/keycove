<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Product;

use App\Enums\GeneralStatus;
use App\Models\Product;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductBulkChangeStatusForm extends Form
{
    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

    public function setStatus(array $ids): bool
    {
        $this->validate();

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'bulkChangeStatusForm.status' => 'Cannot set status to Deleted via bulk action.',
            ]);
        }

        return (bool) Product::whereIn('id', $ids)->update([
            'status' => $this->status,
        ]);
    }
}
