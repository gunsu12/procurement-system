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
                            <div class="mt-2" id="password-requirements">
                                <small class="text-muted d-block mb-1">Syarat Kata Sandi:</small>
                                <ul class="list-unstyled text-sm mb-0" style="font-size: 0.9rem;">
                                    <li id="req-length" class="text-muted"><i class="fas fa-circle fa-xs mr-1"></i>
                                        Minimal 6 karakter</li>
                                </ul>
                            </div>
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
<script>
    $(document).ready(function () {
        var $passwordInput = $('#password');

        // Check initial state (e.g. if browser auto-fills)
        checkPassword($passwordInput.val());

        $passwordInput.on('input propertychange', function () {
            checkPassword($(this).val());
        });

        function checkPassword(password) {
            // Validation Logic
            var hasLength = password.length >= 6;

            // Update UI
            updateRequirement('#req-length', hasLength);
        }

        function updateRequirement(selector, isValid) {
            var $el = $(selector);
            var $icon = $el.find('i');

            if (isValid) {
                $el.removeClass('text-muted text-danger').addClass('text-success');
                $icon.removeClass('fa-circle fa-times').addClass('fa-check');
            } else {
                // Only show danger if input is not empty, otherwise keep muted (neutral)
                // But usually user wants to know what is missing.
                // Let's stick to: neutral if empty? No, request wants "early warning".
                // If empty, probably keep neutral. If user types, show success/fail.

                if ($('#password').val().length === 0) {
                    $el.removeClass('text-success text-danger').addClass('text-muted');
                    $icon.removeClass('fa-check fa-times').addClass('fa-circle');
                } else {
                    $el.removeClass('text-success text-muted').addClass('text-danger');
                    $icon.removeClass('fa-check fa-circle').addClass('fa-times');
                }
            }
        }
    });
</script>
@yield('js')
@stop