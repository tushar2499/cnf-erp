@extends('layouts.auth')
@section('title', 'Select Branch — NAS Trading')

@section('content')
<div style="width:100%; max-width:680px;" class="mx-3">
    <div class="text-center mb-4">
        <div class="auth-logo mb-1" style="color:#fff;">
            <i class="fa fa-file-contract text-info"></i>
            <span style="color:#fff;">NAS Group ERP</span>
        </div>
        <p style="color:#c8d0e0;" class="small">
            <strong>NAS Trading</strong> — Select your branch to continue.
        </p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger text-center mb-3">{{ session('error') }}</div>
    @endif

    <div class="row g-3 justify-content-center">
        @forelse($branches as $branch)
        <div class="col-12 col-sm-6 col-md-4">
            <form method="POST" action="{{ route('nas-trading.select-branch.store') }}">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <button type="submit" class="card border-0 shadow w-100 text-start p-0"
                        style="cursor:pointer;transition:transform .15s;border-radius:14px;background:#fff;"
                        onmouseover="this.style.transform='translateY(-3px)'"
                        onmouseout="this.style.transform=''">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3" style="width:54px;height:54px;border-radius:14px;background:rgba(13,202,240,.12);display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.4rem;color:#0dcaf0;">
                            <i class="fa fa-code-branch"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="font-size:.9rem;color:#1e293b;">{{ $branch->name }}</h6>
                        @if($branch->code)
                        <span class="badge bg-info bg-opacity-10 text-info" style="font-size:.68rem;letter-spacing:.05em;">{{ $branch->code }}</span>
                        @endif
                        @if($branch->address)
                        <p class="text-muted mt-2 mb-0" style="font-size:.72rem;">{{ $branch->address }}</p>
                        @endif
                        <div class="mt-3">
                            <span class="btn btn-sm btn-outline-info w-100">Select <i class="fa fa-arrow-right ms-1"></i></span>
                        </div>
                    </div>
                </button>
            </form>
        </div>
        @empty
        <div class="col-12 text-center" style="color:#c8d0e0;">
            <i class="fa fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i>
            No active branches found. Contact admin to set up branches.
        </div>
        @endforelse
    </div>

    <div class="text-center mt-4 d-flex justify-content-center gap-3">
        <a href="{{ route('company.select') }}" class="btn btn-sm btn-outline-light">
            <i class="fa fa-building me-1"></i> Switch Company
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-light"><i class="fa fa-sign-out-alt me-1"></i> Logout</button>
        </form>
    </div>
</div>
@endsection
