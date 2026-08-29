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
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        NAS Trading
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

    @php
        $lcOpsActive       = request()->routeIs('nas-trading.lcs.*', 'nas-trading.shipments.*');
        $billingActive     = request()->routeIs('nas-trading.customer-bills.*', 'nas-trading.lc-bill-statements.*', 'nas-trading.due-lists.*', 'nas-trading.money-receipts.*');
        $mastersActive     = request()->routeIs('nas-trading.customers.*', 'nas-trading.suppliers.*', 'nas-trading.items.*', 'nas-trading.expense-heads.*', 'nas-trading.banks.*', 'nas-trading.importers.*', 'nas-trading.ports.*', 'nas-trading.cnf-agents.*', 'nas-trading.transport-companies.*', 'nas-trading.psi-companies.*');
        $settingsActive    = request()->routeIs('nas-trading.settings.*');
        $dataActive        = request()->routeIs('nas-trading.import.*');
    @endphp

    <div class="nav-item-group">
        <div class="nav-section">Main</div>
        <a href="{{ route('nas-trading.dashboard') }}"
           class="nav-link {{ request()->routeIs('nas-trading.dashboard') ? 'active' : '' }}">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a>
    </div>

    {{-- LC Operations --}}
    <div class="nav-item-group">
        <div class="nav-section">LC Operations</div>
        <a href="#tradingLcOpsMenu"
           class="nav-link {{ $lcOpsActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $lcOpsActive ? 'true' : 'false' }}" aria-controls="tradingLcOpsMenu">
            <i class="fa fa-file-contract"></i><span> LC Operations</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $lcOpsActive ? 'show' : '' }}" id="tradingLcOpsMenu">
            <a href="{{ route('nas-trading.lcs.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.lcs.*') ? 'active' : '' }}">
                <i class="fa fa-file-contract"></i> LC Entry
            </a>
            <a href="{{ route('nas-trading.shipments.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.shipments.*') ? 'active' : '' }}">
                <i class="fa fa-ship"></i> Shipment Entry
            </a>
        </div>
    </div>

    {{-- Billing --}}
    <div class="nav-item-group">
        <div class="nav-section">Billing</div>
        <a href="#tradingBillingMenu"
           class="nav-link {{ $billingActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $billingActive ? 'true' : 'false' }}" aria-controls="tradingBillingMenu">
            <i class="fa fa-file-invoice-dollar"></i><span> Billing</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $billingActive ? 'show' : '' }}" id="tradingBillingMenu">
            <a href="{{ route('nas-trading.customer-bills.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.customer-bills.*') ? 'active' : '' }}">
                <i class="fa fa-file-invoice-dollar"></i> Customer Bills
            </a>
            <a href="{{ route('nas-trading.lc-bill-statements.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.lc-bill-statements.*') ? 'active' : '' }}">
                <i class="fa fa-file-alt"></i> LC Bill Statement
            </a>
            <a href="{{ route('nas-trading.due-lists.customer') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.due-lists.customer') ? 'active' : '' }}">
                <i class="fa fa-user-clock"></i> Customer Due
            </a>
            <a href="{{ route('nas-trading.money-receipts.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.money-receipts.*') ? 'active' : '' }}">
                <i class="fa fa-money-bill-wave"></i> Money Receipts
            </a>
        </div>
    </div>

    {{-- Delivery (single item — flat) --}}
    <div class="nav-item-group">
        <div class="nav-section">Delivery</div>
        <a href="{{ route('nas-trading.deliveries.index') }}"
           class="nav-link {{ request()->routeIs('nas-trading.deliveries.*') ? 'active' : '' }}">
            <i class="fa fa-truck"></i> Deliveries
        </a>
    </div>

    {{-- Masters --}}
    <div class="nav-item-group">
        <div class="nav-section">Masters</div>
        <a href="#tradingMastersMenu"
           class="nav-link {{ $mastersActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $mastersActive ? 'true' : 'false' }}" aria-controls="tradingMastersMenu">
            <i class="fa fa-database"></i><span> Masters</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $mastersActive ? 'show' : '' }}" id="tradingMastersMenu">
            <a href="{{ route('nas-trading.customers.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.customers.*') ? 'active' : '' }}">
                <i class="fa fa-users"></i> Customers
            </a>
            <a href="{{ route('nas-trading.suppliers.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.suppliers.*') ? 'active' : '' }}">
                <i class="fa fa-industry"></i> Suppliers
            </a>
            <a href="{{ route('nas-trading.items.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.items.*') ? 'active' : '' }}">
                <i class="fa fa-boxes"></i> Items
            </a>
            <a href="{{ route('nas-trading.expense-heads.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.expense-heads.*') ? 'active' : '' }}">
                <i class="fa fa-tags"></i> Expense Heads
            </a>
            <a href="{{ route('nas-trading.banks.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.banks.*') ? 'active' : '' }}">
                <i class="fa fa-university"></i> Banks
            </a>
            <a href="{{ route('nas-trading.importers.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.importers.*') ? 'active' : '' }}">
                <i class="fa fa-building"></i> Importers
            </a>
            <a href="{{ route('nas-trading.ports.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.ports.*') ? 'active' : '' }}">
                <i class="fa fa-anchor"></i> Ports
            </a>
            <a href="{{ route('nas-trading.cnf-agents.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.cnf-agents.*') ? 'active' : '' }}">
                <i class="fa fa-handshake"></i> CNF Agents
            </a>
            <a href="{{ route('nas-trading.transport-companies.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.transport-companies.*') ? 'active' : '' }}">
                <i class="fa fa-truck-moving"></i> Transport Cos.
            </a>
            <a href="{{ route('nas-trading.psi-companies.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.psi-companies.*') ? 'active' : '' }}">
                <i class="fa fa-search"></i> PSI Companies
            </a>
        </div>
    </div>

    {{-- Settings --}}
    <div class="nav-item-group">
        <div class="nav-section">Settings</div>
        <a href="#tradingSettingsMenu"
           class="nav-link {{ $settingsActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $settingsActive ? 'true' : 'false' }}" aria-controls="tradingSettingsMenu">
            <i class="fa fa-cog"></i><span> Settings</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $settingsActive ? 'show' : '' }}" id="tradingSettingsMenu">
            <a href="{{ route('nas-trading.settings.branches.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.settings.branches.*') ? 'active' : '' }}">
                <i class="fa fa-code-branch"></i> Branches
            </a>
        </div>
    </div>

    {{-- Data (single item — flat) --}}
    {{-- <div class="nav-item-group">
        <div class="nav-section">Data</div>
        <a href="{{ route('nas-trading.import.chevron.preview') }}"
           class="nav-link {{ $dataActive ? 'active' : '' }}">
            <i class="fa fa-file-import"></i> Import Data
        </a>
    </div> --}}
