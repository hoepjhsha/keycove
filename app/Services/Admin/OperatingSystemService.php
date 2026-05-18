<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Contracts\Repositories\OperatingSystemRepositoryInterface;
use App\Enums\GeneralStatus;
use App\Models\OperatingSystem;
use App\Utilities\StorageUtility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OperatingSystemService
{
    public function __construct(private OperatingSystemRepositoryInterface $operatingSystems) {}

    /**
     * @param  array{name: string, slug: string, icon_file: mixed, icon_path: string}  $data
     */
    public function create(array $data, string $slugErrorKey = 'slug'): OperatingSystem
    {
        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, null, $slugErrorKey);

        /** @var OperatingSystem $operatingSystem */
        $operatingSystem = $this->operatingSystems->create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'icon_path' => $this->storeIcon($data['icon_file'], $data['icon_path']),
        ]);

        return $operatingSystem;
    }

    /**
     * @param  array{name: string, slug: string, icon_file: mixed, icon_path: string, status: int}  $data
     */
    public function update(OperatingSystem $operatingSystem, array $data, string $slugErrorKey = 'slug', string $statusErrorKey = 'status'): bool
    {
        if ($data['status'] === GeneralStatus::Deleted->value) {
            throw ValidationException::withMessages([
                $statusErrorKey => __('admin.validation.status_deleted_update'),
            ]);
        }

        $slug = $this->normalizeSlug($data['slug'], $data['name']);
        $this->ensureUniqueSlug($slug, $operatingSystem->id, $slugErrorKey);

        return $operatingSystem->update([
            'name'      => $data['name'],
            'slug'      => $slug,
            'icon_path' => $this->storeIcon($data['icon_file'], $data['icon_path']),
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

        return $this->operatingSystems->newQuery()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    public function toggleStatus(OperatingSystem $operatingSystem): bool
    {
        $operatingSystem->status = match ($operatingSystem->status) {
            GeneralStatus::Inactive => GeneralStatus::Active,
            default                 => GeneralStatus::Inactive,
        };

        return $operatingSystem->save();
    }

    public function delete(OperatingSystem $operatingSystem): bool
    {
        return DB::transaction(function () use ($operatingSystem): bool {
            $operatingSystem->status = GeneralStatus::Deleted;
            $operatingSystem->save();

            return (bool) $operatingSystem->delete();
        });
    }

    public function restore(OperatingSystem $operatingSystem): bool
    {
        return DB::transaction(function () use ($operatingSystem): bool {
            $operatingSystem->restore();
            $operatingSystem->status = GeneralStatus::Inactive;

            return $operatingSystem->save();
        });
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            $this->operatingSystems->newQuery()
                ->whereIn('id', $ids)
                ->update(['status' => GeneralStatus::Deleted]);

            $this->operatingSystems->newQuery()
                ->whereIn('id', $ids)
                ->delete();
        });
    }

    protected function normalizeSlug(string $slug, string $name): string
    {
        return $slug !== '' ? $slug : Str::slug($name);
    }

    protected function ensureUniqueSlug(string $slug, ?int $ignoreOperatingSystemId, string $errorKey): void
    {
        if (! $this->operatingSystems->slugExists($slug, $ignoreOperatingSystemId)) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => __('admin.validation.duplicate_operating_system_slug'),
        ]);
    }

    protected function storeIcon(mixed $file, string $currentPath): string
    {
        if (! $file) {
            return $currentPath;
        }

        $storedPath = StorageUtility::store($file, 'icons/operating-systems');

        return is_string($storedPath) ? $storedPath : $currentPath;
    }
}
