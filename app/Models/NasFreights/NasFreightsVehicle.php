<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsVehicle extends Model
{
    protected $table = 'nas_freights_vehicles';

    protected $fillable = [
        'branch_id', 'vehicle_number', 'vehicle_name', 'vehicle_class', 'vehicle_type',
        'supplier_id', 'supplier_name', 'purchase_unit', 'price',
        'description', 'remarks', 'status',
        'availability_in_po', 'availability_in_so', 'image',
    ];

    protected $casts = [
        'availability_in_po' => 'boolean',
        'availability_in_so' => 'boolean',
        'price'              => 'decimal:2',
    ];

    public static function vehicleClasses(): array
    {
        return ['Finished Goods', 'Fixed_assets', 'Intermediate Goods', 'Raw Materials', 'Services'];
    }

    public static function vehicleTypes(): array
    {
        return ['Cold Chain Van', 'Dry Cover Van', 'Open Truck', 'Trailer'];
    }

    public static function purchaseUnits(): array
    {
        return ['Piece', 'Unit', 'Set', 'Lot', 'Trip'];
    }
}
