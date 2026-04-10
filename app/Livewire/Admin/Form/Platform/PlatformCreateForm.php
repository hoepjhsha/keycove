<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Platform;

use App\Models\Platform;
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
        'string',
    ])]
    public string $icon_path = '';

    #[Validate([
        'nullable',
        'string',
        'url',
    ])]
    public string $base_url = '';

    public function store()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Platform::where('slug', $this->slug)->exists()) {
            throw ValidationException::withMessages([
                'createForm.slug' => 'Platform already exists. Write your own slug or change platform name',
            ]);
        }

        return Platform::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'icon_path' => $this->icon_path,
            'base_url' => $this->base_url,
        ]);
    }
}
