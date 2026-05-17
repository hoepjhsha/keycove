<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\OperatingSystem;

use App\Enums\GeneralStatus;
use App\Services\Admin\OperatingSystemService;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OperatingSystemBulkChangeStatusForm extends Form
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

        return app(OperatingSystemService::class)->bulkChangeStatus($ids, (int) $this->status, 'bulkChangeStatusForm.status');
    }
}
