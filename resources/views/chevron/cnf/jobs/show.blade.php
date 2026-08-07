@extends('chevron.layouts.app')

@section('title', 'Job — ' . $job->job_no)

@push('styles')
<style>
:root {
    --c-border  : #e2e8f0;
    --c-hdr-bg  : #f8fafc;
    --c-hdr-txt : #1e293b;
    --c-shadow  : 0 1px 3px rgba(0,0,0,.06);
    --c-radius  : .45rem;
    --c-label   : #64748b;
}
.job-card {
    border: 1px solid var(--c-border);
    border-radius: var(--c-radius);
    overflow: hidden;
    margin-bottom: .85rem;
    box-shadow: var(--c-shadow);
    background: #fff;
}
.job-card-hdr {
    padding: .5rem 1rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    background: var(--c-hdr-bg);
    border-bottom: 1px solid var(--c-border);
    display: flex;
    align-items: center;
    gap: .45rem;
    color: var(--c-hdr-txt);
}
.job-card-body { padding: .75rem 1rem; }

.ac-blue   { border-left: 4px solid #2563eb; }
.ac-green  { border-left: 4px solid #16a34a; }
.ac-violet { border-left: 4px solid #7c3aed; }
.ac-sky    { border-left: 4px solid #0284c7; }
.ac-teal   { border-left: 4px solid #0d9488; }
.ac-red    { border-left: 4px solid #dc2626; }
.ac-slate  { border-left: 4px solid #475569; }
.ac-amber  { border-left: 4px solid #d97706; }
.ac-indigo { border-left: 4px solid #4f46e5; }

.form-grp-lbl {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #94a3b8;
    margin: .9rem 0 .5rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.form-grp-lbl::after { content:''; flex:1; height:1px; background:#e2e8f0; }

.info-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0;
}
.info-cell {
    flex: 0 0 25%;
    padding: .3rem .5rem .3rem 0;
}
.info-cell.half  { flex: 0 0 50%; }
.info-cell.full  { flex: 0 0 100%; }
.info-lbl {
    font-size: .68rem;
    font-weight: 600;
    color: var(--c-label);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .1rem;
}
.info-val {
    font-size: .83rem;
    color: #1e293b;
    font-weight: 500;
    min-height: 1.2rem;
}
.info-val.empty { color: #cbd5e1; font-style: italic; font-weight: 400; }

.page-hdr {
    background:#fff; border:1px solid var(--c-border); border-radius:var(--c-radius);
    padding:.6rem 1rem; margin-bottom:1rem; box-shadow:var(--c-shadow);
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem;
}

/* Duty table in sidebar */
.duty-tbl { width:100%; border-collapse:collapse; font-size:.78rem; }
.duty-tbl td { padding:.25rem .3rem; vertical-align:middle; }
.duty-tbl td:first-child { color:#374151; font-weight:500; }
.duty-tbl td:last-child { text-align:right; font-weight:600; color:#1e293b; }
.duty-tbl .total-row td { border-top:2px solid #1e293b; font-weight:700; padding-top:.4rem; }
</style>
@endpush

@section('content')

<div class="page-hdr">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="fa fa-file-alt text-primary"></i>
        <h6 class="mb-0 fw-bold">Job Sheet &mdash; <span class="text-primary">{{ $job->job_no }}</span></h6>
        <span class="badge bg-{{ $job->status === 'Active' ? 'success' : 'warning text-dark' }} ms-1">{{ $job->status }}</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.cnf.jobs.print', $job->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
            <i class="fa fa-print me-1"></i> Print
        </a>
        <a href="{{ route('chevron.cnf.jobs.edit', $job->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('chevron.cnf.jobs.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-3 align-items-start">
<div class="col-md-8">

    {{-- Job Information --}}
    <div class="form-grp-lbl"><i class="fa fa-info-circle"></i> Job Information</div>

    <div class="job-card ac-blue">
        <div class="job-card-hdr"><i class="fa fa-briefcase" style="color:#2563eb;"></i> Job Header</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Job No</div>
                    <div class="info-val">{{ $job->job_no }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Job Type</div>
                    <div class="info-val">{{ $job->jobType?->name ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Port</div>
                    <div class="info-val">{{ $job->port?->name ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Country of Origin</div>
                    <div class="info-val">{{ $job->country_of_origin ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Job Date</div>
                    <div class="info-val">{{ $job->job_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Status</div>
                    <div class="info-val">
                        <span class="badge bg-{{ $job->status === 'Active' ? 'success' : 'warning text-dark' }}">{{ $job->status }}</span>
                    </div>
                </div>
                <div class="info-cell half">
                    <div class="info-lbl">Remark</div>
                    <div class="info-val">{{ $job->remarks ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="job-card ac-green">
        <div class="job-card-hdr"><i class="fa fa-user-tie" style="color:#16a34a;"></i> Party &amp; Goods</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Party Name</div>
                    <div class="info-val">{{ $job->party_name ?: '—' }}</div>
                </div>
                <div class="info-cell half">
                    <div class="info-lbl">Party Address</div>
                    <div class="info-val">{{ $job->party_address ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Goods Name</div>
                    <div class="info-val">{{ $job->goods_name ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Pack Quantity</div>
                    <div class="info-val">{{ $job->pack_quantity ? rtrim(rtrim(number_format($job->pack_quantity, 3), '0'), '.').' '.($job->pack_unit ?? '') : '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Gross Weight</div>
                    <div class="info-val">{{ $job->gross_weight ? rtrim(rtrim(number_format($job->gross_weight, 3), '0'), '.').' '.($job->gross_weight_unit ?? 'KGS') : '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Net Weight</div>
                    <div class="info-val">{{ $job->net_weight ? rtrim(rtrim(number_format($job->net_weight, 3), '0'), '.').' '.($job->net_weight_unit ?? 'KGS') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="form-grp-lbl"><i class="fa fa-folder-open"></i> Documents</div>

    <div class="job-card ac-violet">
        <div class="job-card-hdr"><i class="fa fa-file-alt" style="color:#7c3aed;"></i> Custom Document &amp; Detail</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Copy Doc. Received Date</div>
                    <div class="info-val">{{ $job->copy_doc_received_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Original Doc. Received Date</div>
                    <div class="info-val">{{ $job->original_doc_received_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">ETA Date</div>
                    <div class="info-val">{{ $job->eta_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">HBL/HAWB No</div>
                    <div class="info-val">{{ $job->hbi_hawb_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">HBL/HAWB Date</div>
                    <div class="info-val">{{ $job->hbi_hawb_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">MBL/MAWB No</div>
                    <div class="info-val">{{ $job->mbl_mawb_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">MBL/MAWB Date</div>
                    <div class="info-val">{{ $job->mbl_mawb_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Invoice No</div>
                    <div class="info-val">{{ $job->invoice_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Invoice Date</div>
                    <div class="info-val">{{ $job->invoice_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">B/E No</div>
                    <div class="info-val">{{ $job->be_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">B/E Date</div>
                    <div class="info-val">{{ $job->be_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">LC No</div>
                    <div class="info-val">{{ $job->lc_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">LC Date</div>
                    <div class="info-val">{{ $job->lc_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">LCA No</div>
                    <div class="info-val">{{ $job->lca_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">LCA Date</div>
                    <div class="info-val">{{ $job->lca_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">PO No</div>
                    <div class="info-val">{{ $job->po_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Mate Code</div>
                    <div class="info-val">{{ $job->mate_code ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Logistics --}}
    <div class="form-grp-lbl"><i class="fa fa-ship"></i> Logistics</div>

    <div class="job-card ac-sky">
        <div class="job-card-hdr"><i class="fa fa-ship" style="color:#0284c7;"></i> Vessel &amp; Transport</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Lading No</div>
                    <div class="info-val">{{ $job->lading_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Lading Date</div>
                    <div class="info-val">{{ $job->lading_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Flight No</div>
                    <div class="info-val">{{ $job->flight_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Flight Date</div>
                    <div class="info-val">{{ $job->flight_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Vessel's Name</div>
                    <div class="info-val">{{ $job->vessel_name ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Boyge No</div>
                    <div class="info-val">{{ $job->boyge_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Vessel's ETB Agent</div>
                    <div class="info-val">{{ $job->vessel_etb_agent ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">AL No</div>
                    <div class="info-val">{{ $job->al_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Sailed No</div>
                    <div class="info-val">{{ $job->sailed_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Arrived Date</div>
                    <div class="info-val">{{ $job->arrived_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Common Lading Date</div>
                    <div class="info-val">{{ $job->common_lading_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">W/Rent Due Date</div>
                    <div class="info-val">{{ $job->w_rent_due_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Berthing</div>
                    <div class="info-val">{{ $job->berthing ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Berthing Date</div>
                    <div class="info-val">{{ $job->berthing_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Shed No</div>
                    <div class="info-val">{{ $job->shed_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Yard No</div>
                    <div class="info-val">{{ $job->yard_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Transport Name</div>
                    <div class="info-val">{{ $job->transport_name ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Transport No</div>
                    <div class="info-val">{{ $job->transport_no ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="job-card ac-teal">
        <div class="job-card-hdr"><i class="fa fa-warehouse" style="color:#0d9488;"></i> Port Operations</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Rot No</div>
                    <div class="info-val">{{ $job->rot_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">B/L Weight Measurement</div>
                    <div class="info-val">{{ $job->bl_weight_measurement ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Jetty Sarker Name</div>
                    <div class="info-val">{{ $job->jetty_sarker_name ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Contact No</div>
                    <div class="info-val">{{ $job->contact_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Unit No</div>
                    <div class="info-val">{{ $job->unit_no ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Operations & Delivery --}}
    <div class="form-grp-lbl"><i class="fa fa-truck"></i> Operations &amp; Delivery</div>

    <div class="job-card ac-red">
        <div class="job-card-hdr"><i class="fa fa-receipt" style="color:#dc2626;"></i> Bills &amp; Delivery</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">ETB Date</div>
                    <div class="info-val">{{ $job->etb_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Delivery Date</div>
                    <div class="info-val">{{ $job->delivery_date?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="job-card ac-slate">
        <div class="job-card-hdr"><i class="fa fa-handshake" style="color:#475569;"></i> Consignee &amp; Agent</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Consignee Name</div>
                    <div class="info-val">{{ $job->consignee_name ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Consignee Address</div>
                    <div class="info-val">{{ $job->consignee_address ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Agent Name</div>
                    <div class="info-val">{{ $job->agent_name ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Agent Address</div>
                    <div class="info-val">{{ $job->agent_address ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="job-card ac-slate">
        <div class="job-card-hdr"><i class="fa fa-boxes" style="color:#475569;"></i> Container &amp; Freight</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-lbl">Container No</div>
                    <div class="info-val">{{ $job->container_no ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Commodity</div>
                    <div class="info-val">{{ $job->commodity ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">No of Container</div>
                    <div class="info-val">{{ $job->no_of_container ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">POL</div>
                    <div class="info-val">{{ $job->pol ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">Destination</div>
                    <div class="info-val">{{ $job->destination ?: '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-lbl">ETD Date</div>
                    <div class="info-val">{{ $job->etd_date?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end col-md-8 --}}

<div class="col-md-4" style="position:sticky;top:70px;align-self:start;max-height:calc(100vh - 80px);overflow-y:auto;">

    {{-- Financial --}}
    <div class="job-card ac-amber">
        <div class="job-card-hdr"><i class="fa fa-coins" style="color:#d97706;"></i> Financial</div>
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell half">
                    <div class="info-lbl">Currency</div>
                    <div class="info-val">{{ $job->currency_type ?: '—' }}</div>
                </div>
                <div class="info-cell half">
                    <div class="info-lbl">Exchange Rate (BDT)</div>
                    <div class="info-val">{{ $job->currency_rate ? rtrim(rtrim(number_format($job->currency_rate, 9), '0'), '.') : '—' }}</div>
                </div>
                <div class="info-cell half">
                    <div class="info-lbl">Invoice Value ({{ $job->currency_type ?: 'Orig.' }})</div>
                    <div class="info-val">{{ $job->invoice_value_1 ? number_format($job->invoice_value_1, 2) : '—' }}</div>
                </div>
                <div class="info-cell half">
                    <div class="info-lbl">Invoice Value (BDT)</div>
                    <div class="info-val">{{ $job->invoice_value_2 ? number_format($job->invoice_value_2, 2) : '—' }}</div>
                </div>
                <div class="info-cell full">
                    <div class="info-lbl">Assessable Value</div>
                    <div class="info-val fw-bold text-primary">{{ $job->assessable_value ? number_format($job->assessable_value, 2) : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Duty --}}
    <div class="job-card ac-indigo">
        <div class="job-card-hdr"><i class="fa fa-percent" style="color:#4f46e5;"></i> Duty</div>
        <div class="job-card-body">
            @php
            $dutyRows = [
                ['Duty/CD',  'duty'],
                ['R/D',      'rd'],
                ['SD',       'sup_tax'],
                ['VAT',      'vat'],
                ['AIT',      'ait'],
                ['AT',       'atv'],
                ['DF VAT',   'df_vat'],
                ['Other',    'other'],
            ];
            $totalDuty = collect($dutyRows)->sum(fn($r) => (float)($job->{$r[1].'_amount'} ?? 0));
            @endphp
            <table class="duty-tbl">
                @foreach($dutyRows as [$label, $key])
                @if($job->{$key.'_amount'})
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format($job->{$key.'_amount'}, 2) }}</td>
                </tr>
                @endif
                @endforeach
                <tr class="total-row">
                    <td>Total Duty</td>
                    <td>{{ $totalDuty ? number_format($totalDuty, 2) : '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Timestamps --}}
    <div class="job-card" style="border-left:4px solid #94a3b8;">
        <div class="job-card-body">
            <div class="info-row">
                <div class="info-cell half">
                    <div class="info-lbl">Created</div>
                    <div class="info-val" style="font-size:.75rem;">{{ $job->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="info-cell half">
                    <div class="info-lbl">Last Updated</div>
                    <div class="info-val" style="font-size:.75rem;">{{ $job->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end col-md-4 --}}
</div>{{-- end row --}}

@endsection
