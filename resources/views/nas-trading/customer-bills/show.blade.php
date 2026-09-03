@extends('nas-trading.layouts.app')
@section('title', 'Bill — ' . $customerBill->bill_no)
@push('styles')
    <style>
        .info-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .info-header {
            background: #0c2340;
            color: #fff;
            padding: .45rem 1rem;
            font-size: .8rem;
            font-weight: 700;
        }

        .info-body {
            padding: .75rem 1rem;
        }

        .info-label {
            font-size: .72rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        .info-value {
            font-size: .85rem;
            font-weight: 600;
        }

        .bill-items th {
            background: #1a6b60;
            color: #fff;
            font-size: .77rem;
            padding: .4rem .5rem;
        }

        .bill-items td {
            font-size: .8rem;
            padding: .35rem .5rem;
            vertical-align: middle;
        }

        @page {
            size: A4 portrait;
            margin: 40mm 12mm 25mm;
        }

        @media print {

            .top-navbar,
            .mobile-context-bar,
            .sidebar,
            footer,
            .d-print-none {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                min-height: 0 !important;
                width: 100% !important;
            }

            body {
                background: #fff !important;
                font-size: 9px !important;
                min-height: 0 !important;
                width: 100% !important;
            }

            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-card {
                border: 1px solid #ccc;
                border-radius: 0;
                margin-bottom: .35rem;
                page-break-inside: avoid;
            }

            .info-header {
                background: #0c2340 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: .3rem .6rem;
                font-size: .7rem;
            }

            .info-body {
                padding: .4rem .6rem;
            }

            .info-label {
                font-size: .62rem;
            }

            .info-value {
                font-size: .72rem;
            }

            .bill-items th {
                background: #1a6b60 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: .25rem .35rem;
                font-size: .65rem;
            }

            .bill-items td {
                padding: .2rem .35rem;
                font-size: .65rem;
            }

            .table-success {
                background: #d1e7dd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .row {
                page-break-inside: avoid;
                margin: 0 !important;
            }

            .row.g-3>* {
                padding: 0 !important;
            }

            .col-md-8,
            .col-md-4 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            .info-card .row.g-2>* {
                padding: 0 .2rem !important;
            }

            .info-card .col-md-3 {
                width: 50% !important;
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            a[href]::after {
                content: none !important;
            }

            .container-fluid {
                padding: 0 !important;
            }

            .table {
                margin-bottom: 0 !important;
            }

            .table-sm td,
            .table-sm th {
                padding: .2rem .35rem;
                font-size: .65rem;
            }

            .print-totals td {
                border: none !important;
            }

            .print-totals tr.border-dotted-top td {
                border-top: 1px dotted #999 !important;
            }

            .print-totals tr.border-solid-both td {
                border-top: 1px solid #000 !important;
                border-bottom: 1px solid #000 !important;
            }

            .print-totals tr.border-solid-top td {
                border-top: 2px solid #000 !important;
            }
        }
    </style>
@endpush

@section('content')


    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <div>
            <h4 class="mb-0"><i class="fa fa-file-invoice-dollar me-2 text-success"></i> {{ $customerBill->bill_no }}</h4>
            <small class="text-muted">{{ $customerBill->bill_date?->format('d-M-Y') }}</small>
        </div>
        <div class="d-flex gap-2">
            @if ($customerBill->status === 'Draft')
                <a href="{{ route('nas-trading.customer-bills.edit', $customerBill->id) }}"
                    class="btn btn-sm btn-outline-primary"><i class="fa fa-edit me-1"></i>Edit</a>
                <button class="btn btn-sm btn-outline-success" id="btnConfirm"><i
                        class="fa fa-check me-1"></i>Confirm</button>
            @endif
            @if ($customerBill->status === 'Confirmed')
                <a href="{{ route('nas-trading.money-receipts.create') }}?bill_id={{ $customerBill->id }}"
                    class="btn btn-sm btn-success"><i class="fa fa-money-bill-wave me-1"></i>Receive Payment</a>
                <a href="{{ route('nas-trading.deliveries.create') }}?bill_id={{ $customerBill->id }}"
                    class="btn btn-sm btn-outline-info"><i class="fa fa-truck me-1"></i>Create Delivery</a>
            @endif
            <button class="btn btn-sm btn-outline-dark" onclick="window.print()"><i
                    class="fa fa-print me-1"></i>Print</button>
            <a href="{{ route('nas-trading.customer-bills.index') }}" class="btn btn-sm btn-outline-secondary"><i
                    class="fa fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    {{-- ======================== PRINT-ONLY LAYOUT ======================== --}}
    <div class="d-none d-print-block" style="width:100%;box-sizing:border-box;">

        <p style="font-size:1.1rem;font-weight:700;color:#000;margin:0 0 .6rem;text-align:center;text-decoration:underline;letter-spacing:.05em;">Bill</p>

        {{-- Bill Information --}}
        <table style="width:100%;border-collapse:collapse;margin-bottom:.6rem;font-size:.72rem;">
            <tbody>
                <tr>
                    <td style="width:25%;padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Bill No
                    </td>
                    <td style="width:25%;padding:.25rem .5rem;font-weight:600;color:#000;">{{ $customerBill->bill_no }}</td>
                    <td style="width:25%;padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Bill
                        Date</td>
                    <td style="width:25%;padding:.25rem .5rem;font-weight:600;color:#000;">
                        {{ $customerBill->bill_date?->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">LC No</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $customerBill->lc->lc_no ?? '---' }}</td>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">LC Date</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">
                        {{ $customerBill->lc?->lc_open_date?->format('d-M-Y') ?? '---' }}</td>
                </tr>
                <tr>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">PFI No</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $customerBill->pfi_no ?? '---' }}</td>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Currency</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $customerBill->currency }}</td>
                </tr>
                <tr>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Customer</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $customerBill->customer_name }}</td>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Customer
                        Address</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">
                        {{ $customerBill->customer_address ?? '--' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Item Description
                    </td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">
                        {{ $customerBill->lc->item_description ?? '---' }}</td>
                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Note</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">
                        {{ $customerBill->note ?? '---' }}</td>
                </tr>
                <tr>

                    <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Status</td>
                    <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $customerBill->status }}</td>
                    <td></td>
                    <td></td>

                </tr>
            </tbody>
        </table>

        {{-- Bill Items --}}
        <table style="width:100%;border-collapse:collapse;margin-bottom:.6rem;font-size:.72rem;">
            <thead>
                <tr>
                    <th style="border:1px solid #999;padding:.25rem .4rem;width:30px;text-align:center;font-size:.65rem;font-weight:700;color:#000;">#</th>
                    <th style="border:1px solid #999;padding:.25rem .4rem;font-size:.65rem;font-weight:700;color:#000;">Items Description</th>
                    <th style="border:1px solid #999;padding:.25rem .4rem;font-size:.65rem;font-weight:700;color:#000;">Note</th>
                    <th style="border:1px solid #999;padding:.25rem .4rem;width:110px;text-align:right;font-size:.65rem;font-weight:700;color:#000;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerBill->items as $i => $item)
                    <tr>
                        <td style="border:1px solid #999;padding:.25rem .4rem;text-align:center;color:#000;">{{ $i + 1 }}</td>
                        <td style="border:1px solid #999;padding:.25rem .4rem;color:#000;">{{ $item->description }}</td>
                        <td style="border:1px solid #999;padding:.25rem .4rem;color:#555;">{{ $item->note }}</td>
                        <td style="border:1px solid #999;padding:.25rem .4rem;text-align:right;color:#000;">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="border:1px solid #999;padding:.3rem;text-align:center;color:#555;">No
                            items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Bill Totals (no card header) --}}
        @php
            $advancePayment = $customerBill->lc?->payments?->where('payment_type', 'advance')->sum('amount') ?? 0;
            $dutyAdvance = (float) ($customerBill->lc?->billOfEntries->flatMap->dutyAdvances->sum('amount') ?? 0);
            $totalAdvance = $advancePayment + $dutyAdvance;
            $totalBillPaid = $customerBill->lc?->total_bill_paid ?? 0;
            $transportAmt = $customerBill->sub_total + $customerBill->vat_amount - $totalAdvance - $totalBillPaid;

            $amountInWords = \App\Helpers\NumberHelper::amountInWords($transportAmt);
        @endphp
        <table class="print-totals" style="width:50%;margin-left:auto;border-collapse:collapse;font-size:.72rem;">
            <tr>
                <td style="padding:.2rem .5rem;color:#555;">Sub Total</td>
                <td style="padding:.2rem .5rem;text-align:right;font-weight:600;color:#000;">
                    BDT {{ number_format($customerBill->sub_total, 2) }}</td>
            </tr>
            <tr class="border-dotted-top">
                <td style="padding:.2rem .5rem;color:#555;">VAT ({{ floatval($customerBill->vat_pct) }}%)</td>
                <td style="padding:.2rem .5rem;text-align:right;font-weight:600;color:#000;">
                    BDT {{ number_format($customerBill->vat_amount, 2) }}</td>
            </tr>
            <tr class="border-solid-both">
                <td style="padding:.25rem .5rem;font-weight:700;color:#000;">Total Amount</td>
                <td style="padding:.25rem .5rem;text-align:right;font-weight:700;color:#000;">
                    BDT {{ number_format($customerBill->sub_total + $customerBill->vat_amount, 2) }}
                    {{ $customerBill->currency }}</td>
            </tr>
            <tr class="border-dotted-top">
                <td style="padding:.2rem .5rem;color:#555;">LC Advance Payment</td>
                <td style="padding:.2rem .5rem;text-align:right;color:#000;">BDT {{ number_format($advancePayment, 2) }}
                </td>
            </tr>
            <tr class="border-dotted-top">
                <td style="padding:.2rem .5rem;color:#555;">Duty Advance</td>
                <td style="padding:.2rem .5rem;text-align:right;color:#000;">BDT {{ number_format($dutyAdvance, 2) }}</td>
            </tr>
            <tr class="border-dotted-top">
                <td style="padding:.2rem .5rem;font-weight:700;color:#000;">Total Advance</td>
                <td style="padding:.2rem .5rem;text-align:right;font-weight:700;color:#000;">BDT
                    {{ number_format($totalAdvance, 2) }}</td>
            </tr>
            <tr class="border-dotted-top">
                <td style="padding:.2rem .5rem;font-weight:700;color:#000;">Total Bill Paid</td>
                <td style="padding:.2rem .5rem;text-align:right;font-weight:700;color:#000;">BDT
                    {{ number_format($totalBillPaid, 2) }}</td>
            </tr>
            <tr class="border-solid-top">
                <td style="padding:.25rem .5rem;font-weight:700;color:#000;">{{ $transportAmt >= 0 ? 'Due' : 'Balance' }}
                    Amount</td>
                <td style="padding:.25rem .5rem;text-align:right;font-weight:700;color:#000;">BDT
                    {{ number_format(abs($transportAmt), 2) }}</td>
            </tr>
        </table>

        {{-- In Word + Enclosed + Signatures --}}
        <div style="margin-top:.6rem;font-size:.72rem;">
            <div style="margin-bottom:.8rem;">
                <span style="color:#555;">In Word:</span>
                <span style="font-weight:600;"> {{ $amountInWords }}</span>
            </div>
            <div style="margin-bottom:1.5rem;">
                <span style="color:#555;">Enclosed:</span>
                <span id="enclosedPrintVal" style="font-weight:600;min-width:200px;display:inline-block;margin-left:.3rem;padding-bottom:1px;"></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:8rem;">
                <div style="text-align:center;width:30%;">
                    <div style="border-top:1px solid #000;padding-top:.3rem;font-size:.7rem;">Checked By</div>
                </div>
                <div style="text-align:center;width:30%;">
                    <div style="border-top:1px solid #000;padding-top:.3rem;font-size:.7rem;">Prepared By</div>
                </div>
            </div>
        </div>

    </div>
    {{-- ==================== END PRINT-ONLY LAYOUT ==================== --}}


    {{-- Enclosed input — visible on screen, value synced to print span via JS --}}
    <div class="d-print-none mt-3 mb-2">
        <div class="d-flex align-items-center gap-2" style="max-width:500px;">
            <label class="fw-semibold text-nowrap" style="font-size:.82rem;">Enclosed:</label>
            <input type="text" id="enclosedScreenInput" class="form-control form-control-sm"
                   placeholder="Type enclosed value before printing...">
        </div>
    </div>

    {{-- ======================== SCREEN-ONLY LAYOUT ======================== --}}
    <div class="row g-3 d-print-none">
        <div class="col-md-8">
            <div class="info-card">
                <div class="info-header"><i class="fa fa-info-circle me-2"></i> Bill Information</div>
                <div class="info-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="info-label">Bill No</div>
                            <div class="info-value">{{ $customerBill->bill_no }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Bill Date</div>
                            <div class="info-value">{{ $customerBill->bill_date?->format('d-M-Y') }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">LC No</div>
                            <div class="info-value">{{ $customerBill->lc->lc_no ?? '---' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">LC Date</div>
                            <div class="info-value">{{ $customerBill->lc?->lc_open_date?->format('d-M-Y') ?? '---' }}</div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-label">PFI No</div>
                            <div class="info-value">{{ $customerBill->pfi_no ?? '---' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Currency</div>
                            <div class="info-value">{{ $customerBill->currency }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Customer</div>
                            <div class="info-value">{{ $customerBill->customer_name }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Customer Address</div>
                            <div class="info-value">{{ $customerBill->customer_address ?? '---' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Item Description</div>
                            <div class="info-value">{{ $customerBill->lc->item_description ?? '---' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Note</div>
                            <div class="info-value">{{ $customerBill->note ?? '---' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                @php $badge = ['Draft' => 'secondary', 'Confirmed' => 'success', 'Paid' => 'primary'] @endphp
                                <span
                                    class="badge bg-{{ $badge[$customerBill->status] ?? 'secondary' }}">{{ $customerBill->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-header"><i class="fa fa-list-ul me-2"></i> Bill Items</div>
                <div style="overflow-x:auto">
                    <table class="table table-bordered bill-items mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Description</th>
                                <th>Note</th>
                                <th style="width:120px">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerBill->items as $i => $item)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-muted" style="font-size:.75rem">{{ $item->note }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No items</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background:#f0f4f8">
                                <td colspan="3" class="text-end fw-bold" style="font-size:.82rem">Total</td>
                                <td class="text-end fw-bold" style="font-size:.82rem">{{ number_format($customerBill->items->sum('amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-card">
                <div class="info-header"><i class="fa fa-calculator me-2"></i> Bill Totals</div>
                <div class="info-body">
                    @php
                        $advancePayment =
                            $customerBill->lc?->payments?->where('payment_type', 'advance')->sum('amount') ?? 0;
                        $dutyAdvance = (float) ($customerBill->lc?->billOfEntries->flatMap->dutyAdvances->sum('amount') ?? 0);
                        $totalAdvance = $advancePayment + $dutyAdvance;
                        $totalBillPaid = $customerBill->lc?->total_bill_paid ?? 0;
                        $transportAmt = $customerBill->sub_total + $customerBill->vat_amount - $totalAdvance - $totalBillPaid;
                    @endphp
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Sub Total</td>
                            <td class="text-end fw-bold">BDT {{ number_format($customerBill->sub_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">VAT ({{ floatval($customerBill->vat_pct) }}%)</td>
                            <td class="text-end fw-bold">BDT {{ number_format($customerBill->vat_amount, 2) }}</td>
                        </tr>
                        <tr class="table-success">
                            <td class="fw-bold fs-6">Total Amount</td>
                            <td class="text-end fw-bold fs-6">
                                BDT {{ number_format($customerBill->sub_total + $customerBill->vat_amount, 2) }}
                                {{ $customerBill->currency }}</td>
                        </tr>
                    </table>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.82rem;color:#6c757d">LC Advance Payment</span>
                        <span style="font-size:.82rem">BDT {{ number_format($advancePayment, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.82rem;color:#6c757d">Duty Advance</span>
                        <span style="font-size:.82rem">BDT {{ number_format($dutyAdvance, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold" style="font-size:.85rem">Total Advance</span>
                        <strong style="font-size:.9rem;color:#0c2340">BDT {{ number_format($totalAdvance, 2) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold" style="font-size:.85rem">Bill Paid</span>
                        <strong style="font-size:.9rem;color:#0c2340">BDT {{ number_format($totalBillPaid, 2) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold" style="font-size:.85rem">{{ $transportAmt >= 0 ? 'Due' : 'Balance' }}
                            Amount</span>
                        <strong class="{{ $transportAmt >= 0 ? 'text-success' : 'text-danger' }}"
                            style="font-size:1rem">BDT
                            {{ number_format(abs($transportAmt), 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#btnConfirm').on('click', function() {
            Swal.fire({
                    title: 'Confirm this bill?',
                    text: 'Bill will move to Confirmed and appear in Due List.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Yes, Confirm'
                })
                .then(res => {
                    if (res.isConfirmed) {
                        $.post('{{ route('nas-trading.customer-bills.confirm', $customerBill->id) }}', {
                                _token: '{{ csrf_token() }}'
                            })
                            .done(r => Swal.fire({
                                    icon: 'success',
                                    title: r.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(() => location.reload()))
                            .fail(() => Swal.fire({
                                icon: 'error',
                                title: 'Failed.'
                            }));
                    }
                });
        });

        document.getElementById('enclosedScreenInput').addEventListener('input', function () {
            document.getElementById('enclosedPrintVal').textContent = this.value;
        });

        const _originalTitle = document.title;
        window.addEventListener('beforeprint', () => {
            document.title = 'Bill Information';
            document.getElementById('enclosedPrintVal').textContent = document.getElementById('enclosedScreenInput').value;
        });
        window.addEventListener('afterprint',  () => { document.title = _originalTitle; });
    </script>
@endpush
