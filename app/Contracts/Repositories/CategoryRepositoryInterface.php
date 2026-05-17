<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function findForAdminOrFail(int $categoryId, bool $withTrashed = false): Category;

    public function hasAnyTrashed(array $ids): bool;

    public function hasDeletedStatus(array $ids): bool;

    public function slugExists(string $slug, ?int $ignoreCategoryId = null): bool;

    /**
     * @return EloquentCollection<int, Category>
     */
    public function getParentOptions(): EloquentCollection;

    /**
     * @return EloquentCollection<int, Category>
     */
    public function getRootOptions(): EloquentCollection;
}
