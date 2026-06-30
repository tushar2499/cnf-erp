<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $activeCompany->name ?? 'NAS Group ERP' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

{{-- Top Navbar --}}
<nav class="top-navbar">
    <button id="sidebarToggle" class="btn btn-sm btn-light me-1" title="Toggle sidebar">
        <i class="fa fa-bars"></i>
    </button>
    <span class="brand">
        <i class="fa fa-layer-group me-1 text-primary"></i> NAS Group ERP
    </span>
    @if(isset($activeCompany))
    <span class="badge company-badge
        {{ $activeCompany->type === 'cnf' ? 'bg-success' : ($activeCompany->type === 'freight' ? 'bg-info text-dark' : 'bg-warning text-dark') }}">
        {{ $activeCompany->name }}
    </span>
    @endif
    @if(isset($activeBranch))
    <span class="badge bg-primary bg-opacity-75 ms-1" style="font-size:.68rem">
        <i class="fa fa-code-branch me-1"></i>{{ $activeBranch->name }}
    </span>
    @endif
    <div class="ms-auto d-flex align-items-center gap-3">
        @if(isset($activeBranch))
        @php $switchRoute = match(session('active_company_slug')) {
            'nas-freights' => 'nas-freights.select-branch',
            'nas-trading'  => 'nas-trading.select-branch',
            default        => 'chevron.select-branch',
        }; @endphp
        <a href="{{ route($switchRoute) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-code-branch me-1"></i> Switch Branch
        </a>
        @endif
        <a href="{{ route('company.select') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-building me-1"></i> Switch Company
        </a>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa fa-user-circle me-1"></i> {{ auth()->user()->name }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.companies.index') }}">
                        <i class="fa fa-cog me-1"></i> Admin Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="fa fa-sign-out-alt me-1"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Sidebar --}}
<aside class="sidebar">
    @yield('sidebar')
</aside>

{{-- Main Content --}}
<main class="main-content">
    @yield('content')
</main>

<footer class="text-center py-2" style="font-size:12px;color:#888;border-top:1px solid #e0e0e0;">
    Powered By: <a href="https://a4bbd.com/" target="_blank" style="color:#888;text-decoration:none;font-weight:600;">Advertising For Business - A4B</a>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
@if(session('success'))
<script>
Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3500, timerProgressBar: true });
</script>
@endif
@if(session('error'))
<script>
Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: @json(session('error')), showConfirmButton: false, timer: 4500, timerProgressBar: true });
</script>
@endif
</body>
</html>
