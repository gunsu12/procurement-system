@extends('layouts.error')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center text-center h-100">
        <h2 class="display-1 font-weight-bold text-warning">419</h2>
        <div class="mt-4">
            <h3 class="h2"><i class="fas fa-exclamation-triangle text-warning"></i> Halaman Kadaluarsa.</h3>
            <p class="lead">
                Sesi Anda telah berakhir karena tidak ada aktivitas.
                <br>
                Silakan muat ulang halaman dan coba lagi.
            </p>
            <div class="mt-4">
                 <a href="{{ url('/') }}" class="btn btn-warning btn-lg px-4"><i class="fas fa-home mr-2"></i> Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
@endsection