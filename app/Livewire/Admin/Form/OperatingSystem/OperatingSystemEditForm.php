<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\OperatingSystem;

use App\Enums\GeneralStatus;
use App\Models\OperatingSystem;
use App\Services\Admin\OperatingSystemService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OperatingSystemEditForm extends Form
{
    public ?OperatingSystem $operatingSystem = null;

    #[Validate([
        'required',
        'string',
        'regex:/^[\pL\s]+$/u',
    ])]
    public string $name = '';

    #[Validate([
        'nullable',
        'string',
        'regex:/^[a-z]([a-z0-9-]*[a-z0-9])?$/',
    ])]
    public string $slug = '';

    #[Validate([
        'nullable',
        'file',
        'mimes:svg,png,jpg,jpeg,webp,gif',
        'max:512',
    ])]
    public $icon_file = null;

    #[Validate([
        'nullable',
        'string',
    ])]
    public string $icon_path = '';

    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

    public function setOperatingSystem(OperatingSystem $operatingSystem): void
    {
        $this->operatingSystem = $operatingSystem;
        $this->name = $operatingSystem->name;
        $this->slug = $operatingSystem->slug;
        $this->icon_path = $operatingSystem->icon_path ?? '';
        $this->icon_file = null;
        $this->status = $operatingSystem->status->value;
    }

    /**
     * @return array{name: string, slug: string, icon_file: mixed, icon_path: string, status: int}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'icon_file' => $this->icon_file,
            'icon_path' => $this->icon_path,
            'status'    => $this->status,
        ];
    }

    public function update(): bool
    {
        if (! $this->operatingSystem instanceof OperatingSystem) {
            throw ValidationException::withMessages([
                'operatingSystem' => 'Hệ điều hành không tồn tại.',
            ]);
        }

        return app(OperatingSystemService::class)->update(
            $this->operatingSystem,
            $this->validatedData(),
            'editForm.slug',
            'editForm.status',
        );
    }
}
