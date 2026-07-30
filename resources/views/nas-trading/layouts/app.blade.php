@extends('layouts.app')

@section('sidebar')
<div class="pt-2 pb-4">
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        NAS Trading
    </div>

    @php
        $lcOpsActive       = request()->routeIs('nas-trading.lcs.*', 'nas-trading.shipments.*');
        $billingActive     = request()->routeIs('nas-trading.customer-bills.*', 'nas-trading.lc-bill-statements.*', 'nas-trading.due-lists.*', 'nas-trading.money-receipts.*');
        $mastersActive     = request()->routeIs('nas-trading.customers.*', 'nas-trading.suppliers.*', 'nas-trading.items.*', 'nas-trading.expense-heads.*', 'nas-trading.banks.*', 'nas-trading.importers.*', 'nas-trading.employees.*', 'nas-trading.ports.*', 'nas-trading.cnf-agents.*', 'nas-trading.transport-companies.*', 'nas-trading.psi-companies.*');
        $settingsActive    = request()->routeIs('nas-trading.users.*', 'nas-trading.settings.*');
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
            <a href="{{ route('nas-trading.employees.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.employees.*') ? 'active' : '' }}">
                <i class="fa fa-user-tie"></i> Employees
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
            <a href="{{ route('nas-trading.users.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.users.*') ? 'active' : '' }}">
                <i class="fa fa-users-cog"></i> Users
            </a>
            <a href="{{ route('nas-trading.settings.branches.index') }}"
               class="nav-link ps-4 {{ request()->routeIs('nas-trading.settings.branches.*') ? 'active' : '' }}">
                <i class="fa fa-code-branch"></i> Branches
            </a>
        </div>
    </div>

    {{-- Data (single item — flat) --}}
    <div class="nav-item-group">
        <div class="nav-section">Data</div>
        <a href="{{ route('nas-trading.import.chevron.preview') }}"
           class="nav-link {{ $dataActive ? 'active' : '' }}">
            <i class="fa fa-file-import"></i> Import Data
        </a>
    </div>
</div>
@endsection
