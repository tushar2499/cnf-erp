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
body.sidebar-collapsed .sidebar-search-wrap,
body.sidebar-collapsed .sidebar-search-results { display: none !important; }
</style>
@endpush

@section('sidebar')
<div class="pt-2 pb-4">
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        Chevron Lines
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

    @php
        $user               = auth()->user();
        $cnfOpsActive       = request()->routeIs('chevron.cnf.jobs.*', 'chevron.cnf.job-expenses.*', 'chevron.cnf.bills.*', 'chevron.cnf.money-receipts.*');
        $reportsActive      = request()->routeIs('chevron.reports.*');
        $stakeholdersActive = request()->routeIs('chevron.stakeholders.*');
        $settingsActive     = request()->routeIs('chevron.settings.*');

        $canSeeJob         = $user->hasPermission('cnf.job.list');
        $canSeeJobExpense  = $user->hasPermission('cnf.job-expense.list');
        $canSeeBill        = $user->hasPermission('cnf.bill.list');
        $canSeeReceipt     = $user->hasPermission('cnf.money-receipt.list');
        $canSeeCnfOps      = $canSeeJob || $canSeeJobExpense || $canSeeBill || $canSeeReceipt;

        $canSeeCustomer = $user->hasPermission('cnf.customer.list');

        $canSeeService          = $user->hasPermission('cnf.service.list');
        $canSeeJobType          = $user->hasPermission('cnf.job-type.list');
        $canSeePort             = $user->hasPermission('cnf.port.list');
        $canSeeExpenseCategory  = $user->hasPermission('cnf.expense-category.list');
        $canSeeExpenseHead      = $user->hasPermission('cnf.expense-head.list');
        $canSeeBranch           = $user->hasPermission('cnf.branch.list');
        $canSeeItem             = $user->hasPermission('cnf.item.list');
        $canSeeAccount          = $user->hasPermission('cnf.account.list');
        $canSeeSettings = $canSeeService || $canSeeJobType || $canSeePort || $canSeeExpenseCategory
            || $canSeeExpenseHead || $canSeeBranch || $canSeeItem || $canSeeAccount;
    @endphp

    <div class="nav-item-group">
        <div class="nav-section">Main</div>
        <a href="{{ route('chevron.dashboard') }}"
           class="nav-link {{ request()->routeIs('chevron.dashboard') ? 'active' : '' }}">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a>
    </div>

    {{-- C&F Operations --}}
    @if($canSeeCnfOps)
    <div class="nav-item-group">
        <div class="nav-section">C&amp;F Operations</div>
        <a href="#chevronCnfOpsMenu"
           class="nav-link {{ $cnfOpsActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $cnfOpsActive ? 'true' : 'false' }}" aria-controls="chevronCnfOpsMenu">
            <i class="fa fa-file-alt"></i><span> C&amp;F Operations</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $cnfOpsActive ? 'show' : '' }}" id="chevronCnfOpsMenu">
            @if($canSeeJob)
            <a href="{{ route('chevron.cnf.jobs.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.cnf.jobs.*') ? 'active' : '' }}">
                <i class="fa fa-file-alt"></i> C&amp;F Jobs
            </a>
            @endif
            @if($canSeeJobExpense)
            <a href="{{ route('chevron.cnf.job-expenses.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.cnf.job-expenses.*') ? 'active' : '' }}">
                <i class="fa fa-money-check-alt"></i> Job Expenses
            </a>
            @endif
            @if($canSeeBill)
            <a href="{{ route('chevron.cnf.bills.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.cnf.bills.*') ? 'active' : '' }}">
                <i class="fa fa-file-invoice"></i> Bills
            </a>
            @endif
            @if($canSeeReceipt)
            <a href="{{ route('chevron.cnf.money-receipts.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.cnf.money-receipts.*') ? 'active' : '' }}">
                <i class="fa fa-money-bill-wave"></i> Money Receipts
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Reports (single item — flat) --}}
    <div class="nav-item-group">
        <div class="nav-section">Reports</div>
        <a href="{{ route('chevron.reports.job-expense-summary') }}"
           class="nav-link {{ $reportsActive ? 'active' : '' }}">
            <i class="fa fa-chart-line"></i> Expense Summary
        </a>
    </div>

    {{-- Stakeholders --}}
    @if($canSeeCustomer)
    <div class="nav-item-group">
        <div class="nav-section">Stakeholders</div>
        <a href="#stakeholdersMenu"
           class="nav-link {{ $stakeholdersActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $stakeholdersActive ? 'true' : 'false' }}" aria-controls="stakeholdersMenu">
            <i class="fa fa-users"></i><span> Stakeholders</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $stakeholdersActive ? 'show' : '' }}" id="stakeholdersMenu">
            <a href="{{ route('chevron.stakeholders.customers.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.stakeholders.customers.*') ? 'active' : '' }}">
                <i class="fa fa-users"></i> Customers
            </a>
        </div>
    </div>
    @endif

    {{-- Settings --}}
    @if($canSeeSettings)
    <div class="nav-item-group">
        <div class="nav-section">Settings</div>
        <a href="#settingsMenu"
           class="nav-link {{ $settingsActive ? 'active' : '' }}"
           data-bs-toggle="collapse" aria-expanded="{{ $settingsActive ? 'true' : 'false' }}" aria-controls="settingsMenu">
            <i class="fa fa-cog"></i><span> Settings</span>
            <i class="fa fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse {{ $settingsActive ? 'show' : '' }}" id="settingsMenu">
            @if($canSeeService)
            <a href="{{ route('chevron.settings.services.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.services.*') ? 'active' : '' }}">
                <i class="fa fa-concierge-bell"></i> Services
            </a>
            @endif
            @if($canSeeJobType)
            <a href="{{ route('chevron.settings.job-types.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.job-types.*') ? 'active' : '' }}">
                <i class="fa fa-tags"></i> Job Types
            </a>
            @endif
            @if($canSeePort)
            <a href="{{ route('chevron.settings.ports.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.ports.*') ? 'active' : '' }}">
                <i class="fa fa-anchor"></i> Ports
            </a>
            @endif
            @if($canSeeExpenseCategory)
            <a href="{{ route('chevron.settings.expense-categories.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.expense-categories.*') ? 'active' : '' }}">
                <i class="fa fa-receipt"></i> Expense Categories
            </a>
            @endif
            @if($canSeeExpenseHead)
            <a href="{{ route('chevron.settings.expense-heads.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.expense-heads.*') ? 'active' : '' }}">
                <i class="fa fa-money-bill"></i> Expense Heads
            </a>
            @endif
            @if($canSeeBranch)
            <a href="{{ route('chevron.settings.branches.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.branches.*') ? 'active' : '' }}">
                <i class="fa fa-code-branch"></i> Branches
            </a>
            @endif
            @if($canSeeItem)
            <a href="{{ route('chevron.settings.items.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.items.*') ? 'active' : '' }}">
                <i class="fa fa-boxes"></i> Items
            </a>
            @endif
            @if($canSeeAccount)
            <a href="{{ route('chevron.settings.accounts.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('chevron.settings.accounts.*') ? 'active' : '' }}">
                <i class="fa fa-university"></i> Account No
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var items = [
        { label: 'Dashboard',          section: 'Main',             url: '{{ route("chevron.dashboard") }}',                             icon: 'fa-tachometer-alt' },
        @if($canSeeJob)
        { label: 'C&F Jobs',           section: 'C&F Operations',   url: '{{ route("chevron.cnf.jobs.index") }}',                        icon: 'fa-file-alt' },
        @endif
        @if($canSeeJobExpense)
        { label: 'Job Expenses',       section: 'C&F Operations',   url: '{{ route("chevron.cnf.job-expenses.index") }}',                icon: 'fa-money-check-alt' },
        @endif
        @if($canSeeBill)
        { label: 'Bills',              section: 'C&F Operations',   url: '{{ route("chevron.cnf.bills.index") }}',                       icon: 'fa-file-invoice' },
        @endif
        @if($canSeeReceipt)
        { label: 'Money Receipts',     section: 'C&F Operations',   url: '{{ route("chevron.cnf.money-receipts.index") }}',              icon: 'fa-money-bill-wave' },
        @endif
        { label: 'Expense Summary',    section: 'Reports',          url: '{{ route("chevron.reports.job-expense-summary") }}',           icon: 'fa-chart-line' },
        @if($canSeeCustomer)
        { label: 'Customers',          section: 'Stakeholders',     url: '{{ route("chevron.stakeholders.customers.index") }}',          icon: 'fa-users' },
        @endif
        @if($canSeeService)
        { label: 'Services',           section: 'Settings',         url: '{{ route("chevron.settings.services.index") }}',              icon: 'fa-concierge-bell' },
        @endif
        @if($canSeeJobType)
        { label: 'Job Types',          section: 'Settings',         url: '{{ route("chevron.settings.job-types.index") }}',             icon: 'fa-tags' },
        @endif
        @if($canSeePort)
        { label: 'Ports',              section: 'Settings',         url: '{{ route("chevron.settings.ports.index") }}',                 icon: 'fa-anchor' },
        @endif
        @if($canSeeExpenseCategory)
        { label: 'Expense Categories', section: 'Settings',         url: '{{ route("chevron.settings.expense-categories.index") }}',   icon: 'fa-receipt' },
        @endif
        @if($canSeeExpenseHead)
        { label: 'Expense Heads',      section: 'Settings',         url: '{{ route("chevron.settings.expense-heads.index") }}',        icon: 'fa-money-bill' },
        @endif
        @if($canSeeBranch)
        { label: 'Branches',           section: 'Settings',         url: '{{ route("chevron.settings.branches.index") }}',             icon: 'fa-code-branch' },
        @endif
        @if($canSeeItem)
        { label: 'Items',              section: 'Settings',         url: '{{ route("chevron.settings.items.index") }}',                icon: 'fa-boxes' },
        @endif
        @if($canSeeAccount)
        { label: 'Account No',         section: 'Settings',         url: '{{ route("chevron.settings.accounts.index") }}',             icon: 'fa-university' },
        @endif
    ];

    var input       = document.getElementById('sidebarMenuSearch');
    var clearBtn    = document.getElementById('sidebarSearchClear');
    var resultsWrap = document.getElementById('sidebarSearchResults');
    var resultsList = document.getElementById('sidebarSearchResultsList');
    var emptyEl     = document.getElementById('sidebarSearchEmpty');
    var navGroups   = document.querySelectorAll('.sidebar .nav-item-group');
    var focusIdx    = -1;

    function esc(str) { return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function highlight(text, q) {
        if (!q) { return esc(text); }
        var re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        return esc(text).replace(re, function (m) { return '<mark>' + m + '</mark>'; });
    }

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
            resultsList.innerHTML   = '';
            emptyEl.style.display   = '';
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

    function getResultItems() {
        return resultsList.querySelectorAll('.sidebar-search-result-item');
    }

    function applyFocus(items) {
        items.forEach(function (el, i) { el.classList.toggle('kb-focus', i === focusIdx); });
        if (items[focusIdx]) { items[focusIdx].scrollIntoView({ block: 'nearest' }); }
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
})();
</script>
@endpush
