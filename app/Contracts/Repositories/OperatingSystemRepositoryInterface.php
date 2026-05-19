<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\OperatingSystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface OperatingSystemRepositoryInterface extends RepositoryInterface
{
    public function findForAdminOrFail(int $operatingSystemId, bool $withTrashed = false): OperatingSystem;

    public function hasAnyTrashed(array $ids): bool;

    public function hasDeletedStatus(array $ids): bool;

    public function slugExists(string $slug, ?int $ignoreOperatingSystemId = null): bool;

    /**
     * @return EloquentCollection<int, OperatingSystem>
     */
    public function getActiveOrdered(): EloquentCollection;
}
