@extends('layouts.error')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center text-center h-100">
        <h2 class="display-1 font-weight-bold text-warning">404</h2>
        <div class="mt-4">
            <h3 class="h2"><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Halaman tidak ditemukan.</h3>
            <p class="lead">
                Kami tidak dapat menemukan halaman yang Anda cari.
                <br>
                Sementara itu, Anda dapat <a href="{{ url('/') }}">kembali ke dashboard</a>.
            </p>
            <div class="mt-4">
                 <a href="javascript:history.back()" class="btn btn-warning btn-lg px-4"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
            </div>
        </div>
    </div>
@endsection