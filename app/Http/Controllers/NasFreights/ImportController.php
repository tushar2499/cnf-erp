<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsCustomerBillItem;
use App\Models\NasFreights\NasFreightsSupplierPayment;
use App\Models\NasFreights\NasFreightsVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportController extends Controller
{
    private array $supplierMap = [];
    private array $customerMap = [];

    private function supplierId(string $name): ?int
    {
        if (!$this->supplierMap) {
            $this->supplierMap = DB::table('nas_freights_suppliers')
                ->pluck('id', 'company_name')->map(fn($id) => (int) $id)->toArray();
        }
        if (isset($this->supplierMap[$name])) {
            return $this->supplierMap[$name];
        }
        // Normalize: lowercase, strip trailing punctuation/spaces, collapse spaces
        $norm = fn(string $s) => preg_replace('/\s+/', ' ', strtolower(rtrim(trim($s), '. ')));
        $needle = $norm($name);
        foreach ($this->supplierMap as $key => $id) {
            if ($norm($key) === $needle) return $id;
        }
        return null;
    }

    private function customerId(string $name): ?int
    {
        if (!$this->customerMap) {
            $this->customerMap = DB::table('nas_freights_customers')
                ->pluck('id', 'name')->map(fn($id) => (int) $id)->toArray();
        }
        if (isset($this->customerMap[$name])) {
            return $this->customerMap[$name];
        }
        $norm = fn(string $s) => preg_replace('/\s+/', ' ', strtolower(rtrim(trim($s), '. ')));
        $needle = $norm($name);
        foreach ($this->customerMap as $key => $id) {
            if ($norm($key) === $needle) return $id;
        }
        return null;
    }

    private function parseDate(mixed $val): ?string
    {
        if (!$val) return null;
        if (is_numeric($val)) {
            return ExcelDate::excelToDateTimeObject((float) $val)->format('Y-m-d');
        }
        try {
            return \Carbon\Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function loadRows(string $storedPath): array
    {
        $path = Storage::path($storedPath);
        $reader = IOFactory::createReaderForFile($path);
        return $reader->load($path)->getActiveSheet()->toArray(null, true, true, true);
    }

    // ── Supplier Payments ──────────────────────────────────────────────────

    public function supplierPaymentsIndex()
    {
        return view('nas-freights.import.supplier-payments');
    }

    public function supplierPaymentsPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        $storedPath = $request->file('file')->store('imports');
        try {
            $rows = $this->parseSupplierPaymentRows($this->loadRows($storedPath));
        } catch (\Throwable) {
            Storage::delete($storedPath);
            return back()->withErrors(['file' => 'Could not read this file. If it is a .xls file, open it in Excel, save as .xlsx, then upload the .xlsx version.']);
        }
        return view('nas-freights.import.supplier-payments', [
            'rows'       => $rows,
            'total'      => count($rows),
            'storedPath' => $storedPath,
        ]);
    }

    public function supplierPaymentsImport(Request $request)
    {
        $request->validate(['file_path' => 'required|string']);
        $rows  = $this->parseSupplierPaymentRows($this->loadRows($request->file_path));
        $count = 0;
        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                NasFreightsSupplierPayment::updateOrCreate(['payment_no' => $row['payment_no']], $row);
                $count++;
            }
        });
        Storage::delete($request->file_path);
        return redirect()->route('nas-freights.import.supplier-payments')
            ->with('success', "Imported {$count} supplier payment(s) successfully.");
    }

    private function parseSupplierPaymentRows(array $rawRows): array
    {
        $rows = [];
        $isFirst = true;
        foreach ($rawRows as $r) {
            if ($isFirst) { $isFirst = false; continue; }
            $paymentNo = trim($r['C'] ?? '');
            if (!$paymentNo) continue;
            $supplierName = trim($r['D'] ?? '');
            $rows[] = [
                'payment_date'  => $this->parseDate($r['B']) ?? now()->format('Y-m-d'),
                'payment_no'    => $paymentNo,
                'supplier_name' => $supplierName ?: null,
                'supplier_id'   => $supplierName ? $this->supplierId($supplierName) : null,
                'amount_paid'   => is_numeric($r['F']) ? (float) $r['F'] : 0,
                'note'          => trim($r['I'] ?? '') ?: null,
                'entry_by'      => trim($r['K'] ?? '') ?: null,
            ];
        }
        return $rows;
    }

    // ── Customer Bills ─────────────────────────────────────────────────────

    public function customerBillsIndex()
    {
        return view('nas-freights.import.customer-bills');
    }

    public function customerBillsPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        $storedPath = $request->file('file')->store('imports');
        try {
            $rows = $this->parseCustomerBillRows($this->loadRows($storedPath));
        } catch (\Throwable) {
            Storage::delete($storedPath);
            return back()->withErrors(['file' => 'Could not read this file. If it is a .xls file, open it in Excel, save as .xlsx, then upload the .xlsx version.']);
        }
        return view('nas-freights.import.customer-bills', [
            'rows'       => $rows,
            'total'      => count($rows),
            'storedPath' => $storedPath,
        ]);
    }

    public function customerBillsImport(Request $request)
    {
        $request->validate(['file_path' => 'required|string']);
        $rows  = $this->parseCustomerBillRows($this->loadRows($request->file_path));
        $count = 0;
        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                NasFreightsCustomerBill::updateOrCreate(['bill_no' => $row['bill_no']], $row);
                $count++;
            }
        });
        Storage::delete($request->file_path);
        return redirect()->route('nas-freights.import.customer-bills')
            ->with('success', "Imported {$count} customer bill(s) successfully.");
    }

    private function parseCustomerBillRows(array $rawRows): array
    {
        $statusMap = ['SUBMITTED' => 'Submitted', 'APPROVED' => 'Approved', 'PAID' => 'Paid'];
        $rows = [];
        $isFirst = true;
        foreach ($rawRows as $r) {
            if ($isFirst) { $isFirst = false; continue; }
            $billNo = trim($r['D'] ?? '');
            if (!$billNo) continue;
            $customerName = trim($r['E'] ?? '');
            $amount       = is_numeric($r['I']) ? (float) $r['I'] : 0;
            $rawStatus    = strtoupper(trim($r['K'] ?? ''));
            $billDate     = $this->parseDate($r['C']) ?? now()->format('Y-m-d');
            $rows[] = [
                'bill_no'          => $billNo,
                'delivery_no'      => $billNo,
                'bill_date'        => $billDate,
                'from_date'        => $billDate,
                'to_date'          => $billDate,
                'customer_name'    => $customerName ?: null,
                'customer_id'      => $customerName ? $this->customerId($customerName) : null,
                'customer_address' => trim($r['F'] ?? '') ?: null,
                'note'             => trim($r['G'] ?? '') ?: null,
                'total_amount'     => $amount,
                'sub_total'        => $amount,
                'status'           => $statusMap[$rawStatus] ?? 'Submitted',
                'entry_by'         => trim($r['M'] ?? '') ?: null,
            ];
        }
        return $rows;
    }

    // ── Vehicles ──────────────────────────────────────────────────────────

    public function vehiclesIndex()
    {
        return view('nas-freights.import.vehicles');
    }

    public function vehiclesPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        $storedPath = $request->file('file')->store('imports');
        try {
            $rows = $this->parseVehicleRows($this->loadRows($storedPath));
        } catch (\Throwable) {
            Storage::delete($storedPath);
            return back()->withErrors(['file' => 'Could not read this file. If it is a .xls file, open it in Excel, save as .xlsx, then upload the .xlsx version.']);
        }
        return view('nas-freights.import.vehicles', [
            'rows'       => $rows,
            'total'      => count($rows),
            'storedPath' => $storedPath,
        ]);
    }

    public function vehiclesImport(Request $request)
    {
        $request->validate(['file_path' => 'required|string']);
        $rows  = $this->parseVehicleRows($this->loadRows($request->file_path));
        $count = 0;
        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                NasFreightsVehicle::updateOrCreate(['vehicle_number' => $row['vehicle_number']], $row);
                $count++;
            }
        });
        Storage::delete($request->file_path);
        return redirect()->route('nas-freights.import.vehicles')
            ->with('success', "Imported {$count} vehicle(s) successfully.");
    }

    private function parseVehicleRows(array $rawRows): array
    {
        $rows = [];
        $isFirst = true;
        foreach ($rawRows as $r) {
            if ($isFirst) { $isFirst = false; continue; }
            $vehicleNumber = trim($r['A'] ?? '');
            if (!$vehicleNumber) continue;
            $uom = trim($r['C'] ?? '');
            $rows[] = [
                'vehicle_number'    => $vehicleNumber,
                'vehicle_name'      => trim($r['B'] ?? '') ?: $vehicleNumber,
                'purchase_unit'     => ($uom && strtolower($uom) !== 'null') ? $uom : null,
                'price'             => is_numeric($r['D']) ? (float) $r['D'] : 0,
                'availability_in_so'=> strtoupper(trim($r['E'] ?? '')) === 'YES',
                'availability_in_po'=> strtoupper(trim($r['F'] ?? '')) === 'YES',
                'status'            => 'Active',
                'branch_id'         => 1,
            ];
        }
        return $rows;
    }

    // ── Customer Bill Summary ──────────────────────────────────────────────

    public function customerBillSummaryIndex()
    {
        return view('nas-freights.import.customer-bill-summary');
    }

    public function customerBillSummaryPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        $storedPath = $request->file('file')->store('imports');
        try {
            $parsed = $this->parseCustomerBillSummaryFile($this->loadRows($storedPath));
        ['rows' => $rows, 'customer' => $customer, 'address' => $address, 'from_date' => $fromDate, 'to_date' => $toDate]
                = $parsed;
        } catch (\Throwable) {
            Storage::delete($storedPath);
            return back()->withErrors(['file' => 'Could not read this file. If it is a .xls file, open it in Excel, save as .xlsx, then upload the .xlsx version.']);
        }
        return view('nas-freights.import.customer-bill-summary', [
            'rows'          => $rows,
            'total'         => count($rows),
            'storedPath'    => $storedPath,
            'customer'      => $customer,
            'fromDate'      => $fromDate,
            'toDate'        => $toDate,
            'debugHeaders'  => $parsed['debug_headers'] ?? [],
        ]);
    }

    public function customerBillSummaryImport(Request $request)
    {
        $request->validate(['file_path' => 'required|string']);
        $parsed = $this->parseCustomerBillSummaryFile($this->loadRows($request->file_path));
        $rows   = $parsed['rows'];
        $count  = 0;

        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                $bill = NasFreightsCustomerBill::updateOrCreate(
                    ['bill_no' => $row['bill_no']],
                    [
                        'delivery_no'      => $row['bill_no'],
                        'bill_date'        => $row['bill_date'],
                        'from_date'        => $row['from_date'],
                        'to_date'          => $row['to_date'],
                        'customer_name'    => $row['customer_name'],
                        'customer_id'      => $row['customer_id'],
                        'customer_address' => $row['customer_address'],
                        'tds_percent'      => $row['tds_percent'],
                        'tds_amount'       => $row['tds_amount'],
                        'vat_percent'      => $row['vat_percent'],
                        'vat_amount'       => $row['vat_amount'],
                        'sub_total'        => $row['sub_total'],
                        'total_amount'     => $row['total_amount'],
                        'note'             => $row['note'],
                        'branch_id'        => 1,
                        'status'           => 'Submitted',
                    ]
                );

                $bill->items()->delete();

                foreach ($row['_booking_matches'] as $jobNo => $bookingId) {
                    if (!$bookingId) continue;
                    $booking = NasFreightsBooking::with('items')->find($bookingId);
                    if (!$booking) continue;
                    foreach ($booking->items as $item) {
                        NasFreightsCustomerBillItem::create([
                            'bill_id'          => $bill->id,
                            'booking_id'       => $booking->id,
                            'booking_item_id'  => $item->id,
                            'booking_date'     => $booking->job_date,
                            'delivery_date'    => $booking->delivery_date,
                            'item_code'        => $item->cover_van_no,
                            'item_name'        => $item->cover_van_no,
                            'location'         => trim(($item->location_from ?? '') . ' - ' . ($item->location_to ?? ''), ' -'),
                            'price'            => $item->customer_rate,
                            'line_amount'      => $item->customer_rate,
                            'b_qty'            => 1,
                            'd_qty'            => 1,
                            'due_qty'          => 0,
                            'disc_percent'     => 0,
                            'discount'         => 0,
                            'ait_percent'      => 0,
                            'demurrage_day'    => 0,
                            'demurrage_amount' => 0,
                        ]);
                    }
                    if ($booking->status === 'Draft') {
                        $booking->update(['status' => 'Submitted']);
                    }
                }
                $count++;
            }
        });

        Storage::delete($request->file_path);
        return redirect()->route('nas-freights.import.customer-bill-summary')
            ->with('success', "Imported {$count} customer bill(s) successfully.");
    }

    private function parseCustomerBillSummaryFile(array $rawRows): array
    {
        $customerName    = null;
        $customerAddress = null;
        $fromDate        = null;
        $toDate          = null;
        $colMap          = null;
        $rows            = [];
        $addressLines    = [];
        $foundTo         = false;
        $toRowIndex      = -1;
        $rowIndex        = 0;

        foreach ($rawRows as $r) {
            $vals = array_values($r);
            $rowIndex++;

            // Build header from first non-null cell in row
            $rowText = implode(' ', array_filter(array_map(fn($v) => trim((string) $v), $vals)));

            if ($colMap === null) {
                // Check for billing period
                if (str_contains($rowText, 'Billing Period') || str_contains($rowText, 'From:')) {
                    if (preg_match('/From[:\s]+(\d{1,2}\s+\w+[,\s]+\d{4})/i', $rowText, $m1) &&
                        preg_match('/To[:\s]+(\d{1,2}\s+\w+[,\s]+\d{4})/i', $rowText, $m2)) {
                        try { $fromDate = \Carbon\Carbon::parse(trim($m1[1], ', '))->format('Y-m-d'); } catch (\Exception) {}
                        try { $toDate   = \Carbon\Carbon::parse(trim($m2[1], ', '))->format('Y-m-d'); } catch (\Exception) {}
                    }
                }

                // Check for "To," marker — record which row it was on
                if (!$foundTo) {
                    foreach ($vals as $v) {
                        if (trim((string) $v) === 'To,') { $foundTo = true; $toRowIndex = $rowIndex; break; }
                    }
                }

                // Customer name: first non-empty, non-meta cell on rows AFTER the "To," row
                if ($foundTo && $rowIndex > $toRowIndex && !$customerName) {
                    foreach ($vals as $v) {
                        $s = trim((string) $v);
                        if ($s
                            && !str_contains($s, 'Total Bill')
                            && !str_contains($s, 'Billing Period')
                            && !is_numeric($s)
                        ) {
                            $customerName = $s;
                            break;
                        }
                    }
                } elseif ($foundTo && $customerName && !$fromDate) {
                    // Collect address lines
                    $line = implode(', ', array_filter(array_map(fn($v) => trim((string) $v), $vals)));
                    if ($line) $addressLines[] = $line;
                }

                // Detect header row by scanning for "bill no"
                foreach ($vals as $cell) {
                    if (strtolower(trim((string) $cell)) === 'bill no') {
                        $colMap = [];
                        foreach ($vals as $j => $h) {
                            $key = strtolower(trim(preg_replace('/\s+/', ' ', (string) $h)));
                            $colMap[$key] = $j;
                        }
                        break;
                    }
                }
                continue;
            }

            $get = fn(string $k) => $vals[$colMap[$k] ?? PHP_INT_MAX] ?? null;

            $billNo = trim((string) $get('bill no'));
            if (!$billNo) continue;

            // Parse job numbers — may be multi-line merged cell, comma-separated
            $rawJobNos = trim((string) $get('job no'));
            $jobNos = array_filter(array_map('trim', explode(',', $rawJobNos)));

            $num = fn($v) => is_numeric($clean = str_replace(',', '', (string) ($v ?? ''))) ? (float) $clean : 0;

            $billDate   = $this->parseDate($get('bill date')) ?? now()->format('Y-m-d');
            $netAmount  = $num($get('net amount'));
            $tdsPercent = $num($get('tds %'));
            $tdsAmount  = $num($get('tds amt'));
            $vatPercent = $num($get('vat %'));
            $vatAmount  = $num($get('vat amt'));
            $totalAmt   = $num($get('total amt'));

            $customerId = $customerName ? $this->customerId($customerName) : null;

            // Resolve booking IDs per job_no
            $bookingMatches = [];
            foreach ($jobNos as $jn) {
                $booking = NasFreightsBooking::select('id', 'customer_id')->where('job_no', $jn)->first();
                $bookingMatches[$jn] = $booking?->id;
                // Fallback: derive customer_id from booking if name lookup failed
                if (!$customerId && $booking?->customer_id) {
                    $customerId = $booking->customer_id;
                }
            }

            $rows[] = [
                'bill_no'           => $billNo,
                'bill_date'         => $billDate,
                'from_date'         => $fromDate ?? $billDate,
                'to_date'           => $toDate   ?? $billDate,
                'customer_name'     => $customerName,
                'customer_id'       => $customerId,
                'customer_address'  => $customerAddress,
                'tds_percent'       => $tdsPercent,
                'tds_amount'        => $tdsAmount,
                'vat_percent'       => $vatPercent,
                'vat_amount'        => $vatAmount,
                'sub_total'         => $netAmount,
                'total_amount'      => $totalAmt ?: $netAmount,
                'note'              => trim((string) $get('remarks')) ?: null,
                '_job_nos'          => array_values($jobNos),
                '_booking_matches'  => $bookingMatches,
            ];
        }

        $customerAddress = implode(', ', array_filter($addressLines)) ?: null;
        // Backfill address into rows
        foreach ($rows as &$row) { $row['customer_address'] = $customerAddress; }
        unset($row);

        return [
            'rows'      => $rows,
            'customer'  => $customerName,
            'address'   => $customerAddress,
            'from_date' => $fromDate,
            'to_date'   => $toDate,
            'debug_headers' => $colMap ? array_keys($colMap) : [],
        ];
    }

    // ── Booking Updates ────────────────────────────────────────────────────

    public function bookingUpdatesIndex()
    {
        return view('nas-freights.import.booking-updates');
    }

    public function bookingUpdatesPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        $storedPath = $request->file('file')->store('imports');
        try {
            $rows = $this->parseBookingUpdateRows($this->loadRows($storedPath));
        } catch (\Throwable) {
            Storage::delete($storedPath);
            return back()->withErrors(['file' => 'Could not read this file. If it is a .xls file, open it in Excel, save as .xlsx, then upload the .xlsx version.']);
        }
        return view('nas-freights.import.booking-updates', [
            'rows'       => $rows,
            'total'      => count($rows),
            'storedPath' => $storedPath,
        ]);
    }

    public function bookingUpdatesImport(Request $request)
    {
        $request->validate(['file_path' => 'required|string']);
        $rows  = $this->parseBookingUpdateRows($this->loadRows($request->file_path));
        $count = 0;
        $skipped = 0;
        DB::transaction(function () use ($rows, &$count, &$skipped) {
            foreach ($rows as $row) {
                $jobNo = $row['job_no'];
                unset($row['job_no']);
                $affected = NasFreightsBooking::where('job_no', $jobNo)->update($row);
                $affected ? $count++ : $skipped++;
            }
        });
        Storage::delete($request->file_path);
        return redirect()->route('nas-freights.import.booking-updates')
            ->with('success', "Updated {$count} booking(s). {$skipped} job no(s) not found.");
    }

    private function parseBookingUpdateRows(array $rawRows): array
    {
        $statusMap = ['SUBMITTED' => 'Submitted', 'APPROVED' => 'Approved', 'REJECTED' => 'Rejected', 'PAID' => 'Paid'];
        $rows      = [];
        $isFirst   = true;
        foreach ($rawRows as $r) {
            if ($isFirst) { $isFirst = false; continue; }
            $jobNo = trim($r['A'] ?? '');
            if (!$jobNo) continue;
            $deliveryMap    = ['PENDING' => 'Pending', 'PARTIALLY_DELIVERED' => 'Partially Delivered', 'FULLY_DELIVERED' => 'Fully Delivered'];
            $rawStatus      = strtoupper(trim($r['P'] ?? ''));
            $rawDelivery    = strtoupper(trim($r['Q'] ?? ''));
            $baseAmount     = is_numeric($r['I']) ? (float) $r['I'] : 0;
            $aitAmount      = is_numeric($r['J']) ? (float) $r['J'] : 0;
            $tdsAmount      = is_numeric($r['K']) ? (float) $r['K'] : 0;
            $vatAmount      = is_numeric($r['L']) ? (float) $r['L'] : 0;
            $rows[] = [
                'job_no'           => $jobNo,
                'delivery_date'    => $this->parseDate($r['C']) ?? null,
                'qty'              => is_numeric($r['H']) ? (float) $r['H'] : 0,
                'ait_amount'       => $aitAmount,
                'ait_percent'      => $baseAmount > 0 ? round($aitAmount / $baseAmount * 100, 4) : 0,
                'tds_amount'       => $tdsAmount,
                'tds_percent'      => $baseAmount > 0 ? round($tdsAmount / $baseAmount * 100, 4) : 0,
                'vat_amount'       => $vatAmount,
                'vat_percent'      => $baseAmount > 0 ? round($vatAmount / $baseAmount * 100, 4) : 0,
                'total_amount'     => is_numeric($r['M']) ? (float) $r['M'] : 0,
                'discount'         => is_numeric($r['N']) ? (float) $r['N'] : 0,
                'forfeited_amount' => is_numeric($r['O']) ? (float) $r['O'] : 0,
                'status'           => $statusMap[$rawStatus] ?? 'Submitted',
                'delivery_status'  => $deliveryMap[$rawDelivery] ?? null,
                'note'             => trim($r['R'] ?? '') ?: null,
                'sales_type'       => trim($r['S'] ?? '') ?: null,
            ];
        }
        return $rows;
    }

    // ── Bookings ───────────────────────────────────────────────────────────

    public function bookingsIndex()
    {
        return view('nas-freights.import.bookings');
    }

    public function bookingsPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        $storedPath = $request->file('file')->store('imports');
        try {
            $rows = $this->parseBookingRows($this->loadRows($storedPath));
        } catch (\Throwable) {
            Storage::delete($storedPath);
            return back()->withErrors(['file' => 'Could not read this file. If it is a .xls file, open it in Excel, save as .xlsx, then upload the .xlsx version.']);
        }
        return view('nas-freights.import.bookings', [
            'rows'       => $rows,
            'total'      => count($rows),
            'storedPath' => $storedPath,
            'headers'    => $this->detectedHeaders,
        ]);
    }

    public function bookingsImport(Request $request)
    {
        $request->validate(['file_path' => 'required|string']);
        $rows = $this->parseBookingRows($this->loadRows($request->file_path));

        // Group flat rows by job_no — one booking may have multiple items (suppliers)
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['job_no']][] = $row;
        }

        $count = 0;
        DB::transaction(function () use ($grouped, &$count) {
            foreach ($grouped as $jobNo => $jobRows) {
                $first       = $jobRows[0];
                $totalAmount = array_sum(array_column($jobRows, '_customer_rate'));

                $jobDate = $first['job_date'] ?? now()->format('Y-m-d');
                $booking = NasFreightsBooking::updateOrCreate(
                    ['job_no' => $jobNo],
                    [
                        'booking_prefix'    => 'CORPORATE_SALES',
                        'sales_type'        => 'DISTRIBUTION',
                        'job_date'          => $jobDate,
                        'goods_name'        => '-',
                        'delivery_date'     => $jobDate,
                        'customer_name'     => $first['customer_name'],
                        'customer_id'       => $first['customer_id'],
                        'sales_person_name' => $first['sales_person_name'],
                        'cover_van_no'      => $first['cover_van_no'],
                        'note'              => $first['note'],
                        'entry_by'          => $first['entry_by'],
                        'total_amount'      => $totalAmount,
                        'status'            => 'Submitted',
                        'branch_id'         => 1,
                    ]
                );

                // Delete existing items so re-import always gets fresh supplier data
                NasFreightsBookingItem::where('booking_id', $booking->id)->delete();

                foreach ($jobRows as $row) {
                    $item = $row['_item'];

                    // Auto-create vehicle if not already in master table; always sync supplier
                    if (!empty($item['cover_van_no'])) {
                        $vehicle = NasFreightsVehicle::firstOrCreate(
                            ['vehicle_number' => $item['cover_van_no']],
                            ['vehicle_name' => $item['cover_van_no'], 'branch_id' => 1, 'status' => 'Active']
                        );
                        if (!empty($item['supplier_id']) || !empty($item['supplier_name'])) {
                            $vehicle->update([
                                'supplier_id'   => $item['supplier_id'],
                                'supplier_name' => $item['supplier_name'],
                            ]);
                        }
                    }

                    NasFreightsBookingItem::create(
                        array_merge($item, ['booking_id' => $booking->id])
                    );
                }
                $count++;
            }
        });

        Storage::delete($request->file_path);
        return redirect()->route('nas-freights.import.bookings')
            ->with('success', "Imported {$count} booking(s) successfully.");
    }

    public array $detectedHeaders = [];

    private function parseBookingRows(array $rawRows): array
    {
        $rows   = [];
        $colMap = null; // built dynamically from header row

        foreach ($rawRows as $r) {
            $vals = array_values($r);

            if ($colMap === null) {
                // Scan every cell in this row looking for "job no"
                foreach ($vals as $cell) {
                    if (strtolower(trim((string) $cell)) === 'job no') {
                        // Build name→index map from this header row
                        $colMap = [];
                        foreach ($vals as $j => $h) {
                            $key = strtolower(trim(preg_replace('/\s+/', ' ', (string) $h)));
                            $colMap[$key] = $j;
                        }
                        $this->detectedHeaders = array_keys($colMap);
                        break;
                    }
                }
                continue; // always skip the header row itself
            }

            $get = fn(string $k) => $vals[$colMap[$k] ?? PHP_INT_MAX] ?? null;

            $jobNo = trim((string) $get('job no'));
            if (!$jobNo) continue;

            $customerName = trim((string) $get('customer'));
            $supplierName = trim((string) ($get('supplier') ?? $get('supllier')));
            $location     = trim((string) $get('location'));
            $locParts     = explode(' - ', $location, 2);
            $coverVan     = trim((string) $get('cover van details')) ?: null;
            $supplierRate = is_numeric($get('supplier rate')) ? (float) $get('supplier rate') : 0;
            $customerRate = is_numeric($get('customer rate')) ? (float) $get('customer rate') : 0;

            $rows[] = [
                'job_no'            => $jobNo,
                'job_date'          => $this->parseDate($get('job date')),
                'customer_name'     => $customerName ?: null,
                'customer_id'       => $customerName ? $this->customerId($customerName) : null,
                'sales_person_name' => trim((string) $get('sales person')) ?: null,
                'cover_van_no'      => $coverVan,
                'note'              => trim((string) $get('remarks')) ?: null,
                'entry_by'          => trim((string) $get('entry by')) ?: null,
                '_customer_rate'    => $customerRate,
                '_item' => [
                    'supplier_name' => $supplierName ?: null,
                    'supplier_id'   => $supplierName ? $this->supplierId($supplierName) : null,
                    'cover_van_no'  => $coverVan,
                    'supplier_rate' => $supplierRate,
                    'customer_rate' => $customerRate,
                    'amount'        => $customerRate,
                    'qty'           => 1,
                    'location_from' => $locParts[0] ?? null,
                    'location_to'   => $locParts[1] ?? null,
                ],
            ];
        }
        return $rows;
    }
}
