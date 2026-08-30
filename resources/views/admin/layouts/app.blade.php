@extends('layouts.app')

@push('styles')
<style>
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

.sidebar-search-collapsed-btn {
    display: none; width: 100%; border: none; background: none; color: #a8c8cc;
    font-size: .8rem; padding: 8px 0; cursor: pointer; text-align: center;
    border-radius: 6px; transition: background .12s, color .12s, outline-color .12s;
}
.sidebar-search-collapsed-btn:hover { background: rgba(255,255,255,.08); color: #e0f0f0; }
.sidebar-search-collapsed-btn:focus-visible {
    outline: 2px solid #14b8a6; outline-offset: -2px; color: #fff;
}

.sidebar-search-flyout {
    display: none; position: fixed; left: 46px; background: var(--sidebar-bg);
    min-width: 230px; z-index: 1045; border-radius: 0 6px 6px 6px;
    box-shadow: 4px 4px 16px rgba(0,0,0,.38); padding: 8px; animation: flyout-in .15s ease-out;
}
.sidebar-search-flyout .sidebar-search-input { min-height: 36px; background: rgba(255,255,255,.10); }
.sidebar-search-flyout .sidebar-search-results { max-height: calc(100vh - 190px); margin-top: 6px; }
.sidebar-search-flyout .sidebar-search-result-item { padding: 7px 10px; }

@media (min-width: 769px) {
    body.sidebar-collapsed .sidebar-search-wrap,
    body.sidebar-collapsed .sidebar .sidebar-search-results { display: none !important; }
    body.sidebar-collapsed .sidebar-search-collapsed-btn { display: block; }
}
</style>
@endpush

@section('sidebar')
    <div class="pt-2 pb-4">
        <div class="px-3 py-2 mb-1"
            style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
            Admin Panel
        </div>

        {{-- Menu Search --}}
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

        <button id="sidebarSearchCollapsedBtn" class="sidebar-search-collapsed-btn"
                aria-label="Search menu" aria-expanded="false" title="Search menu">
            <i class="fa fa-search"></i>
        </button>

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

    var navGroups = document.querySelectorAll('.sidebar .nav-item-group');
    var collapsedBtn = document.getElementById('sidebarSearchCollapsedBtn');

    function esc(str) { return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function highlight(text, q) {
        if (!q) { return esc(text); }
        var re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        return esc(text).replace(re, function (m) { return '<mark>' + m + '</mark>'; });
    }

    function renderResults(q) {
        var raw = q.trim();
        if (!raw) { return null; }
        var lower    = raw.toLowerCase();
        var filtered = items.filter(function (item) {
            return item.label.toLowerCase().includes(lower) || item.section.toLowerCase().includes(lower);
        });
        var html;
        if (filtered.length) {
            html = filtered.map(function (item) {
                return '<a href="' + item.url + '" class="sidebar-search-result-item">' +
                    '<i class="fa ' + item.icon + '"></i>' +
                    '<span class="sidebar-search-result-label">' + highlight(item.label, raw) + '</span>' +
                    '<span class="sidebar-search-result-section">' + esc(item.section) + '</span>' +
                    '</a>';
            }).join('');
        }
        return { html: html || '', empty: html === undefined };
    }

    function showNormal(s, list, empty, wrap, hideNav, clearBtn) {
        if (hideNav) { navGroups.forEach(function (g) { g.style.display = ''; }); }
        if (wrap) { wrap.style.display = 'none'; }
        list.innerHTML = '';
        empty.style.display = 'none';
        if (clearBtn) { clearBtn.style.display = 'none'; }
    }

    function initSearch(opts) {
        var input       = opts.input;
        var clearBtn    = opts.clearBtn;
        var resultsList = opts.resultsList;
        var emptyEl     = opts.emptyEl;
        var resultsWrap = opts.resultsWrap;   // inline wrapper (may be null for flyout)
        var hideNav     = !!opts.hideNav;     // only inline search hides nav groups
        var focusIdx    = -1;

        clearBtn.addEventListener('click', function () {
            input.value = '';
            input.focus();
            showNormal(input, resultsList, emptyEl, resultsWrap, hideNav, clearBtn);
        });

        input.addEventListener('input', function () {
            clearBtn.style.display = this.value ? '' : 'none';
            var res = renderResults(this.value);
            if (!res) { showNormal(this, resultsList, emptyEl, resultsWrap, hideNav, clearBtn); return; }
            if (hideNav) { navGroups.forEach(function (g) { g.style.display = 'none'; }); }
            if (resultsWrap) { resultsWrap.style.display = 'block'; }
            resultsList.innerHTML = res.html;
            emptyEl.style.display = res.empty ? '' : 'none';
            focusIdx = -1;
        });

        input.addEventListener('keydown', function (e) {
            var els = resultsList.querySelectorAll('.sidebar-search-result-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusIdx = Math.min(focusIdx + 1, els.length - 1);
                applyFocus(els);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusIdx = Math.max(focusIdx - 1, -1);
                applyFocus(els);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (focusIdx >= 0 && els[focusIdx]) { els[focusIdx].click(); }
            } else if (e.key === 'Escape') {
                if (resultsWrap) { showNormal(this, resultsList, emptyEl, resultsWrap, hideNav, clearBtn); }
                else {
                    input.value = '';
                    clearBtn.style.display = 'none';
                    resultsList.innerHTML = '';
                    emptyEl.style.display = 'none';
                    closeFlyout();
                }
                input.blur();
            }
        });

        function applyFocus(els) {
            els.forEach(function (el, i) { el.classList.toggle('kb-focus', i === focusIdx); });
            if (els[focusIdx]) { els[focusIdx].scrollIntoView({ block: 'nearest' }); }
        }
    }

    initSearch({
        input:       document.getElementById('sidebarMenuSearch'),
        clearBtn:    document.getElementById('sidebarSearchClear'),
        resultsWrap: document.getElementById('sidebarSearchResults'),
        resultsList: document.getElementById('sidebarSearchResultsList'),
        emptyEl:     document.getElementById('sidebarSearchEmpty'),
        hideNav:     true,
    });

    // Build flyout append to BODY so it escapes the clipped sidebar (overflow-x:hidden)
    var flyout     = document.createElement('div');
    flyout.className = 'sidebar-search-flyout';
    flyout.setAttribute('role', 'dialog');
    flyout.setAttribute('aria-label', 'Search menu');
    flyout.innerHTML =
        '<div class="sidebar-search-input-wrap">' +
            '<i class="fa fa-search sidebar-search-icon"></i>' +
            '<input type="text" id="sidebarFlyoutSearchInput" class="sidebar-search-input" ' +
                'placeholder="Search menu..." autocomplete="off" spellcheck="false" aria-label="Search menu">' +
            '<button id="sidebarFlyoutSearchClear" class="sidebar-search-clear" style="display:none" aria-label="Clear search">' +
                '<i class="fa fa-times"></i>' +
            '</button>' +
        '</div>' +
        '<div id="sidebarFlyoutSearchResultsList" class="sidebar-search-results"></div>' +
        '<div id="sidebarFlyoutSearchEmpty" class="sidebar-search-empty" style="display:none">' +
            '<i class="fa fa-search-minus"></i>No results' +
        '</div>';
    document.body.appendChild(flyout);

    initSearch({
        input:       document.getElementById('sidebarFlyoutSearchInput'),
        clearBtn:    document.getElementById('sidebarFlyoutSearchClear'),
        resultsWrap: null,
        resultsList: document.getElementById('sidebarFlyoutSearchResultsList'),
        emptyEl:     document.getElementById('sidebarFlyoutSearchEmpty'),
        hideNav:     false,
    });

    function openFlyout() {
        flyout.style.display = 'block';
        if (collapsedBtn) collapsedBtn.setAttribute('aria-expanded', 'true');
        var rect = collapsedBtn ? collapsedBtn.getBoundingClientRect() : { top: 40, height: 20 };
        flyout.style.top = (rect.top + rect.height / 2 - 20) + 'px';
        document.getElementById('sidebarFlyoutSearchInput').focus();
    }

    function closeFlyout() {
        flyout.style.display = 'none';
        if (collapsedBtn) collapsedBtn.setAttribute('aria-expanded', 'false');
        var fi = document.getElementById('sidebarFlyoutSearchInput');
        fi.value = '';
        document.getElementById('sidebarFlyoutSearchClear').style.display = 'none';
        document.getElementById('sidebarFlyoutSearchResultsList').innerHTML = '';
        document.getElementById('sidebarFlyoutSearchEmpty').style.display = 'none';
    }

    if (collapsedBtn) {
        collapsedBtn.addEventListener('click', function () {
            var open = flyout.style.display === 'block';
            if (open) { closeFlyout(); } else { openFlyout(); }
        });
    }

    document.addEventListener('click', function (e) {
        if (flyout.style.display === 'block' &&
            !flyout.contains(e.target) && (!collapsedBtn || !collapsedBtn.contains(e.target))) {
            closeFlyout();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && flyout.style.display === 'block') { closeFlyout(); }
    });
})();
</script>
@endpush
