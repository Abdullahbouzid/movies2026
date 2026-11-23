<?php

namespace App\Support;

/**
 * Partial masking for personal data shown on pages that are reachable
 * without authentication (booking QR verification links).
 */
class Mask
{
    /**
     * Keep the leading and trailing digits, hide the middle ones.
     * 0913504510 → 0913***510
     */
    public static function phone(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $length = mb_strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $head = mb_substr($value, 0, 4);
        $tail = mb_substr($value, -3);
        $hidden = max(3, $length - 7);

        return $head . str_repeat('*', $hidden) . $tail;
    }
}
