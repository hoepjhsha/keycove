<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Region;

use App\Enums\GeneralStatus;
use App\Models\Region;
use Illuminate\Support\Str;
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

    public function update(): bool
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Region::where('slug', $this->slug)->where('id', '!=', $this->region->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.slug' => 'Region already exists. Write your own slug or change region name',
            ]);
        }

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => 'Cannot set status to Deleted via update.',
            ]);
        }

        return $this->region->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parentId,
            'flag_code' => $this->flag_code,
            'status' => $this->status,
        ]);
    }
}
