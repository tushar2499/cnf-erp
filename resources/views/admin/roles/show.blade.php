@extends('admin.layouts.app')

@section('title', 'Role — ' . $role->name)

@push('styles')
<style>
.view-topbar {
    display: flex; align-items: center; gap: .75rem;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; padding: .65rem 1.1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    margin-bottom: 1rem;
}
.view-topbar .role-title   { font-size: .9rem; font-weight: 700; color: #1e293b; }
.view-topbar .role-sub     { font-size: .72rem; color: #64748b; }
.view-topbar .actions      { margin-left: auto; display: flex; gap: .5rem; }

.view-body { display: grid; grid-template-columns: 260px 1fr; gap: 1rem; align-items: start; }

/* ── panel card ── */
.panel-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    margin-bottom: .75rem;
}
.panel-card-header {
    padding: .55rem .9rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; color: #64748b;
    display: flex; align-items: center; gap: .4rem;
}
.panel-card-body { padding: .85rem .9rem; }

/* ── stat row ── */
.stat-row {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: .35rem 0; border-bottom: 1px solid #f1f5f9;
    font-size: .78rem;
}
.stat-row:last-child { border-bottom: none; }
.stat-label { color: #64748b; font-weight: 500; }
.stat-val   { font-weight: 700; color: #1e293b; }

/* ── company pill ── */
.co-type-pill {
    font-size: .62rem; font-weight: 800; letter-spacing: .05em;
    padding: .12rem .4rem; border-radius: 4px; line-height: 1.2;
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

/* ── permissions card ── */
.perms-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.perms-card-header {
    padding: .65rem 1.1rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: .6rem;
}
.perms-card-header .ph-title { font-size: .8rem; font-weight: 700; color: #1e293b; }
.perms-card-header .ph-total {
    margin-left: auto; font-size: .73rem; font-weight: 600;
    color: #6366f1; background: #eef2ff;
    padding: .15rem .55rem; border-radius: 999px;
}

/* ── section block per company ── */
.co-section { padding: 1rem 1.1rem; border-bottom: 1px solid #f1f5f9; }
.co-section:last-child { border-bottom: none; }
.co-section-header {
    display: flex; align-items: center; gap: .55rem;
    margin-bottom: .75rem;
}
.co-section-name { font-size: .8rem; font-weight: 700; color: #1e293b; }
.co-section-count {
    font-size: .7rem; font-weight: 600; color: #64748b;
    background: #f1f5f9; padding: .1rem .4rem; border-radius: 999px;
}

/* ── module group ── */
.mod-group { margin-bottom: .65rem; }
.mod-label {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #94a3b8; margin-bottom: .35rem;
}
.perms-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
.perm-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .28rem .65rem; border-radius: .3rem;
    border: 1.5px solid #bfdbfe; background: #eff6ff;
    font-size: .74rem; font-weight: 600; color: #1d4ed8;
}
.perm-chip i { font-size: .65rem; }

/* ── empty state ── */
.empty-perms {
    text-align: center; padding: 2.5rem 1rem; color: #94a3b8;
}
.empty-perms i { font-size: 2rem; display: block; margin-bottom: .5rem; }
</style>
@endpush

@section('content')

{{-- Top bar --}}
<div class="view-topbar">
    <div>
        <div class="role-title">
            <i class="fa fa-user-shield me-2 text-primary"></i>{{ $role->name }}
        </div>
        <div class="role-sub">Role details and assigned permissions</div>
    </div>
    <div class="actions">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">
            <i class="fa fa-edit me-1"></i> Edit Role
        </a>
    </div>
</div>

<div class="view-body">

    {{-- Left panel --}}
    <div>

        {{-- Role info --}}
        <div class="panel-card">
            <div class="panel-card-header">
                <i class="fa fa-circle-info text-primary"></i> Role Information
            </div>
            <div class="panel-card-body">
                <div class="stat-row">
                    <span class="stat-label">Role Name</span>
                    <span class="stat-val">{{ $role->name }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Guard</span>
                    <span class="stat-val">{{ $role->guard_name }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Total Permissions</span>
                    <span class="stat-val text-primary">{{ $role->permissions->count() }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Created</span>
                    <span class="stat-val">{{ $role->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Companies --}}
        <div class="panel-card">
            <div class="panel-card-header">
                <i class="fa fa-building text-primary"></i> Companies
            </div>
            <div class="panel-card-body">
                @if($systemPermissions->isNotEmpty())
                <div class="co-row">
                    <span class="co-type-pill pill-system">SYS</span>
                    System
                    <span class="co-count">{{ $systemPermissions->flatten()->count() }}</span>
                </div>
                @endif

                @forelse($role->companies as $co)
                @php
                    $pilClass = match($co->type) {
                        'cnf'     => 'pill-cnf',
                        'freight' => 'pill-freight',
                        'trading' => 'pill-trading',
                        default   => 'pill-system',
                    };
                    $coPerms = isset($companyPermissions[$co->id]) ? $companyPermissions[$co->id]->count() : 0;
                @endphp
                <div class="co-row">
                    <span class="co-type-pill {{ $pilClass }}">{{ strtoupper($co->type) }}</span>
                    {{ $co->name }}
                    <span class="co-count">{{ $coPerms }}</span>
                </div>
                @empty
                    @if($systemPermissions->isEmpty())
                    <p class="text-muted small mb-0">No companies assigned.</p>
                    @endif
                @endforelse
            </div>
        </div>

    </div>

    {{-- Right: Permissions --}}
    <div class="perms-card">
        <div class="perms-card-header">
            <i class="fa fa-key text-primary"></i>
            <span class="ph-title">Permissions</span>
            <span class="ph-total">{{ $role->permissions->count() }} total</span>
        </div>

        @if($role->permissions->isEmpty())
        <div class="empty-perms">
            <i class="fa fa-lock-open opacity-40"></i>
            No permissions assigned to this role.
        </div>
        @else

            {{-- System permissions --}}
            @if($systemPermissions->isNotEmpty())
            <div class="co-section">
                <div class="co-section-header">
                    <span class="co-type-pill pill-system">SYS</span>
                    <span class="co-section-name">System</span>
                    <span class="co-section-count">{{ $systemPermissions->flatten()->count() }} permissions</span>
                </div>
                @foreach($systemPermissions as $module => $perms)
                <div class="mod-group">
                    <div class="mod-label">{{ $module }}</div>
                    <div class="perms-chips">
                        @foreach($perms->sortBy('sorting_order') as $perm)
                        @php
                            $action = ucfirst(last(explode('.', $perm->name)));
                            $icon   = match($action) {
                                'View'   => 'fa-eye',
                                'Create' => 'fa-plus',
                                'Edit'   => 'fa-pen',
                                'Delete' => 'fa-trash',
                                default  => 'fa-check',
                            };
                        @endphp
                        <span class="perm-chip">
                            <i class="fa {{ $icon }}"></i> {{ $action }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Company-wise permissions --}}
            @foreach($role->companies as $co)
            @php
                $coPermsGrouped = isset($companyPermissions[$co->id])
                    ? $companyPermissions[$co->id]->groupBy('module')
                    : collect();
                $pilClass = match($co->type) {
                    'cnf'     => 'pill-cnf',
                    'freight' => 'pill-freight',
                    'trading' => 'pill-trading',
                    default   => 'pill-system',
                };
            @endphp
            @if($coPermsGrouped->isNotEmpty())
            <div class="co-section">
                <div class="co-section-header">
                    <span class="co-type-pill {{ $pilClass }}">{{ strtoupper($co->type) }}</span>
                    <span class="co-section-name">{{ $co->name }}</span>
                    <span class="co-section-count">
                        {{ $coPermsGrouped->flatten()->count() }} permissions
                    </span>
                </div>
                @foreach($coPermsGrouped as $module => $perms)
                <div class="mod-group">
                    <div class="mod-label">{{ $module }}</div>
                    <div class="perms-chips">
                        @foreach($perms->sortBy('sorting_order') as $perm)
                        @php
                            $action = ucfirst(last(explode('.', $perm->name)));
                            $icon   = match($action) {
                                'View'   => 'fa-eye',
                                'Create' => 'fa-plus',
                                'Edit'   => 'fa-pen',
                                'Delete' => 'fa-trash',
                                'Print'  => 'fa-print',
                                'Export' => 'fa-file-export',
                                default  => 'fa-check',
                            };
                        @endphp
                        <span class="perm-chip">
                            <i class="fa {{ $icon }}"></i> {{ $action }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            @endforeach

        @endif
    </div>

</div>
@endsection
