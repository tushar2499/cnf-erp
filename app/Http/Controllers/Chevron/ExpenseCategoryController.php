<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronExpenseCategory;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronExpenseCategory::query()->latest())
                ->addIndexColumn()
                ->addColumn('type_badge', fn ($row) => $row->typeBadge())
                ->addColumn('status_badge', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn ($row) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'.$row->id.'"
                        data-name="'.e($row->name).'"
                        data-is_bill="'.(int) $row->is_bill.'"
                        data-is_job="'.(int) $row->is_job.'"
                        data-description="'.e($row->description).'"
                        data-is_active="'.(int) $row->is_active.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('chevron.settings.expense-categories.destroy', $row->id).'"
                        data-name="'.e($row->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.expense-categories.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:chevron_expense_categories,name'],
        ]);

        if (! $request->boolean('is_bill') && ! $request->boolean('is_job')) {
            return response()->json([
                'errors' => ['type' => ['Select at least one: Bill or Job (or both).']],
            ], 422);
        }

        ChevronExpenseCategory::create([
            'name'        => $request->name,
            'is_bill'     => $request->boolean('is_bill'),
            'is_job'      => $request->boolean('is_job'),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense category created successfully.']);
    }

    public function update(Request $request, ChevronExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:chevron_expense_categories,name,'.$expenseCategory->id],
        ]);

        if (! $request->boolean('is_bill') && ! $request->boolean('is_job')) {
            return response()->json([
                'errors' => ['type' => ['Select at least one: Bill or Job (or both).']],
            ], 422);
        }

        $expenseCategory->update([
            'name'        => $request->name,
            'is_bill'     => $request->boolean('is_bill'),
            'is_job'      => $request->boolean('is_job'),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense category updated successfully.']);
    }

    public function destroy(ChevronExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return response()->json(['message' => 'Expense category deleted.']);
    }

    public function sampleDownload()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expense Categories');

        $sheet->fromArray(['Name', 'Type (bill/job/both)', 'Description', 'Status'], null, 'A1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1A4A6B');
        $sheet->getStyle('A1:D1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $sheet->fromArray([
            ['CUSTOMS DUTY',   'bill', 'Customs duty charges',  'Active'],
            ['PORT CHARGES',   'both', 'Port handling charges', 'Active'],
            ['TRANSPORTATION', 'job',  '',                      'Active'],
        ], null, 'A2');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(12);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'expense-categories-sample.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $existing = ChevronExpenseCategory::pluck('name')
            ->map(fn ($n) => strtolower(trim($n)))
            ->flip()
            ->all();

        $preview = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $name = trim($row[0] ?? '');
            if ($name === '') {
                continue;
            }

            [$isBill, $isJob] = $this->parseTypeString(trim($row[1] ?? ''));

            $preview[] = [
                'name'        => $name,
                'is_bill'     => $isBill,
                'is_job'      => $isJob,
                'description' => trim($row[2] ?? ''),
                'status'      => trim($row[3] ?? 'Active') ?: 'Active',
                'exists'      => isset($existing[strtolower($name)]),
            ];
        }

        return response()->json(['rows' => $preview]);
    }

    public function import(Request $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);

        $inserted = 0;
        foreach ($request->rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $exists = ChevronExpenseCategory::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists();
            if ($exists) {
                continue;
            }

            ChevronExpenseCategory::create([
                'name'        => $name,
                'is_bill'     => (bool) ($row['is_bill'] ?? true),
                'is_job'      => (bool) ($row['is_job'] ?? false),
                'description' => $row['description'] ?? null,
                'is_active'   => strtolower($row['status'] ?? 'active') === 'active',
            ]);
            $inserted++;
        }

        return response()->json([
            'message'  => "{$inserted} category(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }

    private function parseTypeString(string $type): array
    {
        $type = strtolower($type);

        return match ($type) {
            'both'  => [true, true],
            'job'   => [false, true],
            default => [true, false],
        };
    }
}
