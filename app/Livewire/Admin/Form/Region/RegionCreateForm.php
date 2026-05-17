<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Region;

use App\Models\Region;
use App\Services\Admin\RegionService;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RegionCreateForm extends Form
{
    #[Validate([
        'nullable',
        'int',
        new Exists(table: Region::class, column: 'id'),
    ])]
    public ?int $parentId = null;

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
        'required',
        'string',
        'max:10',
    ])]
    public string $flag_code = '';

    /**
     * @return array{name: string, slug: string, parent_id: int|null, flag_code: string}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parentId,
            'flag_code' => $this->flag_code,
        ];
    }

    public function store(): Region
    {
        return app(RegionService::class)->create($this->validatedData(), 'createForm.slug');
    }
}
