<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

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
