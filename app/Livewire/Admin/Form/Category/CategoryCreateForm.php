<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Category;

use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Validation\Rules\Exists;
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
        'regex:/^[\pL\s()]+$/u',
    ])]
    public string $name = '';

    #[Validate([
        'nullable',
        'string',
        'regex:/^[a-z]([a-z0-9-]*[a-z0-9])?$/',
    ])]
    public string $slug = '';

    /**
     * @return array{name: string, slug: string, parent_id: int|null}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parentId,
        ];
    }

    public function store(): Category
    {
        return app(CategoryService::class)->create($this->validatedData(), 'createForm.slug');
    }
}
