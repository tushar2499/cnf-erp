@extends('layouts.app')

@push('styles')
<style>
/* ── Expanded sidebar search ── */
.sidebar-search-wrap { padding: 4px 8px 6px; }
.sidebar-search-input-wrap { position: relative; display: flex; align-items: center; }
.sidebar-search-icon { position: absolute; left: 10px; font-size: .68rem; color: #a8c8cc; pointer-events: none; z-index: 1; }
.sidebar-search-input {
    width: 100%; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.30);
    border-radius: 6px; color: #e0f0f0; font-size: .77rem; padding: 7px 26px 7px 28px;
    outline: none; transition: border-color .15s, background .15s; min-height: 34px;
}
.sidebar-search-input::placeholder { color: #a8c8cc; }
.sidebar-search-input:focus { border-color: #14b8a6; background: rgba(20,184,166,.14); color: #fff; outline: 2px solid rgba(20,184,166,.35); outline-offset: 0; }
.sidebar-search-clear {
    position: absolute; right: 7px; background: none; border: none; color: #6b7a99;
    cursor: pointer; padding: 2px 4px; font-size: .62rem; line-height: 1;
}
.sidebar-search-clear:hover { color: #b2d8d8; }
.sidebar-search-results { overflow-y: auto; max-height: calc(100vh - 160px); }
.sidebar-search-result-item {
    display: flex; align-items: center; gap: 8px; padding: 7px 12px;
    color: #b2d8d8; font-size: .78rem; cursor: pointer; text-decoration: none;
    border-left: 3px solid transparent; transition: background .12s;
}
.sidebar-search-result-item:hover,
.sidebar-search-result-item.kb-focus { background: #1a3d3d; color: #fff; }
.sidebar-search-result-item i { width: 14px; text-align: center; font-size: .68rem; opacity: .8; flex-shrink: 0; }
.sidebar-search-result-label { flex: 1; min-width: 0; }
.sidebar-search-result-label mark { background: rgba(20,184,166,.28); color: #14b8a6; padding: 0 2px; border-radius: 2px; }
.sidebar-search-result-section { font-size: .6rem; color: #6b7a99; white-space: nowrap; }
.sidebar-search-empty { padding: 16px 12px; color: #6b7a99; font-size: .74rem; text-align: center; }
.sidebar-search-empty i { display: block; font-size: 1.1rem; opacity: .35; margin-bottom: 4px; }

/* ── Collapsed mode: hide expanded search, show icon btn ── */
.sidebar-search-collapsed-btn { display: none !important; }
@media (min-width: 769px) {
    body.sidebar-collapsed .sidebar-search-wrap,
    body.sidebar-collapsed #sidebarSearchResults { display: none !important; }
    body.sidebar-collapsed .sidebar-search-collapsed-btn { display: flex !important; }
}

/* ── Collapsed search flyout (appears to the right of 46px sidebar) ── */
.sidebar-search-flyout {
    display: none;
    position: fixed;
    left: 46px;
    background: var(--sidebar-bg);
    width: 230px;
    z-index: 1046;
    border-radius: 0 6px 6px 6px;
    box-shadow: 4px 4px 16px rgba(0,0,0,.45);
    padding: 8px 8px 4px;
    animation: flyout-in .15s ease-out;
}
.sidebar-search-flyout .flyout-search-wrap { position: relative; display: flex; align-items: center; }
.sidebar-search-flyout .flyout-search-icon { position: absolute; left: 9px; font-size: .68rem; color: #a8c8cc; pointer-events: none; }
.sidebar-search-flyout .flyout-search-input {
    width: 100%; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.30);
    border-radius: 6px; color: #e0f0f0; font-size: .77rem; padding: 7px 10px 7px 28px;
    outline: none; transition: border-color .15s, background .15s; min-height: 34px;
}
.sidebar-search-flyout .flyout-search-input::placeholder { color: #a8c8cc; }
.sidebar-search-flyout .flyout-search-input:focus { border-color: #14b8a6; background: rgba(20,184,166,.14); color: #fff; }
.sidebar-search-flyout .flyout-results { overflow-y: auto; max-height: 260px; margin-top: 4px; }
.sidebar-search-flyout .flyout-result-item {
    display: flex; align-items: center; gap: 8px; padding: 7px 10px;
    color: #b2d8d8; font-size: .78rem; text-decoration: none;
    border-left: 3px solid transparent; transition: background .12s; white-space: nowrap;
}
.sidebar-search-flyout .flyout-result-item:hover,
.sidebar-search-flyout .flyout-result-item.kb-focus { background: #1a3d3d; color: #fff; }
.sidebar-search-flyout .flyout-result-item i { width: 14px; text-align: center; font-size: .68rem; opacity: .8; flex-shrink: 0; }
.sidebar-search-flyout .flyout-result-label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
.sidebar-search-flyout .flyout-result-label mark { background: rgba(20,184,166,.28); color: #14b8a6; padding: 0 2px; border-radius: 2px; }
.sidebar-search-flyout .flyout-result-section { font-size: .6rem; color: #6b7a99; }
.sidebar-search-flyout .flyout-empty { padding: 12px 10px; color: #6b7a99; font-size: .74rem; text-align: center; }
</style>
@endpush

@section('sidebar')
    <div class="pt-2 pb-4">
        <div class="px-3 py-2 mb-1"
            style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
            Admin Panel
        </div>

        {{-- Collapsed-mode: icon-only search button (shows flyout popover) --}}
        <button id="sidebarSearchCollapsedBtn" class="sidebar-search-collapsed-btn nav-link"
                style="background:none;border:none;width:100%;justify-content:center;cursor:pointer;"
                aria-label="Search menu">
            <i class="fa fa-search"></i>
        </button>

        {{-- Expanded-mode: inline search box --}}
        <div class="sidebar-search-wrap">
            <div class="sidebar-search-input-wrap">
                <i class="fa fa-search sidebar-search-icon"></i>
                <input type="text" id="sidebarMenuSearch" class="sidebar-search-input"
                       placeholder="Search menu..." autocomplete="off" spellcheck="false" aria-label="Search menu">
                <button id="sidebarSearchClear" class="sidebar-search-clear" style="display:none" aria-label="Clear search">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <div id="sidebarSearchResults" style="display:none">
            <div id="sidebarSearchResultsList" class="sidebar-search-results"></div>
            <div id="sidebarSearchEmpty" class="sidebar-search-empty" style="display:none">
                <i class="fa fa-search-minus"></i>No results
            </div>
        </div>

        <div class="nav-item-group">
            <div class="nav-section">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
        </div>

        @if (auth()->user()->hasPermission('admin.companies.list') ||
                auth()->user()->hasPermission('admin.users.list') ||
                auth()->user()->hasPermission('admin.roles.list') ||
                auth()->user()->hasPermission('admin.employees.list') ||
                auth()->user()->hasPermission('admin.designations.list'))
            <div class="nav-item-group">
                <div class="nav-section">Settings</div>

                @if (auth()->user()->hasPermission('admin.users.list'))
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fa fa-users"></i> Users
                    </a>
                @endif

                @if (auth()->user()->hasPermission('admin.roles.list'))
                    <a href="{{ route('admin.roles.index') }}"
                        class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="fa fa-user-shield"></i> Roles
                    </a>
                @endif

                @if (auth()->user()->hasPermission('admin.companies.list'))
                    <a href="{{ route('admin.companies.index') }}"
                        class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                        <i class="fa fa-building"></i> Companies
                    </a>
                @endif

                @if (auth()->user()->hasPermission('admin.employees.list'))
                    <a href="{{ route('admin.employees.index') }}"
                        class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <i class="fa fa-user-tie"></i> Employees
                    </a>
                @endif

                @if (auth()->user()->hasPermission('admin.designations.list'))
                    <a href="{{ route('admin.designations.index') }}"
                        class="nav-link {{ request()->routeIs('admin.designations.*') ? 'active' : '' }}">
                        <i class="fa fa-id-badge"></i> Designations
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var items = [
        { label: 'Dashboard', section: 'Main', url: '{{ route("admin.dashboard") }}', icon: 'fa-gauge' },
        @if (auth()->user()->hasPermission('admin.users.list'))
        { label: 'Users', section: 'Settings', url: '{{ route("admin.users.index") }}', icon: 'fa-users' },
        @endif
        @if (auth()->user()->hasPermission('admin.roles.list'))
        { label: 'Roles', section: 'Settings', url: '{{ route("admin.roles.index") }}', icon: 'fa-user-shield' },
        @endif
        @if (auth()->user()->hasPermission('admin.companies.list'))
        { label: 'Companies', section: 'Settings', url: '{{ route("admin.companies.index") }}', icon: 'fa-building' },
        @endif
        @if (auth()->user()->hasPermission('admin.employees.list'))
        { label: 'Employees', section: 'Settings', url: '{{ route("admin.employees.index") }}', icon: 'fa-user-tie' },
        @endif
        @if (auth()->user()->hasPermission('admin.designations.list'))
        { label: 'Designations', section: 'Settings', url: '{{ route("admin.designations.index") }}', icon: 'fa-id-badge' },
        @endif
    ];

    function esc(str) { return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

    function highlight(text, q) {
        if (!q) { return esc(text); }
        var re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        return esc(text).replace(re, function (m) { return '<mark>' + m + '</mark>'; });
    }

    /* ── Expanded sidebar search ── */
    var input       = document.getElementById('sidebarMenuSearch');
    var clearBtn    = document.getElementById('sidebarSearchClear');
    var resultsWrap = document.getElementById('sidebarSearchResults');
    var resultsList = document.getElementById('sidebarSearchResultsList');
    var emptyEl     = document.getElementById('sidebarSearchEmpty');
    var navGroups   = document.querySelectorAll('.sidebar .nav-item-group');
    var focusIdx    = -1;

    function showNormal() {
        navGroups.forEach(function (g) { g.style.display = ''; });
        resultsWrap.style.display = 'none';
        clearBtn.style.display    = 'none';
        focusIdx = -1;
    }

    function renderResults(q) {
        var raw = q.trim();
        if (!raw) { showNormal(); return; }
        var lower    = raw.toLowerCase();
        var filtered = items.filter(function (item) {
            return item.label.toLowerCase().includes(lower) || item.section.toLowerCase().includes(lower);
        });
        navGroups.forEach(function (g) { g.style.display = 'none'; });
        resultsWrap.style.display = 'block';
        focusIdx = -1;

        if (!filtered.length) {
            resultsList.innerHTML = '';
            emptyEl.style.display = '';
            return;
        }
        emptyEl.style.display = 'none';
        resultsList.innerHTML = filtered.map(function (item, i) {
            return '<a href="' + item.url + '" class="sidebar-search-result-item" data-idx="' + i + '">' +
                '<i class="fa ' + item.icon + '"></i>' +
                '<span class="sidebar-search-result-label">' + highlight(item.label, raw) + '</span>' +
                '<span class="sidebar-search-result-section">' + esc(item.section) + '</span>' +
                '</a>';
        }).join('');
    }

    function getResultItems() { return resultsList.querySelectorAll('.sidebar-search-result-item'); }

    function applyFocus(els) {
        els.forEach(function (el, i) { el.classList.toggle('kb-focus', i === focusIdx); });
        if (els[focusIdx]) { els[focusIdx].scrollIntoView({ block: 'nearest' }); }
    }

    input.addEventListener('input', function () {
        clearBtn.style.display = this.value ? '' : 'none';
        renderResults(this.value);
    });

    clearBtn.addEventListener('click', function () {
        input.value = '';
        input.focus();
        showNormal();
    });

    input.addEventListener('keydown', function (e) {
        var els = getResultItems();
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            focusIdx = Math.min(focusIdx + 1, els.length - 1);
            applyFocus(els);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            focusIdx = Math.max(focusIdx - 1, -1);
            applyFocus(els);
        } else if (e.key === 'Enter') {
            if (focusIdx >= 0 && els[focusIdx]) { els[focusIdx].click(); }
        } else if (e.key === 'Escape') {
            input.value = '';
            showNormal();
            input.blur();
        }
    });

    /* ── Collapsed sidebar: flyout search popover ── */
    var collapsedBtn = document.getElementById('sidebarSearchCollapsedBtn');

    // Build flyout DOM once
    var flyout = document.createElement('div');
    flyout.className = 'sidebar-search-flyout';
    flyout.innerHTML =
        '<div class="flyout-search-wrap">' +
            '<i class="fa fa-search flyout-search-icon"></i>' +
            '<input type="text" class="flyout-search-input" placeholder="Search menu..." autocomplete="off" spellcheck="false">' +
        '</div>' +
        '<div class="flyout-results"></div>';
    document.body.appendChild(flyout);

    var flyoutInput   = flyout.querySelector('.flyout-search-input');
    var flyoutResults = flyout.querySelector('.flyout-results');
    var flyoutFocusIdx = -1;

    function flyoutRender(q) {
        var raw = q.trim();
        flyoutFocusIdx = -1;
        if (!raw) { flyoutResults.innerHTML = ''; return; }
        var lower    = raw.toLowerCase();
        var filtered = items.filter(function (item) {
            return item.label.toLowerCase().includes(lower) || item.section.toLowerCase().includes(lower);
        });
        if (!filtered.length) {
            flyoutResults.innerHTML = '<div class="flyout-empty">No results</div>';
            return;
        }
        flyoutResults.innerHTML = filtered.map(function (item, i) {
            return '<a href="' + item.url + '" class="flyout-result-item" data-idx="' + i + '">' +
                '<i class="fa ' + item.icon + '"></i>' +
                '<span class="flyout-result-label">' + highlight(item.label, raw) + '</span>' +
                '<span class="flyout-result-section">' + esc(item.section) + '</span>' +
                '</a>';
        }).join('');
    }

    function flyoutGetItems() { return flyoutResults.querySelectorAll('.flyout-result-item'); }

    function flyoutApplyFocus(els) {
        els.forEach(function (el, i) { el.classList.toggle('kb-focus', i === flyoutFocusIdx); });
        if (els[flyoutFocusIdx]) { els[flyoutFocusIdx].scrollIntoView({ block: 'nearest' }); }
    }

    function openFlyout() {
        var rect = collapsedBtn.getBoundingClientRect();
        flyout.style.top = rect.top + 'px';
        flyoutInput.value = '';
        flyoutResults.innerHTML = '';
        flyout.style.display = 'block';
        setTimeout(function () { flyoutInput.focus(); }, 30);
    }

    function closeFlyout() {
        flyout.style.display = 'none';
        flyoutInput.value = '';
        flyoutResults.innerHTML = '';
        flyoutFocusIdx = -1;
    }

    if (collapsedBtn) {
        collapsedBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (flyout.style.display === 'block') { closeFlyout(); return; }
            openFlyout();
        });
    }

    flyoutInput.addEventListener('input', function () { flyoutRender(this.value); });

    flyoutInput.addEventListener('keydown', function (e) {
        var els = flyoutGetItems();
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            flyoutFocusIdx = Math.min(flyoutFocusIdx + 1, els.length - 1);
            flyoutApplyFocus(els);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            flyoutFocusIdx = Math.max(flyoutFocusIdx - 1, -1);
            flyoutApplyFocus(els);
        } else if (e.key === 'Enter') {
            if (flyoutFocusIdx >= 0 && els[flyoutFocusIdx]) { els[flyoutFocusIdx].click(); }
        } else if (e.key === 'Escape') {
            closeFlyout();
        }
    });

    flyout.addEventListener('click', function (e) { e.stopPropagation(); });

    document.addEventListener('click', function () { closeFlyout(); });
})();
</script>
@endpush
