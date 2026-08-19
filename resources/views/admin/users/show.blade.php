@extends('admin.layouts.app')

@section('title', 'User — ' . $user->name)

@push('styles')
<style>
.view-topbar {
    display: flex; align-items: center; gap: .75rem;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; padding: .65rem 1.1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    margin-bottom: 1rem;
}
.view-topbar .u-title { font-size: .9rem; font-weight: 700; color: #1e293b; }
.view-topbar .u-sub   { font-size: .72rem; color: #64748b; }
.view-topbar .actions { margin-left: auto; display: flex; gap: .5rem; }

.view-body { display: grid; grid-template-columns: 260px 1fr; gap: 1rem; align-items: start; }

.panel-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04); margin-bottom: .75rem;
}
.panel-card-header {
    padding: .55rem .9rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #64748b;
    display: flex; align-items: center; gap: .4rem;
}
.panel-card-body { padding: .85rem .9rem; }

.stat-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .35rem 0; border-bottom: 1px solid #f1f5f9; font-size: .78rem;
}
.stat-row:last-child { border-bottom: none; }
.stat-label { color: #64748b; font-weight: 500; }
.stat-val   { font-weight: 700; color: #1e293b; }

.co-type-pill {
    font-size: .6rem; font-weight: 800; letter-spacing: .06em;
    padding: .13rem .42rem; border-radius: 4px; line-height: 1.3; flex-shrink: 0;
}
.pill-cnf     { background: #dcfce7; color: #15803d; }
.pill-freight { background: #cffafe; color: #0e7490; }
.pill-trading { background: #fef9c3; color: #a16207; }
.pill-system  { background: #f1f5f9; color: #475569; }

.co-row {
    display: flex; align-items: center; gap: .5rem;
    padding: .3rem 0; border-bottom: 1px solid #f1f5f9;
    font-size: .78rem; font-weight: 600; color: #1e293b;
}
.co-row:last-child { border-bottom: none; }
.co-row .co-count {
    margin-left: auto; font-size: .7rem; font-weight: 700;
    color: #1d4ed8; background: #eff6ff;
    padding: .1rem .45rem; border-radius: 999px;
}

.perms-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.perms-card-header {
    padding: .65rem 1.1rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    border-radius: .5rem .5rem 0 0;
    display: flex; align-items: center; gap: .6rem;
}
.perms-card-header .ph-title { font-size: .8rem; font-weight: 700; color: #1e293b; }
.perms-card-header .ph-total {
    margin-left: auto; font-size: .73rem; font-weight: 600;
    color: #6366f1; background: #eef2ff;
    padding: .15rem .55rem; border-radius: 999px;
}

.perm-tab-strip {
    padding: .5rem .75rem;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    display: flex; gap: .3rem; flex-wrap: wrap;
}
.perm-tab-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .38rem .7rem;
    border-radius: .38rem;
    border: 1px solid transparent;
    font-size: .75rem; font-weight: 700; color: #64748b;
    background: transparent; cursor: pointer;
    white-space: nowrap; flex-shrink: 0;
    transition: background 120ms, box-shadow 120ms, border-color 120ms;
    line-height: 1;
}
.perm-tab-btn:hover:not(.active) { background: rgba(255,255,255,.7); color: #334155; }
.perm-tab-btn.active {
    background: #fff; color: #1e293b;
    border-color: #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,.1);
}
.tab-badge {
    font-size: .65rem; font-weight: 700;
    padding: .1rem .4rem; border-radius: 999px; line-height: 1.5;
    background: #e2e8f0; color: #64748b;
    transition: background 120ms, color 120ms;
}
.perm-tab-btn.active .tab-badge { background: #dbeafe; color: #1d4ed8; }

.perm-tab-btn.active[data-type="system"]  { border-top: 2px solid #64748b; }
.perm-tab-btn.active[data-type="cnf"]     { border-top: 2px solid #16a34a; }
.perm-tab-btn.active[data-type="freight"] { border-top: 2px solid #0891b2; }
.perm-tab-btn.active[data-type="trading"] { border-top: 2px solid #ca8a04; }

.pane-banner {
    display: flex; align-items: center; gap: .55rem;
    padding: .55rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: .78rem; font-weight: 700; color: #1e293b;
}
.pane-banner .pane-count {
    margin-left: auto; font-size: .7rem; font-weight: 600;
    color: #64748b; background: #f1f5f9;
    padding: .1rem .45rem; border-radius: 999px;
}
.pane-banner-system  { background: #f8fafc; }
.pane-banner-cnf     { background: #f0fdf4; }
.pane-banner-freight { background: #ecfeff; }
.pane-banner-trading { background: #fefce8; }

.perm-tab-pane-inner { display: none; }
.perm-tab-pane-inner.active { display: block; }

.branch-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: .35rem;
    padding: .6rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    background: #fafafa;
    font-size: .72rem;
}
.branch-row .branch-label {
    font-weight: 600; color: #64748b; flex-shrink: 0; margin-right: .25rem;
}
.branch-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .55rem; border-radius: 999px;
    border: 1px solid #d1d5db; background: #fff;
    font-size: .7rem; font-weight: 600; color: #374151;
}
.branch-chip-cnf     { border-color: #86efac; background: #f0fdf4; color: #15803d; }
.branch-chip-freight { border-color: #67e8f9; background: #ecfeff; color: #0e7490; }
.branch-chip-trading { border-color: #fde047; background: #fefce8; color: #a16207; }
.no-branch-note { color: #94a3b8; font-style: italic; font-size: .7rem; }

.pane-content { padding: .9rem 1.1rem; }

.mod-group { margin-bottom: .85rem; }
.mod-group:last-child { margin-bottom: 0; }
.mod-label {
    display: inline-flex; align-items: center;
    font-size: .67rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #64748b;
    background: #f8fafc; border: 1px solid #e2e8f0;
    padding: .18rem .55rem; border-radius: .25rem;
    margin-bottom: .5rem;
}
.perms-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
.perm-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .7rem; border-radius: .3rem;
    border: 1.5px solid #bfdbfe; background: #eff6ff;
    font-size: .74rem; font-weight: 600; color: #1d4ed8;
}
.perm-chip i { font-size: .63rem; }

.empty-perms { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; }
.empty-perms i { font-size: 2rem; display: block; margin-bottom: .5rem; }

@media (max-width: 768px) {
    .view-body { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Top bar --}}
<div class="view-topbar">
    <div>
        <div class="u-title">
            <i class="fa fa-user me-2 text-primary"></i>{{ $user->name }}
            @if($user->is_super)
            <span class="badge ms-1" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:.65rem"><i class="fa fa-crown me-1"></i>Super</span>
            @endif
        </div>
        <div class="u-sub">{{ $user->username }}@if($user->email) &bull; {{ $user->email }}@endif &bull; Permission overview</div>
    </div>
    <div class="actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        @if(auth()->user()->hasPermission('admin.users.edit'))
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
        @endif
    </div>
</div>

<div class="view-body">

    {{-- Left panel --}}
    <div>

        {{-- User info --}}
        <div class="panel-card">
            <div class="panel-card-header">
                <i class="fa fa-circle-info text-primary"></i> User Information
            </div>
            <div class="panel-card-body">
                <div class="stat-row">
                    <span class="stat-label">Name</span>
                    <span class="stat-val">{{ $user->name }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Username</span>
                    <span class="stat-val">{{ $user->username }}</span>
                </div>
                @if($user->email)
                <div class="stat-row">
                    <span class="stat-label">Email</span>
                    <span class="stat-val" style="font-size:.72rem">{{ $user->email }}</span>
                </div>
                @endif
                <div class="stat-row">
                    <span class="stat-label">Role</span>
                    <span class="stat-val">
                        @if($user->is_super)
                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;"><i class="fa fa-crown me-1"></i>Super Admin</span>
                        @elseif($user->role)
                        <span class="badge" style="background:#ede9fe;color:#6d28d9;">{{ $user->role->name }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </span>
                </div>
                @if($user->employee)
                <div class="stat-row">
                    <span class="stat-label">Employee</span>
                    <span class="stat-val" style="font-size:.72rem">{{ $user->employee->code ? $user->employee->code.' — ' : '' }}{{ $user->employee->name }}</span>
                </div>
                @endif
                <div class="stat-row">
                    <span class="stat-label">Status</span>
                    <span class="stat-val">
                        @if($user->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Inactive</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Company & branch summary --}}
        <div class="panel-card">
            <div class="panel-card-header">
                <i class="fa fa-building text-primary"></i> Company Access
            </div>
            <div class="panel-card-body">
                @if($user->is_super)
                <p class="text-muted small mb-0"><i class="fa fa-crown me-1 text-warning"></i>Super admin — all companies.</p>
                @elseif($user->role && $user->role->companies->isNotEmpty())

                    @if($systemPermissions->isNotEmpty())
                    <div class="co-row">
                        <span class="co-type-pill pill-system">SYS</span>
                        System
                        <span class="co-count">{{ $systemPermissions->flatten()->count() }}</span>
                    </div>
                    @endif

                    @foreach($user->role->companies as $co)
                    @php
                        $coBranches  = $branchAccess[$co->id] ?? [];
                        if (empty($coBranches)) { continue; }
                        $pilClass = match($co->type) {
                            'cnf'     => 'pill-cnf',
                            'freight' => 'pill-freight',
                            'trading' => 'pill-trading',
                            default   => 'pill-system',
                        };
                        $coPermCount = isset($companyPermissions[$co->id]) ? $companyPermissions[$co->id]->count() : 0;
                    @endphp
                    <div style="padding:.3rem 0;border-bottom:1px solid #f1f5f9;">
                        <div class="co-row" style="border:none;padding:.15rem 0;">
                            <span class="co-type-pill {{ $pilClass }}">{{ strtoupper($co->type) }}</span>
                            <span style="font-size:.78rem;font-weight:600">{{ $co->name }}</span>
                            <span class="co-count">{{ $coPermCount }}</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-top:.3rem;padding-left:.25rem;">
                            @foreach($coBranches as $bn)
                            <span class="branch-chip branch-chip-{{ $co->type }}"><i class="fa fa-code-branch" style="font-size:.6rem"></i>{{ $bn }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                @else
                <p class="text-muted small mb-0">No company access configured.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Right: Permissions panel --}}
    <div class="perms-card">
        <div class="perms-card-header">
            <i class="fa fa-key text-primary"></i>
            <span class="ph-title">Permissions</span>
            @if($user->is_super)
            <span class="ph-total" style="background:#fef3c7;color:#92400e;">All permissions</span>
            @else
            @php
                $effectiveTotal = $systemPermissions->flatten()->count();
                if ($user->role) {
                    foreach ($user->role->companies as $co) {
                        if (empty($branchAccess[$co->id])) { continue; }
                        $effectiveTotal += isset($companyPermissions[$co->id]) ? $companyPermissions[$co->id]->count() : 0;
                    }
                }
            @endphp
            <span class="ph-total">{{ $effectiveTotal }} total</span>
            @endif
        </div>

        @if($user->is_super)
        <div class="empty-perms">
            <i class="fa fa-crown opacity-40" style="color:#ca8a04"></i>
            Super admin has unrestricted access to all permissions.
        </div>

        @elseif(! $user->role || $user->role->permissions->isEmpty())
        <div class="empty-perms">
            <i class="fa fa-lock-open opacity-40"></i>
            No role or permissions assigned to this user.
        </div>

        @else

        {{-- Tab strip --}}
        @php $firstTab = true; @endphp
        <div class="perm-tab-strip" role="tablist">

            @if($systemPermissions->isNotEmpty())
            <button class="perm-tab-btn {{ $firstTab ? 'active' : '' }}"
                    data-tab="perm-pane-system" data-type="system" role="tab" type="button">
                <span class="co-type-pill pill-system">SYS</span>
                System
                <span class="tab-badge">{{ $systemPermissions->flatten()->count() }}</span>
            </button>
            @php $firstTab = false; @endphp
            @endif

            @foreach($user->role->companies as $co)
            @php
                $coTabCount = isset($companyPermissions[$co->id]) ? $companyPermissions[$co->id]->count() : 0;
                if ($coTabCount === 0) { continue; }
                if (empty($branchAccess[$co->id])) { continue; }
                $pilClass = match($co->type) {
                    'cnf'     => 'pill-cnf',
                    'freight' => 'pill-freight',
                    'trading' => 'pill-trading',
                    default   => 'pill-system',
                };
            @endphp
            <button class="perm-tab-btn {{ $firstTab ? 'active' : '' }}"
                    data-tab="perm-pane-co-{{ $co->id }}" data-type="{{ $co->type }}" role="tab" type="button">
                <span class="co-type-pill {{ $pilClass }}">{{ strtoupper($co->type) }}</span>
                {{ $co->name }}
                <span class="tab-badge">{{ $coTabCount }}</span>
            </button>
            @php $firstTab = false; @endphp
            @endforeach

        </div>

        {{-- Panes --}}
        @php $firstPane = true; @endphp

        @if($systemPermissions->isNotEmpty())
        <div class="perm-tab-pane-inner {{ $firstPane ? 'active' : '' }}" id="perm-pane-system">
            <div class="pane-banner pane-banner-system">
                <span class="co-type-pill pill-system">SYS</span>
                System
                <span class="pane-count">{{ $systemPermissions->flatten()->count() }} permissions</span>
            </div>
            <div class="pane-content">
                @foreach($systemPermissions as $module => $perms)
                <div class="mod-group">
                    <div class="mod-label">{{ $module }}</div>
                    <div class="perms-chips">
                        @foreach($perms->sortBy('sorting_order') as $perm)
                        @php
                            $action = ucfirst(last(explode('.', $perm->name)));
                            $icon   = match($action) {
                                'List'   => 'fa-list',
                                'View'   => 'fa-eye',
                                'Create' => 'fa-plus',
                                'Edit'   => 'fa-pen',
                                'Delete' => 'fa-trash',
                                default  => 'fa-check',
                            };
                        @endphp
                        <span class="perm-chip"><i class="fa {{ $icon }}"></i> {{ $action }}</span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @php $firstPane = false; @endphp
        @endif

        @foreach($user->role->companies as $co)
        @php
            $coBranches     = $branchAccess[$co->id] ?? [];
            $coPermsGrouped = isset($companyPermissions[$co->id])
                ? $companyPermissions[$co->id]->groupBy('module')
                : collect();
            if ($coPermsGrouped->isEmpty()) { continue; }
            if (empty($coBranches)) { continue; }
            $pilClass = match($co->type) {
                'cnf'     => 'pill-cnf',
                'freight' => 'pill-freight',
                'trading' => 'pill-trading',
                default   => 'pill-system',
            };
        @endphp
        @if(true)
        <div class="perm-tab-pane-inner {{ $firstPane ? 'active' : '' }}" id="perm-pane-co-{{ $co->id }}">
            <div class="pane-banner pane-banner-{{ $co->type }}">
                <span class="co-type-pill {{ $pilClass }}">{{ strtoupper($co->type) }}</span>
                {{ $co->name }}
                <span class="pane-count">{{ $coPermsGrouped->flatten()->count() }} permissions</span>
            </div>

            <div class="branch-row">
                <span class="branch-label"><i class="fa fa-code-branch me-1 opacity-50"></i>Branch Access:</span>
                @foreach($coBranches as $bn)
                <span class="branch-chip branch-chip-{{ $co->type }}">{{ $bn }}</span>
                @endforeach
            </div>

            <div class="pane-content">
                @foreach($coPermsGrouped as $module => $perms)
                <div class="mod-group">
                    <div class="mod-label">{{ $module }}</div>
                    <div class="perms-chips">
                        @foreach($perms->sortBy('sorting_order') as $perm)
                        @php
                            $action = ucfirst(last(explode('.', $perm->name)));
                            $icon   = match($action) {
                                'List'    => 'fa-list',
                                'View'    => 'fa-eye',
                                'Create'  => 'fa-plus',
                                'Edit'    => 'fa-pen',
                                'Delete'  => 'fa-trash',
                                'Print'   => 'fa-print',
                                'Export'  => 'fa-file-export',
                                'Confirm' => 'fa-circle-check',
                                'Reject'  => 'fa-circle-xmark',
                                default   => 'fa-check',
                            };
                        @endphp
                        <span class="perm-chip"><i class="fa {{ $icon }}"></i> {{ $action }}</span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @php $firstPane = false; @endphp
        @endif
        @endforeach

        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const btns  = document.querySelectorAll('.perm-tab-btn');
    const panes = document.querySelectorAll('.perm-tab-pane-inner');

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            btns.forEach(function (b) { b.classList.remove('active'); });
            panes.forEach(function (p) { p.classList.remove('active'); });

            btn.classList.add('active');
            var target = document.getElementById(btn.dataset.tab);
            if (target) { target.classList.add('active'); }
        });
    });
})();
</script>
@endpush
