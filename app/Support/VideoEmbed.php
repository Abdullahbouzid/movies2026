<?php

namespace App\Support;

/**
 * Resolves a stored video reference into something a browser can actually play.
 *
 * A bare URL cannot be dropped into <video src>: YouTube/Vimeo links are HTML
 * pages, not media files, so they must go through an <iframe> embed instead.
 */
class VideoEmbed
{
    public const TYPE_FILE = 'file';

    public const TYPE_EMBED = 'embed';

    /**
     * Direct media extensions a <video> element can play natively.
     */
    private const FILE_EXTENSIONS = ['mp4', 'webm', 'ogv', 'ogg', 'mov', 'm4v'];

    /**
     * @return array{type: string, url: string}|null
     */
    public static function resolve(?string $reference): ?array
    {
        $reference = trim((string) $reference);

        if ($reference === '') {
            return null;
        }

        $embed = self::embedUrl($reference);

        if ($embed !== null) {
            return ['type' => self::TYPE_EMBED, 'url' => $embed];
        }

        if (self::looksLikeFile($reference)) {
            return ['type' => self::TYPE_FILE, 'url' => $reference];
        }

        return null;
    }

    /**
     * Turn a YouTube/Vimeo/Dailymotion page URL into its embeddable equivalent.
     */
    public static function embedUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', $parts['host']));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $youtubeId = match (true) {
            $host === 'youtu.be' => trim($path, '/'),
            in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com'], true) => match (true) {
                isset($query['v']) => (string) $query['v'],
                (bool) preg_match('#^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{6,})#', $path, $m) => $m[1],
                default => null,
            },
            $host === 'youtube-nocookie.com' => (bool) preg_match('#^/embed/([A-Za-z0-9_-]{6,})#', $path, $m) ? $m[1] : null,
            default => null,
        };

        if ($youtubeId !== null && $youtubeId !== '') {
            $start = isset($query['t']) ? self::seconds((string) $query['t']) : null;

            return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($youtubeId)
                . '?rel=0' . ($start ? '&start=' . $start : '');
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)
            && preg_match('#/(?:video/)?(\d{6,})#', $path, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        if (in_array($host, ['dailymotion.com', 'dai.ly'], true)) {
            $id = $host === 'dai.ly' ? trim($path, '/') : (preg_match('#/video/([A-Za-z0-9]+)#', $path, $m) ? $m[1] : null);

            if ($id) {
                return 'https://www.dailymotion.com/embed/video/' . $id;
            }
        }

        return null;
    }

    /**
     * Whether the reference points at a media file rather than a web page.
     */
    public static function looksLikeFile(string $url): bool
    {
        if (str_starts_with($url, 'storage/') || str_starts_with($url, '/storage/')) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::FILE_EXTENSIONS, true);
    }

    /**
     * Parse a YouTube-style timestamp ("90", "1m30s", "1:30") into seconds.
     */
    private static function seconds(string $value): ?int
    {
        if (ctype_digit($value)) {
            return (int) $value;
        }

        if (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/', $value, $m)) {
            return ((int) ($m[1] ?? 0)) * 3600 + ((int) ($m[2] ?? 0)) * 60 + (int) ($m[3] ?? 0);
        }

        if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $value, $m)) {
            return count($m) === 4
                ? ((int) $m[1]) * 3600 + ((int) $m[2]) * 60 + (int) $m[3]
                : ((int) $m[1]) * 60 + (int) $m[2];
        }

        return null;
    }
}
