<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Category;

use App\Enums\GeneralStatus;
use App\Models\Category;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CategoryBulkChangeStatusForm extends Form
{
    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
        'not_in:3',
    ])]
    public ?int $status;

    public function setStatus(array $ids): bool|int
    {
        $this->validate();

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => __('admin.validation.status_deleted_bulk'),
            ]);
        }

        return Category::whereIn('id', $ids)
            ->update(['status' => $this->status]);
    }
}
