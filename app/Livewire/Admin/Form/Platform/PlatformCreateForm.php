<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Platform;

use App\Models\Platform;
use App\Services\Admin\PlatformService;
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

    /**
     * @return array{name: string, slug: string, icon_file: mixed, icon_path: string, base_url: string}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'icon_file' => $this->icon_file,
            'icon_path' => $this->icon_path,
            'base_url'  => $this->base_url,
        ];
    }

    public function store(): Platform
    {
        return app(PlatformService::class)->create($this->validatedData(), 'createForm.slug');
    }
}
