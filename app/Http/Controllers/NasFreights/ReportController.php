<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsCustomerBillItem;
use App\Models\NasFreights\NasFreightsVehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    // ─────────────────────────── BOOKING REPORT ───────────────────────────

    public function bookingReport(Request $request)
    {
        $rows = $this->getBookingRows($request);
        return view('nas-freights.reports.booking', compact('rows'));
    }

    public function bookingReportPrint(Request $request)
    {
        $rows    = $this->getBookingRows($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        return view('nas-freights.reports.booking-print', compact('rows', 'company'));
    }

    public function bookingReportPdf(Request $request)
    {
        $rows    = $this->getBookingRows($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        $pdf = Pdf::loadView('nas-freights.reports.booking-pdf', compact('rows', 'company'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('booking-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function bookingReportExcel(Request $request)
    {
        $rows    = $this->getBookingRows($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        $coName  = $company?->name ?? 'NAS Freights And Logistics Ltd.';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Booking Report');

        $statusLabel = $request->filled('status') ? $request->status . ' ' : '';
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', $coName);
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', $statusLabel . 'Booking Report');
        $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $dateStr = '';
        if ($request->filled('from_date')) $dateStr .= 'From: ' . \Carbon\Carbon::parse($request->from_date)->format('d/m/Y') . '   ';
        if ($request->filled('to_date'))   $dateStr .= 'To: '   . \Carbon\Carbon::parse($request->to_date)->format('d/m/Y');
        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3', trim($dateStr));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['SL','Job No','Job Date','Entry Date','Entry By','Sales Person','Customer','Supplier','Cover Van Details','Location','Supplier Rate','Customer Rate','Profit','Remarks','Billed','Bill No'];
        $col = 'A';
        foreach ($headers as $h) { $sheet->setCellValue($col . '4', $h); $col++; }
        $sheet->getStyle('A4:P4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A6B60']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 5; $ts = 0; $tc = 0; $tp = 0;
        foreach ($rows as $i => $item) {
            $b = $item->booking;
            $loc = trim(($item->location_from ?? '') . ($item->location_to ? ' - ' . $item->location_to : ''));
            $profit = $item->customer_rate - $item->supplier_rate;
            $ts += $item->supplier_rate; $tc += $item->customer_rate; $tp += $profit;
            $sheet->fromArray([$i+1, $b?->job_no, $b?->job_date?->format('d M Y'), $b?->created_at?->format('d M Y'), $b?->entry_by, $b?->sales_person_name, $b?->customer_name, $item->supplier_name, $item->cover_van_no, $loc, (float)$item->supplier_rate, (float)$item->customer_rate, (float)$profit, $b?->note, $item->is_billed ? 'Billed' : 'Pending', $item->bill_no ?? ''], null, 'A'.$row);
            foreach (['K','L','M'] as $c) $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            if ($row % 2 === 0) $sheet->getStyle('A'.$row.':P'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5FAF9');
            $row++;
        }
        $sheet->mergeCells('A'.$row.':J'.$row);
        $sheet->setCellValue('A'.$row, 'Total ('.$rows->count().' rows)');
        $sheet->setCellValue('K'.$row, $ts); $sheet->setCellValue('L'.$row, $tc); $sheet->setCellValue('M'.$row, $tp);
        $sheet->getStyle('A'.$row.':P'.$row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4E8D4']]]);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        foreach (['K','L','M'] as $c) $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (['A'=>4,'B'=>14,'C'=>12,'D'=>12,'E'=>10,'F'=>14,'G'=>20,'H'=>20,'I'=>16,'J'=>28,'K'=>14,'L'=>14,'M'=>14,'N'=>24,'O'=>10,'P'=>16] as $c => $w) $sheet->getColumnDimension($c)->setWidth($w);
        if ($row > 5) $sheet->getStyle('A4:P'.$row)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]]);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), 'booking-report-'.now()->format('Ymd-His').'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'max-age=0']);
    }

    // ─────────────────────── PARTY BILL SUMMARY ───────────────────────────

    public function partyBillSummary(Request $request)
    {
        [$bills, $customer] = $this->getBills($request);
        return view('nas-freights.reports.party-bill-summary', compact('bills', 'customer'));
    }

    public function partyBillSummaryPrint(Request $request)
    {
        [$bills, $customer] = $this->getBills($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        return view('nas-freights.reports.party-bill-summary-print', compact('bills', 'customer', 'company'));
    }

    public function partyBillSummaryPdf(Request $request)
    {
        [$bills, $customer] = $this->getBills($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        $pdf = Pdf::loadView('nas-freights.reports.party-bill-summary-pdf', compact('bills', 'customer', 'company'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('party-bill-summary-'.now()->format('Ymd-His').'.pdf');
    }

    public function partyBillSummaryExcel(Request $request)
    {
        [$bills, $customer] = $this->getBills($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        $coName  = $company?->name ?? 'NAS Freights And Logistics Ltd.';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Bill Summary');

        $sheet->mergeCells('A1:M1'); $sheet->setCellValue('A1', $coName);
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:M2'); $sheet->setCellValue('A2', 'Transport Bill Summary');
        $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $dateStr = '';
        if ($request->filled('from_date')) $dateStr .= 'From: '.\Carbon\Carbon::parse($request->from_date)->format('d M, Y').'   ';
        if ($request->filled('to_date'))   $dateStr .= 'To: '.\Carbon\Carbon::parse($request->to_date)->format('d M, Y');
        $sheet->mergeCells('A3:M3'); $sheet->setCellValue('A3', trim($dateStr));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($customer) {
            $sheet->mergeCells('A4:M4'); $sheet->setCellValue('A4', 'Customer: '.$customer->name);
            $sheet->getStyle('A4')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $headerRow = 5;
        } else { $headerRow = 4; }

        $headers = ['SL','Job No','Bill No','Bill Date','LC No','Invoice No','Net Amount','TDS %','TDS Amt','Vat %','Vat Amt','Total Amt','Remarks'];
        $col = 'A';
        foreach ($headers as $h) { $sheet->setCellValue($col.$headerRow, $h); $col++; }
        $sheet->getStyle('A'.$headerRow.':M'.$headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A6B60']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = $headerRow + 1; $tNet = 0; $tTds = 0; $tVat = 0; $tTotal = 0;
        foreach ($bills as $i => $bill) {
            $jobNos  = $bill->items->pluck('booking.job_no')->filter()->unique()->implode(', ');
            $lcNos   = $bill->items->pluck('booking.lc_no')->filter()->unique()->implode(', ');
            $invNos  = $bill->items->pluck('booking.invoice_no')->filter()->unique()->implode(', ');
            $tNet += $bill->sub_total; $tTds += $bill->tds_amount; $tVat += $bill->vat_amount; $tTotal += $bill->total_amount;
            $sheet->fromArray([$i+1, $jobNos, $bill->bill_no, $bill->bill_date?->format('d M Y'), $lcNos, $invNos, (float)$bill->sub_total, (float)$bill->tds_percent, (float)$bill->tds_amount, (float)$bill->vat_percent, (float)$bill->vat_amount, (float)$bill->total_amount, $bill->note], null, 'A'.$row);
            foreach (['G','I','K','L'] as $c) $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            if ($row % 2 === 0) $sheet->getStyle('A'.$row.':M'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5FAF9');
            $row++;
        }
        $sheet->mergeCells('A'.$row.':F'.$row); $sheet->setCellValue('A'.$row, 'Total ('.$bills->count().' bills)');
        $sheet->setCellValue('G'.$row, $tNet); $sheet->setCellValue('I'.$row, $tTds); $sheet->setCellValue('K'.$row, $tVat); $sheet->setCellValue('L'.$row, $tTotal);
        $sheet->getStyle('A'.$row.':M'.$row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4E8D4']]]);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        foreach (['G','I','K','L'] as $c) $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (['A'=>4,'B'=>16,'C'=>18,'D'=>12,'E'=>20,'F'=>20,'G'=>14,'H'=>8,'I'=>12,'J'=>8,'K'=>12,'L'=>14,'M'=>20] as $c => $w) $sheet->getColumnDimension($c)->setWidth($w);
        if ($row > $headerRow+1) $sheet->getStyle('A'.$headerRow.':M'.$row)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]]);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), 'party-bill-summary-'.now()->format('Ymd-His').'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'max-age=0']);
    }

    // ─────────────────────────── BILL DETAILS ─────────────────────────────

    public function billDetails(Request $request)
    {
        [$items, $customer, $bills] = $this->getBillItems($request);
        return view('nas-freights.reports.bill-details', compact('items', 'customer', 'bills'));
    }

    public function billDetailsPrint(Request $request)
    {
        [$items, $customer, $bills] = $this->getBillItems($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        return view('nas-freights.reports.bill-details-print', compact('items', 'customer', 'bills', 'company'));
    }

    public function billDetailsPdf(Request $request)
    {
        [$items, $customer, $bills] = $this->getBillItems($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        $pdf = Pdf::loadView('nas-freights.reports.bill-details-pdf', compact('items', 'customer', 'bills', 'company'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('bill-details-'.now()->format('Ymd-His').'.pdf');
    }

    public function billDetailsExcel(Request $request)
    {
        [$items, $customer, $bills] = $this->getBillItems($request);
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        $coName  = $company?->name ?? 'NAS Freights And Logistics Ltd.';

        // Bulk load vehicle types
        $vanNos   = $items->pluck('item_code')->filter()->unique();
        $vehicles = NasFreightsVehicle::whereIn('vehicle_number', $vanNos)->pluck('vehicle_type', 'vehicle_number');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Bill Details');

        $sheet->mergeCells('A1:M1'); $sheet->setCellValue('A1', $coName);
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:M2'); $sheet->setCellValue('A2', 'Monthwise Transport Bill Details');
        $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $dateStr = '';
        if ($request->filled('from_date')) $dateStr .= 'From: '.\Carbon\Carbon::parse($request->from_date)->format('d M, Y').'   ';
        if ($request->filled('to_date'))   $dateStr .= 'To: '.\Carbon\Carbon::parse($request->to_date)->format('d M, Y');
        $sheet->mergeCells('A3:M3'); $sheet->setCellValue('A3', trim($dateStr));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($customer) {
            $sheet->mergeCells('A4:M4'); $sheet->setCellValue('A4', 'Customer: '.$customer->name);
            $sheet->getStyle('A4')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $headerRow = 5;
        } else { $headerRow = 4; }

        $headers = ['SL','Bill No','Bill Date','Cover Van No','Vehicle Type','Capacity','Source','Destination','Net Amount','Total Dem.','Vat %','Vat Amt','Total Amt'];
        $col = 'A';
        foreach ($headers as $h) { $sheet->setCellValue($col.$headerRow, $h); $col++; }
        $sheet->getStyle('A'.$headerRow.':M'.$headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A6B60']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = $headerRow + 1; $tNet = 0; $tDem = 0; $tVat = 0; $tTotal = 0;
        foreach ($items as $i => $item) {
            $bill    = $item->bill;
            $bItem   = $item->bookingItem;
            $src     = $bItem?->location_from ?? '';
            $dst     = $bItem?->location_to ?? '';
            $cap     = $bItem?->capacity ?? '';
            $dem     = (float)($item->demurrage_amount ?: 0);
            $vatPct  = (float)($bill?->vat_percent ?? 0);
            $vatAmt  = round($item->line_amount * $vatPct / 100, 2);
            $rowTotal = $item->line_amount + $dem + $vatAmt;
            $vanType = $vehicles[$item->item_code] ?? '';
            $tNet += $item->line_amount; $tDem += $dem; $tVat += $vatAmt; $tTotal += $rowTotal;
            $sheet->fromArray([$i+1, $bill?->bill_no, $bill?->bill_date?->format('d M Y'), $item->item_code, $vanType, $cap, $src, $dst, (float)$item->line_amount, $dem, $vatPct, $vatAmt, $rowTotal], null, 'A'.$row);
            foreach (['I','J','L','M'] as $c) $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            if ($row % 2 === 0) $sheet->getStyle('A'.$row.':M'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5FAF9');
            $row++;
        }
        $sheet->mergeCells('A'.$row.':H'.$row); $sheet->setCellValue('A'.$row, 'Total ('.$items->count().' items)');
        $sheet->setCellValue('I'.$row, $tNet); $sheet->setCellValue('J'.$row, $tDem); $sheet->setCellValue('L'.$row, $tVat); $sheet->setCellValue('M'.$row, $tTotal);
        $sheet->getStyle('A'.$row.':M'.$row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4E8D4']]]);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        foreach (['I','J','L','M'] as $c) $sheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (['A'=>4,'B'=>18,'C'=>12,'D'=>14,'E'=>16,'F'=>10,'G'=>18,'H'=>18,'I'=>14,'J'=>12,'K'=>8,'L'=>12,'M'=>14] as $c => $w) $sheet->getColumnDimension($c)->setWidth($w);
        if ($row > $headerRow+1) $sheet->getStyle('A'.$headerRow.':M'.$row)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]]);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), 'bill-details-'.now()->format('Ymd-His').'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'max-age=0']);
    }

    // ──────────────────────────── HELPERS ─────────────────────────────────

    private function getBookingRows(Request $request): \Illuminate\Support\Collection
    {
        if (!$request->hasAny(['from_date','to_date','customer_id','supplier_id','vehicle','location_from','status'])) {
            return collect();
        }
        $rows = NasFreightsBookingItem::with('booking')
            ->whereHas('booking', function ($q) use ($request) {
                $q->where('branch_id', session('nas_freights_branch_id'));
                if ($request->filled('from_date'))   $q->whereDate('job_date', '>=', $request->from_date);
                if ($request->filled('to_date'))     $q->whereDate('job_date', '<=', $request->to_date);
                if ($request->filled('customer_id')) $q->where('customer_id', $request->customer_id);
                if ($request->filled('status'))      $q->where('status', $request->status);
            })
            ->when($request->filled('supplier_id'),   fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('vehicle'),        fn($q) => $q->where('cover_van_no', 'like', "%{$request->vehicle}%"))
            ->when($request->filled('location_from'),  fn($q) => $q->where('location_from', 'like', "%{$request->location_from}%"))
            ->orderBy('id')
            ->get();

        // Attach billed status: match by booking_id + cover_van_no = booking_id + item_code
        $billedMap = NasFreightsCustomerBillItem::select('booking_id', 'item_code', 'bill_id')
            ->with('bill:id,bill_no')
            ->whereNotNull('booking_id')
            ->get()
            ->keyBy(fn($r) => $r->booking_id . '_' . $r->item_code);

        $rows->each(function ($item) use ($billedMap) {
            $match = $billedMap->get($item->booking_id . '_' . $item->cover_van_no);
            $item->is_billed = (bool) $match;
            $item->bill_no   = $match?->bill?->bill_no;
        });

        return $rows;
    }

    private function getBills(Request $request): array
    {
        if (!$request->hasAny(['from_date','to_date','customer_id','bill_type'])) {
            return [collect(), null];
        }
        $query = NasFreightsCustomerBill::with(['items.booking'])
            ->where('branch_id', session('nas_freights_branch_id'));
        if ($request->filled('from_date'))   $query->whereDate('bill_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->whereDate('bill_date', '<=', $request->to_date);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('bill_type'))   $query->where('bill_type', $request->bill_type);

        $bills    = $query->orderBy('bill_date')->orderBy('bill_no')->get();
        $customer = $request->filled('customer_id')
            ? \App\Models\NasFreights\NasFreightsCustomer::find($request->customer_id)
            : null;

        return [$bills, $customer];
    }

    private function getBillItems(Request $request): array
    {
        [$bills, $customer] = $this->getBills($request);
        if ($bills->isEmpty()) return [collect(), $customer, $bills];

        $billIds = $bills->pluck('id');
        $items   = NasFreightsCustomerBillItem::with('bill')
            ->whereIn('bill_id', $billIds)
            ->orderBy('bill_id')
            ->orderBy('id')
            ->get();

        // booking_item_id FK may be stale — resolve via booking_id + cover_van_no
        $bookingIds   = $items->pluck('booking_id')->filter()->unique();
        $bookingItems = \App\Models\NasFreights\NasFreightsBookingItem::whereIn('booking_id', $bookingIds)
            ->get()
            ->groupBy('booking_id');

        $items->each(function ($item) use ($bookingItems) {
            $candidates = $bookingItems[$item->booking_id] ?? collect();
            $resolved   = $candidates->firstWhere('cover_van_no', $item->item_code)
                       ?? $candidates->first(); // fallback: first item of that booking
            $item->setRelation('bookingItem', $resolved);
        });

        return [$items, $customer, $bills];
    }
}
