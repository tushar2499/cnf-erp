{{-- Create-partial-job button. Vars: $job (ChevronJob, partials loaded), $isEditPage (bool, optional) --}}
@php
    $canCreatePartial = ! $job->isPartial() && auth()->user()?->hasPermission('cnf.job.create');
    $unsavedNote = ($isEditPage ?? false) ? ' Unsaved changes on this page are not copied.' : '';
    $existingPartialNos = $job->partials->pluck('job_no')->values();
    $nextPartialNo = $job->nextPartialJobNo();
@endphp

@if ($canCreatePartial)
    <form method="POST" action="{{ route('chevron.cnf.jobs.partial.store', $job->id) }}"
        class="d-inline js-partial-job-form" data-job-no="{{ $job->job_no }}" data-note="{{ $unsavedNote }}"
        data-existing="{{ json_encode($existingPartialNos) }}" data-next="{{ $nextPartialNo }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="fa fa-code-branch me-1" aria-hidden="true"></i> Create Partial Job
            @if ($existingPartialNos->isNotEmpty())
                <span class="badge bg-success ms-1">{{ $existingPartialNos->count() }}<span class="visually-hidden"> existing partial jobs</span></span>
            @endif
        </button>
    </form>

    @once
        @push('scripts')
            <script>
                document.querySelectorAll('.js-partial-job-form').forEach(function(form) {
                    var MAX_LISTED = 8;

                    function esc(value) {
                        var el = document.createElement('div');
                        el.textContent = value;
                        return el.innerHTML;
                    }

                    function badge(text, cls) {
                        return '<span class="badge ' + cls + ' text-nowrap">' + esc(text) + '</span>';
                    }

                    function dialogHtml() {
                        var existing = JSON.parse(form.dataset.existing || '[]');
                        var count = existing.length;
                        var html = '<div class="text-start">';

                        if (count === 0) {
                            html += '<p class="mb-2"><strong>' + esc(form.dataset.jobNo) +
                                '</strong> has no partial jobs yet. This will be the first one.</p>';
                        } else {
                            var listed = existing.slice(0, MAX_LISTED).map(function(no) {
                                return badge(no, 'bg-secondary');
                            }).join(' ');

                            if (count > MAX_LISTED) {
                                listed += ' ' + badge('+' + (count - MAX_LISTED) + ' more', 'bg-light text-dark border');
                            }

                            html += '<p class="mb-2"><strong>' + esc(form.dataset.jobNo) + '</strong> already has <strong>' +
                                count + '</strong> partial job' + (count === 1 ? '' : 's') + ':</p>' +
                                '<div class="d-flex flex-wrap gap-1 mb-2">' + listed + '</div>';
                        }

                        html += '<p class="mb-2">New partial job: ' + badge(form.dataset.next, 'bg-success') + '</p>' +
                            '<p class="small text-muted mb-0">All job fields are copied. Bills and expenses are not copied.' +
                            esc(form.dataset.note) + '</p></div>';

                        return html;
                    }

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        Swal.fire({
                            title: 'Create partial job?',
                            html: dialogHtml(),
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Create partial job',
                            cancelButtonText: 'Cancel'
                        }).then(function(result) {
                            if (!result.isConfirmed) {
                                return;
                            }

                            var btn = form.querySelector('button[type="submit"]');
                            btn.disabled = true;
                            btn.innerHTML =
                                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating...';
                            form.submit();
                        });
                    });
                });
            </script>
        @endpush
    @endonce
@endif
