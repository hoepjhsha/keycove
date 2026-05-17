<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @param  array{name: string, slug: string, parent_id: int|null}  $data
     */
    public function create(array $data, string $slugErrorKey = 'slug'): Category
    {
        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, null, $slugErrorKey);

        /** @var Category $category */
        $category = $this->categories->create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'parent_id' => $data['parent_id'],
        ]);

        return $category;
    }

    /**
     * @param  array{name: string, slug: string, parent_id: int|null, status: int}  $data
     */
    public function update(Category $category, array $data, string $slugErrorKey = 'slug', string $statusErrorKey = 'status'): bool
    {
        if ($data['status'] === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_update'),
            ]);
        }

        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, $category->id, $slugErrorKey);

        return $category->update([
            'name'      => $data['name'],
            'slug'      => $slug,
            'parent_id' => $data['parent_id'],
            'status'    => $data['status'],
        ]);
    }

    public function bulkChangeStatus(array $ids, int $status, string $statusErrorKey = 'status'): bool|int
    {
        if ($status === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_bulk'),
            ]);
        }

        return $this->categories->newQuery()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    public function toggleStatus(Category $category): bool
    {
        $category->status = match ($category->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default                 => GeneralStatus::Inactive,
        };

        return $category->save();
    }

    public function delete(Category $category): bool
    {
        return DB::transaction(function () use ($category): bool {
            $category->products()->detach();
            $category->status = GeneralStatus::Deleted;
            $category->save();

            return (bool) $category->delete();
        });
    }

    public function restore(Category $category): bool
    {
        return DB::transaction(function () use ($category): bool {
            $category->restore();
            $category->status = GeneralStatus::Inactive;

            return $category->save();
        });
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            DB::table('category_product')->whereIn('category_id', $ids)->delete();

            $this->categories->newQuery()
                ->whereIn('id', $ids)
                ->update(['status' => GeneralStatus::Deleted]);

            $this->categories->newQuery()
                ->whereIn('id', $ids)
                ->delete();
        });
    }

    protected function normalizeSlug(string $slug, string $name): string
    {
        return $slug !== '' ? $slug : Str::slug($name);
    }

    protected function ensureUniqueSlug(string $slug, ?int $ignoreCategoryId, string $errorKey): void
    {
        if (! $this->categories->slugExists($slug, $ignoreCategoryId)) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => __('admin.validation.duplicate_category_slug'),
        ]);
    }
}
