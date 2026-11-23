<?php

namespace App\Support;

/**
 * Generates placeholder movie posters as gradient JPEGs.
 *
 * Seeded movies reference `movies/movie-N.jpg`; without these files every
 * poster request 404s and the UI shows empty frames. The generator draws the
 * movie's own gradient palette plus a light/shadow treatment so the seeded
 * catalogue looks like real artwork without shipping binary assets in git.
 */
class PosterFactory
{
    private const WIDTH = 600;

    private const HEIGHT = 900;

    /**
     * Write a poster for the given relative storage path (e.g. movies/movie-1.jpg).
     */
    public static function make(string $relativePath, string $from, string $to, ?string $accent = null): bool
    {
        if (! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $target = storage_path('app/public/' . ltrim($relativePath, '/'));

        if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0o755, true) && ! is_dir(dirname($target))) {
            return false;
        }

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($canvas, true);

        $start = self::rgb($from);
        $end = self::rgb($to);
        $spot = self::rgb($accent ?? $to);

        for ($y = 0; $y < self::HEIGHT; $y++) {
            $ratio = $y / self::HEIGHT;
            $color = imagecolorallocate(
                $canvas,
                (int) round($start[0] + ($end[0] - $start[0]) * $ratio),
                (int) round($start[1] + ($end[1] - $start[1]) * $ratio),
                (int) round($start[2] + ($end[2] - $start[2]) * $ratio),
            );
            imageline($canvas, 0, $y, self::WIDTH, $y, $color);
        }

        // Soft light source top-left, mirroring the radial highlights used on the cards.
        for ($step = 26; $step > 0; $step--) {
            $radius = 40 + $step * 16;
            $alpha = 118 - $step * 4;
            $shade = imagecolorallocatealpha($canvas, $spot[0], $spot[1], $spot[2], max(0, min(127, $alpha)));
            imagefilledellipse($canvas, (int) (self::WIDTH * 0.28), (int) (self::HEIGHT * 0.2), $radius * 2, $radius * 2, $shade);
        }

        // Vignette: darken the lower third so overlaid captions stay readable.
        for ($y = (int) (self::HEIGHT * 0.55); $y < self::HEIGHT; $y++) {
            $depth = ($y - self::HEIGHT * 0.55) / (self::HEIGHT * 0.45);
            $alpha = (int) round(96 * $depth);
            $line = imagecolorallocatealpha($canvas, 4, 4, 10, 127 - $alpha);
            imageline($canvas, 0, $y, self::WIDTH, $y, $line);
        }

        // Thin frame to read as a poster edge.
        $edge = imagecolorallocatealpha($canvas, 255, 255, 255, 108);
        imagerectangle($canvas, 6, 6, self::WIDTH - 7, self::HEIGHT - 7, $edge);

        $ok = imagejpeg($canvas, $target, 82);
        imagedestroy($canvas);

        return (bool) $ok;
    }

    /** @return array{0:int,1:int,2:int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            $hex = 'e71d24';
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
