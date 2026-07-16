<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronEmployee;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronPort;
use App\Models\Chevron\ChevronRfq;
use App\Models\Chevron\ChevronRfqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RfqController extends Controller
{
    private function formData(): array
    {
        return [
            'ports'             => ChevronPort::where('is_active', true)->orderBy('name')->get(),
            'types'             => ChevronRfq::types(),
            'serviceTypes'      => ChevronRfq::serviceTypes(),
            'statuses'          => ChevronRfq::statuses(),
            'incoterms'         => ChevronRfq::incoterms(),
            'currencies'        => ChevronRfq::currencies(),
            'lostReasons'       => ChevronRfq::lostReasons(),
            'containerSizes'    => ChevronRfqItem::containerSizes(),
            'packageTypes'      => ChevronRfqItem::packageTypes(),
            'weightUnits'       => ChevronRfqItem::weightUnits(),
            'today'             => now()->format('Y-m-d'),
            'defaultValidUntil' => now()->addDays(30)->format('Y-m-d'),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ChevronRfq::with(['customer', 'pol', 'pod'])
                ->where('branch_id', session('active_branch_id'))
                ->when($request->status_filter, fn ($q, $s) => $q->where('status', $s));

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('rfq_date', fn ($r) => $r->rfq_date?->format('d M Y') ?? '—')
                ->editColumn('valid_until', fn ($r) => $r->valid_until?->format('d M Y') ?? '—')
                ->addColumn('customer_name', fn ($r) => $r->customer?->name ?? '—')
                ->addColumn('type_badge', fn ($r) => $r->type === 'import'
                    ? '<span class="badge bg-info text-dark">Import</span>'
                    : '<span class="badge bg-warning text-dark">Export</span>')
                ->addColumn('route', fn ($r) => ($r->pol?->name ?? '—').' → '.($r->pod?->name ?? '—'))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    'Win'     => '<span class="badge bg-success">Win</span>',
                    'Lose'    => '<span class="badge bg-danger">Lose</span>',
                    default   => '<span class="badge bg-secondary">Draft</span>',
                })
                ->addColumn('action', fn ($r) => '
                    <a href="'.route('chevron.cnf.rfqs.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-1" title="View"><i class="fa fa-eye"></i></a>
                    <a href="'.route('chevron.cnf.rfqs.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"
                        data-url="'.route('chevron.cnf.rfqs.destroy', $r->id).'"
                        data-name="'.e($r->rfq_no).'"><i class="fa fa-trash"></i></button>')
                ->filterColumn('customer_name', fn ($q, $k) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.cnf.rfqs.index');
    }

    public function create()
    {
        return view('chevron.cnf.rfqs.create', array_merge($this->formData(), [
            'rfq'           => null,
            'existingItems' => [],
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfq_date'   => ['required', 'date'],
            'type'       => ['required', 'in:import,export'],
        ]);

        DB::transaction(function () use ($request) {
            $rfq = ChevronRfq::create(array_merge($this->prepareData($request), [
                'rfq_no' => ChevronRfq::generateRfqNo(),
            ]));
            $this->saveItems($rfq, $request->input('items', []));
        });

        return redirect()->route('chevron.cnf.rfqs.index')
            ->with('success', 'RFQ created successfully.');
    }

    public function show(ChevronRfq $rfq)
    {
        $rfq->load(['customer', 'pol', 'pod', 'salesperson', 'convertedJob', 'items']);

        return view('chevron.cnf.rfqs.show', [
            'rfq'         => $rfq,
            'lostReasons' => ChevronRfq::lostReasons(),
        ]);
    }

    public function edit(ChevronRfq $rfq)
    {
        $rfq->load('items');
        $existingItems = $rfq->items->map(fn ($i) => [
            'item_type'          => $i->item_type,
            'container_size'     => $i->container_size,
            'package_type'       => $i->package_type,
            'hs_code'            => $i->hs_code,
            'commodity'          => $i->commodity,
            'quantity'           => $i->quantity,
            'gross_weight'       => $i->gross_weight,
            'weight_unit'        => $i->weight_unit,
            'volume_cbm'         => $i->volume_cbm,
            'cargo_value'        => $i->cargo_value,
            'country_of_origin'  => $i->country_of_origin,
            'is_dangerous_goods' => $i->is_dangerous_goods ? '1' : '0',
            'special_handling'   => $i->special_handling,
        ])->values();

        return view('chevron.cnf.rfqs.create', array_merge($this->formData(), [
            'rfq'           => $rfq,
            'existingItems' => $existingItems,
        ]));
    }

    public function update(Request $request, ChevronRfq $rfq)
    {
        $request->validate([
            'rfq_date'   => ['required', 'date'],
            'type'       => ['required', 'in:import,export'],
        ]);

        DB::transaction(function () use ($request, $rfq) {
            $rfq->update($this->prepareData($request));
            $rfq->items()->delete();
            $this->saveItems($rfq, $request->input('items', []));
        });

        return back()->with('success', 'RFQ '.$rfq->rfq_no.' updated successfully.');
    }

    public function updateStatus(Request $request, ChevronRfq $rfq)
    {
        $request->validate([
            'status'      => ['required', 'in:Draft,Pending,Win,Lose'],
            'lost_reason' => ['required_if:status,Lose'],
        ]);

        $rfq->update([
            'status'      => $request->status,
            'lost_reason' => $request->status === 'Lose' ? $request->lost_reason : null,
        ]);

        return back()->with('success', 'Status updated to '.$request->status.'.');
    }

    public function convertToJob(ChevronRfq $rfq)
    {
        if ($rfq->status !== 'Win') {
            return back()->with('error', 'Only Win RFQs can be converted to a job.');
        }

        if ($rfq->converted_job_id) {
            return redirect()->route('chevron.cnf.jobs.edit', $rfq->converted_job_id)
                ->with('info', 'This RFQ was already converted to job '.$rfq->convertedJob?->job_no.'.');
        }

        $job = DB::transaction(function () use ($rfq) {
            $job = ChevronJob::create([
                'job_no'        => ChevronJob::generateJobNo(),
                'branch_id'     => $rfq->branch_id,
                'customer_id'   => $rfq->customer_id,
                'party_name'    => $rfq->customer?->name,
                'party_address' => $rfq->customer?->address,
                'goods_name'    => $rfq->commodity_description,
                'pol'           => $rfq->pol?->name,
                'destination'   => $rfq->pod?->name,
                'remarks'       => $rfq->remarks,
                'job_date'      => now()->toDateString(),
                'status'        => 'Active',
            ]);

            $rfq->update(['converted_job_id' => $job->id]);

            return $job;
        });

        return redirect()->route('chevron.cnf.jobs.edit', $job->id)
            ->with('success', 'Job '.$job->job_no.' created from RFQ '.$rfq->rfq_no.'.');
    }

    public function destroy(ChevronRfq $rfq)
    {
        $rfq->delete();

        return response()->json(['message' => 'RFQ '.$rfq->rfq_no.' deleted.']);
    }

    public function searchCustomers(Request $request)
    {
        $q = $request->get('q', '');
        $results = ChevronCustomer::where('name', 'like', '%'.$q.'%')
            ->orWhere('customer_id', 'like', '%'.$q.'%')
            ->limit(20)
            ->select(['id', 'name', 'customer_id', 'address'])
            ->get()
            ->map(fn ($c) => [
                'id'      => $c->id,
                'text'    => $c->customer_id.' — '.$c->name,
                'name'    => $c->name,
                'address' => $c->address,
            ]);

        return response()->json($results);
    }

    public function searchPorts(Request $request)
    {
        $q = $request->get('q', '');
        $results = ChevronPort::where('name', 'like', '%'.$q.'%')
            ->where('is_active', true)
            ->limit(20)
            ->select(['id', 'name'])
            ->get()
            ->map(fn ($p) => ['id' => $p->id, 'text' => $p->name]);

        return response()->json($results);
    }

    public function searchEmployees(Request $request)
    {
        $q = $request->get('q', '');
        $results = ChevronEmployee::where('name', 'like', '%'.$q.'%')
            ->where('is_active', true)
            ->limit(20)
            ->select(['id', 'name', 'employee_id'])
            ->get()
            ->map(fn ($e) => ['id' => $e->id, 'text' => $e->name]);

        return response()->json($results);
    }

    private function prepareData(Request $request): array
    {
        $status = $request->status ?: 'Draft';

        return [
            'branch_id'             => session('active_branch_id'),
            'customer_id'           => $request->customer_id ?: null,
            'rfq_date'              => $request->rfq_date,
            'valid_until'           => $request->valid_until ?: null,
            'type'                  => $request->type,
            'service_type'          => $request->service_type ?: 'FCL',
            'incoterms'             => $request->incoterms ?: null,
            'currency'              => $request->currency ?: 'BDT',
            'pol_id'                => $request->pol_id ?: null,
            'pod_id'                => $request->pod_id ?: null,
            'place_of_receipt'      => $request->place_of_receipt ?: null,
            'place_of_delivery'     => $request->place_of_delivery ?: null,
            'commodity_description' => $request->commodity_description ?: null,
            'remarks'               => $request->remarks ?: null,
            'salesperson_id'        => $request->salesperson_id ?: null,
            'status'                => $status,
            'lost_reason'           => $status === 'Lose' ? ($request->lost_reason ?: null) : null,
        ];
    }

    private function saveItems(ChevronRfq $rfq, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['item_type'])) {
                continue;
            }

            $rfq->items()->create([
                'item_type'          => $item['item_type'],
                'container_size'     => $item['item_type'] === 'container' ? ($item['container_size'] ?? null) : null,
                'package_type'       => $item['item_type'] === 'package' ? ($item['package_type'] ?? null) : null,
                'hs_code'            => $item['hs_code'] ?? null,
                'commodity'          => $item['commodity'] ?? null,
                'quantity'           => max(1, (int) ($item['quantity'] ?? 1)),
                'gross_weight'       => is_numeric($item['gross_weight'] ?? '') ? $item['gross_weight'] : null,
                'weight_unit'        => $item['weight_unit'] ?? 'KG',
                'volume_cbm'         => is_numeric($item['volume_cbm'] ?? '') ? $item['volume_cbm'] : null,
                'cargo_value'        => is_numeric($item['cargo_value'] ?? '') ? $item['cargo_value'] : null,
                'country_of_origin'  => $item['country_of_origin'] ?? null,
                'is_dangerous_goods' => ! empty($item['is_dangerous_goods']),
                'special_handling'   => $item['special_handling'] ?? null,
            ]);
        }
    }
}
