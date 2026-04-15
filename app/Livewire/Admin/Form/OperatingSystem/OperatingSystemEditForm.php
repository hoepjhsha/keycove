<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\OperatingSystem;

use App\Enums\GeneralStatus;
use App\Models\OperatingSystem;
use App\Utilities\StorageUtility;
use Illuminate\Support\Str;
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

    public function update(): bool
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (OperatingSystem::where('slug', $this->slug)->where('id', '!=', $this->operatingSystem->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.slug' => 'OperatingSystem already exists. Write your own slug or change operating system name',
            ]);
        }

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => 'Cannot set status to Deleted via update.',
            ]);
        }

        $iconPath = $this->icon_path;
        if ($this->icon_file) {
            $storedPath = StorageUtility::store($this->icon_file, 'icons/operating-systems');
            $iconPath = $storedPath;
        }

        return $this->operatingSystem->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'icon_path' => $iconPath,
            'status' => $this->status,
        ]);
    }
}
