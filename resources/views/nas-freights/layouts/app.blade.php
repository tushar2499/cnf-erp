@extends('layouts.app')

@section('sidebar')
<div class="pt-2 pb-4">
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        NAS Freights
    </div>

    <div class="nav-section">Main</div>
    <a href="{{ route('nas-freights.dashboard') }}"
       class="nav-link {{ request()->routeIs('nas-freights.dashboard') ? 'active' : '' }}">
        <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>

    <div class="nav-section">Operations</div>
    <a href="{{ route('nas-freights.bookings.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.bookings.*') ? 'active' : '' }}">
        <i class="fa fa-clipboard-list"></i> Bookings
    </a>
    <a href="{{ route('nas-freights.customer-bills.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.customer-bills.*') ? 'active' : '' }}">
        <i class="fa fa-file-invoice-dollar"></i> Customer Bills
    </a>
    <a href="{{ route('nas-freights.supplier-bills.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.supplier-bills.*') ? 'active' : '' }}">
        <i class="fa fa-file-invoice"></i> Supplier Bills
    </a>

    <div class="nav-section">Due Lists</div>
    <a href="{{ route('nas-freights.due-lists.customer') }}"
       class="nav-link {{ request()->routeIs('nas-freights.due-lists.customer') ? 'active' : '' }}">
        <i class="fa fa-user-clock"></i> Customer Due
    </a>
    <a href="{{ route('nas-freights.due-lists.supplier') }}"
       class="nav-link {{ request()->routeIs('nas-freights.due-lists.supplier') ? 'active' : '' }}">
        <i class="fa fa-truck-loading"></i> Supplier Due
    </a>

    <div class="nav-section">Collections</div>
    <a href="{{ route('nas-freights.money-receipts.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.money-receipts.*') ? 'active' : '' }}">
        <i class="fa fa-money-bill-wave"></i> Money Receipts
    </a>
    <a href="{{ route('nas-freights.supplier-payments.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.supplier-payments.*') ? 'active' : '' }}">
        <i class="fa fa-hand-holding-usd"></i> Supplier Payments
    </a>

    <div class="nav-section">Reports</div>
    <a href="{{ route('nas-freights.reports.booking') }}"
       class="nav-link {{ request()->routeIs('nas-freights.reports.booking*') ? 'active' : '' }}">
        <i class="fa fa-chart-bar"></i> Booking Report
    </a>
    <a href="{{ route('nas-freights.reports.party-bill-summary') }}"
       class="nav-link {{ request()->routeIs('nas-freights.reports.party-bill-summary*') ? 'active' : '' }}">
        <i class="fa fa-file-alt"></i> Bill Summary
    </a>
    <a href="{{ route('nas-freights.reports.bill-details') }}"
       class="nav-link {{ request()->routeIs('nas-freights.reports.bill-details*') ? 'active' : '' }}">
        <i class="fa fa-list-alt"></i> Bill Details
    </a>

    <div class="nav-section">Fleet</div>
    <a href="{{ route('nas-freights.vehicles.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.vehicles.*') ? 'active' : '' }}">
        <i class="fa fa-truck"></i> Vehicles
    </a>

    <div class="nav-section">HR</div>
    <a href="{{ route('nas-freights.employees.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.employees.*') ? 'active' : '' }}">
        <i class="fa fa-user-tie"></i> Employees
    </a>

    <div class="nav-section">Stakeholders</div>
    <a href="{{ route('nas-freights.stakeholders.suppliers.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.stakeholders.suppliers.*') ? 'active' : '' }}">
        <i class="fa fa-truck-loading"></i> Suppliers
    </a>
    <a href="{{ route('nas-freights.stakeholders.customers.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.stakeholders.customers.*') ? 'active' : '' }}">
        <i class="fa fa-users"></i> Customers
    </a>

    <div class="nav-section">Settings</div>
    <a href="{{ route('nas-freights.users.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.users.*') ? 'active' : '' }}">
        <i class="fa fa-users-cog"></i> Users
    </a>
    <a href="{{ route('nas-freights.settings.branches.index') }}"
       class="nav-link {{ request()->routeIs('nas-freights.settings.branches.*') ? 'active' : '' }}">
        <i class="fa fa-code-branch"></i> Branches
    </a>
</div>
@endsection
