<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Platform;

use App\Enums\GeneralStatus;
use App\Models\Platform;
use App\Utilities\StorageUtility;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PlatformEditForm extends Form
{
    public ?Platform $platform = null;

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
        'nullable',
        'string',
        'url',
    ])]
    public string $base_url = '';

    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

    public function setPlatform(Platform $platform): void
    {
        $this->platform = $platform;
        $this->name = $platform->name;
        $this->slug = $platform->slug;
        $this->icon_path = $platform->icon_path ?? '';
        $this->base_url = $platform->base_url ?? '';
        $this->status = $platform->status->value;
    }

    public function update(): bool
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Platform::where('slug', $this->slug)->where('id', '!=', $this->platform->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.slug' => 'Platform already exists. Write your own slug or change platform name',
            ]);
        }

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => 'Cannot set status to Deleted via update.',
            ]);
        }

        $iconPath = $this->icon_path;
        if ($this->icon_file) {
            $storedPath = StorageUtility::store($this->icon_file, 'icons/platforms', config('filesystems.public_disk'));
            $iconPath = $storedPath;
        }

        return $this->platform->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'icon_path' => $iconPath,
            'base_url' => $this->base_url,
            'status' => $this->status,
        ]);
    }
}
