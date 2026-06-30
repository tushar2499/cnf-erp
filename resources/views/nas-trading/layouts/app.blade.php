@extends('layouts.app')

@section('sidebar')
<div class="pt-2 pb-4">
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        NAS Trading
    </div>

    <div class="nav-section">Main</div>
    <a href="{{ route('nas-trading.dashboard') }}"
       class="nav-link {{ request()->routeIs('nas-trading.dashboard') ? 'active' : '' }}">
        <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>

    <div class="nav-section">LC Operations</div>
    <a href="{{ route('nas-trading.lcs.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.lcs.*') ? 'active' : '' }}">
        <i class="fa fa-file-contract"></i> LC Entry
    </a>
    <a href="{{ route('nas-trading.shipments.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.shipments.*') ? 'active' : '' }}">
        <i class="fa fa-ship"></i> Shipment Entry
    </a>

    <div class="nav-section">Billing</div>
    <a href="{{ route('nas-trading.customer-bills.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.customer-bills.*') ? 'active' : '' }}">
        <i class="fa fa-file-invoice-dollar"></i> Customer Bills
    </a>
    <a href="{{ route('nas-trading.due-lists.customer') }}"
       class="nav-link {{ request()->routeIs('nas-trading.due-lists.customer') ? 'active' : '' }}">
        <i class="fa fa-user-clock"></i> Customer Due
    </a>
    <a href="{{ route('nas-trading.money-receipts.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.money-receipts.*') ? 'active' : '' }}">
        <i class="fa fa-money-bill-wave"></i> Money Receipts
    </a>

    <div class="nav-section">Delivery</div>
    <a href="{{ route('nas-trading.deliveries.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.deliveries.*') ? 'active' : '' }}">
        <i class="fa fa-truck"></i> Deliveries
    </a>

    <div class="nav-section">Masters</div>
    <a href="{{ route('nas-trading.customers.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.customers.*') ? 'active' : '' }}">
        <i class="fa fa-users"></i> Customers
    </a>
    <a href="{{ route('nas-trading.suppliers.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.suppliers.*') ? 'active' : '' }}">
        <i class="fa fa-industry"></i> Suppliers
    </a>
    <a href="{{ route('nas-trading.items.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.items.*') ? 'active' : '' }}">
        <i class="fa fa-boxes"></i> Items
    </a>
    <a href="{{ route('nas-trading.expense-heads.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.expense-heads.*') ? 'active' : '' }}">
        <i class="fa fa-tags"></i> Expense Heads
    </a>
    <a href="{{ route('nas-trading.banks.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.banks.*') ? 'active' : '' }}">
        <i class="fa fa-university"></i> Banks
    </a>
    <a href="{{ route('nas-trading.importers.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.importers.*') ? 'active' : '' }}">
        <i class="fa fa-building"></i> Importers
    </a>

    <div class="nav-section">Settings</div>
    <a href="{{ route('nas-trading.employees.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.employees.*') ? 'active' : '' }}">
        <i class="fa fa-user-tie"></i> Employees
    </a>
    <a href="{{ route('nas-trading.ports.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.ports.*') ? 'active' : '' }}">
        <i class="fa fa-anchor"></i> Ports
    </a>
    <a href="{{ route('nas-trading.cnf-agents.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.cnf-agents.*') ? 'active' : '' }}">
        <i class="fa fa-handshake"></i> CNF Agents
    </a>
    <a href="{{ route('nas-trading.transport-companies.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.transport-companies.*') ? 'active' : '' }}">
        <i class="fa fa-truck-moving"></i> Transport Cos.
    </a>
    <a href="{{ route('nas-trading.psi-companies.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.psi-companies.*') ? 'active' : '' }}">
        <i class="fa fa-search"></i> PSI Companies
    </a>

    <div class="nav-section">Settings</div>
    <a href="{{ route('nas-trading.users.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.users.*') ? 'active' : '' }}">
        <i class="fa fa-users-cog"></i> Users
    </a>
    <a href="{{ route('nas-trading.settings.branches.index') }}"
       class="nav-link {{ request()->routeIs('nas-trading.settings.branches.*') ? 'active' : '' }}">
        <i class="fa fa-code-branch"></i> Branches
    </a>

    <div class="nav-section">Data</div>
    <a href="{{ route('nas-trading.import.chevron.preview') }}"
       class="nav-link {{ request()->routeIs('nas-trading.import.*') ? 'active' : '' }}">
        <i class="fa fa-file-import"></i> Import Data
    </a>
</div>
@endsection
