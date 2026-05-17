<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Platform;

use App\Enums\GeneralStatus;
use App\Services\Admin\PlatformService;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PlatformBulkChangeStatusForm extends Form
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

        return app(PlatformService::class)->bulkChangeStatus($ids, (int) $this->status, 'bulkChangeStatusForm.status');
    }
}
