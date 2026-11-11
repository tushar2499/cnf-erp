<?php

namespace Tests\Unit\Helpers;

use App\Helpers\NumberHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NumberHelperTrimDecimalsTest extends TestCase
{
    /**
     * @return array<string, array{0: mixed, 1: int, 2: bool, 3: ?string}>
     */
    public static function cases(): array
    {
        return [
            'whole decimal string'     => ['201.000', 3, false, '201'],
            'one decimal kept'         => ['250.500', 3, false, '250.5'],
            'three decimals kept'      => ['201.125', 3, false, '201.125'],
            'zero'                     => ['0.000', 3, false, '0'],
            'integer input'            => [1500, 3, false, '1500'],
            'float input'              => [3.5, 3, false, '3.5'],
            'grouped thousands'        => ['1500.250', 3, true, '1,500.25'],
            'grouped whole'            => ['1500.000', 3, true, '1,500'],
            'two decimal scale'        => ['1.00', 2, true, '1'],
            'beyond scale is rounded'  => ['1.23456', 3, false, '1.235'],
            'null'                     => [null, 3, false, null],
            'empty string'             => ['', 3, false, null],
            'non numeric'              => ['abc', 3, false, null],
        ];
    }

    #[DataProvider('cases')]
    public function test_trim_decimals(mixed $value, int $scale, bool $grouped, ?string $expected): void
    {
        $this->assertSame($expected, NumberHelper::trimDecimals($value, $scale, $grouped));
    }
}
