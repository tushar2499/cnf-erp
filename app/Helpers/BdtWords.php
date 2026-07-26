<?php

namespace App\Helpers;

class BdtWords
{
    private static array $ones = [
        '', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE',
        'TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN',
        'SEVENTEEN', 'EIGHTEEN', 'NINETEEN',
    ];

    private static array $tens = [
        '', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY',
    ];

    public static function convert(float $amount): string
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
