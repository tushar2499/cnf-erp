<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class ExpenseHeadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronExpenseHead::with('expenseCategory'))
                ->addIndexColumn()
                ->addColumn('category_name', fn($row) => $row->expenseCategory?->name ?? '-')
                ->addColumn('category_type_badge', fn($row) => $row->expenseCategory
                    ? $row->expenseCategory->type->badge()
                    : '-')
                ->addColumn('status_badge', fn($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($row) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="' . $row->id . '"
                        data-name="' . e($row->name) . '"
                        data-expense_category_id="' . $row->expense_category_id . '"
                        data-type="' . $row->type . '"
                        data-amount="' . $row->amount . '"
                        data-is_active="' . (int)$row->is_active . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('chevron.settings.expense-heads.destroy', $row->id) . '"
                        data-name="' . e($row->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['category_type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        $categories = ChevronExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return view('chevron.settings.expense-heads.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:chevron_expense_categories,id'],
            'type'                => ['required', 'in:External,Internal'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
        ]);

        ChevronExpenseHead::create([
            'name'                => $request->name,
            'expense_category_id' => $request->expense_category_id,
            'type'                => $request->type,
            'amount'              => $request->amount,
            'is_active'           => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense head created successfully.']);
    }

    public function update(Request $request, ChevronExpenseHead $expenseHead)
    {
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:chevron_expense_categories,id'],
            'type'                => ['required', 'in:External,Internal'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
        ]);

        $expenseHead->update([
            'name'                => $request->name,
            'expense_category_id' => $request->expense_category_id,
            'type'                => $request->type,
            'amount'              => $request->amount,
            'is_active'           => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Expense head updated successfully.']);
    }

    public function destroy(ChevronExpenseHead $expenseHead)
    {
        $expenseHead->delete();
        return response()->json(['message' => 'Expense head deleted.']);
    }

    public function sampleDownload()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expense Heads');

        $sheet->fromArray(['Name', 'Category', 'Type', 'Amount', 'Status'], null, 'A1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1A4A6B');
        $sheet->getStyle('A1:E1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Sample rows — pull first 3 categories from DB
        $cats = ChevronExpenseCategory::limit(3)->pluck('name')->all();
        $sheet->fromArray([
            ['PORT HANDLING FEE',   $cats[0] ?? 'CUSTOMS',      'External', '',       'Active'],
            ['DOCUMENTATION CHARGE',$cats[1] ?? 'TRANSPORTATION','Internal', '500.00', 'Active'],
            ['CUSTOMS DUTY',        $cats[2] ?? 'PORT & JETTY',  'External', '',       'Active'],
        ], null, 'A2');

        foreach (['A'=>36,'B'=>28,'C'=>12,'D'=>12,'E'=>10] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'expense-heads-sample.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Existing head names (lowercase)
        $existingHeads = ChevronExpenseHead::pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->flip()->all();

        // Category name → id map (lowercase keys)
        $catMap = ChevronExpenseCategory::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $preview = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) continue;

            $name = trim($row[0] ?? '');
            if ($name === '') continue;

            $catName  = trim($row[1] ?? '');
            $type     = trim($row[2] ?? '');
            $amount   = trim($row[3] ?? '');
            $status   = trim($row[4] ?? 'Active') ?: 'Active';

            // Resolve category id
            $catId    = $catMap[strtolower($catName)] ?? null;
            $catFound = $catId !== null;

            // Normalise type
            $typeNorm = match(strtolower($type)) {
                'internal' => 'Internal',
                'external' => 'External',
                default    => null,
            };

            $preview[] = [
                'name'                => $name,
                'category_name'       => $catName,
                'expense_category_id' => $catId,
                'category_found'      => $catFound,
                'type'                => $typeNorm ?? $type,
                'type_valid'          => $typeNorm !== null,
                'amount'              => is_numeric($amount) ? $amount : null,
                'status'              => $status,
                'exists'              => isset($existingHeads[strtolower($name)]),
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
            if ($name === '') continue;

            if (ChevronExpenseHead::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) continue;

            ChevronExpenseHead::create([
                'name'                => $name,
                'expense_category_id' => $row['expense_category_id'] ?? null,
                'type'                => in_array($row['type'] ?? '', ['Internal','External']) ? $row['type'] : 'External',
                'amount'              => is_numeric($row['amount'] ?? '') ? $row['amount'] : null,
                'is_active'           => strtolower($row['status'] ?? 'active') === 'active',
            ]);
            $inserted++;
        }

        return response()->json([
            'message'  => "{$inserted} expense head(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }
}
