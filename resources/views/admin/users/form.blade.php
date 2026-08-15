@extends('admin.layouts.app')

@section('title', $user ? 'Edit User — ' . $user->name : 'Create User')

@push('styles')
<style>
.form-topbar {
    display: flex; align-items: center; gap: .85rem;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .6rem; padding: .75rem 1.25rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    margin-bottom: 1.25rem;
}
.form-topbar .ft-icon {
    width: 38px; height: 38px; border-radius: .4rem;
    background: #f0fdf4; color: #15803d;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
}
.form-topbar .ft-title { font-size: .92rem; font-weight: 700; color: #1e293b; line-height: 1.3; }
.form-topbar .ft-sub   { font-size: .73rem; color: #64748b; margin-top: .1rem; }
.form-topbar .ft-actions { margin-left: auto; display: flex; gap: .5rem; }

.form-body {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 1.25rem;
    align-items: start;
}

/* Constrain inputs so they don't stretch edge-to-edge on wide screens */
.profile-col .form-control,
.profile-col .form-select { max-width: 100%; }

@media (max-width: 1199px) {
    .form-body { grid-template-columns: 360px 1fr; }
}
@media (max-width: 991px) {
    .form-body { grid-template-columns: 1fr; }
    .form-topbar { flex-wrap: wrap; }
}
@media (max-width: 575px) {
    .form-topbar .ft-actions { width: 100%; }
    .form-topbar .ft-actions .btn { width: 100%; justify-content: center; }
    .panel-card-body { padding: .75rem; }
    .branch-checkboxes { gap: .35rem .6rem; }
}

.panel-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .6rem; overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    margin-bottom: 1rem;
}
.panel-card-header {
    padding: .6rem 1rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em; color: #64748b;
    display: flex; align-items: center; gap: .45rem;
}
.panel-card-body { padding: 1rem; }
.panel-card-footer { padding: .85rem 1rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }

.form-label-sm { font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: .25rem; display: block; }

/* Tighten form controls */
.form-control-sm, .form-select-sm { font-size: .82rem !important; }
.form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(99,102,241,.12); border-color: #a5b4fc; }


/* Company pill */
.co-pill {
    font-size: .62rem; font-weight: 800; letter-spacing: .05em;
    padding: .12rem .4rem; border-radius: 4px; line-height: 1.2; white-space: nowrap;
}
.pill-cnf     { background: #dcfce7; color: #15803d; }
.pill-freight { background: #cffafe; color: #0e7490; }
.pill-trading { background: #fef9c3; color: #a16207; }

/* Branch block */
.branch-company-block {
    border: 1px solid #e2e8f0; border-radius: .5rem;
    margin-bottom: .75rem; overflow: hidden;
    transition: box-shadow .15s;
}
.branch-company-block:last-child { margin-bottom: 0; }
.branch-company-block:hover { box-shadow: 0 2px 8px rgba(0,0,0,.07); }
.branch-company-header {
    padding: .5rem .85rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: .5rem;
    font-size: .78rem; font-weight: 700; color: #374151;
}
.branch-checkboxes {
    padding: .65rem .85rem;
    display: flex; flex-wrap: wrap; gap: .5rem 1.25rem;
}
.branch-check-item {
    display: flex; align-items: center; gap: .35rem;
    font-size: .8rem; color: #374151; cursor: pointer;
    padding: .2rem 0;
}
.branch-check-item input[type=checkbox] {
    cursor: pointer; width: 15px; height: 15px; accent-color: #6366f1;
}
.branch-check-item:hover { color: #4f46e5; }

.no-role-msg {
    text-align: center; padding: 2rem 1rem;
    color: #94a3b8; font-size: .8rem;
}
.no-role-msg i { font-size: 1.75rem; display: block; margin-bottom: .5rem; opacity: .6; }

/* Employee link */
.emp-link-wrap {
    border: 1.5px solid #d1fae5; border-radius: .45rem;
    padding: .6rem .75rem; background: #f0fdf4;
}
</style>
@endpush

@section('content')

@php
    $pillMap = ['cnf' => 'pill-cnf', 'freight' => 'pill-freight', 'trading' => 'pill-trading'];
@endphp

<form method="POST"
      action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if($user) @method('PUT') @endif

    {{-- Topbar --}}
    <div class="form-topbar">
        <div class="ft-icon"><i class="fa fa-user"></i></div>
        <div>
            <div class="ft-title">{{ $user ? 'Edit User — '.$user->name : 'Create User' }}</div>
            <div class="ft-sub">{{ $user ? 'Update profile, role and branch access' : 'Add new user, assign role and branch access' }}</div>
        </div>
        <div class="ft-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="form-body">

        {{-- Left: User Profile --}}
        <div class="profile-col">
            <div class="panel-card">
                <div class="panel-card-header">
                    <i class="fa fa-user text-success"></i> User Profile
                </div>
                <div class="panel-card-body">

                    <div class="mb-3">
                        <label class="form-label form-label-sm">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               value="{{ old('name', $user?->name) }}"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               placeholder="Full name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-sm">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username"
                               value="{{ old('username', $user?->username) }}"
                               class="form-control form-control-sm @error('username') is-invalid @enderror"
                               placeholder="e.g. john_doe">
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-sm">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $user?->email) }}"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               placeholder="email@example.com (optional)">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-sm">
                            Password
                            @if($user)
                                <span class="text-muted fw-normal">(blank = keep current)</span>
                            @else
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input type="password" name="password"
                               class="form-control form-control-sm @error('password') is-invalid @enderror"
                               placeholder="Min 6 characters">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-1">
                        <label class="form-label form-label-sm">Account Status</label>
                        <select name="is_active" class="form-select form-select-sm">
                            <option value="1" {{ old('is_active', $user?->is_active ?? true) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ! old('is_active', $user?->is_active ?? true) ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="panel-card">
                <div class="panel-card-footer">
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="fa fa-save me-1"></i> {{ $user ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Right: Role + Employee Link + Branch Access --}}
        <div>

            @if($user?->is_super)
            <div class="panel-card">
                <div class="panel-card-body text-center py-3" style="color:#92400e; background:#fffbeb; border:1px dashed #fde68a; border-radius:.5rem;">
                    <i class="fa fa-crown me-1" style="color:#d97706;"></i>
                    <span style="font-size:.82rem; font-weight:600;">Super Admin — all companies and branches accessible by default.</span>
                </div>
            </div>
            @else
            {{-- Role --}}
            <div class="panel-card">
                <div class="panel-card-header">
                    <i class="fa fa-user-shield text-primary"></i> Role Assignment
                </div>
                <div class="panel-card-body">
                    <select name="role_id" id="roleSelect"
                            class="form-select form-select-sm @error('role_id') is-invalid @enderror">
                        <option value="">Select the Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                                {{ old('role_id', $currentRoleId ?? '') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Branch Access --}}
            <div class="panel-card">
                <div class="panel-card-header">
                    <i class="fa fa-code-branch text-warning"></i> Branch Access
                    <span style="font-weight:400; margin-left:.25rem; color:#94a3b8;">(per company, role-based)</span>
                </div>
                <div class="panel-card-body" id="branchAccessBody">
                    <div class="no-role-msg" id="noRoleMsg">
                        <i class="fa fa-user-shield"></i>
                        Select a role above to configure branch access
                    </div>

                    {{-- Branch blocks per company, hidden until role selected --}}
                    @foreach($companiesData as $co)
                    @php
                        $pillClass    = $pillMap[$co['type']] ?? '';
                        $savedBranches = old('branch_access.'.$co['id'], $branchAccess[$co['id']] ?? []);
                    @endphp
                    <div class="branch-company-block" id="branchBlock_{{ $co['id'] }}" style="display:none;">
                        <div class="branch-company-header">
                            <span class="co-pill {{ $pillClass }}">{{ strtoupper($co['type']) }}</span>
                            {{ $co['name'] }}
                        </div>
                        <div class="branch-checkboxes">
                            @forelse($co['branches'] as $branch)
                            <label class="branch-check-item">
                                <input type="checkbox"
                                       name="branch_access[{{ $co['id'] }}][]"
                                       value="{{ $branch->id }}"
                                       {{ in_array((string) $branch->id, array_map('strval', (array) $savedBranches)) ? 'checked' : '' }}>
                                {{ $branch->name }}
                            </label>
                            @empty
                            <span class="text-muted small">No branches configured</span>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Employee Link --}}
            <div class="panel-card">
                <div class="panel-card-header">
                    <i class="fa fa-id-badge text-success"></i> Employee Link
                    <span style="font-weight:400; margin-left:.25rem; color:#94a3b8;">(optional, one per user)</span>
                </div>
                <div class="panel-card-body">
                    <div class="emp-link-wrap">
                        <select name="employee_link" id="employeeLinkSelect"
                                class="form-select form-select-sm @error('employee_link') is-invalid @enderror">
                            <option value="">Select Employee</option>
                            @foreach($companiesData as $co)
                                @if($co['employees']->isNotEmpty())
                                <optgroup label="{{ $co['name'] }}">
                                    @foreach($co['employees'] as $emp)
                                    <option value="{{ $co['id'] }}:{{ $emp->id }}"
                                        {{ old('employee_link', $employeeLink ?? '') == $co['id'].':'.$emp->id ? 'selected' : '' }}>
                                        {{ $emp->emp_code }} — {{ $emp->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @error('employee_link') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <p class="text-muted small mb-0 mt-2" style="font-size:.7rem;">
                        <i class="fa fa-info-circle me-1"></i>
                        Link this user to their employee record. Optional and user-specific.
                    </p>
                </div>
            </div>
            @endif {{-- end non-super --}}

        </div>
    </div>
</form>

@endsection

@push('scripts')
@if(!($user?->is_super))
<script>
$(function () {
    var rolesMap = {!! $rolesJson !!};

    $('#roleSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select the Role',
        allowClear: true,
        width: '100%',
    });

    $('#employeeLinkSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Employee',
        allowClear: true,
        width: '100%',
    });

    function getSelectedCompanyIds() {
        var roleId = $('#roleSelect').val();
        if (!roleId || !rolesMap[roleId]) return [];
        return rolesMap[roleId];
    }

    function updateBranchBlocks() {
        var companyIds = getSelectedCompanyIds();
        var $noMsg = $('#noRoleMsg');

        if (companyIds.length === 0) {
            $noMsg.show();
            $('.branch-company-block').hide();
            return;
        }

        $noMsg.hide();
        $('.branch-company-block').each(function () {
            var blockId = parseInt($(this).attr('id').replace('branchBlock_', ''));
            $(this).toggle(companyIds.indexOf(blockId) !== -1);
        });
    }

    $('#roleSelect').on('change', updateBranchBlocks);

    // Init on page load (edit mode)
    updateBranchBlocks();
});
</script>
@endif
@endpush
