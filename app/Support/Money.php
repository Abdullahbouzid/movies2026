<?php

namespace App\Support;

/**
 * Formats money for every surface of the app.
 *
 * Prices are stored as decimals; only presentation lives here so the currency
 * symbol never gets hardcoded into a view or a table column again.
 */
class Money
{
    public static function symbol(): string
    {
        return (string) config('currency.symbol', 'د.ل');
    }

    public static function code(): string
    {
        return (string) config('currency.code', 'LYD');
    }

    /** e.g. "1,250.00 د.ل" */
    public static function format(float|int|string|null $value, bool $withSymbol = true): string
    {
        $amount = number_format(
            (float) $value,
            (int) config('currency.decimals', 2),
            (string) config('currency.decimal_separator', '.'),
            (string) config('currency.thousands_separator', ','),
        );

        return $withSymbol ? $amount . ' ' . self::symbol() : $amount;
    }

    /** Drops trailing zeros: "25 د.ل" instead of "25.00 د.ل". */
    public static function short(float|int|string|null $value, bool $withSymbol = true): string
    {
        $amount = rtrim(rtrim(self::format($value, false), '0'), '.');

        return $withSymbol ? $amount . ' ' . self::symbol() : $amount;
    }

    /** Field label suffix: "السعر (د.ل)". */
    public static function label(string $base): string
    {
        return $base . ' (' . self::symbol() . ')';
    }
}
