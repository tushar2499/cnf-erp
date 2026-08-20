<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsCustomerBillItem;
use App\Models\NasFreights\NasFreightsVehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class CustomerBillController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsCustomerBill::where('branch_id', session('nas_freights_branch_id'))->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date', fn ($r) => $r->bill_date?->format('d-M-Y'))
                ->editColumn('from_date', fn ($r) => $r->from_date?->format('d-M-Y'))
                ->editColumn('to_date', fn ($r) => $r->to_date?->format('d-M-Y'))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Approved'  => '<span class="badge bg-success">CONFIRMED</span>',
                    'Paid'      => '<span class="badge bg-primary">PAID</span>',
                    'Submitted' => '<span class="badge bg-warning text-dark">SUBMITTED</span>',
                    default     => '<span class="badge bg-secondary">DRAFT</span>',
                })
                ->addColumn('action', function ($r) {
                    $edit = ($r->status === 'Draft' || $r->status === 'Submitted')
                        ? '<a href="'.route('nas-freights.customer-bills.edit', $r->id).'" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a> '
                        : '';
                    $view = '<a href="'.route('nas-freights.customer-bills.show', $r->id).'" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a> '
                          .'<a href="'.route('nas-freights.customer-bills.print', $r->id).'" target="_blank" class="btn btn-sm btn-outline-dark" title="Print"><i class="fa fa-print"></i></a> '
                          .'<a href="'.route('nas-freights.customer-bills.mushak', $r->id).'" target="_blank" class="btn btn-sm btn-outline-secondary" title="Mushak-6.3"><i class="fa fa-file-invoice"></i></a> '
                          .'<a href="'.route('nas-freights.customer-bills.excel', $r->id).'" class="btn btn-sm btn-outline-success" title="Excel"><i class="fa fa-file-excel"></i></a> '
                          .$edit;
                    $confirm = ($r->status === 'Draft' || $r->status === 'Submitted')
                        ? '<button class="btn btn-sm btn-outline-success btn-confirm" data-url="'.route('nas-freights.customer-bills.confirm', $r->id).'" data-name="'.e($r->bill_no).'" title="Confirm"><i class="fa fa-check"></i></button> '
                        : '';
                    $del = ($r->status !== 'Paid')
                        ? '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="'.route('nas-freights.customer-bills.destroy', $r->id).'" data-name="'.e($r->bill_no).'"><i class="fa fa-trash"></i></button>'
                        : '';

                    return $view.$confirm.$del;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.customer-bills.index');
    }

    public function create()
    {
        return view('nas-freights.customer-bills.create', [
            'deliveryTypes' => NasFreightsCustomerBill::deliveryTypes(),
            'billTypes'     => NasFreightsCustomerBill::billTypes(),
        ]);
    }

    public function loadItems(Request $request)
    {
        $request->validate([
            'from_date'   => ['required', 'date'],
            'to_date'     => ['required', 'date'],
            'customer_id' => ['required'],
        ]);

        // (booking_id, cover_van_no) pairs already present in any customer bill item
        $billedPairs = NasFreightsCustomerBillItem::select('booking_id', 'item_code')
            ->whereNotNull('booking_id')
            ->get()
            ->map(fn ($r) => $r->booking_id.'_'.$r->item_code)
            ->flip()
            ->toArray();

        $bookingItems = NasFreightsBookingItem::with('booking')
            ->whereHas('booking', function ($q) use ($request) {
                $q->whereBetween('job_date', [$request->from_date, $request->to_date])
                    ->where('customer_id', $request->customer_id);
            })
            ->get()
            ->filter(fn ($item) => ! isset($billedPairs[$item->booking_id.'_'.$item->cover_van_no]))
            ->values();

        $firstBooking = $bookingItems->first()?->booking;

        $items = $bookingItems->map(function ($item) {
            $b = $item->booking;
            $loc = trim(($item->location_from ?? '').($item->location_to ? ' - '.$item->location_to : ''));

            return [
                'booking_id'       => $b->id,
                'booking_item_id'  => $item->id,
                'booking_date'     => $b->job_date?->format('Y-m-d'),
                'delivery_date'    => $b->delivery_date?->format('Y-m-d'),
                'item_code'        => $item->cover_van_no,
                'item_name'        => $item->cover_van_no.($item->location_from ? ' || '.$item->location_from : ''),
                'location'         => $loc,
                'b_qty'            => (float) $item->qty,
                'd_qty'            => (float) $item->qty,
                'due_qty'          => 0,
                'price'            => (float) $item->customer_rate,
                'disc_percent'     => 0,
                'discount'         => 0,
                'ait_percent'      => (float) ($b->ait_percent ?? 0),
                'demurrage_day'    => (float) ($item->demurrage_days ?: 0),
                'demurrage_rate'   => (float) ($item->cus_demurrage_charge ?: 0),
                'demurrage_amount' => round((float) ($item->demurrage_days ?: 0) * (float) ($item->cus_demurrage_charge ?: 0), 2),
                'line_amount'      => round((float) $item->qty * (float) $item->customer_rate, 2),
            ];
        });

        $customerAddress = '';
        if ($request->customer_id) {
            $customer = NasFreightsCustomer::find($request->customer_id);
            $customerAddress = $customer?->address ?? '';
        }

        return response()->json([
            'items'            => $items,
            'customer_address' => $customerAddress,
            'delivery_type'    => $firstBooking?->sales_type ?? '',
            'tds_percent'      => (float) ($firstBooking?->tds_percent ?? 0),
            'vat_percent'      => (float) ($firstBooking?->vat_percent ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date'],
            'bill_date'     => ['required', 'date'],
            'delivery_type' => ['required'],
            'bill_type'     => ['required'],
        ]);

        // Items arrive as a JSON string, not a nested array — a bill with enough
        // rows (17 fields each) blows past PHP's default max_input_vars (1000)
        // if posted as `items[0][field]=...`, silently truncating the row list.
        $items = json_decode($request->input('items', '[]'), true) ?: [];

        if (empty($items)) {
            return response()->json(['message' => 'Add at least one item row.'], 422);
        }

        DB::transaction(function () use ($request, $items) {
            $billNo = NasFreightsCustomerBill::generateBillNo(session('nas_freights_branch_id'), $request->delivery_type);
            $subTotal = collect($items)->sum('line_amount');
            $totalDem = collect($items)->sum('demurrage_amount');
            $tdsAmt = round($subTotal * ($request->tds_percent ?? 0) / 100, 2);
            $vatAmt = round($subTotal * ($request->vat_percent ?? 0) / 100, 2);

            $bill = NasFreightsCustomerBill::create([
                'bill_no'          => $billNo,
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'customer_id'      => $request->customer_id ?: null,
                'customer_name'    => $request->customer_name,
                'customer_address' => $request->customer_address,
                'bill_date'        => $request->bill_date,
                'delivery_no'      => $billNo,
                'delivery_type'    => $request->delivery_type,
                'tds_percent'      => $request->tds_percent ?? 0,
                'tds_amount'       => $tdsAmt,
                'vat_percent'      => $request->vat_percent ?? 0,
                'vat_amount'       => $vatAmt,
                'bill_type'        => $request->bill_type,
                'bill_by'          => $request->bill_by,
                'note'             => $request->note,
                'sub_total'        => $subTotal,
                'total_amount'     => $subTotal + $totalDem + $tdsAmt + $vatAmt,
                'branch_id'        => session('nas_freights_branch_id'),
                'status'           => 'Draft',
                'entry_by'         => Auth::user()?->name ?? 'System',
            ]);

            foreach ($items as $item) {
                NasFreightsCustomerBillItem::create([
                    'bill_id'          => $bill->id,
                    'booking_id'       => $item['booking_id'] ?: null,
                    'booking_item_id'  => $item['booking_item_id'] ?: null,
                    'booking_date'     => $item['booking_date'] ? Carbon::parse($item['booking_date'])->format('Y-m-d') : null,
                    'delivery_date'    => $item['delivery_date'] ? Carbon::parse($item['delivery_date'])->format('Y-m-d') : null,
                    'item_code'        => $item['item_code'] ?? null,
                    'item_name'        => $item['item_name'] ?? null,
                    'location'         => $item['location'] ?? null,
                    'b_qty'            => $item['b_qty'] ?? 0,
                    'd_qty'            => $item['d_qty'] ?? 0,
                    'due_qty'          => $item['due_qty'] ?? 0,
                    'price'            => $item['price'] ?? 0,
                    'disc_percent'     => $item['disc_percent'] ?? 0,
                    'discount'         => $item['discount'] ?? 0,
                    'ait_percent'      => $item['ait_percent'] ?? 0,
                    'demurrage_day'    => $item['demurrage_day'] ?? 0,
                    'demurrage_amount' => $item['demurrage_amount'] ?? 0,
                    'line_amount'      => $item['line_amount'] ?? 0,
                ]);
            }
        });

        return response()->json(['message' => 'Customer bill created successfully.', 'redirect' => route('nas-freights.customer-bills.index')]);
    }

    public function show(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load('items');

        return view('nas-freights.customer-bills.show', compact('customerBill'));
    }

    public function edit(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load('items.booking');

        return view('nas-freights.customer-bills.edit', [
            'customerBill'  => $customerBill,
            'deliveryTypes' => NasFreightsCustomerBill::deliveryTypes(),
            'billTypes'     => NasFreightsCustomerBill::billTypes(),
        ]);
    }

    public function update(Request $request, NasFreightsCustomerBill $customerBill)
    {
        $request->validate([
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date'],
            'bill_date'     => ['required', 'date'],
            'delivery_type' => ['required'],
            'bill_type'     => ['required'],
        ]);

        $items = json_decode($request->input('items', '[]'), true) ?: [];

        if (empty($items)) {
            return response()->json(['message' => 'Add at least one item row.'], 422);
        }

        DB::transaction(function () use ($request, $customerBill, $items) {
            $subTotal = collect($items)->sum('line_amount');
            $totalDem = collect($items)->sum('demurrage_amount');
            $tdsAmt = round($subTotal * ($request->tds_percent ?? 0) / 100, 2);
            $vatAmt = round($subTotal * ($request->vat_percent ?? 0) / 100, 2);

            $customerBill->update([
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'customer_id'      => $request->customer_id ?: null,
                'customer_name'    => $request->customer_name,
                'customer_address' => $request->customer_address,
                'bill_date'        => $request->bill_date,
                'delivery_type'    => $request->delivery_type,
                'tds_percent'      => $request->tds_percent ?? 0,
                'tds_amount'       => $tdsAmt,
                'vat_percent'      => $request->vat_percent ?? 0,
                'vat_amount'       => $vatAmt,
                'bill_type'        => $request->bill_type,
                'bill_by'          => $request->bill_by,
                'note'             => $request->note,
                'sub_total'        => $subTotal,
                'total_amount'     => $subTotal + $totalDem + $tdsAmt + $vatAmt,
            ]);

            $customerBill->items()->delete();
            foreach ($items as $item) {
                NasFreightsCustomerBillItem::create([
                    'bill_id'          => $customerBill->id,
                    'booking_id'       => $item['booking_id'] ?: null,
                    'booking_item_id'  => $item['booking_item_id'] ?: null,
                    'booking_date'     => $item['booking_date'] ? Carbon::parse($item['booking_date'])->format('Y-m-d') : null,
                    'delivery_date'    => $item['delivery_date'] ? Carbon::parse($item['delivery_date'])->format('Y-m-d') : null,
                    'item_code'        => $item['item_code'] ?? null,
                    'item_name'        => $item['item_name'] ?? null,
                    'location'         => $item['location'] ?? null,
                    'b_qty'            => $item['b_qty'] ?? 0,
                    'd_qty'            => $item['d_qty'] ?? 0,
                    'due_qty'          => $item['due_qty'] ?? 0,
                    'price'            => $item['price'] ?? 0,
                    'disc_percent'     => $item['disc_percent'] ?? 0,
                    'discount'         => $item['discount'] ?? 0,
                    'ait_percent'      => $item['ait_percent'] ?? 0,
                    'demurrage_day'    => $item['demurrage_day'] ?? 0,
                    'demurrage_amount' => $item['demurrage_amount'] ?? 0,
                    'line_amount'      => $item['line_amount'] ?? 0,
                ]);
            }
        });

        return response()->json(['message' => 'Customer bill updated successfully.', 'redirect' => route('nas-freights.customer-bills.index')]);
    }

    public function printView(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load(['items.booking.products', 'items.bookingItem']);

        return view('nas-freights.customer-bills.print', compact('customerBill'));
    }

    public function billExcel(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load(['items.booking.products', 'items.bookingItem']);

        $firstItem = $customerBill->items->first();
        $firstBooking = $firstItem?->booking;
        $uniqueBookings = $customerBill->items->pluck('booking')->filter()->unique('id');
        $allProducts = $uniqueBookings->flatMap(fn ($b) => $b->products ?? collect());
        $goodsName = $allProducts->pluck('goods_name')->filter()->unique()->join(', ')
            ?: ($firstBooking?->goods_name ?? '—');
        $totalQty = $allProducts->sum('qty') ?: $customerBill->items->sum('b_qty');
        $qtyUnit = $allProducts->first()?->qty_unit ?? '';
        $totalWeight = $allProducts->sum('net_weight');
        $weightUnit = $allProducts->first()?->weight_unit ?? '';

        $subTotal = $customerBill->items->sum('line_amount');
        $totalDem = $customerBill->items->sum('demurrage_amount');
        $totalDemDays = $customerBill->items->sum('demurrage_day');
        $tdsAmt = (float) ($customerBill->tds_amount ?? 0);
        $tdsPct = (float) ($customerBill->tds_percent ?? 0);
        $vatPct = (float) ($customerBill->vat_percent ?? 0);
        $vatAmt = (float) ($customerBill->vat_amount ?? 0);
        $grossAmt = $subTotal + $totalDem + $tdsAmt + $vatAmt;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Transport Bill');

        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'TRANSPORT BILL');
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->setCellValue('A3', 'To,');
        $sheet->setCellValue('A4', $customerBill->customer_name);
        $sheet->setCellValue('A5', $customerBill->customer_address);
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->setCellValue('H3', 'Bill Date:');
        $sheet->setCellValue('I3', $customerBill->bill_date?->format('d/m/Y'));
        $sheet->setCellValue('H4', 'Bill No:');
        $sheet->setCellValue('I4', $customerBill->bill_no);
        $sheet->setCellValue('H5', 'Goods Name:');
        $sheet->setCellValue('I5', $goodsName);
        $sheet->setCellValue('H6', 'Qty & N.Weight:');
        $sheet->setCellValue('I6', ($totalQty > 0 ? number_format($totalQty, 2).($qtyUnit ? ' '.$qtyUnit : '') : '—')
            .($totalWeight > 0 ? ' & '.number_format($totalWeight, 2).' '.$weightUnit : ''));
        $sheet->getStyle('H3:H6')->getFont()->setBold(true);
        $sheet->getStyle('I3:I4')->getFont()->setBold(true);

        $headers = ['SL', 'Job No', 'Delivery Date', 'Cover Van No', 'Cover Van Type', 'Capacity', 'Qty', 'Destination', 'Net Amt', 'Dem. Days', 'Total Dem.', 'Total Amt'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col.'8', $h);
            $col++;
        }
        $sheet->getStyle('A8:L8')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 9;
        foreach ($customerBill->items as $i => $item) {
            $bItem = $item->bookingItem
                ?? ($item->booking_id
                    ? NasFreightsBookingItem::where('booking_id', $item->booking_id)
                        ->where('cover_van_no', $item->item_code)->first()
                    : null);
            $demDays = (float) ($item->demurrage_day ?? 0);
            $demAmt = (float) ($item->demurrage_amount ?? 0);
            $rowTot = $item->line_amount + $demAmt;
            $capacity = $bItem?->capacity ?? '';
            $vehicle = NasFreightsVehicle::where('vehicle_number', $item->item_code)->first();
            $vanType = $vehicle?->vehicle_type ?? '';

            $sheet->fromArray([
                $i + 1,
                $item->booking?->job_no ?? '—',
                $item->booking?->delivery_date ? $item->booking->delivery_date->format('d M Y') : '—',
                $item->item_code,
                $vanType,
                $capacity,
                (float) $item->b_qty,
                $item->location,
                (float) $item->line_amount,
                $demDays,
                $demAmt,
                (float) $rowTot,
            ], null, 'A'.$row);
            foreach (['I', 'K', 'L'] as $c) {
                $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            if ($row % 2 === 0) {
                $sheet->getStyle('A'.$row.':L'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5FAF9');
            }
            $row++;
        }

        $sheet->mergeCells('A'.$row.':H'.$row);
        $sheet->setCellValue('A'.$row, 'Total Amount');
        $sheet->setCellValue('I'.$row, $subTotal);
        $sheet->setCellValue('J'.$row, $totalDemDays);
        $sheet->setCellValue('K'.$row, $totalDem);
        $sheet->setCellValue('L'.$row, $subTotal + $totalDem);
        $sheet->getStyle('A'.$row.':L'.$row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]]);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        foreach (['I', 'K', 'L'] as $c) {
            $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $row++;

        $sheet->setCellValue('K'.$row, 'TDS Amount ('.number_format($tdsPct, 2).'%)');
        $sheet->setCellValue('L'.$row, $tdsAmt);
        $sheet->getStyle('K'.$row)->getFont()->setBold(true);
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $sheet->setCellValue('K'.$row, 'VAT Amount ('.number_format($vatPct, 2).'%)');
        $sheet->setCellValue('L'.$row, $vatAmt);
        $sheet->getStyle('K'.$row)->getFont()->setBold(true);
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $sheet->setCellValue('K'.$row, 'Gross Amount');
        $sheet->setCellValue('L'.$row, $grossAmt);
        $sheet->getStyle('K'.$row.':L'.$row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']]]);
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row += 2;

        $sheet->setCellValue('A'.$row, 'Please make all CHEQUE payable to NAS Freights And Logistics Ltd.');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->getColor()->setRGB('C00000');
        $row++;
        $sheet->setCellValue('A'.$row, 'A/C: Mercantile Bank PLC.(1111001335991)');
        $row++;
        $sheet->setCellValue('A'.$row, 'For NAS Freights And Logistics Ltd.');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true);

        foreach (['A' => 5, 'B' => 12, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 9, 'G' => 8, 'H' => 20, 'I' => 12, 'J' => 10, 'K' => 12, 'L' => 12] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
        $sheet->getStyle('A8:L'.($row - 4))->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]]);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            "transport-bill-{$customerBill->bill_no}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'max-age=0']
        );
    }

    public function mushakView(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load(['items.booking', 'items.bookingItem']);
        $customer = NasFreightsCustomer::find($customerBill->customer_id);

        return view('nas-freights.customer-bills.mushak', compact('customerBill', 'customer'));
    }

    public function confirm(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->update(['status' => 'Approved']);

        return response()->json(['message' => 'Bill confirmed successfully.']);
    }

    public function destroy(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->items()->delete();
        $customerBill->delete();

        return response()->json(['message' => 'Bill deleted.']);
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');

        return response()->json(
            NasFreightsCustomer::where('status', 'Active')
                ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('customer_id', 'like', "%{$term}%")->orWhere('mobile', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'customer_id', 'name', 'mobile', 'address'])
                ->map(fn ($c) => [
                    'id'      => $c->id,
                    'text'    => $c->customer_id.'|'.$c->name.'|'.$c->mobile,
                    'name'    => $c->name,
                    'address' => $c->address,
                ])
        );
    }
}
