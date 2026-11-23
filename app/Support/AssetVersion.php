<?php

namespace App\Support;

/**
 * Cache-busting URLs for files under public/.
 *
 * Browsers hold on to CSS/JS far longer than we change it, so append the file's
 * modification time as a query string. Deploys then invalidate every client
 * cache without needing a build manifest.
 */
class AssetVersion
{
    public static function url(string $path): string
    {
        $path = ltrim($path, '/');
        $url = asset($path);
        $file = public_path($path);

        if (! is_file($file)) {
            return $url;
        }

        return $url . '?v=' . filemtime($file);
    }
}
