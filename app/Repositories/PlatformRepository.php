<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PlatformRepository extends Repository implements PlatformRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Platform);
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminOrFail(int $platformId, bool $withTrashed = false): Platform
    {
        $query = $this->newQuery();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($platformId);
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
    public function slugExists(string $slug, ?int $ignorePlatformId = null): bool
    {
        return $this->newQuery()
            ->when($ignorePlatformId !== null, fn ($query) => $query->whereKeyNot($ignorePlatformId))
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
}
