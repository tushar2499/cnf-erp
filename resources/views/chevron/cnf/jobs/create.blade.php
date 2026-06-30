@extends('chevron.layouts.app')

@section('title', $job ? 'Edit Job — ' . $job->job_no : 'New C&F Job')

@push('styles')
<style>
.job-card { border: 1px solid #e5e7eb; border-radius: .4rem; overflow: hidden; margin-bottom: 1rem; }
.job-card-hdr { padding: .4rem .9rem; font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; display: flex; align-items: center; gap: .4rem; }
.job-card-body { padding: .75rem .9rem .5rem; }
.form-label { font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: .2rem; }
.req { color: #dc2626; }
.ro-field { background: #f8fafc !important; color: #6b7280; }
.charge-tbl { width: 100%; border-collapse: collapse; }
.charge-tbl td, .charge-tbl th { padding: .18rem .3rem; vertical-align: middle; font-size: .78rem; }
.charge-tbl thead th { background: #f1f5f9; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; font-size: .75rem; }
.charge-tbl td:first-child { white-space: nowrap; font-weight: 500; color: #374151; }
.charge-tbl tbody tr:nth-child(even) { background: #fafafa; }
.charge-tbl input.form-control-sm { font-size: .78rem; padding: .18rem .35rem; height: auto; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold">
        <i class="fa fa-file-alt me-2 text-primary"></i>
        @if($job)
            Edit Job &mdash; <span class="text-primary">{{ $job->job_no }}</span>
        @else
            New C&amp;F Job
        @endif
    </h5>
    <a href="{{ route('chevron.cnf.jobs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back to List
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2">
    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ $job ? route('chevron.cnf.jobs.update', $job->id) : route('chevron.cnf.jobs.store') }}">
    @csrf
    @if($job) @method('PUT') @endif
    <input type="hidden" name="customer_id" id="customerId" value="{{ old('customer_id', $job?->customer_id) }}">
    <input type="hidden" name="item_id"     id="itemId"     value="{{ old('item_id',     $job?->item_id) }}">
    <input type="hidden" name="party_name"  id="partyNameHidden" value="{{ old('party_name', $job?->party_name) }}">
    <input type="hidden" name="goods_name"  id="goodsNameHidden" value="{{ old('goods_name', $job?->goods_name) }}">

<div class="row g-3 align-items-start">
<div class="col-md-8">

    {{-- 1. Job Header --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#dbeafe; border-left:4px solid #1d4ed8;">
            <i class="fa fa-briefcase" style="color:#1d4ed8;"></i>
            <span style="color:#1e3a8a;">Job Header</span>
            @if($job)<span class="ms-auto badge bg-primary">{{ $job->job_no }}</span>@endif
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Service <span class="req">*</span></label>
                    <select name="service_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id', $job?->service_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Job Type <span class="req">*</span></label>
                    <select name="job_type_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($jobTypes as $jt)
                            <option value="{{ $jt->id }}" {{ old('job_type_id', $job?->job_type_id) == $jt->id ? 'selected' : '' }}>{{ $jt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Port <span class="req">*</span></label>
                    <select name="port_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($ports as $p)
                            <option value="{{ $p->id }}" {{ old('port_id', $job?->port_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country of Origin</label>
                    <select name="country_of_origin" class="form-select form-select-sm">
                        <option value="">-- Select Origin of Country --</option>
                        @foreach($countries as $c)
                            <option value="{{ $c }}" {{ old('country_of_origin', $job?->country_of_origin) === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Job Date <span class="req">*</span></label>
                    <input type="date" name="job_date" class="form-control form-control-sm" value="{{ old('job_date', $job?->job_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="Active"  {{ old('status', $job?->status ?? 'Active')  === 'Active'  ? 'selected' : '' }}>Active</option>
                        <option value="Pending" {{ old('status', $job?->status)              === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Closed"  {{ old('status', $job?->status)              === 'Closed'  ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Party & Goods --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#dcfce7; border-left:4px solid #16a34a;">
            <i class="fa fa-user-tie" style="color:#16a34a;"></i>
            <span style="color:#14532d;">Party &amp; Goods</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Party Name <span class="req">*</span></label>
                    <select id="partyNameSelect" class="form-select form-select-sm w-100">
                        @if($job?->party_name)
                            <option value="{{ $job->customer_id ?? $job->party_name }}" selected>{{ $job->party_name }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Party Address</label>
                    <textarea name="party_address" class="form-control form-control-sm" rows="1" placeholder="Party address">{{ old('party_address', $job?->party_address) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Goods Name <span class="req">*</span></label>
                    <select id="goodsNameSelect" class="form-select form-select-sm w-100">
                        @if($job?->goods_name)
                            <option value="{{ $job->item_id ?? $job->goods_name }}" selected>{{ $job->goods_name }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pack Quantity</label>
                    <input type="number" name="pack_quantity" class="form-control form-control-sm" step="0.001" value="{{ old('pack_quantity', $job?->pack_quantity) }}" placeholder="Pack Quantity">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pack Unit</label>
                    <input type="text" name="pack_unit" class="form-control form-control-sm" value="{{ old('pack_unit', $job?->pack_unit) }}" placeholder="Entry Pack Unit">
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Document Dates & Weight --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#fef9c3; border-left:4px solid #ca8a04;">
            <i class="fa fa-calendar-alt" style="color:#ca8a04;"></i>
            <span style="color:#713f12;">Document Dates &amp; Weight</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Copy Doc. Received Date</label>
                    <input type="date" name="copy_doc_received_date" class="form-control form-control-sm" value="{{ old('copy_doc_received_date', $job?->copy_doc_received_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Original Doc. Received Date</label>
                    <input type="date" name="original_doc_received_date" class="form-control form-control-sm" value="{{ old('original_doc_received_date', $job?->original_doc_received_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ETA Date</label>
                    <input type="date" name="eta_date" class="form-control form-control-sm" value="{{ old('eta_date', $job?->eta_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">HBI/HAWB No</label>
                    <input type="text" name="hbi_hawb_no" class="form-control form-control-sm" value="{{ old('hbi_hawb_no', $job?->hbi_hawb_no) }}" placeholder="HBI/HAWB No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">HBI/HAWB Date</label>
                    <input type="date" name="hbi_hawb_date" class="form-control form-control-sm" value="{{ old('hbi_hawb_date', $job?->hbi_hawb_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gross Weight</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="gross_weight" class="form-control form-control-sm" step="0.001" value="{{ old('gross_weight', $job?->gross_weight) }}" placeholder="Gross Weight">
                        <input type="text"   name="gross_weight_unit" class="form-control form-control-sm" style="max-width:70px;" value="{{ old('gross_weight_unit', $job?->gross_weight_unit) }}" placeholder="Unit">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Net Weight</label>
                    <input type="number" name="net_weight" class="form-control form-control-sm" step="0.001" value="{{ old('net_weight', $job?->net_weight) }}" placeholder="Net Weight">
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Customs Documents --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#ede9fe; border-left:4px solid #7c3aed;">
            <i class="fa fa-stamp" style="color:#7c3aed;"></i>
            <span style="color:#4c1d95;">Customs Documents</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">B/E No</label>
                    <input type="text" name="be_no" class="form-control form-control-sm" value="{{ old('be_no', $job?->be_no) }}" placeholder="Entry B/E No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">B/E Date</label>
                    <input type="date" name="be_date" class="form-control form-control-sm" value="{{ old('be_date', $job?->be_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LC No</label>
                    <input type="text" name="lc_no" class="form-control form-control-sm" value="{{ old('lc_no', $job?->lc_no) }}" placeholder="Entry LC No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LC Date</label>
                    <input type="date" name="lc_date" class="form-control form-control-sm" value="{{ old('lc_date', $job?->lc_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LCA No</label>
                    <input type="text" name="lca_no" class="form-control form-control-sm" value="{{ old('lca_no', $job?->lca_no) }}" placeholder="Entry LCA No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LCA Date</label>
                    <input type="date" name="lca_date" class="form-control form-control-sm" value="{{ old('lca_date', $job?->lca_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PO No</label>
                    <input type="text" name="po_no" class="form-control form-control-sm" value="{{ old('po_no', $job?->po_no) }}" placeholder="Entry PO No">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mate Code</label>
                    <input type="text" name="mate_code" class="form-control form-control-sm" value="{{ old('mate_code', $job?->mate_code) }}" placeholder="Entry mate code">
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Shipping Documents --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#fce7f3; border-left:4px solid #db2777;">
            <i class="fa fa-file-invoice" style="color:#db2777;"></i>
            <span style="color:#831843;">Shipping Documents</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">B/L No</label>
                    <input type="text" name="bl_no" class="form-control form-control-sm" value="{{ old('bl_no', $job?->bl_no) }}" placeholder="Entry B/L No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">B/L Date</label>
                    <input type="date" name="bl_date" class="form-control form-control-sm" value="{{ old('bl_date', $job?->bl_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">MBL/MAWB No</label>
                    <input type="text" name="mbl_mawb_no" class="form-control form-control-sm" value="{{ old('mbl_mawb_no', $job?->mbl_mawb_no) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">MBL/MAWB Date</label>
                    <input type="date" name="mbl_mawb_date" class="form-control form-control-sm" value="{{ old('mbl_mawb_date', $job?->mbl_mawb_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Invoice No</label>
                    <input type="text" name="invoice_no" class="form-control form-control-sm" value="{{ old('invoice_no', $job?->invoice_no) }}" placeholder="Entry Invoice Number">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ old('invoice_date', $job?->invoice_date?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- 6. Vessel & Transport --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#e0f2fe; border-left:4px solid #0284c7;">
            <i class="fa fa-ship" style="color:#0284c7;"></i>
            <span style="color:#0c4a6e;">Vessel &amp; Transport</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Lading No</label>
                    <input type="text" name="lading_no" class="form-control form-control-sm" value="{{ old('lading_no', $job?->lading_no) }}" placeholder="Lading No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Lading Date</label>
                    <input type="date" name="lading_date" class="form-control form-control-sm" value="{{ old('lading_date', $job?->lading_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Flight No</label>
                    <input type="text" name="flight_no" class="form-control form-control-sm" value="{{ old('flight_no', $job?->flight_no) }}" placeholder="Flight No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Flight Date</label>
                    <input type="date" name="flight_date" class="form-control form-control-sm" value="{{ old('flight_date', $job?->flight_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Vessel's Name</label>
                    <input type="text" name="vessel_name" class="form-control form-control-sm" value="{{ old('vessel_name', $job?->vessel_name) }}" placeholder="Vessel's Name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Boyge No</label>
                    <input type="text" name="boyge_no" class="form-control form-control-sm" value="{{ old('boyge_no', $job?->boyge_no) }}" placeholder="Boyge No">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vessel's ETB Agent</label>
                    <input type="text" name="vessel_etb_agent" class="form-control form-control-sm" value="{{ old('vessel_etb_agent', $job?->vessel_etb_agent) }}" placeholder="Vessel's ETB Agent">
                </div>
                <div class="col-md-2">
                    <label class="form-label">AL No</label>
                    <input type="text" name="al_no" class="form-control form-control-sm" value="{{ old('al_no', $job?->al_no) }}" placeholder="AL No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sailed No</label>
                    <input type="text" name="sailed_no" class="form-control form-control-sm" value="{{ old('sailed_no', $job?->sailed_no) }}" placeholder="Sailed No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Arrived Date</label>
                    <input type="date" name="arrived_date" class="form-control form-control-sm" value="{{ old('arrived_date', $job?->arrived_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Common Lading Date</label>
                    <input type="date" name="common_lading_date" class="form-control form-control-sm" value="{{ old('common_lading_date', $job?->common_lading_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">W/Rent Due Date</label>
                    <input type="date" name="w_rent_due_date" class="form-control form-control-sm" value="{{ old('w_rent_due_date', $job?->w_rent_due_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Berthing</label>
                    <input type="text" name="berthing" class="form-control form-control-sm" value="{{ old('berthing', $job?->berthing) }}" placeholder="BERTHING">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Berthing Date</label>
                    <input type="date" name="berthing_date" class="form-control form-control-sm" value="{{ old('berthing_date', $job?->berthing_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Shed No</label>
                    <input type="text" name="shed_no" class="form-control form-control-sm" value="{{ old('shed_no', $job?->shed_no) }}" placeholder="Shed No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Yard No</label>
                    <input type="text" name="yard_no" class="form-control form-control-sm" value="{{ old('yard_no', $job?->yard_no) }}" placeholder="Yard No">
                </div>
            </div>
        </div>
    </div>

    {{-- 7. Port Operations --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#ccfbf1; border-left:4px solid #0d9488;">
            <i class="fa fa-warehouse" style="color:#0d9488;"></i>
            <span style="color:#134e4a;">Port Operations</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Rot No</label>
                    <input type="text" name="rot_no" class="form-control form-control-sm" value="{{ old('rot_no', $job?->rot_no) }}" placeholder="Rot No">
                </div>
                <div class="col-md-3">
                    <label class="form-label">B/L Weight Measurement</label>
                    <input type="text" name="bl_weight_measurement" class="form-control form-control-sm" value="{{ old('bl_weight_measurement', $job?->bl_weight_measurement) }}" placeholder="B/L Weight Measurement">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jetty Sarker Name</label>
                    <input type="text" name="jetty_sarker_name" class="form-control form-control-sm" value="{{ old('jetty_sarker_name', $job?->jetty_sarker_name) }}" placeholder="Jetty Sarker Name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Contact No</label>
                    <input type="text" name="contact_no" class="form-control form-control-sm" value="{{ old('contact_no', $job?->contact_no) }}" placeholder="Contact No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit No</label>
                    <input type="text" name="unit_no" class="form-control form-control-sm" value="{{ old('unit_no', $job?->unit_no) }}" placeholder="Unit No">
                </div>
            </div>
        </div>
    </div>

    {{-- 8. Bills & Delivery --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#fee2e2; border-left:4px solid #dc2626;">
            <i class="fa fa-receipt" style="color:#dc2626;"></i>
            <span style="color:#7f1d1d;">Bills &amp; Delivery</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Port Bill Amount</label>
                    <input type="number" name="port_bill_amount" class="form-control form-control-sm" step="0.01" value="{{ old('port_bill_amount', $job?->port_bill_amount) }}" placeholder="Port Bill Amount">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Port Bill Date</label>
                    <input type="date" name="port_bill_date" class="form-control form-control-sm" value="{{ old('port_bill_date', $job?->port_bill_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Labour Bill Amount</label>
                    <input type="number" name="labour_bill_amount" class="form-control form-control-sm" step="0.01" value="{{ old('labour_bill_amount', $job?->labour_bill_amount) }}" placeholder="Labour Bill Amount">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Labour Bill Date</label>
                    <input type="date" name="labour_bill_date" class="form-control form-control-sm" value="{{ old('labour_bill_date', $job?->labour_bill_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ETB Date</label>
                    <input type="date" name="etb_date" class="form-control form-control-sm" value="{{ old('etb_date', $job?->etb_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Shipping Charge</label>
                    <input type="number" name="shipping_charge" class="form-control form-control-sm" step="0.01" value="{{ old('shipping_charge', $job?->shipping_charge) }}" placeholder="Shipping Charge">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transport Name</label>
                    <input type="text" name="transport_name" class="form-control form-control-sm" value="{{ old('transport_name', $job?->transport_name) }}" placeholder="Transport Name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Transport No</label>
                    <input type="text" name="transport_no" class="form-control form-control-sm" value="{{ old('transport_no', $job?->transport_no) }}" placeholder="Transport No">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control form-control-sm" value="{{ old('delivery_date', $job?->delivery_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="1" placeholder="Remarks">{{ old('remarks', $job?->remarks) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- 9. Consignee & Agent --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#f0fdf4; border-left:4px solid #22c55e;">
            <i class="fa fa-handshake" style="color:#22c55e;"></i>
            <span style="color:#14532d;">Consignee &amp; Agent</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Consignee Name</label>
                    <input type="text" name="consignee_name" class="form-control form-control-sm" value="{{ old('consignee_name', $job?->consignee_name) }}" placeholder="Name of consignee">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Consignee Address</label>
                    <textarea name="consignee_address" class="form-control form-control-sm" rows="1" placeholder="Consignee address">{{ old('consignee_address', $job?->consignee_address) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Agent Name</label>
                    <input type="text" name="agent_name" class="form-control form-control-sm" value="{{ old('agent_name', $job?->agent_name) }}" placeholder="Name of agent">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Agent Address</label>
                    <textarea name="agent_address" class="form-control form-control-sm" rows="1" placeholder="Agent address">{{ old('agent_address', $job?->agent_address) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- 10. Container & Freight --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#f1f5f9; border-left:4px solid #64748b;">
            <i class="fa fa-boxes" style="color:#64748b;"></i>
            <span style="color:#1e293b;">Container &amp; Freight</span>
        </div>
        <div class="job-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Container No</label>
                    <textarea name="container_no" class="form-control form-control-sm" rows="2" placeholder="Container No">{{ old('container_no', $job?->container_no) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Commodity</label>
                    <input type="text" name="commodity" class="form-control form-control-sm" value="{{ old('commodity', $job?->commodity) }}" placeholder="Commodity">
                </div>
                <div class="col-md-2">
                    <label class="form-label">No of Container</label>
                    <input type="text" name="no_of_container" class="form-control form-control-sm" value="{{ old('no_of_container', $job?->no_of_container) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">POL</label>
                    <input type="text" name="pol" class="form-control form-control-sm" value="{{ old('pol', $job?->pol) }}" placeholder="POL">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Destination</label>
                    <input type="text" name="destination" class="form-control form-control-sm" value="{{ old('destination', $job?->destination) }}" placeholder="Destination">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ETD Date</label>
                    <input type="date" name="etd_date" class="form-control form-control-sm" value="{{ old('etd_date', $job?->etd_date?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

</div>{{-- end col-md-8 left --}}
<div class="col-md-4" style="position:sticky;top:70px;align-self:start;max-height:calc(100vh - 80px);overflow-y:auto;">

    {{-- 11. Financial --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#fffbeb; border-left:4px solid #d97706;">
            <i class="fa fa-dollar-sign" style="color:#d97706;"></i>
            <span style="color:#78350f;">Financial</span>
        </div>
        <div class="job-card-body">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label">Received Amount <span class="req">*</span></label>
                    <input type="number" name="received_amount" class="form-control form-control-sm text-end" step="0.01" value="{{ old('received_amount', $job?->received_amount) }}" placeholder="0.00">
                </div>
                <div class="col-6">
                    <label class="form-label">Due Amount</label>
                    <input type="number" name="due_amount" class="form-control form-control-sm text-end" step="0.01" value="{{ old('due_amount', $job?->due_amount) }}" placeholder="0.00">
                </div>
                <div class="col-12">
                    <label class="form-label">Assessable Value <span class="req">*</span></label>
                    <input type="number" name="assessable_value" id="assessableValue" class="form-control form-control-sm text-end" step="0.01" value="{{ old('assessable_value', $job?->assessable_value) }}" placeholder="0.00">
                </div>
                <div class="col-12">
                    <label class="form-label">Currency Rate</label>
                    <div class="input-group input-group-sm">
                        <select name="currency_type" id="currencyType" class="form-select form-select-sm" style="max-width:80px;">
                            <option value="">--</option>
                            @foreach($currencies as $cur)
                                <option value="{{ $cur }}" {{ old('currency_type', $job?->currency_type) === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-text px-2">=</span>
                        <input type="number" name="currency_rate" id="currencyRate" class="form-control form-control-sm text-end" step="0.0001" value="{{ old('currency_rate', $job?->currency_rate) }}" placeholder="Exchange rate">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">BDT Equivalent <small class="text-muted">(auto)</small></label>
                    <input type="number" id="assessableBdt" name="assessable_value_bdt" class="form-control form-control-sm ro-field text-end" readonly value="{{ old('assessable_value_bdt', $job?->assessable_value_bdt) }}" placeholder="Auto calculated">
                </div>
            </div>
        </div>
    </div>

    {{-- 12. Charges & Taxes --}}
    <div class="job-card">
        <div class="job-card-hdr" style="background:#1e293b; border-left:4px solid #0ea5e9;">
            <i class="fa fa-calculator" style="color:#38bdf8;"></i>
            <span style="color:#e2e8f0;">Charges &amp; Taxes</span>
        </div>
        <div class="p-3">
            {{-- Charges --}}
            <p class="fw-600 text-muted small mb-2" style="color:#475569;border-bottom:1px solid #e2e8f0;padding-bottom:4px;">
                <i class="fa fa-list-ul me-1"></i> Charges
            </p>
            <div class="mb-3">
                <table class="charge-tbl">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end" style="width:90px;">Col 1</th>
                            <th class="text-end" style="width:90px;">BDT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>B/E E.F.R No @</td>
                            <td colspan="2"><input type="text" name="be_efr_no" class="form-control form-control-sm" value="{{ old('be_efr_no', $job?->be_efr_no) }}" placeholder="B/E E. F. R No"></td>
                        </tr>
                        @php
                        $chargeRows = [
                            ['Pickup Charge @',   'pickup_charge'],
                            ['C&F Charge @',      'cnf_charge'],
                            ['Stuffing Charge @', 'stuffing_charge'],
                            ['Carrier Bill @',    'carrier_bill'],
                            ['MB/L Free @',       'mbl_free'],
                            ['HB/L Charge @',     'hbl_charge'],
                            ['P/S to Agent @',    'ps_to_agent'],
                            ['P/S to B & Co @',   'ps_to_b_co'],
                            ['NOC Charge @',      'noc_charge'],
                            ['Other @',           'other_charge'],
                            ['Invoice Value @',   'invoice_value'],
                        ];
                        @endphp
                        @foreach($chargeRows as [$label, $key])
                        <tr>
                            <td>{{ $label }}</td>
                            <td><input type="number" name="{{ $key }}_1" class="form-control form-control-sm charge-c1 text-end" step="0.01" value="{{ old($key.'_1', $job?->{$key.'_1'}) }}" data-key="{{ $key }}"></td>
                            <td><input type="number" name="{{ $key }}_2" id="c2_{{ $key }}" class="form-control form-control-sm charge-c2 text-end" step="0.01" value="{{ old($key.'_2', $job?->{$key.'_2'}) }}"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Taxes --}}
            <p class="fw-600 text-muted small mb-2 mt-1" style="color:#475569;border-bottom:1px solid #e2e8f0;padding-bottom:4px;">
                <i class="fa fa-percent me-1"></i> Taxes
            </p>
            <div>
                <table class="charge-tbl">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end" style="width:80px;">Rate %</th>
                            <th class="text-end" style="width:90px;">Amount (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $taxRows = [
                            ['Duty @',     'duty'],
                            ['AIT @',      'ait'],
                            ['Sup. Tax @', 'sup_tax'],
                            ['VAT @',      'vat'],
                            ['R/D @',      'rd'],
                            ['ATV @',      'atv'],
                            ['DF VAT @',   'df_vat'],
                        ];
                        @endphp
                        @foreach($taxRows as [$label, $key])
                        <tr>
                            <td>{{ $label }}</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="{{ $key }}_rate" class="form-control form-control-sm tax-rate text-end" step="0.01" min="0" value="{{ old($key.'_rate', $job?->{$key.'_rate'}) }}" data-key="{{ $key }}">
                                    <span class="input-group-text px-1">%</span>
                                </div>
                            </td>
                            <td><input type="number" name="{{ $key }}_amount" id="tax_{{ $key }}" class="form-control form-control-sm tax-amount text-end" step="0.01" value="{{ old($key.'_amount', $job?->{$key.'_amount'}) }}"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-2" style="border-top:2px solid #1e293b; padding-top:.5rem; background:#f8fafc; border-radius:.3rem; padding:.5rem .3rem;">
                    <table class="charge-tbl">
                        <tr>
                            <td class="fw-bold" style="color:#374151;">Total Payable</td>
                            <td><input type="number" name="total_payable_1" id="totalPayable1" class="form-control form-control-sm ro-field text-end fw-bold" readonly value="{{ old('total_payable_1', $job?->total_payable_1) }}"></td>
                            <td><input type="number" name="total_payable_2" id="totalPayable2" class="form-control form-control-sm ro-field text-end fw-bold" readonly value="{{ old('total_payable_2', $job?->total_payable_2) }}"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Comm/Discount</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="comm_discount_pct" id="commPct" class="form-control form-control-sm text-end" step="0.01" value="{{ old('comm_discount_pct', $job?->comm_discount_pct) }}">
                                    <span class="input-group-text px-1">%</span>
                                </div>
                            </td>
                            <td><input type="number" name="comm_discount_2" id="commDiscount2" class="form-control form-control-sm ro-field text-end" readonly value="{{ old('comm_discount_2', $job?->comm_discount_2) }}"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold" style="color:#1d4ed8;">Net Payable</td>
                            <td><input type="number" name="net_payable_1" id="netPayable1" class="form-control form-control-sm ro-field text-end fw-bold" style="color:#1d4ed8;" readonly value="{{ old('net_payable_1', $job?->net_payable_1) }}"></td>
                            <td><input type="number" name="net_payable_2" id="netPayable2" class="form-control form-control-sm ro-field text-end fw-bold" style="color:#1d4ed8;" readonly value="{{ old('net_payable_2', $job?->net_payable_2) }}"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        {{-- hidden totals for col1 --}}
        <input type="hidden" name="comm_discount_1" id="commDiscount1">
        <input type="hidden" name="total_payable_1_hidden" id="totalPayable1h">
    </div>

    {{-- Submit bar --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('chevron.cnf.jobs.index') }}" class="btn btn-outline-secondary btn-sm px-4">
            <i class="fa fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-sm px-5 text-white fw-600" style="background:#1565c0; border-color:#1565c0;">
            <i class="fa fa-save me-1"></i> {{ $job ? 'Update Job' : 'Save Job' }}
        </button>
    </div>

</div>{{-- end col-md-4 right --}}
</div>{{-- end row --}}
</form>
@endsection

@push('scripts')
<script>
var SEARCH_CUSTOMERS = '{{ route('chevron.cnf.jobs.search-customers') }}';
var SEARCH_ITEMS     = '{{ route('chevron.cnf.jobs.search-items') }}';

$(function () {
    // ── Party Name Select2 AJAX ──
    $('#partyNameSelect').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search customer by name or ID...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: SEARCH_CUSTOMERS, dataType: 'json', delay: 250,
            data: p => ({ q: p.term }),
            processResults: d => ({ results: d }),
        },
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#partyNameHidden').val(d.name || d.text);
        $('#customerId').val(d.id);
        $('[name="party_address"]').val(d.address || '');
    }).on('select2:clear', function () {
        $('#partyNameHidden').val('');
        $('#customerId').val('');
        $('[name="party_address"]').val('');
    });

    // ── Goods Name Select2 AJAX ──
    $('#goodsNameSelect').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search item by code or name...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: SEARCH_ITEMS, dataType: 'json', delay: 250,
            data: p => ({ q: p.term }),
            processResults: d => ({ results: d }),
        },
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#goodsNameHidden').val(d.name || d.text);
        $('#itemId').val(d.id);
        if (d.purchase_unit) $('[name="pack_unit"]').val(d.purchase_unit);
    }).on('select2:clear', function () {
        $('#goodsNameHidden').val('');
        $('#itemId').val('');
        $('[name="pack_unit"]').val('');
    });

    // ── Calculations ──

    function getCurrencyRate() {
        return parseFloat($('#currencyRate').val()) || 0;
    }

    // BDT = invoice_value col2 (auto-filled by col1 × rate)
    function getInvoiceBdt() {
        return parseFloat($('#c2_invoice_value').val()) || 0;
    }

    // When currency rate changes: recalc assessable BDT + ALL charge col2 values
    function onRateChange() {
        var av   = parseFloat($('#assessableValue').val()) || 0;
        var rate = getCurrencyRate();
        $('#assessableBdt').val(av > 0 && rate > 0 ? (av * rate).toFixed(2) : '');

        // Recalc every charge col2 = col1 × rate
        $('.charge-c1').each(function () {
            var key = $(this).data('key');
            var c1  = parseFloat($(this).val()) || 0;
            $('#c2_' + key).val(c1 > 0 && rate > 0 ? (c1 * rate).toFixed(2) : '');
        });

        recalcTaxes();
        recalcTotals();
    }

    // When a charge col1 changes: update its col2, then refresh taxes/totals
    $(document).on('input', '.charge-c1', function () {
        var key  = $(this).data('key');
        var c1   = parseFloat($(this).val()) || 0;
        var rate = getCurrencyRate();
        $('#c2_' + key).val(c1 > 0 && rate > 0 ? (c1 * rate).toFixed(2) : '');

        if (key === 'invoice_value') recalcTaxes();
        recalcTotals();
    });

    // When invoice_value col2 is manually edited: recalc taxes from new value
    $(document).on('input', '#c2_invoice_value', function () {
        recalcTaxes();
        recalcTotals();
    });

    // Tax amount = invoice_value (BDT) × rate%
    function recalcTaxes() {
        var base = getInvoiceBdt();
        $('.tax-rate').each(function () {
            var key = $(this).data('key');
            var pct = parseFloat($(this).val()) || 0;
            $('#tax_' + key).val(base > 0 && pct > 0 ? (base * pct / 100).toFixed(2) : '');
        });
    }

    // When tax rate typed: recalc that tax, then totals
    $(document).on('input', '.tax-rate', function () {
        var key  = $(this).data('key');
        var pct  = parseFloat($(this).val()) || 0;
        var base = getInvoiceBdt();
        $('#tax_' + key).val(base > 0 && pct > 0 ? (base * pct / 100).toFixed(2) : '');
        recalcTotals();
    });

    // Totals: sum all charge col2 + all tax amounts
    function recalcTotals() {
        var sum2 = 0;
        $('.charge-c2').each(function () { sum2 += parseFloat($(this).val()) || 0; });
        $('.tax-amount').each(function () { sum2 += parseFloat($(this).val()) || 0; });

        $('#totalPayable2').val(sum2 > 0 ? sum2.toFixed(2) : '');

        var pct = parseFloat($('#commPct').val()) || 0;
        var cd2 = sum2 > 0 && pct > 0 ? (sum2 * pct / 100).toFixed(2) : '';
        $('#commDiscount2').val(cd2);
        $('#netPayable2').val(sum2 > 0 ? (sum2 - (parseFloat(cd2) || 0)).toFixed(2) : '');
    }

    $(document).on('input', '.charge-c2, .tax-amount', recalcTotals);
    $(document).on('input', '#commPct', recalcTotals);

    $('#assessableValue, #currencyRate').on('input', onRateChange);
    $('#currencyType').on('change', onRateChange);

    onRateChange(); // init on edit
});
</script>
@endpush
