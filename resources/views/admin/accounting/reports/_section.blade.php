{{-- Grouped Sub-1 > Sub-2 > ledger rows for P&L and balance sheet. Needs $section, $title, $ledgerUrl. --}}
<tr class="group-row"><td colspan="2" class="text-uppercase">{{ $title }}</td></tr>
@forelse($section['groups'] as $group)
    <tr class="subgroup-row"><td>{{ $group['code'] }} {{ $group['name'] }}</td><td class="num">{{ number_format($group['total'], 2) }}</td></tr>
    @foreach($group['subgroups'] as $sub)
        <tr><td class="ps-4 fw-semibold">{{ $sub['code'] }} {{ $sub['name'] }}</td><td class="num fw-semibold">{{ number_format($sub['total'], 2) }}</td></tr>
        @foreach($sub['ledgers'] as $ledger)
            <tr>
                <td class="ps-5"><a href="{{ $ledgerUrl($ledger['id']) }}" class="text-decoration-none">{{ $ledger['code'] }} {{ $ledger['name'] }}</a></td>
                <td class="num">{{ number_format($ledger['amount'], 2) }}</td>
            </tr>
        @endforeach
    @endforeach
@empty
    <tr><td colspan="2" class="text-muted ps-4">No balances.</td></tr>
@endforelse
<tr class="total-row"><td>Total {{ $title }}</td><td class="num">{{ number_format($section['total'], 2) }}</td></tr>
