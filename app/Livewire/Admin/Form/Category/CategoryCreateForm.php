<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Category;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CategoryCreateForm extends Form
{
    #[Validate([
        'nullable',
        'int',
        new Exists(table: Category::class, column: 'id'),
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

    public function store()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Category::where('slug', $this->slug)->exists()) {
            throw ValidationException::withMessages([
                'createForm.slug' => 'Category already exists. Write your own slug or change category name',
            ]);
        }

        return Category::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parentId,
        ]);
    }
}
