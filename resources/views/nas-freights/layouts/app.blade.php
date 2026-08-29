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
            NAS Freights
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
            $operationsActive = request()->routeIs(
                'nas-freights.bookings.*',
                'nas-freights.customer-bills.*',
                'nas-freights.supplier-bills.*',
            );
            $freightImportActive = request()->routeIs('nas-freights.rfqs.*', 'nas-freights.freight-import-bookings.*');
            $freightExportActive = request()->routeIs('nas-freights.rfqs.*', 'nas-freights.freight-export-bookings.*');

            $dueListsActive = request()->routeIs('nas-freights.due-lists.*');
            $collectionsActive = request()->routeIs(
                'nas-freights.money-receipts.*',
                'nas-freights.supplier-payments.*',
            );
            $reportsActive = request()->routeIs('nas-freights.reports.*');
            $stakeholdersActive = request()->routeIs('nas-freights.stakeholders.*');
            $importActive = request()->routeIs('nas-freights.import.*');
            $settingsActive = request()->routeIs('nas-freights.settings.*');
        @endphp

        <div class="nav-item-group">
            <div class="nav-section">Main</div>
            <a href="{{ route('nas-freights.dashboard') }}"
                class="nav-link {{ request()->routeIs('nas-freights.dashboard') ? 'active' : '' }}">
                <i class="fa fa-tachometer-alt"></i> Dashboard
            </a>
        </div>

        {{-- Operations --}}
        <div class="nav-item-group">
            <div class="nav-section">Local Logistics</div>
            <a href="#freightOperationsMenu" class="nav-link {{ $operationsActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $operationsActive ? 'true' : 'false' }}"
                aria-controls="freightOperationsMenu">
                <i class="fa fa-truck"></i><span> Local Logistics</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $operationsActive ? 'show' : '' }}" id="freightOperationsMenu">
                <a href="{{ route('nas-freights.bookings.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.bookings.*') ? 'active' : '' }}">
                    <i class="fa fa-truck"></i> Transport Bookings
                </a>
                <a href="{{ route('nas-freights.customer-bills.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.customer-bills.*') ? 'active' : '' }}">
                    <i class="fa fa-file-invoice-dollar"></i> Customer Bills
                </a>
                <a href="{{ route('nas-freights.supplier-bills.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.supplier-bills.*') ? 'active' : '' }}">
                    <i class="fa fa-file-invoice"></i> Supplier Bills
                </a>
            </div>
        </div>

        {{-- Freight Operations --}}
        <div class="nav-item-group">
            <div class="nav-section">Freight Import</div>
            <a href="#freightFreightOpsMenu" class="nav-link {{ $freightImportActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $freightImportActive ? 'true' : 'false' }}"
                aria-controls="freightFreightOpsMenu">
                <i class="fa fa-ship"></i><span> Freight Import</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $freightImportActive ? 'show' : '' }}" id="freightFreightOpsMenu">
                <a href="{{ route('nas-freights.rfqs.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.rfqs.*') ? 'active' : '' }}">
                    <i class="fa fa-file-signature"></i> RFQs
                </a>
                <a href="{{ route('nas-freights.freight-import-bookings.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.freight-import-bookings.*') ? 'active' : '' }}">
                    <i class="fa fa-ship"></i> Freight Import Bookings
                </a>
            </div>
        </div>

        {{-- Freight Operations --}}
        <div class="nav-item-group">
            <div class="nav-section">Freight Export</div>
            <a href="#freightFreightExportMenu" class="nav-link {{ $freightExportActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $freightExportActive ? 'true' : 'false' }}"
                aria-controls="freightFreightExportMenu">
                <i class="fa fa-plane-departure"></i><span> Freight Export</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $freightExportActive ? 'show' : '' }}" id="freightFreightExportMenu">
                <a href="{{ route('nas-freights.freight-export-bookings.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.freight-export-bookings.*') ? 'active' : '' }}">
                    <i class="fa fa-ship"></i> Freight Export Bookings
                </a>
            </div>
        </div>

        {{-- Due Lists --}}
        <div class="nav-item-group">
            <div class="nav-section">Due Lists</div>
            <a href="#freightDueListMenu" class="nav-link {{ $dueListsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                aria-expanded="{{ $dueListsActive ? 'true' : 'false' }}" aria-controls="freightDueListMenu">
                <i class="fa fa-user-clock"></i><span> Due Lists</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $dueListsActive ? 'show' : '' }}" id="freightDueListMenu">
                <a href="{{ route('nas-freights.due-lists.customer') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.due-lists.customer') ? 'active' : '' }}">
                    <i class="fa fa-user-clock"></i> Customer Due
                </a>
                <a href="{{ route('nas-freights.due-lists.supplier') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.due-lists.supplier') ? 'active' : '' }}">
                    <i class="fa fa-truck-loading"></i> Supplier Due
                </a>
            </div>
        </div>

        {{-- Collections --}}
        <div class="nav-item-group">
            <div class="nav-section">Collections</div>
            <a href="#freightCollectionsMenu" class="nav-link {{ $collectionsActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $collectionsActive ? 'true' : 'false' }}"
                aria-controls="freightCollectionsMenu">
                <i class="fa fa-money-bill-wave"></i><span> Collections</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $collectionsActive ? 'show' : '' }}" id="freightCollectionsMenu">
                <a href="{{ route('nas-freights.money-receipts.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.money-receipts.*') ? 'active' : '' }}">
                    <i class="fa fa-money-bill-wave"></i> Money Receipts
                </a>
                <a href="{{ route('nas-freights.supplier-payments.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.supplier-payments.*') ? 'active' : '' }}">
                    <i class="fa fa-hand-holding-usd"></i> Supplier Payments
                </a>
            </div>
        </div>

        {{-- Reports --}}
        <div class="nav-item-group">
            <div class="nav-section">Reports</div>
            <a href="#freightReportsMenu" class="nav-link {{ $reportsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                aria-expanded="{{ $reportsActive ? 'true' : 'false' }}" aria-controls="freightReportsMenu">
                <i class="fa fa-chart-bar"></i><span> Reports</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $reportsActive ? 'show' : '' }}" id="freightReportsMenu">
                <a href="{{ route('nas-freights.reports.booking') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.reports.booking*') ? 'active' : '' }}">
                    <i class="fa fa-chart-bar"></i> Booking Report
                </a>
                <a href="{{ route('nas-freights.reports.party-bill-summary') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.reports.party-bill-summary*') ? 'active' : '' }}">
                    <i class="fa fa-file-alt"></i> Bill Summary
                </a>
                <a href="{{ route('nas-freights.reports.bill-details') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.reports.bill-details*') ? 'active' : '' }}">
                    <i class="fa fa-list-alt"></i> Bill Details
                </a>
            </div>
        </div>

        {{-- Fleet (single item — flat) --}}
        <div class="nav-item-group">
            <div class="nav-section">Fleet</div>
            <a href="{{ route('nas-freights.vehicles.index') }}"
                class="nav-link {{ request()->routeIs('nas-freights.vehicles.*') ? 'active' : '' }}">
                <i class="fa fa-truck"></i> Vehicles
            </a>
        </div>

        {{-- Stakeholders --}}
        <div class="nav-item-group">
            <div class="nav-section">Stakeholders</div>
            <a href="#freightStakeholdersMenu" class="nav-link {{ $stakeholdersActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $stakeholdersActive ? 'true' : 'false' }}"
                aria-controls="freightStakeholdersMenu">
                <i class="fa fa-users"></i><span> Stakeholders</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $stakeholdersActive ? 'show' : '' }}" id="freightStakeholdersMenu">
                <a href="{{ route('nas-freights.stakeholders.suppliers.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.stakeholders.suppliers.*') ? 'active' : '' }}">
                    <i class="fa fa-truck-loading"></i> Suppliers
                </a>
                <a href="{{ route('nas-freights.stakeholders.customers.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.stakeholders.customers.*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Customers
                </a>
            </div>
        </div>

        {{-- Import --}}
        {{-- <div class="nav-item-group">
            <div class="nav-section">Import</div>
            <a href="#freightImportMenu" class="nav-link {{ $importActive ? 'active' : '' }}" data-bs-toggle="collapse"
                aria-expanded="{{ $importActive ? 'true' : 'false' }}" aria-controls="freightImportMenu">
                <i class="fa fa-file-import"></i><span> Import</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $importActive ? 'show' : '' }}" id="freightImportMenu">
                <a href="{{ route('nas-freights.import.supplier-payments') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.supplier-payments*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Supplier Payments
                </a>
                <a href="{{ route('nas-freights.import.customer-bills') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.customer-bills*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Customer Bills
                </a>
                <a href="{{ route('nas-freights.import.bookings') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.bookings*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Bookings
                </a>
                <a href="{{ route('nas-freights.import.vehicles') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.vehicles*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Vehicles
                </a>
                <a href="{{ route('nas-freights.import.booking-updates') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.booking-updates*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Booking Updates
                </a>
                <a href="{{ route('nas-freights.import.customer-bill-summary') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.customer-bill-summary*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Bill Summary
                </a>
            </div>
        </div> --}}

        {{-- Settings --}}
        <div class="nav-item-group">
            <div class="nav-section">Settings</div>
            <a href="#freightSettingsMenu" class="nav-link {{ $settingsActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $settingsActive ? 'true' : 'false' }}"
                aria-controls="freightSettingsMenu">
                <i class="fa fa-cog"></i><span> Settings</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $settingsActive ? 'show' : '' }}" id="freightSettingsMenu">
                <a href="{{ route('nas-freights.settings.branches.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.branches.*') ? 'active' : '' }}">
                    <i class="fa fa-code-branch"></i> Branches
                </a>
                <a href="{{ route('nas-freights.settings.container-types.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.container-types.*') ? 'active' : '' }}">
                    <i class="fa fa-box"></i> Container Types
                </a>
                <a href="{{ route('nas-freights.settings.package-types.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.package-types.*') ? 'active' : '' }}">
                    <i class="fa fa-cube"></i> Package Types
                </a>
                <a href="{{ route('nas-freights.settings.overseas-agents.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.overseas-agents.*') ? 'active' : '' }}">
                    <i class="fa fa-globe"></i> Overseas Agents
                </a>
                <a href="{{ route('nas-freights.settings.shipping-carriers.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.shipping-carriers.*') ? 'active' : '' }}">
                    <i class="fa fa-ship"></i> Shipping Carriers
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var items = [
        { label: 'Dashboard',          section: 'Main',             url: '{{ route("nas-freights.dashboard") }}',                           icon: 'fa-tachometer-alt' },
        { label: 'Transport Bookings',  section: 'Local Logistics',  url: '{{ route("nas-freights.bookings.index") }}',                      icon: 'fa-truck' },
        { label: 'Customer Bills',      section: 'Local Logistics',  url: '{{ route("nas-freights.customer-bills.index") }}',                icon: 'fa-file-invoice-dollar' },
        { label: 'Supplier Bills',      section: 'Local Logistics',  url: '{{ route("nas-freights.supplier-bills.index") }}',                icon: 'fa-file-invoice' },
        { label: 'RFQs',                section: 'Freight Import',   url: '{{ route("nas-freights.rfqs.index") }}',                          icon: 'fa-file-signature' },
        { label: 'Freight Import Bookings', section: 'Freight Import', url: '{{ route("nas-freights.freight-import-bookings.index") }}',     icon: 'fa-ship' },
        { label: 'Freight Export Bookings', section: 'Freight Export', url: '{{ route("nas-freights.freight-export-bookings.index") }}',    icon: 'fa-ship' },
        { label: 'Customer Due',        section: 'Due Lists',        url: '{{ route("nas-freights.due-lists.customer") }}',                  icon: 'fa-user-clock' },
        { label: 'Supplier Due',        section: 'Due Lists',        url: '{{ route("nas-freights.due-lists.supplier") }}',                  icon: 'fa-truck-loading' },
        { label: 'Money Receipts',      section: 'Collections',      url: '{{ route("nas-freights.money-receipts.index") }}',                icon: 'fa-money-bill-wave' },
        { label: 'Supplier Payments',   section: 'Collections',      url: '{{ route("nas-freights.supplier-payments.index") }}',             icon: 'fa-hand-holding-usd' },
        { label: 'Booking Report',      section: 'Reports',          url: '{{ route("nas-freights.reports.booking") }}',                      icon: 'fa-chart-bar' },
        { label: 'Bill Summary',        section: 'Reports',          url: '{{ route("nas-freights.reports.party-bill-summary") }}',          icon: 'fa-file-alt' },
        { label: 'Bill Details',        section: 'Reports',          url: '{{ route("nas-freights.reports.bill-details") }}',                icon: 'fa-list-alt' },
        { label: 'Vehicles',            section: 'Fleet',            url: '{{ route("nas-freights.vehicles.index") }}',                      icon: 'fa-truck' },
        { label: 'Suppliers',           section: 'Stakeholders',     url: '{{ route("nas-freights.stakeholders.suppliers.index") }}',        icon: 'fa-truck-loading' },
        { label: 'Customers',           section: 'Stakeholders',     url: '{{ route("nas-freights.stakeholders.customers.index") }}',        icon: 'fa-users' },
        { label: 'Branches',            section: 'Settings',         url: '{{ route("nas-freights.settings.branches.index") }}',             icon: 'fa-code-branch' },
        { label: 'Container Types',     section: 'Settings',         url: '{{ route("nas-freights.settings.container-types.index") }}',     icon: 'fa-box' },
        { label: 'Package Types',       section: 'Settings',         url: '{{ route("nas-freights.settings.package-types.index") }}',       icon: 'fa-cube' },
        { label: 'Overseas Agents',     section: 'Settings',         url: '{{ route("nas-freights.settings.overseas-agents.index") }}',     icon: 'fa-globe' },
        { label: 'Shipping Carriers',   section: 'Settings',         url: '{{ route("nas-freights.settings.shipping-carriers.index") }}',   icon: 'fa-ship' },
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
        collapsedBtn.setAttribute('aria-expanded', 'true');
        var rect = collapsedBtn.getBoundingClientRect();
        flyout.style.top = (rect.top + rect.height / 2 - 20) + 'px';
        document.getElementById('sidebarFlyoutSearchInput').focus();
    }

    function closeFlyout() {
        flyout.style.display = 'none';
        collapsedBtn.setAttribute('aria-expanded', 'false');
        var fi = document.getElementById('sidebarFlyoutSearchInput');
        fi.value = '';
        document.getElementById('sidebarFlyoutSearchClear').style.display = 'none';
        document.getElementById('sidebarFlyoutSearchResultsList').innerHTML = '';
        document.getElementById('sidebarFlyoutSearchEmpty').style.display = 'none';
    }

    collapsedBtn.addEventListener('click', function () {
        var open = flyout.style.display === 'block';
        if (open) { closeFlyout(); } else { openFlyout(); }
    });

    document.addEventListener('click', function (e) {
        if (flyout.style.display === 'block' &&
            !flyout.contains(e.target) && !collapsedBtn.contains(e.target)) {
            closeFlyout();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && flyout.style.display === 'block') { closeFlyout(); }
    });
})();
</script>
@endpush
