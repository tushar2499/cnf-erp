{{-- Partial job panel. Var: $job (ChevronJob with parent + partials loaded) --}}
@if ($job->isPartial())
    <div class="job-card ac-amber" id="partial-jobs">
        <div class="job-card-hdr">
            <i class="fa fa-code-branch" style="color:#d97706;" aria-hidden="true"></i> Partial Job
            <span class="badge bg-warning text-dark ms-auto text-nowrap">Partial #{{ $job->partial_seq }}</span>
        </div>
        <div class="job-card-body small">
            This is a partial job of
            <a href="{{ route('chevron.cnf.jobs.show', $job->parent_job_id) }}" class="fw-semibold text-nowrap">
                {{ $job->parent?->job_no }}
            </a>.
        </div>
    </div>
@else
    <div class="job-card ac-green" id="partial-jobs">
        <div class="job-card-hdr">
            <i class="fa fa-code-branch" style="color:#16a34a;" aria-hidden="true"></i> Partial Jobs
            <span class="badge bg-secondary ms-1">{{ $job->partials->count() }}</span>
        </div>
        <div class="job-card-body p-0">
            @if ($job->partials->isEmpty())
                <p class="text-muted small mb-0 p-3">No partial jobs yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Job No</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Net Pay (FCY)</th>
                                <th class="text-end">Net Pay (BDT)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($job->partials as $partial)
                                <tr>
                                    <td class="text-nowrap fw-semibold">
                                        <a href="{{ route('chevron.cnf.jobs.show', $partial->id) }}">{{ $partial->job_no }}</a>
                                    </td>
                                    <td class="text-nowrap">{{ $partial->job_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $partial->status === 'Active' ? 'success' : ($partial->status === 'Pending' ? 'warning text-dark' : 'secondary') }}">{{ $partial->status }}</span>
                                    </td>
                                    <td class="text-end">{{ $partial->net_payable_1 ? number_format($partial->net_payable_1, 2) : '—' }}</td>
                                    <td class="text-end">{{ $partial->net_payable_2 ? number_format($partial->net_payable_2, 2) : '—' }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('chevron.cnf.jobs.show', $partial->id) }}"
                                            class="btn btn-sm btn-outline-secondary py-0 px-1" title="View"
                                            aria-label="View {{ $partial->job_no }}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                        @if (auth()->user()?->hasPermission('cnf.job.edit'))
                                            <a href="{{ route('chevron.cnf.jobs.edit', $partial->id) }}"
                                                class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"
                                                aria-label="Edit {{ $partial->job_no }}"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif
