@extends('layouts.profile')

@section('title', 'My Profile')

@push('styles')
<style>
/* ── Page header ── */
.profile-page-header {
    display: flex; align-items: center; gap: .75rem;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem; padding: .8rem 1.1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    margin-bottom: 1rem;
}
.profile-page-header .ph-icon {
    width: 38px; height: 38px; border-radius: .4rem;
    background: #eff6ff; color: #1d4ed8;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.profile-page-header .ph-title  { font-size: .9rem; font-weight: 700; color: #1e293b; }
.profile-page-header .ph-sub    { font-size: .72rem; color: #64748b; }

/* ── Tab nav ── */
.profile-tab-nav {
    display: flex; gap: 0;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: .5rem .5rem 0 0;
    border-bottom: none;
    padding: 0 .75rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.profile-tab-btn {
    display: flex; align-items: center; gap: .45rem;
    font-size: .78rem; font-weight: 600; color: #64748b;
    padding: .75rem .85rem;
    border: none; background: none; cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color .15s, border-color .15s;
    white-space: nowrap;
}
.profile-tab-btn:hover { color: #1e293b; }
.profile-tab-btn.active {
    color: #1d4ed8;
    border-bottom-color: #3b82f6;
}
.profile-tab-btn .tab-icon {
    width: 22px; height: 22px; border-radius: .25rem;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; flex-shrink: 0;
    background: #f1f5f9; color: #64748b;
    transition: background .15s, color .15s;
}
.profile-tab-btn.active .tab-icon { background: #eff6ff; color: #1d4ed8; }
.profile-tab-btn .tab-badge {
    font-size: .62rem; font-weight: 700;
    background: #fee2e2; color: #be123c;
    padding: .08rem .35rem; border-radius: 999px;
}

/* ── Tab pane wrapper ── */
.profile-tab-pane-wrap {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 0 0 .5rem .5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    padding: 1.25rem 1.1rem;
}

/* ── Identity block ── */
.avatar-circle {
    width: 68px; height: 68px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; font-weight: 800; color: #fff;
    letter-spacing: -.02em; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.identity-name  { font-size: .95rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.identity-email { font-size: .76rem; color: #64748b; margin-top: .15rem; }
.identity-badges { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .5rem; }
.id-badge {
    display: inline-flex; align-items: center; gap: .28rem;
    font-size: .68rem; font-weight: 600;
    padding: .15rem .5rem; border-radius: 999px;
}
.id-badge-super  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.id-badge-active { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.id-badge-role   { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

/* ── Profile form ── */
.pf-divider {
    border: none; border-top: 1px solid #f1f5f9; margin: 1.1rem 0;
}
.form-label-sm {
    font-size: .75rem; font-weight: 600; color: #475569;
    margin-bottom: .3rem; display: block;
}
.form-control-profile {
    font-size: .83rem; border-color: #e2e8f0;
    border-radius: .35rem; padding: .45rem .7rem;
    transition: border-color .15s, box-shadow .15s;
}
.form-control-profile:focus {
    border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.form-control-profile.is-invalid { border-color: #f87171; }
.form-control-profile.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
.btn-profile-save {
    font-size: .8rem; font-weight: 600;
    padding: .45rem 1.1rem; border-radius: .35rem;
}

/* ── Access section ── */
.access-super-banner {
    display: flex; align-items: center; gap: .8rem;
    background: #fffbeb; border: 1px solid #fde68a;
    border-radius: .4rem; padding: .7rem .9rem;
    margin-bottom: 1rem;
}
.access-super-icon {
    width: 32px; height: 32px; border-radius: 50%;
    background: #fef3c7; color: #b45309;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0;
}
.access-company-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: .75rem;
}
.access-company-card {
    border: 1px solid #e2e8f0; border-radius: .45rem;
    padding: .85rem; background: #fff;
    display: flex; flex-direction: column; gap: .55rem;
}
.access-company-card.acc-green  { border-left: 3px solid #22c55e; }
.access-company-card.acc-blue   { border-left: 3px solid #3b82f6; }
.access-company-card.acc-amber  { border-left: 3px solid #f59e0b; }
.access-company-card.acc-slate  { border-left: 3px solid #94a3b8; }
.acc-company-head {
    display: flex; align-items: center; gap: .45rem;
    padding-bottom: .5rem; border-bottom: 1px solid #f1f5f9;
}
.acc-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.acc-company-name { font-size: .76rem; font-weight: 700; color: #1e293b; flex: 1; line-height: 1.3; }
.acc-row { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.acc-row-label { font-size: .7rem; font-weight: 600; color: #64748b; min-width: 72px; flex-shrink: 0; }
.acc-branch-list { display: flex; flex-wrap: wrap; gap: .3rem; }
.acc-pill {
    font-size: .65rem; font-weight: 600;
    padding: .12rem .45rem; border-radius: 999px;
    display: inline-flex; align-items: center;
}
.acc-pill-green { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.acc-pill-red   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
.acc-pill-slate { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.acc-perm-toggle {
    display: flex; align-items: center; gap: .35rem;
    font-size: .71rem; font-weight: 600; color: #475569;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: .3rem; padding: .28rem .65rem;
    cursor: pointer; width: 100%;
    transition: background .12s;
}
.acc-perm-toggle:hover { background: #f1f5f9; }
.acc-perm-toggle[aria-expanded="true"] .acc-chevron { transform: rotate(180deg); }
.acc-chevron { font-size: .6rem; margin-left: auto; transition: transform .2s; }
.acc-perm-body {
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: .3rem; padding: .6rem .75rem;
    margin-top: .2rem;
    display: flex; flex-direction: column; gap: .5rem;
}
.acc-perm-module-title {
    font-size: .67rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #94a3b8; margin-bottom: .25rem;
}
.acc-perm-tags { display: flex; flex-wrap: wrap; gap: .25rem; }
.acc-perm-tag {
    font-size: .65rem; font-weight: 500;
    background: #fff; border: 1px solid #e2e8f0;
    color: #475569; padding: .1rem .4rem; border-radius: .2rem;
    text-transform: capitalize;
}
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="profile-page-header">
    <div class="ph-icon"><i class="fa fa-user-circle"></i></div>
    <div>
        <div class="ph-title">My Profile</div>
        <div class="ph-sub">View and manage your account information</div>
    </div>
</div>

{{-- Tab nav --}}
<div class="profile-tab-nav">
    <button class="profile-tab-btn {{ $errors->has('name') || $errors->has('username') || $errors->has('email') ? '' : 'active' }}"
        id="tab-profile-btn" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button">
        <span class="tab-icon"><i class="fa fa-id-card"></i></span>
        Profile
        @if($errors->has('name') || $errors->has('username') || $errors->has('email'))
            <span class="tab-badge">!</span>
        @endif
    </button>
    <button class="profile-tab-btn {{ $errors->has('current_password') || $errors->has('password') ? 'active' : '' }}"
        id="tab-password-btn" data-bs-toggle="tab" data-bs-target="#tab-password" type="button">
        <span class="tab-icon"><i class="fa fa-lock"></i></span>
        Password
        @if($errors->has('current_password') || $errors->has('password'))
            <span class="tab-badge">!</span>
        @endif
    </button>
    <button class="profile-tab-btn" id="tab-access-btn" data-bs-toggle="tab" data-bs-target="#tab-access" type="button">
        <span class="tab-icon"><i class="fa fa-shield-halved"></i></span>
        Access & Permissions
    </button>
</div>

{{-- Tab panes --}}
<div class="profile-tab-pane-wrap">
<div class="tab-content">

    {{-- ── Tab 1: Profile ── --}}
    <div class="tab-pane fade {{ $errors->has('name') || $errors->has('username') || $errors->has('email') || (!$errors->has('current_password') && !$errors->has('password')) ? 'show active' : '' }}"
        id="tab-profile">

        {{-- Identity block --}}
        <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid #f1f5f9;">
            <div class="avatar-circle">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}{{ strtoupper(mb_substr(strstr($user->name, ' ') ?: '', 1, 1)) }}
            </div>
            <div>
                <div class="identity-name">{{ $user->name }}</div>
                <div class="identity-email">
                    @if($user->email) {{ $user->email }} @else <span class="fst-italic text-muted">No email set</span> @endif
                </div>
                <div class="identity-badges">
                    @if($user->is_super)
                        <span class="id-badge id-badge-super"><i class="fa fa-crown"></i> Super Admin</span>
                    @else
                        <span class="id-badge id-badge-active"><i class="fa fa-check-circle"></i> Active</span>
                        @if($userRole)
                            <span class="id-badge id-badge-role"><i class="fa fa-user-shield"></i> {{ $userRole }}</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Update form --}}
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label-sm">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                        class="form-control form-control-profile @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback" style="font-size:.75rem;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6">
                    <label for="username" class="form-label-sm">Username <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" style="font-size:.8rem;background:#f8fafc;border-color:#e2e8f0;">@</span>
                        <input type="text" id="username" name="username"
                            class="form-control form-control-profile @error('username') is-invalid @enderror"
                            value="{{ old('username', $user->username) }}" required>
                        @error('username')
                        <div class="invalid-feedback" style="font-size:.75rem;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <label for="email" class="form-label-sm">Email Address</label>
                    <input type="email" id="email" name="email"
                        class="form-control form-control-profile @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}" placeholder="optional">
                    @error('email')
                    <div class="invalid-feedback" style="font-size:.75rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-profile-save">
                    <i class="fa fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- ── Tab 2: Password ── --}}
    <div class="tab-pane fade {{ $errors->has('current_password') || $errors->has('password') ? 'show active' : '' }}"
        id="tab-password">

        <p style="font-size:.78rem;color:#64748b;margin-bottom:1.1rem;">
            All three fields required. Use a strong password — at least 6 characters.
        </p>

        <form method="POST" action="{{ route('profile.password') }}" style="max-width:420px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="current_password" class="form-label-sm">Current Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="password" id="current_password" name="current_password"
                        class="form-control form-control-profile @error('current_password') is-invalid @enderror"
                        autocomplete="current-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-pw" type="button" data-target="current_password" tabindex="-1"
                        style="border-color:#e2e8f0;font-size:.8rem;"><i class="fa fa-eye"></i></button>
                    @error('current_password')
                    <div class="invalid-feedback" style="font-size:.75rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label-sm">New Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="password" id="password" name="password"
                        class="form-control form-control-profile @error('password') is-invalid @enderror"
                        autocomplete="new-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-pw" type="button" data-target="password" tabindex="-1"
                        style="border-color:#e2e8f0;font-size:.8rem;"><i class="fa fa-eye"></i></button>
                    @error('password')
                    <div class="invalid-feedback" style="font-size:.75rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label-sm">Confirm New Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control form-control-profile" autocomplete="new-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-pw" type="button" data-target="password_confirmation" tabindex="-1"
                        style="border-color:#e2e8f0;font-size:.8rem;"><i class="fa fa-eye"></i></button>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-profile-save">
                    <i class="fa fa-key me-1"></i> Change Password
                </button>
            </div>
        </form>
    </div>

    {{-- ── Tab 3: Access & Permissions ── --}}
    <div class="tab-pane fade" id="tab-access">

        @if($user->is_super)
        <div class="access-super-banner mb-3">
            <div class="access-super-icon"><i class="fa fa-crown"></i></div>
            <div>
                <div style="font-size:.82rem;font-weight:700;color:#92400e;">Super Administrator</div>
                <div style="font-size:.72rem;color:#b45309;margin-top:.1rem;">Unrestricted access to all companies, modules, and branches.</div>
            </div>
        </div>
        @endif

        @if(count($accessData) > 0)
        <div class="access-company-grid">
            @foreach($accessData as $entry)
            @php
                $co = $entry['company'];
                $accentClass = match($co->type) {
                    'cnf'     => 'acc-green',
                    'freight' => 'acc-blue',
                    'trading' => 'acc-amber',
                    default   => 'acc-slate',
                };
                $accentDot = match($co->type) {
                    'cnf'     => '#22c55e',
                    'freight' => '#3b82f6',
                    'trading' => '#f59e0b',
                    default   => '#94a3b8',
                };
            @endphp
            <div class="access-company-card {{ $accentClass }}">

                <div class="acc-company-head">
                    <span class="acc-dot" style="background:{{ $accentDot }};"></span>
                    <span class="acc-company-name">{{ $co->name }}</span>
                    @if($entry['is_active'])
                        <span class="acc-pill acc-pill-green">Active</span>
                    @else
                        <span class="acc-pill acc-pill-red">Inactive</span>
                    @endif
                </div>

                {{-- Branches --}}
                <div class="acc-row">
                    <span class="acc-row-label"><i class="fa fa-code-branch me-1"></i>Branches</span>
                    <div class="acc-branch-list">
                        @if($user->is_super)
                            <span class="acc-pill acc-pill-slate">All branches</span>
                        @elseif(count($entry['branches']) > 0)
                            @foreach($entry['branches'] as $branch)
                                <span class="acc-pill acc-pill-slate">{{ $branch }}</span>
                            @endforeach
                        @else
                            <span style="font-size:.72rem;color:#94a3b8;font-style:italic;">No branch assigned</span>
                        @endif
                    </div>
                </div>

                {{-- Permissions collapsible --}}
                @if($user->is_super || count($entry['permissions_by_module']) > 0)
                <div style="display:flex;flex-direction:column;gap:.4rem;">
                    <button class="acc-perm-toggle" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#perms-{{ $co->id }}"
                        aria-expanded="false">
                        <i class="fa fa-key me-1"></i>
                        Permissions
                        @if(!$user->is_super)
                        <span class="acc-pill acc-pill-slate" style="margin-left:.3rem;">
                            {{ collect($entry['permissions_by_module'])->flatten()->count() }}
                        </span>
                        @else
                        <span class="acc-pill acc-pill-slate" style="margin-left:.3rem;">All</span>
                        @endif
                        <i class="fa fa-chevron-down acc-chevron ms-auto"></i>
                    </button>
                    <div class="collapse w-100" id="perms-{{ $co->id }}">
                        <div class="acc-perm-body">
                            @if($user->is_super)
                                <div style="font-size:.71rem;color:#64748b;font-style:italic;">Super admin bypasses all permission checks.</div>
                            @else
                                @foreach($entry['permissions_by_module'] as $module => $perms)
                                <div>
                                    <div class="acc-perm-module-title">{{ $module }}</div>
                                    <div class="acc-perm-tags">
                                        @foreach($perms as $perm)
                                        @php $action = last(explode('.', $perm)); @endphp
                                        <span class="acc-perm-tag">{{ $action }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <div class="acc-row">
                    <span class="acc-row-label"><i class="fa fa-key me-1"></i>Permissions</span>
                    <span style="font-size:.72rem;color:#94a3b8;font-style:italic;">No permissions assigned</span>
                </div>
                @endif

            </div>
            @endforeach
        </div>
        @else
        <div style="font-size:.78rem;color:#94a3b8;font-style:italic;">No company access assigned.</div>
        @endif

    </div>

</div>{{-- end .tab-content --}}
</div>{{-- end .profile-tab-pane-wrap --}}

@endsection

@push('scripts')
<script>
// Password show/hide toggle
document.querySelectorAll('.toggle-pw').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// Wire custom tab buttons to Bootstrap tab API
document.querySelectorAll('.profile-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.profile-tab-btn').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var target = document.querySelector(btn.dataset.bsTarget);
        document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('show','active'); });
        target.classList.add('show','active');
    });
});
</script>
@endpush
