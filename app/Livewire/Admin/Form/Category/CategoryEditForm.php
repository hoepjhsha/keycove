<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Form\Category;

use App\Enums\GeneralStatus;
use App\Models\Category;
use Illuminate\Support\Str;
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

    public function update(): bool
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (Category::where('slug', $this->slug)->where('id', '!=', $this->category->id)->exists()) {
            throw ValidationException::withMessages([
                'editForm.slug' => __('admin.validation.duplicate_category_slug'),
            ]);
        }

        if ($this->status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                'editForm.status' => __('admin.validation.status_deleted_update'),
            ]);
        }

        return $this->category->update([
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parentId,
            'status'    => $this->status,
        ]);
    }
}
