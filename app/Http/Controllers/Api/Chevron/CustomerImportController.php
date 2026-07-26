<?php

namespace App\Http\Controllers\Api\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustomerImportController extends Controller
{
    public function preview(Request $request): JsonResponse
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

        if (! extension_loaded('zip')) {
            return response()->json([
                'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files. Please enable it in cPanel → MultiPHP INI Editor, or upload a .csv file instead.',
            ], 500);
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not read the file: '.$e->getMessage(),
            ], 422);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $existingIds = ChevronCustomer::pluck('customer_id')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

        $existingNames = ChevronCustomer::pluck('name')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

        $preview = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $name = trim($row[2] ?? '');
            if ($name === '') {
                continue;
            }

            $customerId = trim($row[1] ?? '');
            $shortForm = trim($row[3] ?? '');
            $mobile = trim($row[4] ?? '');
            $address = trim($row[5] ?? '');
            $creditTerm = trim($row[6] ?? '');
            $openingBal = is_numeric($row[7] ?? null) ? (float) $row[7] : 0;
            $status = trim($row[8] ?? 'Active') ?: 'Active';

            $exists = $customerId !== ''
                ? isset($existingIds[strtolower($customerId)])
                : isset($existingNames[strtolower($name)]);

            $prefix = $customerId !== '' ? preg_replace('/\d+$/', '', $customerId) : 'CUS-';

            $preview[] = [
                'customer_id'     => $customerId,
                'id_prefix'       => $prefix,
                'name'            => $name,
                'short_form'      => $shortForm,
                'mobile'          => $mobile,
                'address'         => $address,
                'credit_term'     => $creditTerm,
                'opening_balance' => $openingBal,
                'status'          => $status,
                'exists'          => $exists,
            ];
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
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:5120'],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 5 MB.',
            ]
        );

        if (! extension_loaded('zip')) {
            return response()->json([
                'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files. Please enable it in cPanel → MultiPHP INI Editor, or upload a .csv file instead.',
            ], 500);
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not read the file: '.$e->getMessage(),
            ], 422);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $existingNames = ChevronCustomer::pluck('name')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

        $parsed = [];
        foreach ($rows as $i => $row) {

            if ($i === 0) {
                continue;
            }

            $name = trim($row[2] ?? '');
            if ($name === '') {
                continue;
            }

            if (isset($existingNames[strtolower($name)])) {
                continue;
            }

            $parsed[] = [
                'customer_id' => '',
                'id_prefix'   => 'CUS-',
                'name'        => $name,
                'short_form'  => trim($row[3] ?? ''),
                'mobile'      => trim($row[4] ?? ''),
                'address'     => trim($row[5] ?? ''),
                'credit_term' => trim($row[6] ?? ''),
                'status'      => trim($row[8] ?? 'Active') ?: 'Active',
            ];
        }

        if (empty($parsed)) {
            return response()->json([
                'message'  => 'No new customers to import. All records already exist.',
                'inserted' => 0,
            ]);
        }

        $inserted = $this->persistRows($parsed);

        return response()->json([
            'message'  => "{$inserted} customer(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }

    private function importFromRows(Request $request): JsonResponse
    {
        $request->validate(
            [
                'rows'          => 'required|array|min:1',
                'rows.*.name'   => 'required|string|max:255',
                'rows.*.status' => 'nullable|in:Active,Inactive',
            ],
            [
                'rows.required'        => 'No rows found. Please upload a file first and confirm the preview.',
                'rows.array'           => 'The import data must be a list of rows.',
                'rows.min'             => 'At least one customer row is required.',
                'rows.*.name.required' => 'Row :position: Customer Name is required.',
                'rows.*.name.max'      => 'Row :position: Customer Name must not exceed 255 characters.',
                'rows.*.status.in'     => 'Row :position: Status must be either "Active" or "Inactive".',
            ]
        );

        $inserted = $this->persistRows($request->rows);

        return response()->json([
            'message'  => "{$inserted} customer(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }

    private function persistRows(array $rows): int
    {
        $inserted = 0;

        DB::transaction(function () use ($rows, &$inserted) {
            foreach ($rows as $row) {
                $name = trim($row['name'] ?? '');
                $customerId = trim($row['customer_id'] ?? '');
                $prefix = trim($row['id_prefix'] ?? 'CUS-') ?: 'CUS-';

                if ($name === '') {
                    continue;
                }

                if ($customerId !== '' && ChevronCustomer::where('customer_id', $customerId)->exists()) {
                    continue;
                }
                if ($customerId === '' && ChevronCustomer::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
                    continue;
                }

                $finalId = $customerId !== '' ? $customerId : ChevronCustomer::generateCustomerId($prefix);

                ChevronCustomer::create([
                    'id_prefix'   => $prefix,
                    'customer_id' => $finalId,
                    'name'        => $name,
                    'prefix'      => $row['short_form'] ?? null,
                    'mobile'      => $row['mobile'] ?? null,
                    'address'     => $row['address'] ?? null,
                    'limit_days'  => is_numeric($row['credit_term'] ?? null) ? (int) $row['credit_term'] : 0,
                    'status'      => $row['status'] ?? 'Active',
                ]);
                $inserted++;
            }
        });

        return $inserted;
    }
}