</div>
@endsection

@push('scripts')
<script>
(function () {
    var items = [
        { label: 'Dashboard',          section: 'Main',          url: '{{ route("nas-trading.dashboard") }}', icon: 'fa-tachometer-alt' },
        { label: 'LC Entry',           section: 'LC Operations', url: '{{ route("nas-trading.lcs.index") }}', icon: 'fa-file-contract' },
        { label: 'Shipment Entry',     section: 'LC Operations', url: '{{ route("nas-trading.shipments.index") }}', icon: 'fa-ship' },
        { label: 'Customer Bills',     section: 'Billing',       url: '{{ route("nas-trading.customer-bills.index") }}', icon: 'fa-file-invoice-dollar' },
        { label: 'LC Bill Statement',  section: 'Billing',       url: '{{ route("nas-trading.lc-bill-statements.index") }}', icon: 'fa-file-alt' },
        { label: 'Customer Due',       section: 'Billing',       url: '{{ route("nas-trading.due-lists.customer") }}', icon: 'fa-user-clock' },
        { label: 'Money Receipts',     section: 'Billing',       url: '{{ route("nas-trading.money-receipts.index") }}', icon: 'fa-money-bill-wave' },
        { label: 'Deliveries',         section: 'Delivery',      url: '{{ route("nas-trading.deliveries.index") }}', icon: 'fa-truck' },
        { label: 'Customers',          section: 'Masters',       url: '{{ route("nas-trading.customers.index") }}', icon: 'fa-users' },
        { label: 'Suppliers',          section: 'Masters',       url: '{{ route("nas-trading.suppliers.index") }}', icon: 'fa-industry' },
        { label: 'Items',              section: 'Masters',       url: '{{ route("nas-trading.items.index") }}', icon: 'fa-boxes' },
        { label: 'Expense Heads',      section: 'Masters',       url: '{{ route("nas-trading.expense-heads.index") }}', icon: 'fa-tags' },
        { label: 'Banks',              section: 'Masters',       url: '{{ route("nas-trading.banks.index") }}', icon: 'fa-university' },
        { label: 'Importers',          section: 'Masters',       url: '{{ route("nas-trading.importers.index") }}', icon: 'fa-building' },
        { label: 'Ports',              section: 'Masters',       url: '{{ route("nas-trading.ports.index") }}', icon: 'fa-anchor' },
        { label: 'CNF Agents',         section: 'Masters',       url: '{{ route("nas-trading.cnf-agents.index") }}', icon: 'fa-handshake' },
        { label: 'Transport Cos.',     section: 'Masters',       url: '{{ route("nas-trading.transport-companies.index") }}', icon: 'fa-truck-moving' },
        { label: 'PSI Companies',      section: 'Masters',       url: '{{ route("nas-trading.psi-companies.index") }}', icon: 'fa-search' },
        { label: 'Branches',           section: 'Settings',      url: '{{ route("nas-trading.settings.branches.index") }}', icon: 'fa-code-branch' },
        // { label: 'Import Data',        section: 'Data',          url: '{{ route("nas-trading.import.chevron.preview") }}', icon: 'fa-file-import' },
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
