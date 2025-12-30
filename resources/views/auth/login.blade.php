@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
<form action="{{ route('login') }}" method="post">
    @csrf

    {{-- Email field --}}
    <div class="input-group mb-3">
        <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }} or Username" autofocus>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Password field --}}
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
            placeholder="{{ __('adminlte::adminlte.password') }}">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Login field --}}
    <div class="row">
        <div class="col-7">
            <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">
                    {{ __('adminlte::adminlte.remember_me') }}
                </label>
            </div>
        </div>
        <div class="col-5">
            <button type=submit class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                <span class="fas fa-sign-in-alt"></span>
                {{ __('adminlte::adminlte.sign_in') }}
            </button>
        </div>
    </div>
</form>

{{-- SSO Button --}}
<div class="social-auth-links text-center mt-2 mb-3">
    <div class="text-center text-muted my-2">- OR -</div>
    <a href="{{ route('sso.login') }}" class="btn btn-block btn-light border">
        <i class="fas fa-building mr-2 text-primary"></i> Login with SSO
    </a>
</div>

@stop

@section('auth_footer')
{{-- Password reset link --}}
@if(Route::has('password.request'))
    <p class="my-0">
        <a href="{{ route('password.request') }}">
            {{ __('adminlte::adminlte.i_forgot_my_password') }}
        </a>
    </p>
@endif

{{-- Register link --}}
@if(Route::has('register'))
    <p class="my-0">
        <a href="{{ route('register') }}">
            {{ __('adminlte::adminlte.register_a_new_membership') }}
        </a>
    </p>
@endif
@stop