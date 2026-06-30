@extends('layouts.auth')

@section('title', 'Select Company')

@section('content')
<div style="width:100%; max-width:760px;" class="mx-3">
    <div class="text-center mb-4">
        <div class="auth-logo mb-1" style="color:#fff;">
            <i class="fa fa-layer-group text-primary"></i>
            <span style="color:#fff;">NAS Group ERP</span>
        </div>
        <p style="color:#c8d0e0;" class="small">
            Welcome, <strong>{{ auth()->user()->name }}</strong>. Select a company to continue.
        </p>
    </div>

    <div class="row g-4 justify-content-center">
        @forelse($companies as $company)
        <div class="col-12 col-sm-6 col-md-4">
            <form method="POST" action="{{ route('company.switch', $company->slug) }}">
                @csrf
                <button type="submit" class="card company-card border-0 shadow w-100 text-start p-0">
                    <div class="card-body p-4 text-center">
                        <div class="company-icon mb-3">
                            @if($company->type === 'cnf')
                                <i class="fa fa-ship text-success"></i>
                            @elseif($company->type === 'freight')
                                <i class="fa fa-truck text-info"></i>
                            @else
                                <i class="fa fa-store text-warning"></i>
                            @endif
                        </div>
                        <h6 class="fw-700 mb-1" style="font-size:.88rem;">{{ $company->name }}</h6>
                        <span class="badge
                            {{ $company->type === 'cnf' ? 'bg-success' : ($company->type === 'freight' ? 'bg-info text-dark' : 'bg-warning text-dark') }}
                            text-uppercase" style="font-size:.65rem; letter-spacing:.05em;">
                            {{ $company->type === 'cnf' ? 'C&F' : ucfirst($company->type) }}
                        </span>
                        <div class="mt-3">
                            <span class="btn btn-sm btn-outline-primary w-100">
                                Enter <i class="fa fa-arrow-right ms-1"></i>
                            </span>
                        </div>
                    </div>
                </button>
            </form>
        </div>
        @empty
        <div class="col-12 text-center text-white">
            <i class="fa fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
            <p>No companies assigned to your account. Contact admin.</p>
        </div>
        @endforelse
    </div>

    <div class="text-center mt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-light">
                <i class="fa fa-sign-out-alt me-1"></i> Logout
            </button>
        </form>
    </div>
</div>
@endsection
