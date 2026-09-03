<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsCustomerBill extends Model
{
    protected $table = 'nas_freights_customer_bills';

    protected $fillable = [
        'bill_no', 'from_date', 'to_date',
        'customer_id', 'customer_name', 'customer_address',
        'bill_date', 'delivery_no', 'delivery_type',
        'tds_percent', 'tds_amount',
        'vat_percent', 'vat_amount',
        'bill_type', 'bill_by', 'note',
        'branch_id', 'sub_total', 'total_amount', 'status', 'entry_by',
    ];

    protected $casts = [
        'from_date'  => 'date',
        'to_date'    => 'date',
        'bill_date'  => 'date',
    ];

    public function items()
    {
        return $this->hasMany(NasFreightsCustomerBillItem::class, 'bill_id')->orderBy('delivery_date', 'asc');
    }

    public static function generateBillNo(int $branchId, string $deliveryType): string
    {
        $branch = NasFreightsBranch::find($branchId);
        $branchCode = $branch?->code ?? 'XX';
        $typeCode = match ($deliveryType) {
            'EXPORT'       => 'EX',
            'IMPORT'       => 'IM',
            'DISTRIBUTION' => 'DS',
            'LOCAL'        => 'LC',
            default        => 'DS',
        };
        $year = now()->year;
        $prefix = "NAS-L-{$branchCode}-{$typeCode}-{$year}-";

        // Shared sequence across all delivery types for this branch+year — match any
        // 2-char type code so LC/IM/EX/DS all draw from the same running number.
        $patterns = [
            "NAS-L-{$branchCode}-__-{$year}-",
            "NAS-F-{$branchCode}-__-{$year}-",
            "NASF-{$branchCode}-__-{$year}-",
            "BILL-{$branchCode}-__-{$year}-",
        ];

        $max = 0;
        foreach ($patterns as $p) {
            $n = static::where('bill_no', 'like', $p.'%')
                ->lockForUpdate()
                ->max(DB::raw('CAST(SUBSTRING(bill_no, '.(strlen($p) + 1).') AS UNSIGNED)'));
            $max = max($max, (int) $n);
        }

        return $prefix.str_pad($max + 1, 7, '0', STR_PAD_LEFT);
    }

    public static function deliveryTypes(): array
    {
        return ['DISTRIBUTION', 'EXPORT', 'IMPORT', 'LOCAL'];
    }

    public static function billTypes(): array
    {
        return ['COVER VAN', 'PICKUP', 'SE COVERED VAN', 'TRUCK'];
    }
}
