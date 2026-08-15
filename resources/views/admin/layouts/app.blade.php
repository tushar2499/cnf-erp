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

        @if (auth()->user()->hasPermission('admin.companies.list') || auth()->user()->hasPermission('admin.users.list'))
            <div class="nav-section">Settings</div>
        @endif
        @if (auth()->user()->hasPermission('admin.companies.list'))
            <a href="{{ route('admin.companies.index') }}"
                class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                <i class="fa fa-building"></i> Companies
            </a>
        @endif
        @if (auth()->user()->hasPermission('admin.users.list'))
            <a href="{{ route('admin.users.index') }}"
                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa fa-users"></i> Users
            </a>
        @endif

        @if (auth()->user()->hasPermission('admin.roles.list'))
            <div class="nav-section">Access Control</div>
            <a href="{{ route('admin.roles.index') }}"
                class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="fa fa-user-shield"></i> Roles
            </a>
        @endif
    </div>
@endsection
