<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Category;

use App\Enums\GeneralStatus;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CategoryEditForm extends Form
{
    public ?Category $category = null;

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

    #[Validate([
        'required',
        'int',
        new Enum(GeneralStatus::class),
    ])]
    public int $status;

    public function setCategory(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->parentId = $category->parent_id;
        $this->status = $category->status->value;
    }

    /**
     * @return array{name: string, slug: string, parent_id: int|null, status: int}
     */
    public function validatedData(): array
    {
        $this->validate();

        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parentId,
            'status'    => $this->status,
        ];
    }

    public function update(): bool
    {
        if (! $this->category instanceof Category) {
            throw ValidationException::withMessages([
                'category' => 'Danh mục không tồn tại.',
            ]);
        }

        return app(CategoryService::class)->update(
            $this->category,
            $this->validatedData(),
            'editForm.slug',
            'editForm.status',
        );
    }
}
