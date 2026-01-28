@extends('adminlte::auth.auth-page')

@section('auth_header', 'Atur Kata Sandi Anda')

@section('auth_body')
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Selamat datang! Karena ini adalah kali pertama Anda masuk melalui SSO, silakan atur kata sandi untuk akun Anda.
    Kata sandi ini akan digunakan untuk login lokal di masa mendatang.
</div>

<form action="{{ route('sso.password.update') }}" method="post">
    @csrf

    {{-- Password field --}}
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
            placeholder="Kata Sandi" required autofocus>
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
        <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Kata Sandi"
            required>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock"></span>
            </div>
        </div>
    </div>

    {{-- Set password button --}}
    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-check"></i> Atur Kata Sandi
    </button>

</form>
@stop

@section('auth_footer')
<p class="text-muted text-center mt-3">
    <small>Kata sandi harus minimal 8 karakter.</small>
</p>
@stop