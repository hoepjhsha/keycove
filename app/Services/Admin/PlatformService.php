<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\Platform;
use App\Utilities\StorageUtility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlatformService
{
    public function __construct(private PlatformRepositoryInterface $platforms) {}

    /**
     * @param  array{name: string, slug: string, icon_file: mixed, icon_path: string, base_url: string}  $data
     */
    public function create(array $data, string $slugErrorKey = 'slug'): Platform
    {
        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, null, $slugErrorKey);

        /** @var Platform $platform */
        $platform = $this->platforms->create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'icon_path' => $this->storeIcon($data['icon_file'], $data['icon_path']),
            'base_url'  => $data['base_url'],
        ]);

        return $platform;
    }

    /**
     * @param  array{name: string, slug: string, icon_file: mixed, icon_path: string, base_url: string, status: int}  $data
     */
    public function update(Platform $platform, array $data, string $slugErrorKey = 'slug', string $statusErrorKey = 'status'): bool
    {
        if ($data['status'] === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_update'),
            ]);
        }

        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, $platform->id, $slugErrorKey);

        return $platform->update([
            'name'      => $data['name'],
            'slug'      => $slug,
            'icon_path' => $this->storeIcon($data['icon_file'], $data['icon_path']),
            'base_url'  => $data['base_url'],
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

        return $this->platforms->newQuery()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    public function toggleStatus(Platform $platform): bool
    {
        $platform->status = match ($platform->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default                 => GeneralStatus::Inactive,
        };

        return $platform->save();
    }

    public function delete(Platform $platform): bool
    {
        return DB::transaction(function () use ($platform): bool {
            $platform->status = GeneralStatus::Deleted;
            $platform->save();

            return (bool) $platform->delete();
        });
    }

    public function restore(Platform $platform): bool
    {
        return DB::transaction(function () use ($platform): bool {
            $platform->restore();
            $platform->status = GeneralStatus::Inactive;

            return $platform->save();
        });
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            $this->platforms->newQuery()
                ->whereIn('id', $ids)
                ->update(['status' => GeneralStatus::Deleted]);

            $this->platforms->newQuery()
                ->whereIn('id', $ids)
                ->delete();
        });
    }

    protected function normalizeSlug(string $slug, string $name): string
    {
        return $slug !== '' ? $slug : Str::slug($name);
    }

    protected function ensureUniqueSlug(string $slug, ?int $ignorePlatformId, string $errorKey): void
    {
        if (! $this->platforms->slugExists($slug, $ignorePlatformId)) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => __('admin.validation.duplicate_platform_slug'),
        ]);
    }

    protected function storeIcon(mixed $file, string $currentPath): string
    {
        if (! $file) {
            return $currentPath;
        }

        $storedPath = StorageUtility::store($file, 'icons/platforms', config('filesystems.public_disk'));

        return is_string($storedPath) ? $storedPath : $currentPath;
    }
}
