<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Job\CreateJobRequest;
use App\Http\Requests\Chevron\Job\DestroyJobRequest;
use App\Http\Requests\Chevron\Job\EditJobRequest;
use App\Http\Requests\Chevron\Job\IndexJobRequest;
use App\Http\Requests\Chevron\Job\PrintJobRequest;
use App\Http\Requests\Chevron\Job\ShowJobRequest;
use App\Http\Requests\Chevron\Job\StoreJobRequest;
use App\Http\Requests\Chevron\Job\UpdateJobRequest;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronItem;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobType;
use App\Models\Chevron\ChevronPort;
use App\Models\Chevron\ChevronService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CnfJobController extends Controller
{
    private function formData(): array
    {
        return [
            'services'   => ChevronService::where('is_active', true)->orderBy('name')->get(),
            'jobTypes'   => ChevronJobType::where('is_active', true)->orderBy('name')->get(),
            'ports'      => ChevronPort::where('branch_id', session('active_branch_id'))->where('is_active', true)->orderBy('name')->get(),
            'currencies' => ChevronJob::currencies(),
            'countries'  => ChevronJob::countries(),
            'units'      => ChevronItem::units(),
        ];
    }

    public function index(IndexJobRequest $request)
    {

        if ($request->ajax()) {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $query = ChevronJob::with(['service', 'jobType', 'port'])
                ->where('branch_id', session('active_branch_id'))
                ->when($fromDate, fn ($q) => $q->whereDate('job_date', '>=', $fromDate))
                ->when($toDate, fn ($q) => $q->whereDate('job_date', '<=', $toDate))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('service_name', fn ($r) => $r->service?->name ?? '—')
                ->addColumn('job_type_name', fn ($r) => $r->jobType?->name ?? '—')
                ->addColumn('port_name', fn ($r) => $r->port?->name ?? '—')
                ->editColumn('job_date', fn ($r) => $r->job_date?->format('d M Y') ?? '—')
                ->editColumn('be_date', fn ($r) => $r->be_date?->format('d M Y') ?? '—')
                ->editColumn('bl_date', fn ($r) => $r->bl_date?->format('d M Y') ?? '—')
                ->editColumn('invoice_date', fn ($r) => $r->invoice_date?->format('d M Y') ?? '—')
                ->editColumn('delivery_date', fn ($r) => $r->delivery_date?->format('d M Y') ?? '—')
                ->editColumn('eta_date', fn ($r) => $r->eta_date?->format('d M Y') ?? '—')
                ->addColumn('invoice_value_1_fmt', fn ($r) => $r->invoice_value_1 ? number_format($r->invoice_value_1, 2) : '—')
                ->addColumn('invoice_value_2_fmt', fn ($r) => $r->invoice_value_2 ? number_format($r->invoice_value_2, 2) : '—')
                ->addColumn('assessable_value_fmt', fn ($r) => $r->assessable_value ? number_format($r->assessable_value, 2) : '—')
                ->addColumn('assessable_value_bdt_fmt', fn ($r) => $r->assessable_value_bdt ? number_format($r->assessable_value_bdt, 2) : '—')
                ->addColumn('duty_amount_fmt', fn ($r) => $r->duty_amount ? number_format($r->duty_amount, 2) : '—')
                ->addColumn('vat_amount_fmt', fn ($r) => $r->vat_amount ? number_format($r->vat_amount, 2) : '—')
                ->addColumn('net_payable_1_fmt', fn ($r) => $r->net_payable_1 ? number_format($r->net_payable_1, 2) : '—')
                ->addColumn('net_payable_2_fmt', fn ($r) => $r->net_payable_2 ? number_format($r->net_payable_2, 2) : '—')
                ->filterColumn('service_name', fn ($q, $k) => $q->whereHas('service', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->filterColumn('job_type_name', fn ($q, $k) => $q->whereHas('jobType', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->filterColumn('port_name', fn ($q, $k) => $q->whereHas('port', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Active'  => '<span class="badge bg-success">Active</span>',
                    'Pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    default   => '<span class="badge bg-secondary">Closed</span>',
                })
                ->addColumn('action', function ($r) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('cnf.job.view')) {
                        $html .= '<a href="'.route('chevron.cnf.jobs.show', $r->id).'" class="btn btn-sm btn-outline-secondary py-0 px-1" title="View"><i class="fa fa-eye"></i></a> ';
                    }

                    if ($request->user()->hasPermission('cnf.job.print')) {
                        $html .= '<a href="'.route('chevron.cnf.jobs.print', $r->id).'" target="_blank" class="btn btn-sm btn-outline-dark py-0 px-1" title="Print"><i class="fa fa-print"></i></a> ';
                    }

                    if ($request->user()->hasPermission('cnf.job.edit')) {
                        $html .= '<a href="'.route('chevron.cnf.jobs.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a> ';
                    }

                    if ($request->user()->hasPermission('cnf.job.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete" data-url="'.route('chevron.cnf.jobs.destroy', $r->id).'" data-name="'.e($r->job_no).'" title="Delete"><i class="fa fa-trash"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.cnf.jobs.index');
    }

    public function create(CreateJobRequest $request)
    {
        return view('chevron.cnf.jobs.create', array_merge($this->formData(), ['job' => null]));
    }

    public function store(StoreJobRequest $request)
    {

        $data = $this->prepareData($request);
        $jobTypeId = (int) $request->job_type_id;
        $portId = (int) $request->port_id;

        $job = DB::transaction(function () use ($data, $jobTypeId, $portId) {
            $data['job_no'] = ChevronJob::generateJobNo($jobTypeId, $portId);

            return ChevronJob::create($data);
        });

        return redirect()->route('chevron.cnf.jobs.index')
            ->with('success', 'Job '.$job->job_no.' created successfully.');
    }

    public function show(ShowJobRequest $request, ChevronJob $job)
    {
        $job->load(['jobType', 'port', 'customer', 'item']);

        return view('chevron.cnf.jobs.show', compact('job'));
    }

    public function print(PrintJobRequest $request, ChevronJob $job)
    {
        $job->load(['jobType', 'port', 'customer', 'item']);

        return view('chevron.cnf.jobs.print', compact('job'));
    }

    public function edit(EditJobRequest $request, ChevronJob $job)
    {
        return view('chevron.cnf.jobs.create', array_merge($this->formData(), ['job' => $job]));
    }

    public function update(UpdateJobRequest $request, ChevronJob $job)
    {

        $job->update($this->prepareData($request));

        return redirect()->route('chevron.cnf.jobs.index')
            ->with('success', 'Job '.$job->job_no.' updated successfully.');
    }

    public function destroy(DestroyJobRequest $request, ChevronJob $job)
    {
        $job->delete();

        return response()->json(['message' => 'Job '.$job->job_no.' deleted.']);
    }

    public function searchCustomers(IndexJobRequest $request)
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

    public function searchItems(IndexJobRequest $request)
    {
        $q = $request->get('q', '');
        $results = ChevronItem::where('item_code', 'like', '%'.$q.'%')
            ->orWhere('item_name', 'like', '%'.$q.'%')
            ->where('status', 'Active')
            ->limit(20)
            ->select(['id', 'item_code', 'item_name', 'purchase_unit'])
            ->get()
            ->map(fn ($i) => [
                'id'            => $i->id,
                'text'          => $i->item_code.' — '.$i->item_name,
                'name'          => $i->item_name ?: $i->item_code,
                'purchase_unit' => $i->purchase_unit,
            ]);

        return response()->json($results);
    }

    private function prepareData(Request $request): array
    {
        $data = $request->except(['_token', '_method']);

        // Null-ify empty strings for date / numeric fields
        $nullable = [
            'job_date',
            'copy_doc_received_date',
            'original_doc_received_date',
            'eta_date',
            'hbi_hawb_date',
            'be_date',
            'lc_date',
            'lca_date',
            'bl_date',
            'mbl_mawb_date',
            'invoice_date',
            'lading_date',
            'flight_date',
            'arrived_date',
            'common_lading_date',
            'w_rent_due_date',
            'berthing_date',
            'port_bill_date',
            'labour_bill_date',
            'etb_date',
            'delivery_date',
            'etd_date',
        ];

        foreach ($nullable as $field) {

            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }

        }

        // Calculate assessable_value_bdt
        $av = (float) ($data['assessable_value'] ?? 0);
        $rate = (float) ($data['currency_rate'] ?? 0);
        $data['assessable_value_bdt'] = ($av > 0 && $rate > 0) ? round($av * $rate, 2) : null;

        // Null-ify numeric zeros stored as empty strings
        $numerics = [
            'pack_quantity',
            'gross_weight',
            'net_weight',
            'received_amount',
            'due_amount',
            'assessable_value',
            'currency_rate',
            'port_bill_amount',
            'labour_bill_amount',
            'shipping_charge',
            'pickup_charge_1',
            'pickup_charge_2',
            'cnf_charge_1',
            'cnf_charge_2',
            'stuffing_charge_1',
            'stuffing_charge_2',
            'carrier_bill_1',
            'carrier_bill_2',
            'mbl_free_1',
            'mbl_free_2',
            'hbl_charge_1',
            'hbl_charge_2',
            'ps_to_agent_1',
            'ps_to_agent_2',
            'ps_to_b_co_1',
            'ps_to_b_co_2',
            'noc_charge_1',
            'noc_charge_2',
            'other_charge_1',
            'other_charge_2',
            'invoice_value_1',
            'invoice_value_2',
            'duty_rate',
            'duty_amount',
            'ait_rate',
            'ait_amount',
            'sup_tax_rate',
            'sup_tax_amount',
            'vat_rate',
            'vat_amount',
            'rd_rate',
            'rd_amount',
            'atv_rate',
            'atv_amount',
            'df_vat_rate',
            'df_vat_amount',
            'total_payable_1',
            'total_payable_2',
            'comm_discount_pct',
            'comm_discount_1',
            'comm_discount_2',
            'net_payable_1',
            'net_payable_2',
        ];

        foreach ($numerics as $field) {

            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }

        }

        $data['branch_id'] = session('active_branch_id');

        return $data;
    }
}
