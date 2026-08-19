@extends('layouts.app')

@section('sidebar')
    <div class="pt-2 pb-4">
        <div class="px-3 py-2 mb-1"
            style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
            Admin Panel
        </div>

        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fa fa-gauge"></i> Dashboard
        </a>

        @if (auth()->user()->hasPermission('admin.companies.list') ||
                auth()->user()->hasPermission('admin.users.list') ||
                auth()->user()->hasPermission('admin.employees.list') ||
                auth()->user()->hasPermission('admin.designations.list'))
            <div class="nav-section">Settings</div>
        @endif

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
@endsection
