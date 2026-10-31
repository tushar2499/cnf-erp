<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\FreightExportBookingTransport\EditFreightExportBookingTransportRequest;
use App\Http\Requests\NasFreights\FreightExportBookingTransport\UpdateFreightExportBookingTransportRequest;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsSupplier;
use App\Models\NasFreights\NasFreightsVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FreightExportBookingTransportController extends Controller
{
    public function edit(EditFreightExportBookingTransportRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        $exportBooking->load(['customer', 'salesperson', 'transportItems']);

        $suppliers = NasFreightsSupplier::where('is_active', true)
            ->orderBy('company_name')
            ->get(['id', 'code', 'company_name']);

        $existingItems = $exportBooking->transportItems->map(fn ($i) => [
            'cover_van_no'         => $i->cover_van_no,
            'challan_no'           => $i->challan_no,
            'capacity'             => $i->capacity,
            'supplier_id'          => $i->supplier_id,
            'supplier_name'        => $i->supplier_name,
            'qty'                  => $i->qty,
            'supplier_rate'        => $i->supplier_rate,
            'customer_rate'        => $i->customer_rate,
            'demurrage_days'       => $i->demurrage_days,
            'cus_demurrage_charge' => $i->cus_demurrage_charge,
            'sup_demurrage_charge' => $i->sup_demurrage_charge,
            'amount'               => $i->amount,
            'location_from'        => $i->location_from,
            'location_to'          => $i->location_to,
        ])->values();

        return view('nas-freights.freight-export-bookings.transport', compact('exportBooking', 'suppliers', 'existingItems'));
    }

    public function update(UpdateFreightExportBookingTransportRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        DB::transaction(function () use ($request, $exportBooking) {
            $exportBooking->transportItems()->delete();

            $totalTransportAmount = 0;

            foreach ($request->input('items', []) as $item) {
                if (empty($item['cover_van_no'])) {
                    continue;
                }

                $amount = (float) ($item['amount'] ?? 0);
                $totalTransportAmount += $amount;

                $exportBooking->transportItems()->create([
                    'cover_van_no'         => $item['cover_van_no'] ?? null,
                    'challan_no'           => $item['challan_no'] ?? null,
                    'capacity'             => $item['capacity'] ?? null,
                    'supplier_id'          => $item['supplier_id'] ?: null,
                    'supplier_name'        => $item['supplier_name'] ?? null,
                    'qty'                  => $item['qty'] ?? 1,
                    'supplier_rate'        => $item['supplier_rate'] ?? 0,
                    'customer_rate'        => $item['customer_rate'] ?? 0,
                    'demurrage_days'       => $item['demurrage_days'] ?? 0,
                    'cus_demurrage_charge' => $item['cus_demurrage_charge'] ?? 0,
                    'sup_demurrage_charge' => $item['sup_demurrage_charge'] ?? 0,
                    'amount'               => $amount,
                    'location_from'        => $item['location_from'] ?? null,
                    'location_to'          => $item['location_to'] ?? null,
                ]);
            }

            $exportBooking->update(['transport_amount' => $totalTransportAmount]);
        });

        return response()->json([
            'message'  => 'Transport details saved for '.$exportBooking->export_booking_no.'.',
            'redirect' => route('nas-freights.freight-export-bookings.show', $exportBooking),
        ]);
    }

    public function searchVehicles(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasFreightsVehicle::where('status', 'Active')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->where(fn ($q) => $q->where('vehicle_number', 'like', "%{$term}%")->orWhere('vehicle_name', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'vehicle_number', 'vehicle_name', 'vehicle_type'])
                ->map(fn ($v) => ['id' => $v->vehicle_number, 'text' => $v->vehicle_number.($v->vehicle_name ? ' — '.$v->vehicle_name : ''), 'vehicle_type' => $v->vehicle_type])
        );
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasFreightsSupplier::where('is_active', true)
                ->where(fn ($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name'])
                ->map(fn ($s) => ['id' => $s->id, 'text' => $s->code.' — '.$s->company_name, 'name' => $s->company_name])
        );
    }
}
