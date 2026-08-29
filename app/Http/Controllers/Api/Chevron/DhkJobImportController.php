<?php

namespace App\Http\Controllers\Api\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobType;
use App\Models\Chevron\ChevronPort;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Head Office / Dhaka (branch "DK") job importer.
 *
 * Reads the "DHK JOB INFORMATION" sheet of the Dhaka workbook and must be used
 * before the DHK job expense import so expenses link up to existing jobs.
 * Deliberately separate from JobImportController (Chittagong) so the
 * Chittagong pipeline is never affected.
 */
class DhkJobImportController extends Controller
{
    private const HEAD_OFFICE_BRANCH_ID = 5;

    private const STATUS_MAP = [
        'submitted' => 'Pending',
        'active'    => 'Active',
        'pending'   => 'Pending',
        'closed'    => 'Closed',
    ];

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        try {
            $rows = $this->loadRows($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $lookups = $this->buildLookups();
        $existingJobNos = ChevronJob::pluck('job_no')
            ->map(fn ($v) => strtolower(trim($v)))
            ->flip()
            ->all();

        $preview = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $jobNo = trim($row[2] ?? '');
            if ($jobNo === '') {
                continue;
            }

            $mapped = $this->mapRow($row, $lookups);
            $mapped['exists'] = isset($existingJobNos[strtolower($jobNo)]);
            $preview[] = $mapped;
        }

        return response()->json(['preview' => $preview]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        try {
            $rows = $this->loadRows($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $lookups = $this->buildLookups();
        $existingJobNos = ChevronJob::pluck('job_no')
            ->map(fn ($v) => strtolower(trim($v)))
            ->flip()
            ->all();

        $parsed = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $jobNo = trim($row[2] ?? '');
            if ($jobNo === '' || isset($existingJobNos[strtolower($jobNo)])) {
                continue;
            }

            $parsed[] = $this->mapRow($row, $lookups);
        }

        if (empty($parsed)) {
            return response()->json([
                'message'  => 'No new jobs to import. All records already exist.',
                'inserted' => 0,
            ]);
        }

        $inserted = $this->persistRows($parsed);

        return response()->json([
            'message'  => "{$inserted} job(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }

    // ─── Spreadsheet loading ─────────────────────────────────────────────────

    private function loadRows(string $pathname): array
    {
        $spreadsheet = IOFactory::load($pathname);
        $sheet = $spreadsheet->getSheetByName('DHK JOB INFORMATION') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();

        if ($highestRow <= 1) {
            return [];
        }

        return $sheet->rangeToArray('A1:'.$highestCol.$highestRow, null, true, true, false);
    }

    // ─── Mapping ──────────────────────────────────────────────────────────────

    private function buildLookups(): array
    {
        $ports = [];
        ChevronPort::get(['id', 'name', 'code'])->each(function ($p) use (&$ports) {
            $ports[strtolower(trim($p->name))] = $p->id;
            if (trim($p->code) !== '') {
                $ports[strtolower(trim($p->code))] = $p->id;
            }
        });

        $jobTypes = ChevronJobType::where('is_active', true)
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($t) => [strtolower(trim($t->name)) => $t->id])
            ->all();

        $customers = ChevronCustomer::get(['id', 'name'])
            ->mapWithKeys(fn ($c) => [strtolower(trim($c->name)) => ['id' => $c->id, 'name' => $c->name]])
            ->all();

        return compact('ports', 'jobTypes', 'customers');
    }

    /**
     * DHK job sheet columns (A–AA): Sl, Action, Job No, Job Date, Job Type,
     * Type, Port, Customer, Goods Name, B/E, B/E Date, B/L, Lc, Invoice,
     * M B/L/M A W/B, Gross Weight, Qty, Unit, Assessable Value, Invoice Value,
     * Currency Name, Currency Rate, Invoice Value B D T, Total Payable,
     * Remarks, Status, Bill Status.
     *
     * @return array<string, mixed>
     */
    private function mapRow(array $row, array $lookups): array
    {
        $jobNo = trim($row[2] ?? '');
        $jobDate = trim($row[3] ?? '');
        $jobTypeName = trim($row[5] ?? '');
        $portName = trim($row[6] ?? '');
        $customerName = trim($row[7] ?? '');
        $statusRaw = strtolower(trim($row[25] ?? ''));

        $portId = $portName !== '' ? ($lookups['ports'][strtolower($portName)] ?? null) : null;
        $jobTypeId = $jobTypeName !== '' ? ($lookups['jobTypes'][strtolower($jobTypeName)] ?? null) : null;
        $customer = $customerName !== '' ? ($lookups['customers'][strtolower($customerName)] ?? null) : null;

        $warnings = [];
        if ($portId === null && $portName !== '') {
            $warnings[] = "Port '{$portName}' will be created.";
        }
        if ($jobTypeId === null && $jobTypeName !== '') {
            $warnings[] = "Job type '{$jobTypeName}' will be created.";
        }
        if ($customer === null && $customerName !== '') {
            $warnings[] = "Customer '{$customerName}' not found; saved as party name only.";
        }

        return [
            'job_no'                => $jobNo,
            'job_date'              => $this->parseDate($jobDate),
            'service_id'            => 1,
            'job_type_id'           => $jobTypeId,
            'port_id'               => $portId,
            '_port_name'            => $portName,
            '_job_type_name'        => $jobTypeName,
            'customer_id'           => $customer['id'] ?? null,
            'party_name'            => $customer['name'] ?? $customerName,
            'goods_name'            => trim($row[8] ?? '') ?: null,
            'be_no'                 => trim($row[9] ?? '') ?: null,
            'be_date'               => $this->parseDate($row[10] ?? ''),
            'bl_no'                 => trim($row[11] ?? '') ?: null,
            'lc_no'                 => trim($row[12] ?? '') ?: null,
            'invoice_no'            => trim($row[13] ?? '') ?: null,
            'mbl_mawb_no'           => trim($row[14] ?? '') ?: null,
            'gross_weight'          => is_numeric($row[15] ?? null) ? (float) $row[15] : null,
            'pack_quantity'         => is_numeric($row[16] ?? null) ? (float) $row[16] : null,
            'pack_unit'             => trim($row[17] ?? '') ?: null,
            'assessable_value'      => is_numeric($row[18] ?? null) ? (float) $row[18] : null,
            'currency_type'         => trim($row[20] ?? '') ?: null,
            'currency_rate'         => is_numeric($row[21] ?? null) ? (float) $row[21] : null,
            'assessable_value_bdt'  => is_numeric($row[22] ?? null) ? (float) $row[22] : null,
            'total_payable_1'       => is_numeric($row[23] ?? null) ? (float) $row[23] : null,
            'remarks'               => trim($row[24] ?? '') ?: null,
            'status'                => self::STATUS_MAP[$statusRaw] ?? 'Active',
            'warnings'              => $warnings,
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function persistRows(array $rows): int
    {
        $inserted = 0;

        DB::transaction(function () use ($rows, &$inserted) {
            foreach ($rows as $row) {
                $jobNo = trim($row['job_no'] ?? '');
                if ($jobNo === '') {
                    continue;
                }

                if (ChevronJob::whereRaw('LOWER(TRIM(job_no)) = ?', [strtolower($jobNo)])->exists()) {
                    continue;
                }

                $portId = $row['port_id'] ?? null;
                $jobTypeId = $row['job_type_id'] ?? null;

                $rawPortName = trim($row['_port_name'] ?? '');
                $rawJobTypeName = trim($row['_job_type_name'] ?? '');

                if ($portId === null && $rawPortName !== '') {
                    $port = ChevronPort::firstOrCreate(
                        ['name' => $rawPortName, 'code' => strtoupper($rawPortName)],
                        ['branch_id' => self::HEAD_OFFICE_BRANCH_ID, 'is_active' => true]
                    );
                    $portId = $port->id;
                }

                if ($jobTypeId === null && $rawJobTypeName !== '') {
                    $jobType = ChevronJobType::firstOrCreate(
                        ['name' => $rawJobTypeName],
                        ['is_active' => true]
                    );
                    $jobTypeId = $jobType->id;
                }

                ChevronJob::create([
                    'job_no'               => $jobNo,
                    'branch_id'            => self::HEAD_OFFICE_BRANCH_ID,
                    'service_id'           => $row['service_id'] ?? 1,
                    'job_type_id'          => $jobTypeId,
                    'port_id'              => $portId,
                    'job_date'             => $row['job_date'] ?? null,
                    'customer_id'          => $row['customer_id'] ?? null,
                    'party_name'           => $row['party_name'] ?? null,
                    'goods_name'           => $row['goods_name'] ?? null,
                    'be_no'                => $row['be_no'] ?? null,
                    'be_date'              => $row['be_date'] ?? null,
                    'bl_no'                => $row['bl_no'] ?? null,
                    'lc_no'                => $row['lc_no'] ?? null,
                    'invoice_no'           => $row['invoice_no'] ?? null,
                    'mbl_mawb_no'          => $row['mbl_mawb_no'] ?? null,
                    'gross_weight'         => $row['gross_weight'] ?? null,
                    'pack_quantity'        => $row['pack_quantity'] ?? null,
                    'pack_unit'            => $row['pack_unit'] ?? null,
                    'assessable_value'     => $row['assessable_value'] ?? null,
                    'currency_type'        => $row['currency_type'] ?? null,
                    'currency_rate'        => $row['currency_rate'] ?? null,
                    'assessable_value_bdt' => $row['assessable_value_bdt'] ?? null,
                    'total_payable_1'      => $row['total_payable_1'] ?? null,
                    'remarks'              => $row['remarks'] ?? null,
                    'status'               => $row['status'] ?? 'Active',
                ]);

                $inserted++;
            }
        });

        return $inserted;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function validateFile(Request $request): void
    {
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:5120'],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 5 MB.',
            ]
        );
    }

    private function zipError(): JsonResponse
    {
        return response()->json([
            'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files. Please enable it in cPanel → MultiPHP INI Editor, or upload a .csv file instead.',
        ], 500);
    }
}
