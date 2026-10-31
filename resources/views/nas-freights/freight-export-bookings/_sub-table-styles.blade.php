{{-- Shared styles for the Bills / Expense / Transport tables on a booking/job page.
     Wide: normal table (text columns wrap, numbers stay on one line).
     Narrower than the table can shrink: one card per row with a compact label/value grid.
     Phones: one label/value pair per line. No sideways scrolling in either card mode. --}}
@once
@push('styles')
<style>
    .sub-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        container-type: inline-size;
    }

    .table.sub-table {
        font-size: .73rem;
    }

    .table.sub-table > :not(caption) > * > * {
        padding: .3rem .5rem;
    }

    .table.sub-table > tfoot > tr > td {
        padding: .35rem .5rem;
    }

    .sub-table td.text-end,
    .sub-table td.text-center,
    .sub-table .col-index {
        white-space: nowrap;
    }

    .sub-table .col-index { width: 35px; }

    .section-action { font-size: .72rem; }

    @media (max-width: 767.98px) {
        .section-action {
            padding: .35rem .7rem !important;
            font-size: .78rem;
        }
    }

    {{-- tier = table; value = container width (px) below which the table becomes cards --}}
    @php($stackTiers = ['bills' => 660, 'expense' => 580, 'transport' => 1080])
    @foreach ($stackTiers as $tier => $maxWidth)
    @container (max-width: {{ $maxWidth }}px) {
        .sub-table-wrap--{{ $tier }} .table.sub-table { min-width: 0; border: 0; }
        .sub-table-wrap--{{ $tier }} .sub-table thead {
            position: absolute; width: 1px; height: 1px; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0);
        }
        .sub-table-wrap--{{ $tier }} .sub-table :is(tbody, tfoot) { display: block; padding: .5rem .5rem 0; }
        .sub-table-wrap--{{ $tier }} .sub-table tr {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 190px), 1fr));
            margin-bottom: .5rem; background: #fff;
            border: 1px solid #dee2e6; border-radius: .35rem; overflow: hidden;
        }
        .sub-table-wrap--{{ $tier }} .table.sub-table > :not(caption) > * > * {
            display: flex; flex-direction: column; align-items: flex-start; gap: .15rem;
            padding: .45rem .7rem; text-align: left; white-space: normal;
            border: 0; box-shadow: none;
        }
        .sub-table-wrap--{{ $tier }} .sub-table td::before {
            content: attr(data-label);
            font-size: .62rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #6b7a99;
        }
        .sub-table-wrap--{{ $tier }} .sub-table .stack-hide { display: none !important; }
        .sub-table-wrap--{{ $tier }} .sub-table .cell-value { min-width: 0; overflow-wrap: anywhere; }
        .sub-table-wrap--{{ $tier }} .sub-table td .btn { padding: .35rem .75rem; font-size: .8rem; }
    }
    @container (max-width: 520px) {
        .sub-table-wrap--{{ $tier }} .sub-table tr { display: block; }
        .sub-table-wrap--{{ $tier }} .table.sub-table > :not(caption) > * > * {
            flex-direction: row; align-items: center; justify-content: space-between; gap: .75rem;
            min-height: 34px; text-align: right; border-width: 0 0 1px; border-bottom: 1px solid #f1f3f5;
        }
        .sub-table-wrap--{{ $tier }} .sub-table tr > :last-child { border-bottom-width: 0; }
        .sub-table-wrap--{{ $tier }} .sub-table td::before { flex: 0 0 38%; max-width: 38%; text-align: left; }
    }
    @endforeach
</style>
@endpush
@endonce
