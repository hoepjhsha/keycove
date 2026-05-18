<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Region;

use App\Enums\GeneralStatus;
use App\Models\Region;
use App\Services\Admin\RegionService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RegionEditForm extends Form
{
    public ?Region $region = null;

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

    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

    public function setRegion(Region $region): void
    {
        $this->region = $region;
        $this->name = $region->name;
        $this->slug = $region->slug;
        $this->parentId = $region->parent_id;
        $this->flag_code = $region->flag_code ?? '';
        $this->status = $region->status->value;
    }

    /**
     * @return array{name: string, slug: string, parent_id: int|null, flag_code: string, status: int}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parentId,
            'flag_code' => $this->flag_code,
            'status'    => $this->status,
        ];
    }

    public function update(): bool
    {
        if (! $this->region instanceof Region) {
            throw ValidationException::withMessages([
                'region' => 'Khu vực không tồn tại.',
            ]);
        }

        return app(RegionService::class)->update(
            $this->region,
            $this->validatedData(),
            'editForm.slug',
            'editForm.status',
        );
    }
}
