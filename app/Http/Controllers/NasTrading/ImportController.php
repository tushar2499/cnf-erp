<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronDesignation;
use App\Models\Chevron\ChevronEmployee;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasTrading\NasTradingDepartment;
use App\Models\NasTrading\NasTradingEmployee;
use App\Models\NasTrading\NasTradingExpenseHead;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    private const SKIP_CODES = ['OT', 'NF', '-'];

    private function parseEmployees(): array
    {
        $path = public_path('chevronlines/Employee Name.xlsx');
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $rows = $reader->load($path)->getActiveSheet()->toArray(null, true, true, true);

        $employees = [];
        foreach ($rows as $r) {
            $cell = trim($r['C'] ?? '');
            if (!$cell || str_contains($cell, 'Emp. ID')) continue;

            $parts = explode('_', $cell, 4);
            if (count($parts) < 2) continue;

            $code = trim($parts[0]);
            if (in_array($code, self::SKIP_CODES, true)) continue;

            $employees[] = [
                'code'        => $code,
                'name'        => trim($parts[1]),
                'designation' => trim($parts[2] ?? ''),
                'department'  => trim($parts[3] ?? ($r['D'] ?? '')),
            ];
        }
        return $employees;
    }

    private function parseExpenseHeads(): array
    {
        $path = public_path('chevronlines/Expense Category & Head.xlsx');
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $rows = $reader->load($path)->getActiveSheet()->toArray(null, true, true, true);

        $heads = [];
        $isFirst = true;
        foreach ($rows as $r) {
            $v = array_values($r);
            if ($isFirst) { $isFirst = false; continue; } // header
            $name = trim($v[2] ?? '');
            if (strlen($name) < 3) continue;
            $heads[] = [
                'name'     => $name,
                'type'     => trim($v[3] ?? ''),
                'category' => trim($v[5] ?? ''),
            ];
        }
        return $heads;
    }

    public function preview()
    {
        $employees    = $this->parseEmployees();
        $expenseHeads = $this->parseExpenseHeads();

        return view('nas-trading.import.chevron', compact('employees', 'expenseHeads'));
    }

    public function import()
    {
        $employees    = $this->parseEmployees();
        $expenseHeads = $this->parseExpenseHeads();

        $empCount      = 0;
        $deptCount     = 0;
        $expCount      = 0;
        $freightsCount = 0;
        $chevronCount  = 0;

        foreach ($employees as $emp) {
            // --- NAS Trading ---
            $dept = null;
            if ($emp['department']) {
                $dept = NasTradingDepartment::firstOrCreate(
                    ['name' => $emp['department']],
                    ['status' => 'Active']
                );
                if ($dept->wasRecentlyCreated) $deptCount++;
            }

            NasTradingEmployee::updateOrCreate(
                ['code' => $emp['code']],
                [
                    'name'          => $emp['name'],
                    'designation'   => $emp['designation'] ?: null,
                    'department_id' => $dept?->id,
                    'status'        => 'Active',
                ]
            );
            $empCount++;

            // --- NAS Freights ---
            $result = NasFreightsEmployee::updateOrCreate(
                ['code' => $emp['code']],
                [
                    'name'        => $emp['name'],
                    'designation' => $emp['designation'] ?: null,
                    'status'      => 'Active',
                ]
            );
            if ($result->wasRecentlyCreated) $freightsCount++;

            // --- Chevron Lines ---
            $designationId = null;
            if ($emp['designation']) {
                $designationId = ChevronDesignation::firstOrCreate(
                    ['name' => $emp['designation']],
                    ['is_active' => true]
                )->id;
            }

            $prefix = preg_replace('/\d+$/', '', $emp['code']) ?: $emp['code'];
            $result = ChevronEmployee::updateOrCreate(
                ['employee_id' => $emp['code']],
                [
                    'employee_prefix' => $prefix,
                    'name'            => $emp['name'],
                    'designation_id'  => $designationId,
                    'current_status'  => 'Active',
                    'is_active'       => true,
                ]
            );
            if ($result->wasRecentlyCreated) $chevronCount++;
        }

        foreach ($expenseHeads as $head) {
            $record = NasTradingExpenseHead::firstOrCreate(['name' => $head['name']]);
            $record->fill([
                'type'     => $head['type'] ?: null,
                'category' => $head['category'] ?: 'Other',
                'status'   => 'Active',
            ])->save();
            $expCount++;
        }

        return response()->json([
            'message' => "Import complete. Departments: {$deptCount} new. "
                . "NAS Trading employees: {$empCount}. "
                . "NAS Freights: {$freightsCount} new. "
                . "Chevron Lines: {$chevronCount} new. "
                . "Expense Heads: {$expCount}.",
        ]);
    }
}
