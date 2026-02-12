@extends('layouts.error')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center text-center h-100">
        <h2 class="display-1 font-weight-bold text-danger">500</h2>
        <div class="mt-4">
            <h3 class="h2"><i class="fas fa-exclamation-circle text-danger"></i> Oops! Terjadi kesalahan pada server.</h3>
            <p class="lead">
                Kami akan segera memperbaikinya.
                <br>
                Sementara itu, Anda dapat <a href="{{ url('/') }}">kembali ke dashboard</a>.
            </p>
             <div class="mt-4">
                 <a href="javascript:history.back()" class="btn btn-danger btn-lg px-4"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
            </div>
        </div>
    </div>
@endsection