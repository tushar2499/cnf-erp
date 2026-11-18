<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Money Receipt — {{ $moneyReceipt->receipt_no }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #111; background: #f1f3f5; }
.num { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }

.toolbar { display: flex; gap: 8px; justify-content: center; padding: 10px; }
.toolbar button { padding: 7px 20px; font-size: 13px; cursor: pointer; border-radius: 4px; border: 1px solid #1d3b63; background: #fff; color: #1d3b63; }
.toolbar button.primary { background: #1d3b63; color: #fff; }
.toolbar button:focus-visible { outline: 2px solid #1d3b63; outline-offset: 2px; }

.page { width: 210mm; min-height: 280mm; margin: 0 auto 16px; padding: 12mm 14mm; background: #fff; box-shadow: 0 1px 6px rgba(0, 0, 0, .15); }

/* Letterhead */
.letterhead { display: flex; align-items: center; gap: 14px; padding-bottom: 8px; border-bottom: 3px solid #1d3b63; }
.logo, .logo-default { width: 58px; height: 58px; flex: none; }
.logo { object-fit: contain; }
.logo-default { background: #1d3b63; color: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; }
.co-name { font-size: 18px; font-weight: 700; color: #1d3b63; letter-spacing: .3px; }
.co-line { font-size: 9.5px; color: #444; margin-top: 2px; }

.doc-title { text-align: center; font-size: 15px; font-weight: 700; letter-spacing: 2px; margin: 12px 0 10px; text-transform: uppercase; }

/* Meta */
.meta { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 10px; }
.meta .block { flex: 1; border: 1px solid #c9ced6; border-radius: 3px; padding: 8px 10px; }
.meta .lbl { font-size: 8.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #666; }
.meta .val { font-size: 12px; font-weight: 700; margin-top: 2px; }
.meta table { width: 100%; border-collapse: collapse; }
.meta td { padding: 1.5px 0; vertical-align: top; }
.meta td:first-child { width: 38%; color: #555; }

/* Amount */
.amount { border: 2px solid #1d3b63; border-radius: 4px; padding: 8px 12px; margin-bottom: 10px; text-align: center; }
.amount .big { font-size: 22px; font-weight: 700; color: #1d3b63; letter-spacing: .5px; }
.amount .words { font-size: 10.5px; font-style: italic; margin-top: 3px; }

/* Tables */
.section-title { font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #1d3b63; margin: 12px 0 4px; }
table.grid { width: 100%; border-collapse: collapse; }
table.grid th { background: #e9edf3; border: 1px solid #8a94a3; padding: 4px 6px; text-align: left; font-size: 10px; }
table.grid th.num { text-align: right; }
table.grid td { border: 1px solid #b4bbc6; padding: 3px 6px; vertical-align: middle; }
table.grid tfoot td, table.grid tfoot th { background: #f3f5f8; border: 1px solid #8a94a3; font-weight: 700; padding: 4px 6px; }
.muted { color: #666; }

.note { margin-top: 10px; font-size: 10px; color: #333; }
.note strong { color: #000; }

/* Signatures */
.signatures { display: flex; justify-content: space-between; gap: 24px; margin-top: 54px; }
.sig { flex: 1; text-align: center; font-size: 10px; font-weight: 700; }
.sig .line { border-top: 1px solid #000; margin: 0 14px 4px; }

.footer { margin-top: 18px; padding-top: 6px; border-top: 1px solid #c9ced6; display: flex; justify-content: space-between; font-size: 8.5px; color: #777; }

@media print {
    body { background: #fff; }
    .toolbar { display: none !important; }
    .page { width: 100%; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
    @page { size: A4 portrait; margin: 12mm 14mm; }
    table.grid tr { page-break-inside: avoid; }
    .signatures, .amount { page-break-inside: avoid; }
}
</style>
</head>
<body>
@php
    $branch     = $moneyReceipt->branch;
    $coName     = $company?->name ?? 'Chevron Lines (C&F) Ltd.';
    $address    = $branch?->address ?: $company?->address;
    $phone      = $branch?->phone ?: $company?->phone;
    $isBill     = $moneyReceipt->receipt_type === \App\Models\Chevron\ChevronMoneyReceipt::TYPE_BILL;
    $isAdvance  = $moneyReceipt->receipt_type === \App\Models\Chevron\ChevronMoneyReceipt::TYPE_ADVANCE;
    $billRows   = $moneyReceipt->allocations->whereNotNull('bill_id')->values();
    $advanceRows = $moneyReceipt->allocations->whereNull('bill_id')->sortBy(fn ($a) => $a->job_id === null ? 1 : 0)->values();
    $hasCheque  = $moneyReceipt->items->contains(fn ($i) => stripos((string) $i->payment_type, 'cheque') !== false);
    $typeLabel  = $isBill ? 'Bill Payment' : ($isAdvance ? 'Advance Payment' : 'Payment');
@endphp

<div class="toolbar">
    <button type="button" class="primary" onclick="window.print()">Print</button>
    <button type="button" onclick="window.close()">Close</button>
</div>

<div class="page">

    {{-- Letterhead --}}
    <div class="letterhead">
        @if ($company?->logo)
            <img class="logo" src="{{ asset('storage/'.$company->logo) }}" alt="{{ $coName }} logo">
        @else
            <div class="logo-default" aria-hidden="true">{{ strtoupper(mb_substr($coName, 0, 2)) }}</div>
        @endif
        <div>
            <div class="co-name">{{ $coName }}</div>
            @if ($branch)
                <div class="co-line"><strong>{{ $branch->name }}</strong>@if ($address) — {{ $address }}@endif</div>
            @elseif ($address)
                <div class="co-line">{{ $address }}</div>
            @endif
            @if ($phone || $company?->email)
                <div class="co-line">
                    @if ($phone) Phone: {{ $phone }} @endif
                    @if ($phone && $company?->email) &nbsp;|&nbsp; @endif
                    @if ($company?->email) Email: {{ $company->email }} @endif
                </div>
            @endif
        </div>
    </div>

    <div class="doc-title">Money Receipt</div>

    {{-- Party + receipt meta --}}
    <div class="meta">
        <div class="block">
            <div class="lbl">Received with thanks from</div>
            <div class="val">{{ $moneyReceipt->party_name }}</div>
        </div>
        <div class="block">
            <table>
                <tr><td>Receipt No</td><td><strong>{{ $moneyReceipt->receipt_no }}</strong></td></tr>
                <tr><td>Date</td><td>{{ $moneyReceipt->receipt_date?->format('d M Y') }}</td></tr>
                <tr><td>Receipt For</td><td>{{ $typeLabel }}</td></tr>
                <tr><td>Method</td><td>{{ $moneyReceipt->pay_type }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Amount --}}
    <div class="amount">
        <div class="big">BDT {{ number_format($moneyReceipt->total_amount, 2) }}</div>
        <div class="words">( {{ $amountInWords }} )</div>
    </div>

    {{-- Against --}}
    @if ($isBill && $billRows->isNotEmpty())
        <div class="section-title">In payment of bills</div>
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Bill No</th>
                    <th>Bill Date</th>
                    <th>Job No</th>
                    <th class="num">Bill Amount</th>
                    <th class="num">Received Now</th>
                    <th class="num">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($billRows as $row)
                    @php $bill = $row->bill; @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $bill?->bill_no ?? '—' }}</strong></td>
                        <td>{{ $bill?->bill_date?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $row->job?->job_no ?? $bill?->job_no ?? '—' }}</td>
                        <td class="num">{{ $bill ? number_format($bill->due_amount, 2) : '—' }}</td>
                        <td class="num"><strong>{{ number_format($row->amount, 2) }}</strong></td>
                        <td class="num">{{ $bill ? number_format($bill->balance, 2) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="num">Total</td>
                    <td class="num">{{ number_format($billRows->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @elseif ($isAdvance)
        <div class="section-title">Advance received against</div>
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Job No</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($advanceRows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if ($row->job)
                                <strong>{{ $row->job->job_no }}</strong>
                            @else
                                <span class="muted">General advance (no job)</span>
                            @endif
                        </td>
                        <td class="num">{{ number_format($row->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="num">Total advance</td>
                    <td class="num">{{ number_format($advanceRows->sum('amount'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- Payment details --}}
    <div class="section-title">Payment details</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width:28px">#</th>
                <th>Type</th>
                <th>Account</th>
                <th>Holder</th>
                <th>Cheque / Card No</th>
                <th>Cheque Date</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($moneyReceipt->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->payment_type }}</td>
                    <td>{{ $item->account?->account_no ?? ($item->account_no ?: '—') }}</td>
                    <td>{{ $item->cheque_card_holder ?: '—' }}</td>
                    <td>{{ $item->cheque_card_no ?: '—' }}</td>
                    <td>{{ $item->cheque_date?->format('d M Y') ?? '—' }}</td>
                    <td class="num">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="num">Total</td>
                <td class="num">{{ number_format($moneyReceipt->items->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if (filled($moneyReceipt->description))
        <div class="note"><strong>Remarks:</strong> {{ $moneyReceipt->description }}</div>
    @endif
    @if ($hasCheque)
        <div class="note"><strong>Note:</strong> Cheque payments are subject to realization.</div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig"><div class="line"></div>Received By (Party)</div>
        <div class="sig"><div class="line"></div>Authorized Signature</div>
    </div>

    <div class="footer">
        <span>Printed on {{ now()->format('d M Y, h:i A') }}</span>
        <span>{{ $moneyReceipt->receipt_no }}</span>
    </div>
</div>

</body>
</html>
