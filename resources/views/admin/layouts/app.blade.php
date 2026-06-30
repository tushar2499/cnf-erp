@extends('layouts.app')

@section('sidebar')
<div class="pt-2 pb-4">
    <div class="px-3 py-2 mb-1" style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        Admin Panel
    </div>

    <div class="nav-section">Settings</div>
    <a href="{{ route('admin.companies.index') }}"
       class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
        <i class="fa fa-building"></i> Companies
    </a>
</div>
@endsection
