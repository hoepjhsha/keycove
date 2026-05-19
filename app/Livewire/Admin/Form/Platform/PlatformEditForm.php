<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Platform;

use App\Enums\GeneralStatus;
use App\Models\Platform;
use App\Services\Admin\PlatformService;
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

    /**
     * @return array{name: string, slug: string, icon_file: mixed, icon_path: string, base_url: string, status: int}
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
            'status'    => $this->status,
        ];
    }

    public function update(): bool
    {
        if (! $this->platform instanceof Platform) {
            throw ValidationException::withMessages([
                'platform' => 'Platform không tồn tại.',
            ]);
        }

        return app(PlatformService::class)->update(
            $this->platform,
            $this->validatedData(),
            'editForm.slug',
            'editForm.status',
        );
    }
}
