@extends('adminlte::page')

@section('title', 'Ringkasan Laporan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-chart-pie mr-2 text-primary"></i>Ringkasan Laporan</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item active">Laporan</li>
        </ol>
    </nav>
</div>
@stop

@section('content')
<div class="row">
    <!-- Procurement Section -->
    <div class="col-12 mt-3">
        <h5 class="mb-3 text-muted border-bottom pb-2">
            <i class="fas fa-file-invoice mr-2"></i>Laporan Pengadaan
        </h5>
    </div>

    <!-- Procurement Request Report per Unit -->
    <div class="col-md-4">
        <div class="card card-outline card-primary h-100 shadow-sm hover-shadow transition">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary p-3 mr-3 shadow-sm">
                        <i class="fas fa-building fa-2x text-white"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-0">Ringkasan Permintaan per Unit</h5>
                </div>
                <p class="card-text text-muted">Ringkasan dan detail permintaan pengadaan yang difilter berdasarkan
                    perusahaan dan unit.</p>
                <a href="{{ route('reports.unit') }}" class="btn btn-primary btn-block rounded-pill">
                    Lihat Laporan <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Outstanding Purchasing (> 7 Days) -->
    <div class="col-md-4">
        <div class="card card-outline card-warning h-100 shadow-sm hover-shadow transition">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-warning p-3 mr-3 shadow-sm">
                        <i class="fas fa-hourglass-half fa-2x text-white"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-0">Outstanding Permintaan</h5>
                </div>
                <p class="card-text text-muted">Analisis permintaan pengadaan yang tertunda selama lebih dari 7 hari.
                </p>
                <a href="{{ route('reports.outstanding') }}"
                    class="btn btn-warning btn-block rounded-pill text-white font-weight-bold">
                    Lihat Laporan <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Approval Timeline Analysis -->
    <div class="col-md-4">
        <div class="card card-outline card-info h-100 shadow-sm hover-shadow transition">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-info p-3 mr-3 shadow-sm">
                        <i class="fas fa-history fa-2x text-white"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-0">Analisis Linimasa</h5>
                </div>
                <p class="card-text text-muted">Rincian detail durasi persetujuan dan potensi hambatan proses.</p>
                <a href="{{ route('reports.timeline') }}" class="btn btn-info btn-block rounded-pill">
                    Lihat Laporan <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Purchasing Section -->
    <div class="col-12 mt-5">
        <h5 class="mb-3 text-muted border-bottom pb-2">
            <i class="fas fa-shopping-cart mr-2"></i>Laporan Pembelian
        </h5>
    </div>

    <!-- Purchase History/Detail -->
    <div class="col-md-4">
        <div class="card card-outline card-success h-100 shadow-sm hover-shadow transition">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-success p-3 mr-3 shadow-sm">
                        <i class="fas fa-poll-h fa-2x text-white"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-0">Riwayat Pembelian</h5>
                </div>
                <p class="card-text text-muted">Daftar lengkap barang yang dibeli dan status persetujuan anggarannya.
                </p>
                <a href="{{ route('reports.purchase') }}" class="btn btn-success btn-block rounded-pill">
                    Lihat Laporan <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Purchase Outstanding Detail -->
    <div class="col-md-4">
        <div class="card card-outline card-danger h-100 shadow-sm hover-shadow transition">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-danger p-3 mr-3 shadow-sm">
                        <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-0">Outstanding Pembelian</h5>
                </div>
                <p class="card-text text-muted">Daftar item individu yang diproses oleh purchasing tetapi belum
                    diperoleh/dibeli.</p>
                <a href="{{ route('reports.purchase-outstanding.index') }}"
                    class="btn btn-danger btn-block rounded-pill">
                    Lihat Laporan <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }

    .transition {
        transition: all 0.3s ease-in-out;
    }

    .rounded-circle {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@stop