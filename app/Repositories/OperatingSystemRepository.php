<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Abstracts\Repository;
use App\Contracts\Repositories\OperatingSystemRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\OperatingSystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OperatingSystemRepository extends Repository implements OperatingSystemRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new OperatingSystem);
    }

    /**
     * {@inheritDoc}
     */
    public function findForAdminOrFail(int $operatingSystemId, bool $withTrashed = false): OperatingSystem
    {
        $query = $this->newQuery();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($operatingSystemId);
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
    public function slugExists(string $slug, ?int $ignoreOperatingSystemId = null): bool
    {
        return $this->newQuery()
            ->when($ignoreOperatingSystemId !== null, fn ($query) => $query->whereKeyNot($ignoreOperatingSystemId))
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
