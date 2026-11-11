<?php

namespace App\Helpers;

class NumberHelper
{
    private static array $ones = [
        '', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE',
        'TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN',
        'SEVENTEEN', 'EIGHTEEN', 'NINETEEN',
    ];

    private static array $tens = [
        '', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY',
    ];

    /**
     * Format a DECIMAL column value without needless trailing zeros (201.000 → 201, 250.500 → 250.5).
     * Returns null when the value is not numeric so callers can pick their own placeholder.
     */
    public static function trimDecimals(mixed $value, int $scale = 3, bool $grouped = false): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $formatted = number_format((float) $value, $scale, '.', $grouped ? ',' : '');

        return str_contains($formatted, '.') ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }

    public static function amountInWords(float $amount): string
    {
        $amount = round(abs($amount), 2);
        $taka = (int) $amount;
        $paisa = (int) round(($amount - $taka) * 100);

        if ($taka === 0 && $paisa === 0) {
            return 'BDT ZERO TAKA ONLY';
        }

        $words = 'BDT '.self::spellOut($taka).' TAKA';
        if ($paisa > 0) {
            $words .= ' AND '.self::spellOut($paisa).' PAISA';
        }

        return $words.' ONLY';
    }

    private static function spellOut(int $n): string
    {
        if ($n === 0) {
            return 'ZERO';
        }

        $parts = [];

        if ($n >= 10000000) {
            $parts[] = self::spellOut((int) ($n / 10000000)).' CRORE';
            $n %= 10000000;
        }
        if ($n >= 100000) {
            $parts[] = self::spellOut((int) ($n / 100000)).' LAKH';
            $n %= 100000;
        }
        if ($n >= 1000) {
            $parts[] = self::spellOut((int) ($n / 1000)).' THOUSAND';
            $n %= 1000;
        }
        if ($n >= 100) {
            $parts[] = self::$ones[(int) ($n / 100)].' HUNDRED';
            $n %= 100;
        }
        if ($n >= 20) {
            $word = self::$tens[(int) ($n / 10)];
            if ($n % 10) {
                $word .= ' '.self::$ones[$n % 10];
            }
            $parts[] = $word;
        } elseif ($n > 0) {
            $parts[] = self::$ones[$n];
        }

        return implode(' ', $parts);
    }
}
