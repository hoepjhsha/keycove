<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\RegionRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Region;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class RegionRepository extends Repository implements RegionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Region);
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminOrFail(int $regionId, bool $withTrashed = false): Region
    {
        $query = $this->newQuery()->with('parent');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($regionId);
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
    public function slugExists(string $slug, ?int $ignoreRegionId = null): bool
    {
        return $this->newQuery()
            ->when($ignoreRegionId !== null, fn ($query) => $query->whereKeyNot($ignoreRegionId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveOrdered(): EloquentCollection
    {
        return $this->newQuery()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get();
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
