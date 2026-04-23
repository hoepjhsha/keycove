<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Region;

use App\Models\Region;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
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

    public function store()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Region::where('slug', $this->slug)->exists()) {
            throw ValidationException::withMessages([
                'createForm.slug' => 'Region already exists. Write your own slug or change region name',
            ]);
        }

        return Region::create([
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parentId,
            'flag_code' => $this->flag_code,
        ]);
    }
}
