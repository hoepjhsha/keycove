<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\OperatingSystem;

use App\Models\OperatingSystem;
use App\Services\Admin\OperatingSystemService;
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

    /**
     * @return array{name: string, slug: string, icon_file: mixed, icon_path: string}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'icon_file' => $this->icon_file,
            'icon_path' => $this->icon_path,
        ];
    }

    public function store(): OperatingSystem
    {
        return app(OperatingSystemService::class)->create($this->validatedData(), 'createForm.slug');
    }
}
