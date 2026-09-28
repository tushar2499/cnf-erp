<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\FreightExportBookingBill\CreateFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\DestroyFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\EditFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\IndexFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\PrintFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\ShowFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\StoreFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\UpdateFreightExportBookingBillRequest;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use App\Models\NasFreights\NasFreightsFreightExportBookingBillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FreightExportBookingBillController extends Controller
{
    public function index(IndexFreightExportBookingBillRequest $request)
    {
        if ($request->ajax()) {
            $canView = $request->user()->hasPermission('freight.export-booking-bill.view');
            $canPrint = $request->user()->hasPermission('freight.export-booking-bill.print');
            $canEdit = $request->user()->hasPermission('freight.export-booking-bill.edit');
            $canDelete = $request->user()->hasPermission('freight.export-booking-bill.delete');

            $query = NasFreightsFreightExportBookingBill::with('exportBooking')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->when($request->bill_type, fn ($q) => $q->where('bill_type', $request->bill_type))
                ->when($request->from_date, fn ($q) => $q->whereDate('bill_date', '>=', $request->from_date))
                ->when($request->to_date, fn ($q) => $q->whereDate('bill_date', '<=', $request->to_date))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date', fn ($r) => $r->bill_date?->format('d M Y') ?? '—')
                ->editColumn('total_amount', fn ($r) => number_format($r->total_amount, 2).' '.$r->currency)
                ->editColumn('total_bdt_amount', fn ($r) => number_format($r->total_bdt_amount, 2))
                ->addColumn('booking_no', fn ($r) => $r->exportBooking?->export_booking_no ?? '—')
                ->addColumn('customer_name', fn ($r) => $r->exportBooking?->customer?->name ?? '—')
                ->addColumn('status_badge', fn ($r) => $r->status === 'Confirmed'
                    ? '<span class="badge bg-success">CONFIRMED</span>'
                    : '<span class="badge bg-secondary">DRAFT</span>')
                ->addColumn('action', function ($r) use ($canView, $canPrint, $canEdit, $canDelete) {
                    $actions = '';
                    if ($canView) {
                        $actions .= '<a href="'.route('nas-freights.freight-export-booking-bills.show', $r->id).'" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a> ';
                    }
                    if ($canPrint) {
                        $actions .= '<a href="'.route('nas-freights.freight-export-booking-bills.print', $r->id).'" target="_blank" class="btn btn-sm btn-outline-success" title="Print"><i class="fa fa-print"></i></a> ';
                    }
                    if ($canEdit) {
                        $actions .= '<a href="'.route('nas-freights.freight-export-booking-bills.edit', $r->id).'" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a> ';
                    }
                    if ($canDelete) {
                        $actions .= '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="'.route('nas-freights.freight-export-booking-bills.destroy', $r->id).'" data-name="'.e($r->bill_no).'"><i class="fa fa-trash"></i></button>';
                    }

                    return $actions;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.freight-export-booking-bills.index');
    }

    public function create(CreateFreightExportBookingBillRequest $request)
    {
        $booking = null;
        $existingBillTypes = [];

        if ($request->booking_id) {
            $booking = NasFreightsFreightExportBooking::with(['customer', 'overseasAgent', 'shippingCarrier', 'items', 'bills'])
                ->where('branch_id', session('nas_freights_branch_id'))
                ->findOrFail($request->booking_id);

            $existingBillTypes = $booking->bills->pluck('bill_type')->toArray();
        }

        return view('nas-freights.freight-export-booking-bills.create', compact('booking', 'existingBillTypes'));
    }

    public function store(StoreFreightExportBookingBillRequest $request)
    {
        $booking = NasFreightsFreightExportBooking::where('branch_id', session('nas_freights_branch_id'))
            ->findOrFail($request->export_booking_id);

        $alreadyExists = $booking->bills()->where('bill_type', $request->bill_type)->exists();
        if ($alreadyExists) {
            return back()->withErrors(['bill_type' => 'A '.$request->bill_type.' bill already exists for this booking.'])->withInput();
        }

        $exchangeRate = (float) $request->exchange_rate;

        DB::transaction(function () use ($request, $booking, $exchangeRate) {
            $bill = NasFreightsFreightExportBookingBill::create([
                'bill_no'           => NasFreightsFreightExportBookingBill::generateBillNo(),
                'export_booking_id' => $booking->id,
                'branch_id'         => session('nas_freights_branch_id'),
                'bill_type'         => $request->bill_type,
                'bill_date'         => $request->bill_date,
                'currency'          => $request->currency,
                'exchange_rate'     => $exchangeRate,
                'remarks'           => $request->remarks,
                'status'            => 'Draft',
                'total_amount'      => 0,
                'total_bdt_amount'  => 0,
            ]);

            $totalAmount = 0;
            $totalBdt = 0;

            foreach ($request->items as $i => $item) {
                $amt = (float) $item['amount'];
                $amtBdt = round($amt * $exchangeRate, 2);
                $totalAmount += $amt;
                $totalBdt += $amtBdt;

                NasFreightsFreightExportBookingBillItem::create([
                    'bill_id'    => $bill->id,
                    'name'       => $item['name'],
                    'amount'     => $amt,
                    'amount_bdt' => $amtBdt,
                    'sort_order' => $i,
                ]);
            }

            $bill->update([
                'total_amount'     => round($totalAmount, 2),
                'total_bdt_amount' => round($totalBdt, 2),
            ]);
        });

        return redirect()->route('nas-freights.freight-export-booking-bills.index')
            ->with('success', 'Export booking bill created successfully.');
    }

    public function show(ShowFreightExportBookingBillRequest $request, NasFreightsFreightExportBookingBill $freightExportBookingBill)
    {
        $bill = $freightExportBookingBill->load(['exportBooking.customer', 'exportBooking.overseasAgent', 'exportBooking.shippingCarrier', 'items']);

        return view('nas-freights.freight-export-booking-bills.show', compact('bill'));
    }

    public function edit(EditFreightExportBookingBillRequest $request, NasFreightsFreightExportBookingBill $freightExportBookingBill)
    {
        $bill = $freightExportBookingBill->load(['exportBooking.customer', 'exportBooking.overseasAgent', 'exportBooking.shippingCarrier', 'exportBooking.items', 'items']);
        $booking = $bill->exportBooking;
        $existingBillTypes = $booking->bills()->where('id', '!=', $bill->id)->pluck('bill_type')->toArray();

        return view('nas-freights.freight-export-booking-bills.create', compact('bill', 'booking', 'existingBillTypes'));
    }

    public function update(UpdateFreightExportBookingBillRequest $request, NasFreightsFreightExportBookingBill $freightExportBookingBill)
    {
        $bill = $freightExportBookingBill;

        $newBookingId = (int) $request->export_booking_id;
        $bookingChanged = $newBookingId !== (int) $bill->export_booking_id;

        $targetBooking = $bookingChanged
            ? NasFreightsFreightExportBooking::where('branch_id', session('nas_freights_branch_id'))->findOrFail($newBookingId)
            : $bill->exportBooking;

        $alreadyExists = $targetBooking->bills()
            ->where('bill_type', $request->bill_type)
            ->where('id', '!=', $bill->id)
            ->exists();
        if ($alreadyExists) {
            return back()->withErrors(['bill_type' => 'A '.$request->bill_type.' bill already exists for this booking.'])->withInput();
        }

        $exchangeRate = (float) $request->exchange_rate;

        DB::transaction(function () use ($request, $bill, $exchangeRate, $newBookingId) {
            $bill->items()->delete();

            $totalAmount = 0;
            $totalBdt = 0;

            foreach ($request->items as $i => $item) {
                $amt = (float) $item['amount'];
                $amtBdt = round($amt * $exchangeRate, 2);
                $totalAmount += $amt;
                $totalBdt += $amtBdt;

                NasFreightsFreightExportBookingBillItem::create([
                    'bill_id'    => $bill->id,
                    'name'       => $item['name'],
                    'amount'     => $amt,
                    'amount_bdt' => $amtBdt,
                    'sort_order' => $i,
                ]);
            }

            $bill->update([
                'export_booking_id' => $newBookingId,
                'bill_type'         => $request->bill_type,
                'bill_date'         => $request->bill_date,
                'currency'          => $request->currency,
                'exchange_rate'     => $exchangeRate,
                'remarks'           => $request->remarks,
                'total_amount'      => round($totalAmount, 2),
                'total_bdt_amount'  => round($totalBdt, 2),
            ]);
        });

        return redirect()->route('nas-freights.freight-export-booking-bills.index')
            ->with('success', 'Bill updated successfully.');
    }

    public function printView(PrintFreightExportBookingBillRequest $request, NasFreightsFreightExportBookingBill $freightExportBookingBill)
    {
        $bill = $freightExportBookingBill->load(['exportBooking.customer', 'exportBooking.overseasAgent', 'items']);

        return view('nas-freights.freight-export-booking-bills.print', compact('bill'));
    }

    public function destroy(DestroyFreightExportBookingBillRequest $request, NasFreightsFreightExportBookingBill $freightExportBookingBill)
    {
        $freightExportBookingBill->delete();

        return response()->json(['message' => 'Bill deleted.']);
    }

    public function searchBookings(Request $request)
    {
        if (! $request->user()->hasPermission('freight.export-booking-bill.list') &&
            ! $request->user()->hasPermission('freight.export-booking-bill.create')) {
            abort(403);
        }

        $q = $request->input('q', '');
        $bookings = NasFreightsFreightExportBooking::with('customer')
            ->where('branch_id', session('nas_freights_branch_id'))
            ->where(fn ($query) => $query
                ->where('export_booking_no', 'like', "%{$q}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$q}%"))
            )
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($bookings->map(fn ($b) => [
            'id'   => $b->id,
            'text' => $b->export_booking_no.($b->customer ? ' — '.$b->customer->name : ''),
        ]));
    }
}
