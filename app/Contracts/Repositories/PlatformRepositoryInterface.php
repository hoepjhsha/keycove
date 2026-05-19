<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Abstracts\RepositoryInterface;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface PlatformRepositoryInterface extends RepositoryInterface
{
    public function findForAdminOrFail(int $platformId, bool $withTrashed = false): Platform;

    public function hasAnyTrashed(array $ids): bool;

    public function hasDeletedStatus(array $ids): bool;

    public function slugExists(string $slug, ?int $ignorePlatformId = null): bool;

    /**
     * @return EloquentCollection<int, Platform>
     */
    public function getActiveOrdered(): EloquentCollection;
}
