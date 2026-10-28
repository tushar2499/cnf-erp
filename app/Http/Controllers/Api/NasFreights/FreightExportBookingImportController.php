<?php

namespace App\Http\Controllers\Api\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\NasFreights\NasFreightsContainerType;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsOverseasAgent;
use App\Models\NasFreights\NasFreightsPackageType;
use App\Models\NasFreights\NasFreightsShippingCarrier;
use App\Models\NasFreights\NasFreightsSupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FreightExportBookingImportController extends Controller
{
    private const INCOTERMS = ['EXW', 'FCA', 'FAS', 'FOB', 'CFR', 'CIF', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP'];

    private const PACKAGE_ALIASES = ['cartoon' => 'carton', 'cartoons' => 'carton', 'cartons' => 'carton', 'ctn' => 'carton', 'ctns' => 'carton'];

    private const HEADER_FIELDS = [
        'bookingno'        => 'booking_no',
        'bookingdate'      => 'booking_date',
        'salesperson'      => 'salesperson',
        'customerexporter' => 'customer',
        'overseasagent'    => 'overseas_agent',
        'shippingcarrier'  => 'shipping_carrier',
        'service'          => 'service_type',
        'incoterms'        => 'incoterms',
        'currency'         => 'currency',
        'pol'              => 'pol',
        'pod'              => 'pod',
        'placeofreceipt'   => 'place_of_receipt',
        'placeofdelivery'  => 'place_of_delivery',
        'vessel'           => 'vessel_name',
        'voyageno'         => 'voyage_no',
        'exportblno'       => 'export_bl_no',
        'bookingnoteno'    => 'booking_note_no',
        'etd'              => 'etd',
        'eta'              => 'eta',
        'type'             => 'type',
        'sizepackage'      => 'size_package',
        'containerno'      => 'container_no',
        'sealno'           => 'seal_no',
        'hscode'           => 'hs_code',
        'commodity'        => 'commodity',
        'qty'              => 'qty',
        'weight'           => 'weight',
        'cbm'              => 'cbm',
        'origin'           => 'origin',
        'dg'               => 'dg',
        'special'          => 'special',
        'status'           => 'status',
        'notes'            => 'notes',
        'commodity2'       => 'commodity2',
        'covervanno'       => 'cover_van_no',
        'challanno'        => 'challan_no',
        'capacity'         => 'capacity',
        'supplier'         => 'supplier',
        'qty3'             => 'transport_qty',
        'suprate'          => 'supplier_rate',
        'cusrate'          => 'customer_rate',
        'demrrdays'        => 'demurrage_days',
        'cusdemurrage'     => 'cus_demurrage',
        'supdemurrage'     => 'sup_demurrage',
        'amount'           => 'amount',
        'from'             => 'location_from',
        'to'               => 'location_to',
    ];

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        try {
            $rows = $this->parseFile($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $summary = ['total' => count($rows), 'new' => 0, 'existing' => 0, 'duplicate' => 0, 'invalid' => 0];
        foreach ($rows as $row) {
            match (true) {
                $row['errors'] !== []     => $summary['invalid']++,
                $row['exists']            => $summary['existing']++,
                $row['duplicate_in_file'] => $summary['duplicate']++,
                default                   => $summary['new']++,
            };
        }

        return response()->json(['summary' => $summary, 'preview' => $rows]);
    }

    public function import(Request $request): JsonResponse
    {
        if ($request->hasFile('file')) {
            return $this->importFromFile($request);
        }

        return $this->importFromRows($request);
    }

    private function importFromFile(Request $request): JsonResponse
    {
        $this->validateFile($request);

        try {
            $rows = $this->parseFile($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $skipped = ['existing' => 0, 'duplicate' => 0, 'invalid' => []];
        $importable = [];
        foreach ($rows as $row) {
            if ($row['errors'] !== []) {
                $skipped['invalid'][] = [
                    'sheet'             => $row['sheet'],
                    'row'               => $row['row'],
                    'export_booking_no' => $row['export_booking_no'],
                    'errors'            => $row['errors'],
                ];
            } elseif ($row['exists']) {
                $skipped['existing']++;
            } elseif ($row['duplicate_in_file']) {
                $skipped['duplicate']++;
            } else {
                $importable[] = $row;
            }
        }

        return $this->persistAndRespond($importable, (int) $request->input('branch_id'), $skipped);
    }

    private function importFromRows(Request $request): JsonResponse
    {
        $request->validate(
            [
                'branch_id'                => 'required|integer|exists:nas_freights_branches,id',
                'rows'                     => 'required|array|min:1',
                'rows.*.export_booking_no' => 'required|string|max:255',
                'rows.*.booking_date'      => 'required|date',
                'rows.*.service_type'      => 'required|in:'.implode(',', NasFreightsFreightExportBooking::serviceTypes()),
                'rows.*.status'            => 'nullable|in:'.implode(',', NasFreightsFreightExportBooking::statuses()),
                'rows.*.items'             => 'nullable|array',
                'rows.*.transport_items'   => 'nullable|array',
            ],
            [
                'rows.required'                     => 'No rows found. Please upload a file first and confirm the preview.',
                'rows.*.export_booking_no.required' => 'Row :position: Booking No is required.',
                'rows.*.booking_date.required'      => 'Row :position: Booking Date is required.',
            ]
        );

        return $this->persistAndRespond($request->input('rows'), (int) $request->input('branch_id'), ['existing' => 0, 'duplicate' => 0, 'invalid' => []]);
    }

    private function persistAndRespond(array $rows, int $branchId, array $skipped): JsonResponse
    {
        if ($rows === []) {
            return response()->json([
                'message'  => 'No new export bookings to import.',
                'inserted' => 0,
                'skipped'  => $skipped,
            ]);
        }

        $inserted = $this->persistRows($rows, $branchId);
        $skipped['existing'] += count($rows) - $inserted;

        return response()->json([
            'message'  => "{$inserted} export booking(s) imported successfully.",
            'inserted' => $inserted,
            'skipped'  => $skipped,
        ]);
    }

    private function validateFile(Request $request): void
    {
        $request->validate(
            [
                'branch_id' => 'required|integer|exists:nas_freights_branches,id',
                'file'      => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 5 MB.',
            ]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseFile(Request $request): array
    {
        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $lookups = $this->buildLookups();

        $existingNos = NasFreightsFreightExportBooking::pluck('export_booking_no')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

        $seen = [];
        $parsed = [];
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $fieldColumns = null;

            foreach ($sheet->toArray(null, true, false, false) as $i => $cells) {
                if ($fieldColumns === null) {
                    if ($i > 15) {
                        break;
                    }
                    $candidate = $this->headerColumns($cells);
                    if (isset($candidate['booking_no'])) {
                        $fieldColumns = $candidate;
                    }

                    continue;
                }

                $data = [];
                foreach ($fieldColumns as $field => $col) {
                    $data[$field] = $this->clean($cells[$col] ?? null);
                }

                if ($data['booking_no'] === null) {
                    continue;
                }

                $row = $this->mapRow($data, $sheet->getTitle(), $i + 1, $lookups);
                $key = strtolower($row['export_booking_no']);
                $row['exists'] = isset($existingNos[$key]);
                $row['duplicate_in_file'] = isset($seen[$key]);
                $seen[$key] = true;
                $parsed[] = $row;
            }
        }

        return $parsed;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<string, int>
     */
    private function headerColumns(array $cells): array
    {
        $columns = [];
        foreach ($cells as $col => $label) {
            $key = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $label));
            if (isset(self::HEADER_FIELDS[$key]) && ! isset($columns[self::HEADER_FIELDS[$key]])) {
                $columns[self::HEADER_FIELDS[$key]] = $col;
            }
        }

        return $columns;
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value)) {
            $value = rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.');
        }

        $value = trim((string) $value);

        return in_array($value, ['', '—', '–', '-'], true) ? null : $value;
    }

    private function key(?string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    private function number(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return preg_match('/-?\d+(\.\d+)?/', str_replace(',', '', $value), $m) ? (float) $m[0] : null;
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        foreach (['n/j/Y', 'n/j/y', 'd-M-Y', 'd-M-y', 'Y-m-d', 'j M Y', 'j-n-Y'] as $format) {
            $date = \DateTime::createFromFormat('!'.$format, $value);
            $errors = \DateTime::getLastErrors();

            if ($date && (! $errors || $errors['warning_count'] + $errors['error_count'] === 0) && (int) $date->format('Y') >= 1900) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLookups(): array
    {
        $byName = fn ($query, string $nameColumn) => $query->get(['id', $nameColumn])
            ->mapWithKeys(fn ($m) => [$this->key($m->{$nameColumn}) => $m->id])->all();

        return [
            'customers'     => $byName(NasFreightsCustomer::query(), 'name'),
            'agents'        => $byName(NasFreightsOverseasAgent::query(), 'name'),
            'carriers'      => $byName(NasFreightsShippingCarrier::query(), 'name'),
            'suppliers'     => $byName(NasFreightsSupplier::query(), 'company_name'),
            'employees'     => Employee::where('is_active', true)->get(['id', 'name'])
                ->map(fn ($e) => ['id' => $e->id, 'key' => $this->key($e->name)])->all(),
            'packageTypes'   => NasFreightsPackageType::active()->pluck('name')->all(),
            'containerSizes' => NasFreightsContainerType::active()->pluck('name')->all(),
        ];
    }

    /**
     * @param  array<string, string|null>  $d
     * @param  array<string, mixed>  $lookups
     * @return array<string, mixed>
     */
    private function mapRow(array $d, string $sheet, int $rowNo, array $lookups): array
    {
        $warnings = [];
        $errors = [];

        $bookingNo = mb_substr($d['booking_no'], 0, 255);

        $bookingDate = $this->parseDate($d['booking_date']);
        if ($bookingDate === null) {
            $errors[] = $d['booking_date'] === null
                ? 'Booking Date is empty.'
                : "Booking Date '{$d['booking_date']}' is not a valid date.";
        }

        $customerKey = $this->key($d['customer']);
        $customerId = $d['customer'] !== null ? ($lookups['customers'][$customerKey] ?? null) : null;
        if ($d['customer'] !== null && $customerId === null) {
            $warnings[] = "Customer '{$d['customer']}' will be created.";
        }

        $agentId = $d['overseas_agent'] !== null ? ($lookups['agents'][$this->key($d['overseas_agent'])] ?? null) : null;
        if ($d['overseas_agent'] !== null && $agentId === null) {
            $warnings[] = "Overseas agent '{$d['overseas_agent']}' will be created.";
        }

        $carrierId = $d['shipping_carrier'] !== null ? ($lookups['carriers'][$this->key($d['shipping_carrier'])] ?? null) : null;
        if ($d['shipping_carrier'] !== null && $carrierId === null) {
            $warnings[] = "Shipping carrier '{$d['shipping_carrier']}' will be created.";
        }

        $salespersonId = $this->resolveSalesperson($d['salesperson'], $sheet, $lookups, $warnings);

        $serviceType = $this->matchName($d['service_type'], NasFreightsFreightExportBooking::serviceTypes());
        if ($serviceType === null) {
            $serviceType = 'Other';
            if ($d['service_type'] !== null) {
                $warnings[] = "Service '{$d['service_type']}' is not recognised; saved as 'Other'.";
            }
        }

        $incoterms = $d['incoterms'] !== null ? strtoupper($d['incoterms']) : null;
        if ($incoterms !== null && ! in_array($incoterms, self::INCOTERMS, true)) {
            $warnings[] = "Incoterms '{$d['incoterms']}' is not recognised; left blank.";
            $incoterms = null;
        }

        $currency = $d['currency'] !== null ? strtoupper($d['currency']) : null;
        if ($currency === null || ! preg_match('/^[A-Z]{3}$/', $currency)) {
            if ($currency !== null) {
                $warnings[] = "Currency '{$d['currency']}' is not a currency code; defaulted to BDT.";
            }
            $currency = 'BDT';
        }

        $status = $this->matchName($d['status'], NasFreightsFreightExportBooking::statuses()) ?? 'Draft';

        $hsCodes = $d['hs_code'] !== null
            ? array_values(array_unique(array_filter(array_map('trim', preg_split('/[,;\s]+/', $d['hs_code'])))))
            : [];

        $item = $this->buildItem($d, $lookups, $warnings);
        $transport = $this->buildTransportItem($d, $lookups, $warnings);

        return [
            'sheet'                  => $sheet,
            'row'                    => $rowNo,
            'export_booking_no'      => $bookingNo,
            'booking_date'           => $bookingDate,
            'salesperson_id'         => $salespersonId,
            'customer_id'            => $customerId,
            '_customer_name'         => $d['customer'],
            'overseas_agent_id'      => $agentId,
            '_overseas_agent_name'   => $d['overseas_agent'],
            'shipping_carrier_id'    => $carrierId,
            '_shipping_carrier_name' => $d['shipping_carrier'],
            'service_type'           => $serviceType,
            'incoterms'              => $incoterms,
            'currency'               => $currency,
            'pol'                    => $d['pol'],
            'pod'                    => $d['pod'],
            'place_of_receipt'       => $d['place_of_receipt'],
            'place_of_delivery'      => $d['place_of_delivery'],
            'commodity_description'  => $d['commodity'] ?? $d['commodity2'],
            'hs_codes'               => $hsCodes,
            'vessel_name'            => $d['vessel_name'],
            'voyage_no'              => $d['voyage_no'],
            'export_bl_no'           => $d['export_bl_no'],
            'booking_note_no'        => $d['booking_note_no'],
            'etd'                    => $this->parseDate($d['etd']),
            'eta'                    => $this->parseDate($d['eta']),
            'status'                 => $status,
            'remarks'                => $d['notes'],
            'items'                  => $item ? [$item] : [],
            'transport_items'        => $transport ? [$transport] : [],
            'warnings'               => $warnings,
            'errors'                 => $errors,
        ];
    }

    /**
     * Excel salesperson cell wins; otherwise the sheet name (e.g. "JAHIR") is matched to one central employee.
     *
     * @param  array<string, mixed>  $lookups
     * @param  array<int, string>  $warnings
     */
    private function resolveSalesperson(?string $cell, string $sheet, array $lookups, array &$warnings): ?int
    {
        $needle = $this->key($cell ?? $sheet);
        $matches = array_values(array_filter(
            $lookups['employees'],
            fn ($e) => $e['key'] === $needle || ($cell === null && str_contains($e['key'], $needle))
        ));

        if (count($matches) !== 1) {
            $warnings[] = "Salesperson '".($cell ?? $sheet)."' ".(count($matches) === 0 ? 'not found' : 'matches several employees').'; left blank.';

            return null;
        }

        return $matches[0]['id'];
    }

    /**
     * @param  array<string, string|null>  $d
     * @param  array<string, mixed>  $lookups
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>|null
     */
    private function buildItem(array $d, array $lookups, array &$warnings): ?array
    {
        $cargoFields = ['type', 'size_package', 'container_no', 'seal_no', 'hs_code', 'commodity', 'qty', 'weight', 'cbm', 'origin', 'dg', 'special'];
        if (collect($cargoFields)->every(fn ($f) => $d[$f] === null)) {
            return null;
        }

        $typeKey = strtolower((string) $d['type']);
        $explicitType = in_array($typeKey, ['container', 'package'], true);
        $sizeOrPackage = $d['size_package'];
        $sizeIsCount = $sizeOrPackage !== null && is_numeric($sizeOrPackage);

        $itemType = $explicitType ? $typeKey : ($d['container_no'] !== null ? 'container' : 'package');

        $containerSize = null;
        $packageType = null;
        $packageQty = null;

        if ($itemType === 'container') {
            $containerSize = $sizeOrPackage !== null && ! $sizeIsCount ? $sizeOrPackage : null;
            $packageType = $explicitType ? null : $this->matchPackageType($d['type'], $lookups, $warnings);
            $packageQty = $sizeIsCount ? (int) $sizeOrPackage : null;
        } else {
            $packageType = $this->matchPackageType($explicitType ? $sizeOrPackage : $d['type'], $lookups, $warnings);
        }

        $weightUnit = 'KG';
        if ($d['weight'] !== null && preg_match('/\b(mt|ton|tons|lbs?)\b/i', $d['weight'], $m)) {
            $weightUnit = str_starts_with(strtolower($m[1]), 'l') ? 'LB' : 'MT';
        }

        $quantity = (int) ($this->number($d['qty']) ?? 0);
        if ($quantity < 1) {
            $quantity = $packageQty ?: 1;
        }

        return [
            'item_type'          => $itemType,
            'container_size'     => $containerSize !== null ? ($this->matchName($containerSize, $lookups['containerSizes']) ?? $containerSize) : null,
            'container_no'       => $d['container_no'],
            'seal_no'            => $d['seal_no'],
            'package_type'       => $packageType,
            'package_qty'        => $packageQty,
            'hs_code'            => $d['hs_code'],
            'commodity'          => mb_substr($d['commodity'] ?? $d['commodity2'] ?? '', 0, 255) ?: null,
            'quantity'           => $quantity,
            'gross_weight'       => $this->number($d['weight']),
            'weight_unit'        => $weightUnit,
            'volume_cbm'         => $this->number($d['cbm']),
            'country_of_origin'  => $d['origin'],
            'is_dangerous_goods' => $d['dg'] !== null && in_array(strtolower($d['dg']), ['yes', 'y', 'true', '1', 'dg'], true),
            'special_handling'   => $d['special'],
        ];
    }

    /**
     * @param  array<string, mixed>  $lookups
     * @param  array<int, string>  $warnings
     */
    private function matchPackageType(?string $value, array $lookups, array &$warnings): ?string
    {
        if ($value === null || is_numeric($value)) {
            return null;
        }

        $alias = self::PACKAGE_ALIASES[strtolower($value)] ?? $value;
        $match = $this->matchName($alias, $lookups['packageTypes']) ?? $this->matchName(rtrim($alias, 'sS'), $lookups['packageTypes']);

        if ($match === null) {
            $warnings[] = "Package type '{$value}' is not in the package type list; saved as entered.";
        }

        return $match ?? $value;
    }

    /**
     * @param  array<int, string>  $options
     */
    private function matchName(?string $value, array $options): ?string
    {
        if ($value === null) {
            return null;
        }

        foreach ($options as $option) {
            if (strcasecmp($option, trim($value)) === 0) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string|null>  $d
     * @param  array<string, mixed>  $lookups
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>|null
     */
    private function buildTransportItem(array $d, array $lookups, array &$warnings): ?array
    {
        $transportFields = ['cover_van_no', 'challan_no', 'capacity', 'supplier', 'supplier_rate', 'customer_rate', 'amount'];
        if (collect($transportFields)->every(fn ($f) => $d[$f] === null)) {
            return null;
        }

        if ($d['cover_van_no'] === null) {
            $warnings[] = 'Transport details ignored: Cover Van No is empty.';

            return null;
        }

        $supplierId = $d['supplier'] !== null ? ($lookups['suppliers'][$this->key($d['supplier'])] ?? null) : null;
        if ($d['supplier'] !== null && $supplierId === null) {
            $warnings[] = "Supplier '{$d['supplier']}' not found; saved as name only.";
        }

        $qty = $this->number($d['transport_qty']) ?? 1;
        $customerRate = $this->number($d['customer_rate']) ?? 0;

        return [
            'cover_van_no'         => $d['cover_van_no'],
            'challan_no'           => $d['challan_no'],
            'capacity'             => $d['capacity'],
            'supplier_id'          => $supplierId,
            'supplier_name'        => $d['supplier'],
            'qty'                  => $qty,
            'supplier_rate'        => $this->number($d['supplier_rate']) ?? 0,
            'customer_rate'        => $customerRate,
            'demurrage_days'       => (int) ($this->number($d['demurrage_days']) ?? 0),
            'cus_demurrage_charge' => $this->number($d['cus_demurrage']) ?? 0,
            'sup_demurrage_charge' => $this->number($d['sup_demurrage']) ?? 0,
            'amount'               => $this->number($d['amount']) ?? $qty * $customerRate,
            'location_from'        => $d['location_from'],
            'location_to'          => $d['location_to'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function persistRows(array $rows, int $branchId): int
    {
        $inserted = 0;
        $created = ['customer' => [], 'agent' => [], 'carrier' => []];

        DB::transaction(function () use ($rows, $branchId, &$inserted, &$created) {
            foreach ($rows as $row) {
                $bookingNo = trim($row['export_booking_no'] ?? '');
                if ($bookingNo === '' || NasFreightsFreightExportBooking::where('export_booking_no', $bookingNo)->exists()) {
                    continue;
                }

                $customerId = $row['customer_id'] ?? $this->createOnce($created['customer'], $row['_customer_name'] ?? null, fn ($name) => $this->findOrCreateCustomer($name, $branchId));
                $agentId = $row['overseas_agent_id'] ?? $this->createOnce($created['agent'], $row['_overseas_agent_name'] ?? null, fn ($name) => $this->findOrCreateAgent($name, $row['pod'] ?? null));
                $carrierId = $row['shipping_carrier_id'] ?? $this->createOnce($created['carrier'], $row['_shipping_carrier_name'] ?? null, fn ($name) => $this->findOrCreateCarrier($name));

                $transportItems = $row['transport_items'] ?? [];

                $booking = NasFreightsFreightExportBooking::create([
                    'export_booking_no'     => $bookingNo,
                    'branch_id'             => $branchId,
                    'customer_id'           => $customerId,
                    'salesperson_id'        => $row['salesperson_id'] ?? null,
                    'overseas_agent_id'     => $agentId,
                    'shipping_carrier_id'   => $carrierId,
                    'booking_date'          => $row['booking_date'],
                    'service_type'          => $row['service_type'],
                    'incoterms'             => $row['incoterms'] ?? null,
                    'currency'              => $row['currency'] ?? 'BDT',
                    'pol'                   => $row['pol'] ?? null,
                    'pod'                   => $row['pod'] ?? null,
                    'place_of_receipt'      => $row['place_of_receipt'] ?? null,
                    'place_of_delivery'     => $row['place_of_delivery'] ?? null,
                    'commodity_description' => $row['commodity_description'] ?? null,
                    'hs_codes'              => ($row['hs_codes'] ?? []) ?: null,
                    'vessel_name'           => $row['vessel_name'] ?? null,
                    'voyage_no'             => $row['voyage_no'] ?? null,
                    'export_bl_no'          => $row['export_bl_no'] ?? null,
                    'booking_note_no'       => $row['booking_note_no'] ?? null,
                    'etd'                   => $row['etd'] ?? null,
                    'eta'                   => $row['eta'] ?? null,
                    'status'                => $row['status'] ?? 'Draft',
                    'remarks'               => $row['remarks'] ?? null,
                    'transport_amount'      => collect($transportItems)->sum(fn ($t) => (float) ($t['amount'] ?? 0)),
                ]);

                foreach ($row['items'] ?? [] as $item) {
                    $booking->items()->create([
                        'item_type'          => $item['item_type'] ?? 'package',
                        'container_size'     => $item['container_size'] ?? null,
                        'container_no'       => $item['container_no'] ?? null,
                        'seal_no'            => $item['seal_no'] ?? null,
                        'package_type'       => $item['package_type'] ?? null,
                        'package_qty'        => $item['package_qty'] ?? null,
                        'hs_code'            => $item['hs_code'] ?? null,
                        'commodity'          => $item['commodity'] ?? null,
                        'quantity'           => max(1, (int) ($item['quantity'] ?? 1)),
                        'gross_weight'       => $item['gross_weight'] ?? null,
                        'weight_unit'        => $item['weight_unit'] ?? 'KG',
                        'volume_cbm'         => $item['volume_cbm'] ?? null,
                        'country_of_origin'  => $item['country_of_origin'] ?? null,
                        'is_dangerous_goods' => ! empty($item['is_dangerous_goods']),
                        'special_handling'   => $item['special_handling'] ?? null,
                    ]);
                }

                foreach ($transportItems as $transport) {
                    $booking->transportItems()->create([
                        'cover_van_no'         => $transport['cover_van_no'] ?? null,
                        'challan_no'           => $transport['challan_no'] ?? null,
                        'capacity'             => $transport['capacity'] ?? null,
                        'supplier_id'          => $transport['supplier_id'] ?? null,
                        'supplier_name'        => $transport['supplier_name'] ?? null,
                        'qty'                  => $transport['qty'] ?? 1,
                        'supplier_rate'        => $transport['supplier_rate'] ?? 0,
                        'customer_rate'        => $transport['customer_rate'] ?? 0,
                        'demurrage_days'       => $transport['demurrage_days'] ?? 0,
                        'cus_demurrage_charge' => $transport['cus_demurrage_charge'] ?? 0,
                        'sup_demurrage_charge' => $transport['sup_demurrage_charge'] ?? 0,
                        'amount'               => $transport['amount'] ?? 0,
                        'location_from'        => $transport['location_from'] ?? null,
                        'location_to'          => $transport['location_to'] ?? null,
                    ]);
                }

                $inserted++;
            }
        });

        return $inserted;
    }

    /**
     * @param  array<string, int>  $cache
     */
    private function createOnce(array &$cache, ?string $name, \Closure $create): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return $cache[$this->key($name)] ??= $create($name);
    }

    private function findOrCreateCustomer(string $name, int $branchId): int
    {
        $customer = NasFreightsCustomer::where('name', $name)->first();

        return $customer?->id ?? NasFreightsCustomer::create([
            'branch_id'   => $branchId,
            'id_prefix'   => 'CUS-',
            'customer_id' => NasFreightsCustomer::generateCustomerId('CUS-'),
            'name'        => $name,
            'phone'       => '',
            'status'      => 'Active',
        ])->id;
    }

    private function findOrCreateAgent(string $name, ?string $country): int
    {
        $agent = NasFreightsOverseasAgent::where('name', $name)->first();

        return $agent?->id ?? NasFreightsOverseasAgent::create([
            'agent_code' => NasFreightsOverseasAgent::generateCode(),
            'name'       => $name,
            'country'    => $country ?: 'N/A',
            'is_active'  => true,
        ])->id;
    }

    private function findOrCreateCarrier(string $name): int
    {
        $carrier = NasFreightsShippingCarrier::where('name', $name)->first();

        return $carrier?->id ?? NasFreightsShippingCarrier::create([
            'carrier_code' => NasFreightsShippingCarrier::generateCode(),
            'name'         => $name,
            'is_active'    => true,
        ])->id;
    }
}
