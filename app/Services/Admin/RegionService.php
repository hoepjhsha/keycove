<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Contracts\Repositories\RegionRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegionService
{
    public function __construct(private RegionRepositoryInterface $regions) {}

    /**
     * @param  array{name: string, slug: string, parent_id: int|null, flag_code: string}  $data
     */
    public function create(array $data, string $slugErrorKey = 'slug'): Region
    {
        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, null, $slugErrorKey);

        /** @var Region $region */
        $region = $this->regions->create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'parent_id' => $data['parent_id'],
            'flag_code' => $data['flag_code'],
        ]);

        return $region;
    }

    /**
     * @param  array{name: string, slug: string, parent_id: int|null, flag_code: string, status: int}  $data
     */
    public function update(Region $region, array $data, string $slugErrorKey = 'slug', string $statusErrorKey = 'status'): bool
    {
        if ($data['status'] === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_update'),
            ]);
        }

        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, $region->id, $slugErrorKey);

        return $region->update([
            'name'      => $data['name'],
            'slug'      => $slug,
            'parent_id' => $data['parent_id'],
            'flag_code' => $data['flag_code'],
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

        return $this->regions->newQuery()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    public function toggleStatus(Region $region): bool
    {
        $region->status = match ($region->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default                 => GeneralStatus::Inactive,
        };

        return $region->save();
    }

    public function delete(Region $region): bool
    {
        return DB::transaction(function () use ($region): bool {
            $region->status = GeneralStatus::Deleted;
            $region->save();

            return (bool) $region->delete();
        });
    }

    public function restore(Region $region): bool
    {
        return DB::transaction(function () use ($region): bool {
            $region->restore();
            $region->status = GeneralStatus::Inactive;

            return $region->save();
        });
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            $this->regions->newQuery()
                ->whereIn('id', $ids)
                ->update(['status' => GeneralStatus::Deleted]);

            $this->regions->newQuery()
                ->whereIn('id', $ids)
                ->delete();
        });
    }

    protected function normalizeSlug(string $slug, string $name): string
    {
        return $slug !== '' ? $slug : Str::slug($name);
    }

    protected function ensureUniqueSlug(string $slug, ?int $ignoreRegionId, string $errorKey): void
    {
        if (! $this->regions->slugExists($slug, $ignoreRegionId)) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => __('admin.validation.duplicate_region_slug'),
        ]);
    }
}
