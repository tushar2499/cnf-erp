<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsBookingItem extends Model
{
    protected $table = 'nas_freights_booking_items';

    protected $fillable = [
        'booking_id', 'cover_van_no', 'challan_no', 'capacity',
        'supplier_id', 'supplier_name',
        'qty', 'supplier_rate', 'customer_rate',
        'demurrage_days', 'cus_demurrage_charge', 'sup_demurrage_charge',
        'amount', 'location_from', 'location_to',
    ];

    public function booking()
    {
        return $this->belongsTo(NasFreightsBooking::class, 'booking_id');
    }

    /**
     * Check whether an item capacity matches a human-typed capacity filter
     *
     * Both sides are parsed into numeric ranges ("03-10 M/T" => 3..10, "5" => 5..5)
     * and matched when the ranges overlap, so filtering by "5" also returns
     * "05 M/T", "03-05 M/T" and "03-10 M/T". Filters without numbers fall back
     * to a case-insensitive text match. An empty filter matches everything.
     *
     * @param  string|null  $itemCapacity  Raw capacity stored on the booking item
     * @param  string  $filter  Raw filter input from the user
     */
    public static function capacityOverlaps(?string $itemCapacity, string $filter): bool
    {
        $filter = trim($filter);
        if ($filter === '') {
            return true;
        }

        $itemCapacity = trim((string) $itemCapacity);
        if ($itemCapacity === '') {
            return false;
        }

        $filterRange = static::parseCapacity($filter);
        $itemRange = static::parseCapacity($itemCapacity);

        if ($filterRange === null || $itemRange === null) {
            return stripos($itemCapacity, $filter) !== false;
        }

        if ($filterRange['unit'] !== null && $itemRange['unit'] !== null && $filterRange['unit'] !== $itemRange['unit']) {
            return false;
        }

        return $filterRange['min'] <= $itemRange['max']
            && $filterRange['max'] >= $itemRange['min'];
    }

    /**
     * Parse a capacity string into a numeric range and normalized unit
     *
     * @param  string  $value  Capacity text e.g. "10 M/T", "1.5-03 M/T", "2 - 3", "CBM"
     * @return array{min: float, max: float, unit: string|null}|null Null when no number found
     */
    private static function parseCapacity(string $value): ?array
    {
        $unit = null;
        if (preg_match('/([a-zA-Z]+(?:\/[a-zA-Z]+)?)\s*$/u', $value, $m)) {
            $unit = static::normalizeCapacityUnit($m[1]);
            $value = trim(substr($value, 0, strlen($value) - strlen($m[0])));
        }

        preg_match_all('/\d+(?:\.\d+)?/', $value, $numberMatches);
        $numbers = array_map('floatval', $numberMatches[0] ?? []);
        if ($numbers === []) {
            return null;
        }

        return ['min' => min($numbers), 'max' => max($numbers), 'unit' => $unit];
    }

    /**
     * Normalize a capacity unit token so equivalent spellings compare equal
     *
     * @param  string  $unit  Raw unit token e.g. "M/T", "mt", "CBM"
     */
    private static function normalizeCapacityUnit(string $unit): string
    {
        $unit = strtolower(str_replace([' ', '.'], '', $unit));

        return match ($unit) {
            'm/t', 'mt', 'mtr', 'metricton', 'tons', 'ton' => 'mt',
            'cbm', 'm3', 'cum'                             => 'cbm',
            default                                        => $unit,
        };
    }
}
