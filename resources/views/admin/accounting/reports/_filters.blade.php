{{--
    Shared report filter bar. Pass $fields: list of 'period', 'as_on' and the report's extra fields go in $slot.
--}}
<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="company_id">Company</label>
                <select name="company_id" id="company_id" class="form-select form-select-sm">
                    <option value="">All Companies (Group)</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected($companyId === $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(in_array('period', $fields))
                <div class="col-md-2">
                    <label class="form-label" for="from">From</label>
                    <input type="date" name="from" id="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="to">To</label>
                    <input type="date" name="to" id="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
            @endif
            @if(in_array('as_on', $fields))
                <div class="col-md-2">
                    <label class="form-label" for="as_on">As on</label>
                    <input type="date" name="as_on" id="as_on" class="form-control form-control-sm" value="{{ $asOn }}">
                </div>
            @endif
            {{ $slot ?? '' }}
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-magnifying-glass me-1"></i> Show</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa fa-print me-1"></i> Print</button>
            </div>
        </form>
    </div>
</div>
