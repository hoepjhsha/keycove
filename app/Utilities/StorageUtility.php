<?php

declare(strict_types=1);

namespace App\Utilities;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class StorageUtility
{
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
