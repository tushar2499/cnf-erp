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

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="login">Email or Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user text-muted"></i></span>
                <input id="login" type="text" name="login" value="{{ old('login') }}"
                    class="form-control @error('login') is-invalid @enderror"
                    placeholder="email or username" autocomplete="username" required autofocus>
            </div>
            @error('login')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock text-muted"></i></span>
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••" autocomplete="current-password" required>
                <button type="button" class="btn btn-outline-secondary" id="togglePass">
                    <i class="fa fa-eye" id="togglePassIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
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
