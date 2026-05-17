<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CategoryRepository extends Repository implements CategoryRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Category);
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminOrFail(int $categoryId, bool $withTrashed = false): Category
    {
        $query = $this->newQuery()->with('parent');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($categoryId);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnyTrashed(array $ids): bool
    {
        return $this->newQuery()
            ->onlyTrashed()
            ->whereIn('id', $ids)
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function hasDeletedStatus(array $ids): bool
    {
        return $this->newQuery()
            ->withTrashed()
            ->whereIn('id', $ids)
            ->where('status', GeneralStatus::Deleted)
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function slugExists(string $slug, ?int $ignoreCategoryId = null): bool
    {
        return $this->newQuery()
            ->when($ignoreCategoryId !== null, fn ($query) => $query->whereKeyNot($ignoreCategoryId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function getParentOptions(): EloquentCollection
    {
        return $this->newQuery()
            ->select(['id', 'name'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getRootOptions(): EloquentCollection
    {
        return $this->newQuery()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }
}
