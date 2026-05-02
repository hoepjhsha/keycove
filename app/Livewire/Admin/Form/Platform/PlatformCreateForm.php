<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Platform;

use App\Models\Platform;
use App\Utilities\StorageUtility;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PlatformCreateForm extends Form
{
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

    public function store(): ?Platform
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Platform::where('slug', $this->slug)->exists()) {
            throw ValidationException::withMessages([
                'createForm.slug' => __('admin.validation.duplicate_platform_slug'),
            ]);
        }

        $iconPath = $this->icon_path;
        if ($this->icon_file) {
            $storedPath = StorageUtility::store($this->icon_file, 'icons/platforms', config('filesystems.public_disk'));
            $iconPath = $storedPath;
        }

        return Platform::create([
            'name'      => $this->name,
            'slug'      => $this->slug,
            'icon_path' => $iconPath,
            'base_url'  => $this->base_url,
        ]);
    }
}
