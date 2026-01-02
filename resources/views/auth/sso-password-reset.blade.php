@extends('adminlte::auth.auth-page')

@section('auth_header', 'Set Your Password')

@section('auth_body')
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Welcome! Since this is your first time logging in via SSO, please set a password for your account.
    This password will be used for future local logins.
</div>

<form action="{{ route('sso.password.update') }}" method="post">
    @csrf

    {{-- Password field --}}
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
            placeholder="Password" required autofocus>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock"></span>
            </div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Password confirmation field --}}
    <div class="input-group mb-3">
        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password"
            required>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock"></span>
            </div>
        </div>
    </div>

    {{-- Set password button --}}
    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-check"></i> Set Password
    </button>

</form>
@stop

@section('auth_footer')
<p class="text-muted text-center mt-3">
    <small>Password must be at least 8 characters long.</small>
</p>
@stop