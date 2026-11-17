<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\File;

/**
 * Placeholder photos for local development, read from storage/app/seed-images/{folder}.
 *
 * They come from the reference templates' demo images and must never be shipped
 * to production. When the folder is missing, seeders simply skip the images.
 */
final class SeedImages
{
    /**
     * @return list<string>
     */
    public static function in(string $folder): array
    {
        $directory = storage_path("app/seed-images/{$folder}");

        if (! is_dir($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->map(fn (\SplFileInfo $file): string => $file->getPathname())
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Pick $count images from the pool, starting at $offset and wrapping around.
     *
     * @param  list<string>  $pool
     * @return list<string>
     */
    public static function pick(array $pool, int $offset, int $count): array
    {
        if ($pool === []) {
            return [];
        }

        return collect(range(0, min($count, count($pool)) - 1))
            ->map(fn (int $step): string => $pool[($offset + $step) % count($pool)])
            ->all();
    }
}
