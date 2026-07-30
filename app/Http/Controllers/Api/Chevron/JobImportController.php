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

class JobImportController extends Controller
{
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
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $lookups = $this->buildLookups();

        $existingJobNos = ChevronJob::pluck('job_no')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

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
        if ($request->hasFile('file')) {
            return $this->importFromFile($request);
        }

        return $this->importFromRows($request);
    }

    private function importFromFile(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $lookups = $this->buildLookups();

        $existingJobNos = ChevronJob::pluck('job_no')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

        $parsed = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $jobNo = trim($row[2] ?? '');
            if ($jobNo === '' || isset($existingJobNos[strtolower($jobNo)])) {
                continue;
            }

            $mapped = $this->mapRow($row, $lookups);
            $mapped['branch_id'] = 2;
            $parsed[] = $mapped;
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

    private function importFromRows(Request $request): JsonResponse
    {
        $request->validate(
            [
                'rows'              => 'required|array|min:1',
                'rows.*.job_no'     => 'required|string|max:255',
                'rows.*.job_date'   => 'nullable|date',
                'rows.*.status'     => 'nullable|in:Active,Pending,Closed',
            ],
            [
                'rows.required'          => 'No rows found. Please upload a file first and confirm the preview.',
                'rows.array'             => 'The import data must be a list of rows.',
                'rows.min'               => 'At least one job row is required.',
                'rows.*.job_no.required' => 'Row :position: Job No is required.',
            ]
        );

        $rows = array_map(fn ($r) => array_merge($r, ['branch_id' => 2]), $request->rows);

        $inserted = $this->persistRows($rows);

        return response()->json([
            'message'  => "{$inserted} job(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }

    private function buildLookups(): array
    {
        $branchId = session('active_branch_id');
        $ports = [];
        ChevronPort::where('branch_id', $branchId)->where('is_active', true)->get(['id', 'name', 'code'])->each(function ($p) use (&$ports) {
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

    private function mapRow(array $row, array $lookups): array
    {
        $jobNo = trim($row[2] ?? '');
        $jobDate = trim($row[3] ?? '');
        $jobTypeName = trim($row[5] ?? '');
        $portName = trim($row[6] ?? '');
        $customerName = trim($row[7] ?? '');
        $statusRaw = strtolower(trim($row[17] ?? ''));

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
            'job_no'           => $jobNo,
            'job_date'         => $this->parseDate($jobDate),
            'service_id'       => 1,
            'job_type_id'      => $jobTypeId,
            'port_id'          => $portId,
            '_port_name'       => $portName,
            '_job_type_name'   => $jobTypeName,
            'customer_id'      => $customer['id'] ?? null,
            'party_name'       => $customer['name'] ?? $customerName,
            'invoice_no'       => trim($row[8] ?? '') ?: null,
            'po_no'            => trim($row[9] ?? '') ?: null,
            'pack_quantity'    => is_numeric($row[10] ?? null) ? (float) $row[10] : null,
            'pack_unit'        => trim($row[11] ?? '') ?: null,
            'assessable_value' => is_numeric($row[12] ?? null) ? (float) $row[12] : null,
            'vat_amount'       => is_numeric($row[14] ?? null) ? (float) $row[14] : null,
            'received_amount'  => is_numeric($row[15] ?? null) ? (float) $row[15] : null,
            'remarks'          => trim($row[16] ?? '') ?: null,
            'status'           => self::STATUS_MAP[$statusRaw] ?? 'Active',
            'warnings'         => $warnings,
        ];
    }

    private function parseDate(string $value): ?string
    {
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

                if (ChevronJob::where('job_no', $jobNo)->exists()) {
                    continue;
                }

                $portId = $row['port_id'] ?? null;
                $jobTypeId = $row['job_type_id'] ?? null;

                $rawPortName = trim($row['_port_name'] ?? '');
                $rawJobTypeName = trim($row['_job_type_name'] ?? '');

                if ($portId === null && $rawPortName !== '') {
                    $port = ChevronPort::firstOrCreate(
                        ['name' => $rawPortName, 'branch_id' => $row['branch_id']],
                        ['code' => strtoupper($rawPortName), 'is_active' => true]
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
                    'job_no'           => $jobNo,
                    'branch_id'        => $row['branch_id'] ?? null,
                    'service_id'       => $row['service_id'] ?? 1,
                    'job_type_id'      => $jobTypeId,
                    'port_id'          => $portId,
                    'job_date'         => $row['job_date'] ?? null,
                    'customer_id'      => $row['customer_id'] ?? null,
                    'party_name'       => $row['party_name'] ?? null,
                    'invoice_no'       => $row['invoice_no'] ?? null,
                    'po_no'            => $row['po_no'] ?? null,
                    'pack_quantity'    => $row['pack_quantity'] ?? null,
                    'pack_unit'        => $row['pack_unit'] ?? null,
                    'assessable_value' => $row['assessable_value'] ?? null,
                    'vat_amount'       => $row['vat_amount'] ?? null,
                    'received_amount'  => $row['received_amount'] ?? null,
                    'remarks'          => $row['remarks'] ?? null,
                    'status'           => $row['status'] ?? 'Active',
                ]);

                $inserted++;
            }
        });

        return $inserted;
    }

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
