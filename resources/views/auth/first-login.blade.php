@extends('adminlte::master')

@section('title', 'Ubah Kata Sandi')

@section('adminlte_css')
@yield('css')
@stop

@section('body')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <h2><b>BROS</b> Hospital</h2>
                <p class="text-muted">Silakan perbarui kata sandi Anda untuk melanjutkan.</p>
            </div>

            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Pengaturan Keamanan</h3>
                </div>
                <form action="{{ route('auth.first-login.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                {{ session('warning') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="current_password">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" id="current_password"
                                class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Kata Sandi Baru</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-default mr-2"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-danger">Perbarui Kata Sandi</button>
                    </div>
                </form>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
@yield('js')
@stop