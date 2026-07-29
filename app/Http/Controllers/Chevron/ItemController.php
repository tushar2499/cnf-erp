<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronItem::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('po_so', fn ($r) => ($r->availability_in_po ? '<span class="badge bg-info text-dark me-1">PO</span>' : '').
                    ($r->availability_in_so ? '<span class="badge bg-primary">SO</span>' : ''))
                ->addColumn('action', fn ($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$r->id.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('chevron.settings.items.destroy', $r->id).'"
                        data-name="'.e($r->item_code).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'po_so', 'action'])
                ->make(true);
        }

        return view('chevron.settings.items.index', ['units' => ChevronItem::units()]);
    }

    public function show(ChevronItem $item)
    {
        return response()->json($item);
    }

    public function nextCode(): JsonResponse
    {
        $last = ChevronItem::max('item_code');
        $next = $last
            ? 'ITEM-'.str_pad((intval(substr($last, 5)) + 1), 4, '0', STR_PAD_LEFT)
            : 'ITEM-1001';

        return response()->json(['next_code' => $next]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_unit' => ['required', 'string'],
            'item_price'    => ['required', 'numeric', 'min:0'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chevron/items', 'public');
        }

        DB::transaction(function () use ($request, $imagePath) {
            $last = ChevronItem::lockForUpdate()->max('item_code');
            $code = $last
                ? 'ITEM-'.str_pad((intval(substr($last, 5)) + 1), 4, '0', STR_PAD_LEFT)
                : 'ITEM-1001';

            ChevronItem::create([
                'item_code'          => $code,
                'item_name'          => $request->item_name,
                'supplier'           => $request->supplier,
                'purchase_unit'      => $request->purchase_unit,
                'description'        => $request->description,
                'remarks'            => $request->remarks,
                'status'             => $request->status ?? 'Active',
                'item_price'         => $request->item_price ?? 0,
                'availability_in_po' => $request->boolean('availability_in_po', true),
                'availability_in_so' => $request->boolean('availability_in_so', true),
                'image'              => $imagePath,
            ]);
        });

        return response()->json(['message' => 'Item created successfully.']);
    }

    public function update(Request $request, ChevronItem $item)
    {
        $request->validate([
            'item_code'     => ['required', 'string', 'max:100', 'unique:chevron_items,item_code,'.$item->id],
            'purchase_unit' => ['required', 'string'],
            'item_price'    => ['required', 'numeric', 'min:0'],
        ]);

        $imagePath = $item->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('chevron/items', 'public');
        }

        $item->update([
            'item_code'          => strtoupper($request->item_code),
            'item_name'          => $request->item_name,
            'supplier'           => $request->supplier,
            'purchase_unit'      => $request->purchase_unit,
            'description'        => $request->description,
            'remarks'            => $request->remarks,
            'status'             => $request->status ?? 'Active',
            'item_price'         => $request->item_price ?? 0,
            'availability_in_po' => $request->boolean('availability_in_po', true),
            'availability_in_so' => $request->boolean('availability_in_so', true),
            'image'              => $imagePath,
        ]);

        return response()->json(['message' => 'Item updated successfully.']);
    }

    public function destroy(ChevronItem $item)
    {
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }
        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'item_name'     => ['required', 'string', 'max:255'],
            'purchase_unit' => ['required', 'string'],
            'item_price'    => ['required', 'numeric', 'min:0'],
        ]);

        $item = DB::transaction(function () use ($request) {
            $last = ChevronItem::lockForUpdate()->max('item_code');
            $next = $last ? ('ITEM-'.str_pad((intval(substr($last, 5)) + 1), 4, '0', STR_PAD_LEFT)) : 'ITEM-1001';

            return ChevronItem::create([
                'item_code'          => $next,
                'item_name'          => $request->item_name,
                'purchase_unit'      => $request->purchase_unit,
                'item_price'         => $request->item_price,
                'status'             => 'Active',
                'availability_in_po' => true,
                'availability_in_so' => true,
            ]);
        });

        return response()->json([
            'id'            => $item->id,
            'text'          => $item->item_code.' — '.$item->item_name,
            'name'          => $item->item_name,
            'purchase_unit' => $item->purchase_unit,
            'message'       => 'Item "'.$item->item_name.'" created successfully.',
        ]);
    }
}
