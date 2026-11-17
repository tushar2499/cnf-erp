<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronItem extends Model
{
    protected $fillable = [
        'item_code', 'item_name', 'supplier', 'purchase_unit',
        'description', 'remarks', 'status', 'item_price',
        'availability_in_po', 'availability_in_so', 'image',
    ];

    protected function casts(): array
    {
        return [
            'item_price'         => 'decimal:2',
            'availability_in_po' => 'boolean',
            'availability_in_so' => 'boolean',
        ];
    }

    /**
     * Next sequential item code, e.g. ITEM-0003.
     *
     * Compares the numeric suffix: a string MAX() ranks "ITEM-001" above
     * "ITEM-0002", which regenerated an already used code.
     */
    public static function generateItemCode(): string
    {
        $last = static::where('item_code', 'like', 'ITEM-%')
            ->lockForUpdate()
            ->max(DB::raw('CAST(SUBSTRING(item_code, 6) AS UNSIGNED)'));

        if ($last === null) {
            return 'ITEM-1001';
        }

        return 'ITEM-'.str_pad((string) ((int) $last + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function units(): array
    {
        return [
            'Count / Piece' => ['PCS' => 'PCS — Pieces', 'SET' => 'SET — Set', 'PAIR' => 'PAIR — Pair', 'DZ' => 'DZ — Dozen', 'GROSS' => 'GROSS — Gross (144)', 'NOS' => 'NOS — Numbers', 'LOT' => 'LOT — Lot'],
            'Weight'        => ['KG' => 'KG — Kilogram', 'G' => 'G — Gram', 'MT' => 'MT — Metric Ton', 'LB' => 'LB — Pound', 'OZ' => 'OZ — Ounce'],
            'Volume'        => ['L' => 'L — Liter', 'ML' => 'ML — Milliliter', 'GAL' => 'GAL — Gallon', 'BBL' => 'BBL — Barrel', 'CBM' => 'CBM — Cubic Meter'],
            'Length / Area' => ['M' => 'M — Meter', 'CM' => 'CM — Centimeter', 'MM' => 'MM — Millimeter', 'FT' => 'FT — Feet', 'IN' => 'IN — Inch', 'SQM' => 'SQM — Square Meter', 'SQFT' => 'SQFT — Square Feet'],
            'Packaging'     => ['BOX' => 'BOX — Box', 'CTN' => 'CTN — Carton', 'BAG' => 'BAG — Bag', 'PKT' => 'PKT — Packet', 'ROLL' => 'ROLL — Roll', 'DRUM' => 'DRUM — Drum', 'CAN' => 'CAN — Can', 'BUNDLE' => 'BUNDLE — Bundle'],
        ];
    }
}
