@extends('layouts.error')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center text-center h-100">
        <h2 class="display-1 font-weight-bold text-danger">403</h2>
        <div class="mt-4">
            <h3 class="h2"><i class="fas fa-ban text-danger"></i> Oops! Akses Ditolak.</h3>
            <p class="lead">
                {{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses sumber daya ini.' }}
                <br>
                Sementara itu, Anda dapat <a href="{{ url('/') }}">kembali ke dashboard</a>.
            </p>
            <div class="mt-4">
                <a href="javascript:history.back()" class="btn btn-danger btn-lg px-4"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
            </div>
        </div>
    </div>
@endsection