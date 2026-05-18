<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\Region;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface RegionRepositoryInterface extends RepositoryInterface
{
    public function findForAdminOrFail(int $regionId, bool $withTrashed = false): Region;

    public function hasAnyTrashed(array $ids): bool;

    public function hasDeletedStatus(array $ids): bool;

    public function slugExists(string $slug, ?int $ignoreRegionId = null): bool;

    /**
     * @return EloquentCollection<int, Region>
     */
    public function getActiveOrdered(): EloquentCollection;

    /**
     * @return EloquentCollection<int, Region>
     */
    public function getParentOptions(): EloquentCollection;

    /**
     * @return EloquentCollection<int, Region>
     */
    public function getRootOptions(): EloquentCollection;
}
