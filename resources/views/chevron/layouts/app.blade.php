@extends('layouts.app')

@section('sidebar')
<div class="pt-2 pb-4">
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        Chevron Lines
    </div>

    <div class="nav-section">Main</div>
    <a href="{{ route('chevron.dashboard') }}"
       class="nav-link {{ request()->routeIs('chevron.dashboard') ? 'active' : '' }}">
        <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>

    <div class="nav-section">C&amp;F Operations</div>
    <a href="{{ route('chevron.cnf.jobs.index') }}"
       class="nav-link {{ request()->routeIs('chevron.cnf.jobs.*') ? 'active' : '' }}">
        <i class="fa fa-file-alt"></i> C&amp;F Jobs
    </a>
    <a href="{{ route('chevron.cnf.job-expenses.index') }}"
       class="nav-link {{ request()->routeIs('chevron.cnf.job-expenses.*') ? 'active' : '' }}">
        <i class="fa fa-money-check-alt"></i> Job Expenses
    </a>
    <a href="{{ route('chevron.cnf.bills.index') }}"
       class="nav-link {{ request()->routeIs('chevron.cnf.bills.*') ? 'active' : '' }}">
        <i class="fa fa-file-invoice"></i> Bills
    </a>
    <a href="{{ route('chevron.cnf.money-receipts.index') }}"
       class="nav-link {{ request()->routeIs('chevron.cnf.money-receipts.*') ? 'active' : '' }}">
        <i class="fa fa-money-bill-wave"></i> Money Receipts
    </a>

    <div class="nav-section">Reports</div>
    <a href="{{ route('chevron.reports.job-expense-summary') }}"
       class="nav-link {{ request()->routeIs('chevron.reports.*') ? 'active' : '' }}">
        <i class="fa fa-chart-line"></i> Expense Summary
    </a>

    <div class="nav-section">Stakeholders</div>
    @php $stakeholdersActive = request()->routeIs('chevron.stakeholders.*'); @endphp
    <a href="#stakeholdersMenu" class="nav-link d-flex justify-content-between align-items-center {{ $stakeholdersActive ? 'active' : '' }}"
       data-bs-toggle="collapse" aria-expanded="{{ $stakeholdersActive ? 'true' : 'false' }}" aria-controls="stakeholdersMenu">
        <span><i class="fa fa-users me-1"></i> Stakeholders</span>
        <i class="fa fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ $stakeholdersActive ? 'show' : '' }}" id="stakeholdersMenu">
        <a href="{{ route('chevron.stakeholders.designations.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.stakeholders.designations.*') ? 'active' : '' }}">
            <i class="fa fa-id-badge"></i> Designations
        </a>
        <a href="{{ route('chevron.stakeholders.employees.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.stakeholders.employees.*') ? 'active' : '' }}">
            <i class="fa fa-user-tie"></i> Employees
        </a>
        <a href="{{ route('chevron.stakeholders.customers.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.stakeholders.customers.*') ? 'active' : '' }}">
            <i class="fa fa-users"></i> Customers
        </a>
    </div>

    <div class="nav-section">Settings</div>
    @php $settingsActive = request()->routeIs('chevron.settings.*'); @endphp
    <a href="#settingsMenu" class="nav-link d-flex justify-content-between align-items-center {{ $settingsActive ? 'active' : '' }}"
       data-bs-toggle="collapse" aria-expanded="{{ $settingsActive ? 'true' : 'false' }}" aria-controls="settingsMenu">
        <span><i class="fa fa-cog me-1"></i> Settings</span>
        <i class="fa fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ $settingsActive ? 'show' : '' }}" id="settingsMenu">
        <a href="{{ route('chevron.settings.services.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.services.*') ? 'active' : '' }}">
            <i class="fa fa-concierge-bell"></i> Services
        </a>
        <a href="{{ route('chevron.settings.job-types.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.job-types.*') ? 'active' : '' }}">
            <i class="fa fa-tags"></i> Job Types
        </a>
        <a href="{{ route('chevron.settings.ports.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.ports.*') ? 'active' : '' }}">
            <i class="fa fa-anchor"></i> Ports
        </a>
        <a href="{{ route('chevron.settings.expense-categories.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.expense-categories.*') ? 'active' : '' }}">
            <i class="fa fa-receipt"></i> Expense Categories
        </a>
        <a href="{{ route('chevron.settings.expense-heads.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.expense-heads.*') ? 'active' : '' }}">
            <i class="fa fa-money-bill"></i> Expense Heads
        </a>
        <a href="{{ route('chevron.settings.branches.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.branches.*') ? 'active' : '' }}">
            <i class="fa fa-code-branch"></i> Branches
        </a>
        <a href="{{ route('chevron.users.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.users.*') ? 'active' : '' }}">
            <i class="fa fa-users-cog"></i> Users
        </a>
        <a href="{{ route('chevron.settings.items.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.items.*') ? 'active' : '' }}">
            <i class="fa fa-boxes"></i> Items
        </a>
        <a href="{{ route('chevron.settings.accounts.index') }}"
           class="nav-link ps-4 {{ request()->routeIs('chevron.settings.accounts.*') ? 'active' : '' }}">
            <i class="fa fa-university"></i> Account No
        </a>
    </div>
</div>
@endsection
