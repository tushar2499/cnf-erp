@extends('admin.layouts.app')

@section('title', $role ? 'Edit Role' : 'Create Role')

@push('styles')
<style>
/* ── Page shell ── */
.role-page { display: flex; flex-direction: column; gap: 1rem; }

/* ── Sticky action bar ── */
.role-topbar {
    position: sticky; top: 42px; z-index: 100;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .65rem 1.1rem;
    display: flex; align-items: center; gap: .75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.role-topbar .role-title { font-size: .9rem; font-weight: 700; color: #1e293b; }
.role-topbar .role-subtitle { font-size: .72rem; color: #64748b; }
.role-topbar .actions { margin-left: auto; display: flex; gap: .5rem; }

/* ── Two-col layout ── */
.role-body { display: grid; grid-template-columns: 280px 1fr; gap: 1rem; align-items: start; }

/* ── Left panel ── */
.left-panel { display: flex; flex-direction: column; gap: .75rem; }
.panel-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.panel-card-header {
    padding: .6rem .9rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; color: #64748b;
    display: flex; align-items: center; gap: .45rem;
}
.panel-card-body { padding: .85rem .9rem; }
.panel-card-body .form-label { font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: .3rem; }
.panel-card-body .form-control {
    font-size: .82rem; border-color: #d1d5db;
    border-radius: .375rem; padding: .45rem .65rem;
}
.panel-card-body .form-control:focus {
    border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.panel-card-footer {
    padding: .75rem .9rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

/* ── Permissions card ── */
.perms-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.perms-card-header {
    padding: .65rem 1.1rem;
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: .6rem;
}
.perms-card-header .ph-title { font-size: .8rem; font-weight: 700; color: #1e293b; }
.perms-card-header .ph-count {
    margin-left: auto; font-size: .73rem; font-weight: 600;
    color: #6366f1; background: #eef2ff;
    padding: .15rem .55rem; border-radius: 999px;
}

/* ── Company tabs ── */
.co-tabs {
    display: flex; gap: 0;
    border-bottom: 2px solid #e2e8f0;
    background: #fafafa;
    padding: 0 1.1rem;
}
.co-tab-btn {
    display: flex; align-items: center; gap: .45rem;
    padding: .65rem 1rem; border: none; background: none;
    border-bottom: 3px solid transparent; margin-bottom: -2px;
    font-size: .78rem; font-weight: 600; color: #64748b;
    cursor: pointer; white-space: nowrap;
    transition: color .15s, border-color .15s;
}
.co-tab-btn:hover { color: #334155; }
.co-tab-btn.active { color: #1d4ed8; border-bottom-color: #1d4ed8; }

.co-type-pill {
    font-size: .62rem; font-weight: 800; letter-spacing: .05em;
    padding: .12rem .4rem; border-radius: 4px; line-height: 1.2;
}
.pill-cnf     { background: #dcfce7; color: #15803d; }
.pill-freight { background: #cffafe; color: #0e7490; }
.pill-trading { background: #fef9c3; color: #a16207; }

.co-count-badge {
    font-size: .68rem; font-weight: 700;
    background: #e2e8f0; color: #475569;
    min-width: 1.5rem; text-align: center;
    padding: .1rem .35rem; border-radius: 999px;
    transition: background .2s, color .2s;
}
.co-count-badge.active { background: #1d4ed8; color: #fff; }

/* ── Tab content area ── */
.co-tab-pane { display: none; padding: 1rem 1.1rem; }
.co-tab-pane.active { display: block; }

/* ── Select-all bar ── */
.select-all-bar {
    display: flex; align-items: center; gap: .6rem;
    padding: .5rem .65rem; margin-bottom: .85rem;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: .4rem;
}
.select-all-bar label {
    font-size: .76rem; font-weight: 600; color: #374151; cursor: pointer; margin: 0;
}
.select-all-bar .total-in-co { margin-left: auto; font-size: .72rem; color: #64748b; }

/* ── Module group ── */
.module-group { border: 1px solid #e2e8f0; border-radius: .45rem; margin-bottom: .75rem; overflow: hidden; }

.module-header {
    display: flex; align-items: center; gap: .6rem;
    padding: .55rem .85rem; background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0; cursor: pointer;
    transition: background .12s;
}
.module-header:hover { background: #e9eef6; }
.module-header .mod-name {
    font-size: .78rem; font-weight: 700; color: #1e293b; flex: 1;
}
.module-header .mod-counter {
    font-size: .7rem; font-weight: 600; color: #64748b;
    background: #fff; border: 1px solid #e2e8f0;
    padding: .1rem .45rem; border-radius: 999px;
}

/* ── Permission chips ── */
.perms-grid {
    display: flex; flex-wrap: wrap; gap: .45rem;
    padding: .75rem .85rem;
}
.perm-chip {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .32rem .7rem; border-radius: .35rem;
    border: 1.5px solid #d1d5db; background: #fff;
    font-size: .76rem; font-weight: 500; color: #374151;
    cursor: pointer; user-select: none;
    transition: all .15s ease;
}
.perm-chip:hover {
    border-color: #93c5fd; background: #eff6ff; color: #1d4ed8;
}
.perm-chip.is-checked {
    border-color: #1d4ed8; background: #1d4ed8; color: #fff;
}
.perm-chip input[type=checkbox] { display: none; }
.perm-chip .chip-icon { font-size: .7rem; }

/* ── Summary panel ── */
.summary-section { display: flex; flex-direction: column; gap: .35rem; }
.co-summary-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: .35rem .6rem; border-radius: .35rem;
    background: #f8fafc; border: 1px solid #e2e8f0;
    font-size: .75rem;
}
.co-summary-item .co-label { font-weight: 600; color: #374151; }
.co-summary-item .co-val   { font-weight: 700; color: #1d4ed8; }
.co-summary-item .co-val.zero { color: #94a3b8; }
</style>
@endpush

@section('content')

<form method="POST"
      action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}"
      id="roleForm">
    @csrf
    @if($role) @method('PUT') @endif

    <div class="role-page">

        {{-- Sticky top bar --}}
        <div class="role-topbar">
            <div>
                <div class="role-title">
                    <i class="fa fa-user-shield me-2 text-primary"></i>
                    {{ $role ? 'Edit Role' : 'Create Role' }}
                </div>
                <div class="role-subtitle">Define a role and assign company-wise permissions</div>
            </div>
            <div class="actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-0 py-2" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Two-col body --}}
        <div class="role-body">

            {{-- Left: Role info + summary --}}
            <div class="left-panel">

                {{-- Role info --}}
                <div class="panel-card">
                    <div class="panel-card-header">
                        <i class="fa fa-circle-info text-primary"></i> Role Information
                    </div>
                    <div class="panel-card-body">
                        <label class="form-label">
                            Role Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="fldName"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               value="{{ old('name', $role?->name) }}"
                               placeholder="e.g. Accounts Manager"
                               autocomplete="off" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Permission summary --}}
                <div class="panel-card">
                    <div class="panel-card-header">
                        <i class="fa fa-chart-bar text-indigo-500"></i> Permission Summary
                    </div>
                    <div class="panel-card-body">
                        <div class="summary-section">
                            <div class="co-summary-item">
                                <span class="co-label">
                                    <span class="co-type-pill me-1" style="background:#f1f5f9;color:#475569">SYS</span>
                                    System
                                </span>
                                <span class="co-val zero" id="summary-system">0</span>
                            </div>
                            @foreach($companies as $co)
                            @php
                                $pilClass = match($co->type) {
                                    'cnf'     => 'pill-cnf',
                                    'freight' => 'pill-freight',
                                    'trading' => 'pill-trading',
                                    default   => '',
                                };
                            @endphp
                            <div class="co-summary-item">
                                <span class="co-label">
                                    <span class="co-type-pill {{ $pilClass }} me-1">{{ strtoupper($co->type) }}</span>
                                    {{ $co->name }}
                                </span>
                                <span class="co-val zero" id="summary-{{ $co->id }}">0</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="panel-card-footer">
                        <button type="submit" class="btn btn-sm btn-primary w-100" id="btnSave">
                            <i class="fa fa-save me-1"></i>
                            {{ $role ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>
                </div>

            </div>

            {{-- Right: Permissions --}}
            <div class="perms-card">
                <div class="perms-card-header">
                    <i class="fa fa-key text-primary"></i>
                    <span class="ph-title">Permissions</span>
                    <span class="ph-count" id="totalCount">0 selected</span>
                </div>

                {{-- Company tabs --}}
                <div class="co-tabs" id="coTabs">
                    {{-- System tab --}}
                    <button type="button"
                            class="co-tab-btn active"
                            data-target="pane-co-system"
                            data-company="system">
                        <span class="co-type-pill" style="background:#f1f5f9;color:#475569">SYS</span>
                        System
                        <span class="co-count-badge" id="badge-system">0</span>
                    </button>
                    @foreach($companies as $co)
                    @php
                        $pilClass = match($co->type) {
                            'cnf'     => 'pill-cnf',
                            'freight' => 'pill-freight',
                            'trading' => 'pill-trading',
                            default   => '',
                        };
                    @endphp
                    <button type="button"
                            class="co-tab-btn"
                            data-target="pane-co-{{ $co->id }}"
                            data-company="{{ $co->id }}">
                        <span class="co-type-pill {{ $pilClass }}">{{ strtoupper($co->type) }}</span>
                        {{ $co->name }}
                        <span class="co-count-badge" id="badge-{{ $co->id }}">0</span>
                    </button>
                    @endforeach
                </div>

                {{-- System tab pane --}}
                @php
                    $systemModules = $systemPermissions->groupBy('module');
                @endphp
                <div class="co-tab-pane active" id="pane-co-system">
                    @if($systemModules->isEmpty())
                        <p class="text-muted text-center py-4">
                            <i class="fa fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                            No system permissions defined.
                        </p>
                    @else
                        <div class="select-all-bar">
                            <input type="checkbox" class="form-check-input co-select-all"
                                   id="selectAll-system" data-company="system">
                            <label for="selectAll-system">Select All Permissions</label>
                            <span class="total-in-co" id="co-total-system">
                                0 / {{ $systemPermissions->count() }}
                            </span>
                        </div>
                        @foreach($systemModules as $modName => $permissions)
                        @php $modSlug = 'sys-'.Str::slug($modName); @endphp
                        <div class="module-group">
                            <div class="module-header" data-module="system-{{ $modSlug }}">
                                <input type="checkbox" class="form-check-input mod-check"
                                       id="mod-system-{{ $modSlug }}"
                                       data-company="system" data-module="{{ $modSlug }}"
                                       onclick="event.stopPropagation()">
                                <label class="mod-name mb-0" for="mod-system-{{ $modSlug }}"
                                       onclick="event.stopPropagation()">
                                    {{ $modName }}
                                </label>
                                <span class="mod-counter" id="modcount-system-{{ $modSlug }}">
                                    0 / {{ $permissions->count() }}
                                </span>
                            </div>
                            <div class="perms-grid">
                                @foreach($permissions->sortBy('sorting_order') as $perm)
                                @php
                                    $isChecked = isset($selectedPermissions) && in_array($perm->id, $selectedPermissions);
                                    $action    = ucfirst(last(explode('.', $perm->name)));
                                    $icon      = match($action) {
                                        'View'   => 'fa-eye',
                                        'Create' => 'fa-plus',
                                        'Edit'   => 'fa-pen',
                                        'Delete' => 'fa-trash',
                                        default  => 'fa-check',
                                    };
                                @endphp
                                <label class="perm-chip {{ $isChecked ? 'is-checked' : '' }}"
                                       for="perm-{{ $perm->id }}">
                                    <input type="checkbox" name="permission_ids[]"
                                           id="perm-{{ $perm->id }}" value="{{ $perm->id }}"
                                           class="perm-checkbox"
                                           data-company="system" data-module="{{ $modSlug }}"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <i class="fa {{ $isChecked ? 'fa-check-circle' : 'fa-circle' }} chip-icon"></i>
                                    {{ $action }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>

                {{-- Tab panes --}}
                @foreach($companies as $co)
                @php
                    $modules = $co->permissions->groupBy('module');
                @endphp
                <div class="co-tab-pane"
                     id="pane-co-{{ $co->id }}">

                    @if($modules->isEmpty())
                        <p class="text-muted text-center py-4">
                            <i class="fa fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                            No permissions defined for this company.
                        </p>
                    @else
                        {{-- Select-all bar --}}
                        <div class="select-all-bar">
                            <input type="checkbox"
                                   class="form-check-input co-select-all"
                                   id="selectAll-{{ $co->id }}"
                                   data-company="{{ $co->id }}">
                            <label for="selectAll-{{ $co->id }}">Select All Permissions</label>
                            <span class="total-in-co" id="co-total-{{ $co->id }}">
                                0 / {{ $co->permissions->count() }}
                            </span>
                        </div>

                        @foreach($modules as $modName => $permissions)
                        @php
                            $modSlug = Str::slug($modName);
                        @endphp
                        <div class="module-group">
                            <div class="module-header"
                                 data-module="{{ $co->id }}-{{ $modSlug }}">
                                <input type="checkbox"
                                       class="form-check-input mod-check"
                                       id="mod-{{ $co->id }}-{{ $modSlug }}"
                                       data-company="{{ $co->id }}"
                                       data-module="{{ $modSlug }}"
                                       onclick="event.stopPropagation()">
                                <label class="mod-name mb-0"
                                       for="mod-{{ $co->id }}-{{ $modSlug }}"
                                       onclick="event.stopPropagation()">
                                    {{ $modName }}
                                </label>
                                <span class="mod-counter"
                                      id="modcount-{{ $co->id }}-{{ $modSlug }}">
                                    0 / {{ $permissions->count() }}
                                </span>
                            </div>
                            <div class="perms-grid">
                                @foreach($permissions->sortBy('sorting_order') as $perm)
                                @php
                                    $isChecked = isset($selectedPermissions) && in_array($perm->id, $selectedPermissions);
                                    $action    = ucfirst(last(explode('.', $perm->name)));
                                    $icon      = match($action) {
                                        'View'   => 'fa-eye',
                                        'Create' => 'fa-plus',
                                        'Edit'   => 'fa-pen',
                                        'Delete' => 'fa-trash',
                                        'Print'  => 'fa-print',
                                        'Export' => 'fa-file-export',
                                        default  => 'fa-check',
                                    };
                                @endphp
                                <label class="perm-chip {{ $isChecked ? 'is-checked' : '' }}"
                                       for="perm-{{ $perm->id }}">
                                    <input type="checkbox"
                                           name="permission_ids[]"
                                           id="perm-{{ $perm->id }}"
                                           value="{{ $perm->id }}"
                                           class="perm-checkbox"
                                           data-company="{{ $co->id }}"
                                           data-module="{{ $modSlug }}"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <i class="fa {{ $isChecked ? 'fa-check-circle' : 'fa-circle chip-icon' }} chip-icon"></i>
                                    {{ $action }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                @endforeach

            </div>{{-- .perms-card --}}

        </div>{{-- .role-body --}}

    </div>{{-- .role-page --}}

</form>
@endsection

@push('scripts')
<script>
$(function () {

    /* ── Tab switching ── */
    $(document).on('click', '.co-tab-btn', function () {
        var target = $(this).data('target');
        $('.co-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.co-tab-pane').removeClass('active');
        $('#' + target).addClass('active');
    });

    /* ── Update all counters ── */
    function updateCounts() {
        var grandTotal = 0;

        // Per company
        $('[data-company-id]').length; // just trigger
        var companies = [];
        $('.co-tab-btn').each(function () {
            companies.push($(this).data('company'));
        });

        companies.forEach(function (cid) {
            var $perms  = $('input.perm-checkbox[data-company="' + cid + '"]');
            var count   = $perms.filter(':checked').length;
            var total   = $perms.length;
            grandTotal += count;

            // badge on tab
            var $badge = $('#badge-' + cid);
            $badge.text(count).toggleClass('active', count > 0);

            // summary panel
            var $sum = $('#summary-' + cid);
            $sum.text(count).toggleClass('zero', count === 0);

            // select-all checkbox for this company
            var $sa = $('#selectAll-' + cid);
            if (count === 0)        { $sa.prop({ checked: false, indeterminate: false }); }
            else if (count === total) { $sa.prop({ checked: true,  indeterminate: false }); }
            else                      { $sa.prop({ checked: false, indeterminate: true  }); }

            // co total counter
            $('#co-total-' + cid).text(count + ' / ' + total);
        });

        // Grand total
        var $tc = $('#totalCount');
        $tc.text(grandTotal + ' selected');

        // Per module
        $('.mod-check').each(function () {
            var cid = $(this).data('company');
            var mod = $(this).data('module');
            var $mp    = $('input.perm-checkbox[data-company="' + cid + '"][data-module="' + mod + '"]');
            var cnt    = $mp.filter(':checked').length;
            var tot    = $mp.length;
            $('#modcount-' + cid + '-' + mod).text(cnt + ' / ' + tot);
            if (cnt === 0)        { $(this).prop({ checked: false, indeterminate: false }); }
            else if (cnt === tot) { $(this).prop({ checked: true,  indeterminate: false }); }
            else                  { $(this).prop({ checked: false, indeterminate: true  }); }
        });
    }

    /* ── Permission chip toggle ── */
    $(document).on('change', '.perm-checkbox', function () {
        var checked = this.checked;
        var $label  = $(this).closest('.perm-chip');
        var $icon   = $label.find('.chip-icon');
        $label.toggleClass('is-checked', checked);
        $icon.toggleClass('fa-check-circle', checked).toggleClass('fa-circle', !checked);
        updateCounts();
    });

    /* ── Module select-all ── */
    $(document).on('change', '.mod-check', function () {
        var cid     = $(this).data('company');
        var mod     = $(this).data('module');
        var checked = this.checked;
        $('input.perm-checkbox[data-company="' + cid + '"][data-module="' + mod + '"]').each(function () {
            this.checked = checked;
            var $label = $(this).closest('.perm-chip');
            var $icon  = $label.find('.chip-icon');
            $label.toggleClass('is-checked', checked);
            $icon.toggleClass('fa-check-circle', checked).toggleClass('fa-circle', !checked);
        });
        updateCounts();
    });

    /* ── Module header click (excluding checkbox/label) ── */
    $(document).on('click', '.module-header', function (e) {
        if ($(e.target).is('input, label')) { return; }
        var $cb = $(this).find('.mod-check');
        $cb.prop('checked', !$cb.prop('checked')).trigger('change');
    });

    /* ── Company select-all ── */
    $(document).on('change', '.co-select-all', function () {
        var cid     = $(this).data('company');
        var checked = this.checked;
        $('input.perm-checkbox[data-company="' + cid + '"]').each(function () {
            this.checked = checked;
            var $label = $(this).closest('.perm-chip');
            var $icon  = $label.find('.chip-icon');
            $label.toggleClass('is-checked', checked);
            $icon.toggleClass('fa-check-circle', checked).toggleClass('fa-circle', !checked);
        });
        updateCounts();
    });

    /* ── Submit ── */
    $('#roleForm').on('submit', function () {
        $('#btnSave').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });

    /* ── Init ── */
    updateCounts();
});
</script>
@endpush
