@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-card card shadow-lg p-4 mx-3">
    <div class="text-center mb-4">
        <div class="auth-logo mb-1">
            <i class="fa fa-layer-group text-primary"></i> NAS Group ERP
        </div>
        <p class="text-muted small">Sign in to your account</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope text-muted"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@example.com" required autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock text-muted"></i></span>
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••" required>
                <button type="button" class="btn btn-outline-secondary" id="togglePass">
                    <i class="fa fa-eye" id="togglePassIcon"></i>
                </button>
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-600">
            <i class="fa fa-sign-in-alt me-1"></i> Sign In
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
$('#togglePass').on('click', function () {
    const $input = $('#password');
    const $icon  = $('#togglePassIcon');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $input.attr('type', 'password');
        $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});
</script>
@endpush
