<?php

declare(strict_types=1);

namespace App\Utilities;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class StorageUtility
{
    /**
     * Store an uploaded file to a specific path.
     * Returns the path of the stored file or false on failure.
     */
    public static function store(UploadedFile $file, string $path, ?string $disk = null): string|false
    {
        $disk = $disk ?? config('filesystems.default', 's3_private');

        return $file->store($path, $disk);
    }

    /**
     * Store an uploaded file with a specific name.
     * Returns the path of the stored file or false on failure.
     */
    public static function storeAs(UploadedFile $file, string $path, string $name, ?string $disk = null): string|false
    {
        $disk = $disk ?? config('filesystems.default', 's3_private');

        return $file->storeAs($path, $name, $disk);
    }

    /**
     * Store raw file contents (string, stream, etc.) to a specific path.
     */
    public static function put(string $path, mixed $contents, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default', 's3_private');

        return Storage::disk($disk)->put($path, $contents);
    }

    /**
     * Delete one or multiple files from storage.
     */
    public static function delete(string|array $paths, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default', 's3_private');

        return Storage::disk($disk)->delete($paths);
    }

    /**
     * Get the public URL for a given storage path, handling local environment endpoint replacement if necessary.
     */
    public static function getUrl(?string $path, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        $disk = $disk ?? config('filesystems.public_disk', 's3_public');

        $url = Storage::disk($disk)->url($path);

        return self::replaceLocalEndpoint($url);
    }

    /**
     * Get a temporary URL for a given storage path, with an expiration time, handling local environment endpoint replacement if necessary.
     */
    public static function getTemporaryUrl(?string $path, int $expireMinutes = 5, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        $disk = $disk ?? config('filesystems.default', 's3_private');

        $url = Storage::disk($disk)->temporaryUrl(
            $path,
            Carbon::now()->addMinutes($expireMinutes)
        );

        return self::replaceLocalEndpoint($url);
    }

    /**
     * Replace the local S3 endpoint with the external endpoint in the URL if the application is running in the local environment.
     */
    private static function replaceLocalEndpoint(string $url): string
    {
        if (app()->environment('local')) {
            $internalEndpoint = env('AWS_ENDPOINT');
            $externalEndpoint = env('AWS_ENDPOINT_EXTERNAL');

            if ($internalEndpoint && $externalEndpoint) {
                return str_replace($internalEndpoint, $externalEndpoint, $url);
            }
        }

        return $url;
    }
}
