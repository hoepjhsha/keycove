<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\OperatingSystem;

use App\Models\OperatingSystem;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OperatingSystemCreateForm extends Form
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
        'string',
    ])]
    public string $icon_path = '';

    public function store()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (OperatingSystem::where('slug', $this->slug)->exists()) {
            throw ValidationException::withMessages([
                'createForm.slug' => 'OperatingSystem already exists. Write your own slug or change operating system name',
            ]);
        }

        return OperatingSystem::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'icon_path' => $this->icon_path,
        ]);
    }
}
