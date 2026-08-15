@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
/* ── Stats strip ── */
.stats-strip {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    transition: box-shadow .15s, transform .15s;
    text-decoration: none;
    color: inherit;
}
.stat-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.09);
    transform: translateY(-1px);
    color: inherit;
}
.stat-icon {
    width: 42px; height: 42px;
    border-radius: .4rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.stat-icon.blue    { background: #eff6ff; color: #1d4ed8; }
.stat-icon.green   { background: #f0fdf4; color: #15803d; }
.stat-icon.violet  { background: #f5f3ff; color: #6d28d9; }
.stat-icon.amber   { background: #fffbeb; color: #b45309; }
.stat-icon.slate   { background: #f8fafc; color: #475569; }
.stat-label { font-size: .7rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.stat-value { font-size: 1.55rem; font-weight: 800; color: #1e293b; line-height: 1; }

/* ── Section title ── */
.section-title {
    font-size: .7rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .09em; color: #94a3b8;
    margin-bottom: .65rem; margin-top: .25rem;
    display: flex; align-items: center; gap: .5rem;
}
.section-title::after {
    content: ''; flex: 1; height: 1px; background: #e2e8f0;
}

/* ── Module grid ── */
.module-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .75rem;
    margin-bottom: 1.5rem;
}

/* ── Module tile ── */
.mod-tile {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .55rem;
    padding: 1.1rem 1rem 1rem;
    display: flex; flex-direction: column;
    gap: .55rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    text-decoration: none; color: inherit;
    transition: box-shadow .15s, transform .15s, border-color .15s;
    position: relative; overflow: hidden;
}
.mod-tile:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.1);
    transform: translateY(-2px);
    color: inherit;
}
.mod-tile::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: .55rem .55rem 0 0;
}
.mod-tile.blue::before   { background: #3b82f6; }
.mod-tile.green::before  { background: #22c55e; }
.mod-tile.violet::before { background: #8b5cf6; }
.mod-tile.amber::before  { background: #f59e0b; }
.mod-tile.rose::before   { background: #f43f5e; }
.mod-tile.teal::before   { background: #14b8a6; }

.mod-tile-icon {
    width: 38px; height: 38px;
    border-radius: .35rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.mod-tile.blue   .mod-tile-icon { background: #eff6ff; color: #1d4ed8; }
.mod-tile.green  .mod-tile-icon { background: #f0fdf4; color: #15803d; }
.mod-tile.violet .mod-tile-icon { background: #f5f3ff; color: #6d28d9; }
.mod-tile.amber  .mod-tile-icon { background: #fffbeb; color: #b45309; }
.mod-tile.rose   .mod-tile-icon { background: #fff1f2; color: #be123c; }
.mod-tile.teal   .mod-tile-icon { background: #f0fdfa; color: #0f766e; }

.mod-tile-name {
    font-size: .82rem; font-weight: 700; color: #1e293b;
}
.mod-tile-desc {
    font-size: .7rem; color: #64748b; line-height: 1.4;
}
.mod-tile-actions {
    display: flex; flex-wrap: wrap; gap: .3rem; margin-top: auto; padding-top: .3rem;
}
.mod-action-btn {
    font-size: .68rem; font-weight: 600;
    padding: .2rem .55rem; border-radius: .25rem;
    border: 1px solid; text-decoration: none;
    transition: background .1s;
}
.mod-tile.blue   .mod-action-btn { border-color: #bfdbfe; color: #1d4ed8; background: #eff6ff; }
.mod-tile.blue   .mod-action-btn:hover { background: #dbeafe; }
.mod-tile.green  .mod-action-btn { border-color: #bbf7d0; color: #15803d; background: #f0fdf4; }
.mod-tile.green  .mod-action-btn:hover { background: #dcfce7; }
.mod-tile.violet .mod-action-btn { border-color: #ddd6fe; color: #6d28d9; background: #f5f3ff; }
.mod-tile.violet .mod-action-btn:hover { background: #ede9fe; }
.mod-tile.amber  .mod-action-btn { border-color: #fde68a; color: #b45309; background: #fffbeb; }
.mod-tile.amber  .mod-action-btn:hover { background: #fef3c7; }
.mod-tile.rose   .mod-action-btn { border-color: #fecdd3; color: #be123c; background: #fff1f2; }
.mod-tile.rose   .mod-action-btn:hover { background: #ffe4e6; }
.mod-tile.teal   .mod-action-btn { border-color: #99f6e4; color: #0f766e; background: #f0fdfa; }
.mod-tile.teal   .mod-action-btn:hover { background: #ccfbf1; }

.mod-tile-count {
    position: absolute; top: .65rem; right: .75rem;
    font-size: .7rem; font-weight: 700;
    color: #94a3b8; background: #f1f5f9;
    padding: .1rem .45rem; border-radius: 999px;
}

/* ── Page header ── */
.dash-header {
    display: flex; align-items: center; gap: .75rem;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; padding: .8rem 1.1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    margin-bottom: 1rem;
}
.dash-header .dh-icon {
    width: 38px; height: 38px; border-radius: .4rem;
    background: #eff6ff; color: #1d4ed8;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.dash-header .dh-title  { font-size: .9rem; font-weight: 700; color: #1e293b; }
.dash-header .dh-sub    { font-size: .72rem; color: #64748b; }
.dash-header .dh-time   { margin-left: auto; font-size: .72rem; color: #94a3b8; }

@media (max-width: 1100px) {
    .stats-strip  { grid-template-columns: repeat(3, 1fr); }
    .module-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) {
    .stats-strip  { grid-template-columns: repeat(2, 1fr); }
    .module-grid  { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="dash-header">
    <div class="dh-icon"><i class="fa fa-gauge"></i></div>
    <div>
        <div class="dh-title">Admin Dashboard</div>
        <div class="dh-sub">System overview and quick access to all admin modules</div>
    </div>
    <div class="dh-time">
        <i class="fa fa-clock me-1"></i>{{ now()->format('d M Y, h:i A') }}
    </div>
</div>

{{-- Stats strip --}}
<div class="stats-strip">
    <a href="{{ route('admin.companies.index') }}" class="stat-card">
        <div class="stat-icon blue"><i class="fa fa-building"></i></div>
        <div>
            <div class="stat-label">Companies</div>
            <div class="stat-value">{{ $stats['companies'] }}</div>
        </div>
    </a>
    <a href="{{ route('admin.users.index') }}" class="stat-card">
        <div class="stat-icon green"><i class="fa fa-users"></i></div>
        <div>
            <div class="stat-label">Admin Users</div>
            <div class="stat-value">{{ $stats['users'] }}</div>
        </div>
    </a>
    <a href="{{ route('admin.roles.index') }}" class="stat-card">
        <div class="stat-icon violet"><i class="fa fa-user-shield"></i></div>
        <div>
            <div class="stat-label">Roles</div>
            <div class="stat-value">{{ $stats['roles'] }}</div>
        </div>
    </a>
    <a href="{{ route('admin.roles.index') }}" class="stat-card">
        <div class="stat-icon amber"><i class="fa fa-key"></i></div>
        <div>
            <div class="stat-label">Permissions</div>
            <div class="stat-value">{{ $stats['permissions'] }}</div>
        </div>
    </a>
    <a href="#employees-section" class="stat-card">
        <div class="stat-icon slate"><i class="fa fa-id-badge"></i></div>
        <div>
            <div class="stat-label">Employees</div>
            <div class="stat-value">{{ $stats['employees'] }}</div>
        </div>
    </a>
</div>

{{-- Settings section --}}
<div class="section-title">Settings</div>
<div class="module-grid">

    {{-- Companies --}}
    <a href="{{ route('admin.companies.index') }}" class="mod-tile blue">
        <span class="mod-tile-count">{{ $stats['companies'] }}</span>
        <div class="mod-tile-icon"><i class="fa fa-building"></i></div>
        <div class="mod-tile-name">Companies</div>
        <div class="mod-tile-desc">Manage NAS Group companies — logos, contact info, and active status.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn"><i class="fa fa-list me-1"></i>View All</span>
        </div>
    </a>

    {{-- Users --}}
    <a href="{{ route('admin.users.index') }}" class="mod-tile green">
        <span class="mod-tile-count">{{ $stats['users'] }}</span>
        <div class="mod-tile-icon"><i class="fa fa-users"></i></div>
        <div class="mod-tile-name">Admin Users</div>
        <div class="mod-tile-desc">Manage system users, company access, and account status.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn"><i class="fa fa-list me-1"></i>View All</span>
            <span class="mod-action-btn"><i class="fa fa-plus me-1"></i>Add User</span>
        </div>
    </a>

</div>

{{-- Access Control section --}}
<div class="section-title">Access Control</div>
<div class="module-grid">

    {{-- Roles --}}
    <a href="{{ route('admin.roles.index') }}" class="mod-tile violet">
        <span class="mod-tile-count">{{ $stats['roles'] }}</span>
        <div class="mod-tile-icon"><i class="fa fa-user-shield"></i></div>
        <div class="mod-tile-name">Roles</div>
        <div class="mod-tile-desc">Define roles with company-scoped permissions for access control.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn"><i class="fa fa-list me-1"></i>View All</span>
            <span class="mod-action-btn"><i class="fa fa-plus me-1"></i>Create Role</span>
        </div>
    </a>

    {{-- Permissions --}}
    <div class="mod-tile amber" style="cursor:default;">
        <span class="mod-tile-count">{{ $stats['permissions'] }}</span>
        <div class="mod-tile-icon"><i class="fa fa-key"></i></div>
        <div class="mod-tile-name">Permissions</div>
        <div class="mod-tile-desc">{{ $stats['permissions'] }} permissions across system, C&F, Freights, and Trading.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn" style="opacity:.5;cursor:not-allowed;"><i class="fa fa-list me-1"></i>Managed via seeder</span>
        </div>
    </div>

</div>

{{-- Employees section --}}
<div class="section-title" id="employees-section">Employees</div>
<div class="module-grid">

    {{-- Chevron --}}
    <div class="mod-tile teal" style="cursor:default;">
        <div class="mod-tile-icon"><i class="fa fa-id-badge"></i></div>
        <div class="mod-tile-name">Chevron Lines — Employees</div>
        <div class="mod-tile-desc">C&F division employee records and department management.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn" style="opacity:.5;cursor:not-allowed;"><i class="fa fa-lock me-1"></i>Company Panel</span>
        </div>
    </div>

    {{-- NAS Freights --}}
    <div class="mod-tile rose" style="cursor:default;">
        <div class="mod-tile-icon"><i class="fa fa-id-badge"></i></div>
        <div class="mod-tile-name">NAS Freights — Employees</div>
        <div class="mod-tile-desc">Freights division employee records and department management.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn" style="opacity:.5;cursor:not-allowed;"><i class="fa fa-lock me-1"></i>Company Panel</span>
        </div>
    </div>

    {{-- NAS Trading --}}
    <div class="mod-tile amber" style="cursor:default;">
        <div class="mod-tile-icon"><i class="fa fa-id-badge"></i></div>
        <div class="mod-tile-name">NAS Trading — Employees</div>
        <div class="mod-tile-desc">Trading division employee records and department management.</div>
        <div class="mod-tile-actions">
            <span class="mod-action-btn" style="opacity:.5;cursor:not-allowed;"><i class="fa fa-lock me-1"></i>Company Panel</span>
        </div>
    </div>

</div>

@endsection
