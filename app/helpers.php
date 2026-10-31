<?php

if (! function_exists('numberToWords')) {
    function numberToWords(float $amount): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $convert = function (int $n) use (&$convert, $ones, $tens): string {
            if ($n === 0) { return ''; }
            if ($n < 20) { return $ones[$n]; }
            if ($n < 100) { return $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : ''); }
            if ($n < 1000) { return $ones[intdiv($n, 100)] . ' Hundred' . ($n % 100 ? ' ' . $convert($n % 100) : ''); }
            if ($n < 100000) { return $convert(intdiv($n, 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : ''); }
            if ($n < 10000000) { return $convert(intdiv($n, 100000)) . ' Lakh' . ($n % 100000 ? ' ' . $convert($n % 100000) : ''); }

            return $convert(intdiv($n, 10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
        };

        $taka = (int) abs($amount);
        $poisha = (int) round((abs($amount) - $taka) * 100);
        $words = $taka > 0 ? $convert($taka) : 'Zero';
        if ($poisha > 0) {
            $words .= ' and ' . $convert($poisha) . ' Poisha';
        }

        return $words;
    }
}
