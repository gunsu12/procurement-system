@extends('adminlte::page')

@section('title', 'Laporan Outstanding')

@section('content_header')
<h1>Outstanding Pembelian (> 7 Hari)</h1>
@stop

@php
    $statusMap = [
        'submitted' => 'Diajukan',
        'approved_by_manager' => 'Disetujui Manager',
        'approved_by_budgeting' => 'Disetujui Budgeting',
        'approved_by_dir_company' => 'Disetujui Dir. Perusahaan',
        'approved_by_fin_mgr_holding' => 'Disetujui Manajer Keuangan',
        'approved_by_fin_dir_holding' => 'Disetujui Dir. Keuangan',
        'approved_by_gen_dir_holding' => 'Disetujui Dir. Utama',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'rejected' => 'Ditolak'
    ];
@endphp

@section('content')
<div class="container-fluid">
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Permintaan Tertunda</h3>
        </div>
        <div class="card-body">
            <p class="text-danger">Permintaan yang belum selesai/ditolak dan dibuat lebih dari 7 hari yang lalu.</p>

            <!-- Desktop Layout -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Perusahaan</th>
                            <th>Unit</th>
                            <th>Pemohon</th>
                            <th>Total</th>
                            <th>Status Saat Ini</th>
                            <th>Tanggal Dibuat</th>
                            <th>Hari Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td>
                                    <a
                                        href="{{ route('procurement.show', $req->hashid) }}?back_url={{ urlencode(request()->fullUrl()) }}">
                                        {{ $req->code }}
                                    </a>
                                </td>
                                <td>{{ $req->company->name ?? '-' }}</td>
                                <td>{{ $req->unit->name }}</td>
                                <td>{{ $req->user->name }}</td>
                                <td>Rp {{ number_format($req->total_amount, 2, ',', '.') }}</td>
                                <td>
                                    <span
                                        class="badge badge-warning">{{ $statusMap[$req->status] ?? ucfirst(str_replace('_', ' ', $req->status)) }}</span>
                                </td>
                                <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                @php
                                    $days = $req->created_at->diffInDays(now());
                                @endphp
                                <td class="{{ $days > 7 ? 'text-danger font-weight-bold' : '' }}">
                                    {{ $days }} hari
                                    @if($days > 7)
                                        <i class="fas fa-exclamation-triangle ml-1"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Layout -->
            <div class="d-md-none">
                @foreach($requests as $req)
                    @php
                        $days = $req->created_at->diffInDays(now());
                        $isOverdue = $days > 7;
                    @endphp
                    <div class="card mb-3 {{ $isOverdue ? 'border-danger' : 'border-warning' }}"
                        style="border-left: 5px solid {{ $isOverdue ? '#dc3545' : '#ffc107' }};">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title m-0 font-weight-bold">
                                    {{ $req->code }}
                                </h5>
                                <small class="text-muted">{{ $req->created_at->format('Y-m-d') }}</small>
                            </div>

                            <p class="mb-1"><strong>Unit:</strong> {{ $req->unit->name }}</p>
                            <p class="mb-1"><strong>Perusahaan:</strong> {{ $req->company->name ?? '-' }}</p>
                            <p class="mb-1"><strong>Pemohon:</strong> {{ $req->user->name }}</p>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <strong>Rp {{ number_format($req->total_amount, 2, ',', '.') }}</strong>
                                <span class="badge badge-warning">
                                    {{ $statusMap[$req->status] ?? ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="{{ $isOverdue ? 'text-danger font-weight-bold' : '' }}">
                                    Outstanding: {{ $days }} hari
                                    @if($isOverdue)
                                        <i class="fas fa-exclamation-triangle ml-1"></i>
                                    @endif
                                </div>
                                <a href="{{ route('procurement.show', $req->hashid) }}?back_url={{ urlencode(request()->fullUrl()) }}"
                                    class="btn btn-sm btn-info">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@stop