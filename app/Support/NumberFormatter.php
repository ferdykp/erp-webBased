<?php

namespace App\Support;

final class NumberFormatter
{
    /**
     * Display a technical numeric value without meaningless trailing zeroes.
     *
     * Examples:
     * 1.0000   -> 1
     * 1.5000   -> 1.5
     * 12.34560 -> 12.3456
     *
     * Thousands separators are intentionally disabled for technical values so
     * a decimal value can never be confused with a grouped integer.
     */
    public static function smart(mixed $value, int $maxDecimals = 6, string $fallback = '-'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        if (! is_numeric($value)) {
            return (string) $value;
        }

        $number = (float) $value;

        if (! is_finite($number)) {
            return $fallback;
        }

        $maxDecimals = max(0, min($maxDecimals, 12));
        $formatted = number_format($number, $maxDecimals, '.', '');

        if ($maxDecimals === 0) {
            return $formatted;
        }

        return rtrim(rtrim($formatted, '0'), '.');
    }

    public static function integer(mixed $value, string $fallback = '-'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        if (! is_numeric($value)) {
            return $fallback;
        }

        return (string) ((int) round((float) $value));
    }
}
