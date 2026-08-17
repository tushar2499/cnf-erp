<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Profile') — NAS Group ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <style>
        /* ── Profile layout overrides ── */
        .profile-main {
            margin-left: 0;
            margin-top: var(--navbar-height);
            min-height: calc(100vh - var(--navbar-height));
            background: #f0f2f5;
            padding: 1.25rem 1rem 2rem;
        }
        .profile-shell {
            max-width: 860px;
            margin: 0 auto;
        }
        .profile-back-bar {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
        }
        .profile-back-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            padding: .3rem .65rem;
            border-radius: .35rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            transition: background .12s, color .12s, border-color .12s;
        }
        .profile-back-btn:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }
        .profile-back-sep {
            font-size: .7rem;
            color: #94a3b8;
        }
        .profile-back-crumb {
            font-size: .72rem;
            color: #94a3b8;
            font-weight: 500;
        }
        footer.profile-footer {
            margin-left: 0;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Top Navbar (identical to app layout — no sidebar toggle) --}}
<nav class="top-navbar">
    <span class="brand">
        <i class="fa fa-layer-group me-1 text-primary"></i>
        <span class="d-inline">NAS Group ERP</span>
    </span>
    <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
        <a href="{{ route('company.select') }}" class="btn btn-sm btn-outline-secondary" title="Switch Company">
            <i class="fa fa-building"></i><span class="d-none d-md-inline ms-1">Switch Company</span>
        </a>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" title="{{ auth()->user()->name }}">
                <i class="fa fa-user-circle"></i><span class="d-none d-sm-inline ms-1">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                <li class="user-dropdown-header">
                    <div class="user-dropdown-avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-dropdown-info">
                        <div class="user-dropdown-name">{{ auth()->user()->name }}</div>
                        <div class="user-dropdown-sub">
                            {{ auth()->user()->email ?: auth()->user()->username }}
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item user-dropdown-item active" href="{{ route('profile.show') }}">
                        <span class="udi-icon udi-blue"><i class="fa fa-user-circle"></i></span>
                        My Profile
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li>
                    <a class="dropdown-item user-dropdown-item" href="{{ route('admin.dashboard') }}">
                        <span class="udi-icon udi-slate"><i class="fa fa-cog"></i></span>
                        Admin Settings
                    </a>
                </li>
                @endif
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item user-dropdown-item user-dropdown-logout" type="submit">
                            <span class="udi-icon udi-red"><i class="fa fa-sign-out-alt"></i></span>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="profile-main">
    <div class="profile-shell">

        {{-- Back breadcrumb --}}
        <div class="profile-back-bar">
            @php
                $backRoute = match(session('active_company_slug')) {
                    'nas-freights' => ['route' => 'nas-freights.dashboard', 'label' => 'NAS Freights'],
                    'nas-trading'  => ['route' => 'nas-trading.dashboard',  'label' => 'NAS Trading'],
                    'chevron-lines'=> ['route' => 'chevron.dashboard',      'label' => 'Chevron Lines'],
                    default        => null,
                };
            @endphp
            @if($backRoute)
                <a href="{{ route($backRoute['route']) }}" class="profile-back-btn">
                    <i class="fa fa-arrow-left" style="font-size:.65rem;"></i> Back
                </a>
                <span class="profile-back-sep">/</span>
                <span class="profile-back-crumb">{{ $backRoute['label'] }} Dashboard</span>
                <span class="profile-back-sep">/</span>
                <span class="profile-back-crumb" style="color:#475569;">My Profile</span>
            @else
                <a href="{{ route('company.select') }}" class="profile-back-btn">
                    <i class="fa fa-arrow-left" style="font-size:.65rem;"></i> Select Company
                </a>
                <span class="profile-back-sep">/</span>
                <span class="profile-back-crumb" style="color:#475569;">My Profile</span>
            @endif
        </div>

        @yield('content')
    </div>
</main>

<footer class="profile-footer text-center py-2" style="font-size:12px;color:#888;border-top:1px solid #e0e0e0;">
    Powered By: <a href="https://a4bbd.com/" target="_blank" style="color:#888;text-decoration:none;font-weight:600;">Advertising For Business - A4B</a>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
@if(session('success'))
<script>
Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, showCloseButton: true, timer: 3500, timerProgressBar: true });
</script>
@endif
@if(session('error'))
<script>
Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: @json(session('error')), showConfirmButton: false, showCloseButton: true, timer: 4500, timerProgressBar: true });
</script>
@endif
</body>
</html>
