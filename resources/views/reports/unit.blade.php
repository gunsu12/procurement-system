@extends('adminlte::page')

@section('title', 'Laporan Unit')

@section('content_header')
<h1>Laporan Pengadaan Unit</h1>
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Permintaan</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    @if(isset($companies) && $companies->count() > 0)
                        <div class="col-md-3 mb-2">
                            <label>Perusahaan</label>
                            <select name="company_id" class="form-control">
                                <option value="">Semua Perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-3 mb-2">
                        <label>Unit</label>
                        <select name="unit_id" class="form-control">
                            <option value="">Semua Unit</option>
                            @foreach ($units->groupBy('company.name') as $companyName => $companyUnits)
                                <optgroup label="{{ $companyName ?: 'Tanpa Perusahaan' }}">
                                    @foreach ($companyUnits as $unit)
                                        <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('reports.unit') }}" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Desktop Layout -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Perusahaan</th>
                            <th>Unit</th>
                            <th>Pemohon</th>
                            <th>Catatan</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr class="{{ $req->is_cito ? 'table-warning' : '' }}">
                                <td>
                                    {{ $req->code }} <br>
                                    @if($req->is_cito)
                                        <span class="badge badge-danger">CITO</span>
                                    @endif
                                    @if($req->is_medical)
                                        <span class="badge badge-warning">Medis</span>
                                    @endif
                                    <span class="badge badge-info">{{ ucfirst($req->request_type) }}</span>
                                </td>
                                <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                <td>{{ $req->company->name ?? '-' }}</td>
                                <td>{{ $req->unit->name }}</td>
                                <td>{{ $req->user->name }}</td>
                                <td><small>{{ Str::limit($req->notes, 50) }}</small></td>
                                <td class="text-right">Rp {{ number_format($req->total_amount, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                        $statusLabel = $statusMap[$req->status] ?? ucfirst(str_replace('_', ' ', $req->status));
                                        $badgeClass = 'badge-secondary';

                                        if ($req->status == 'completed')
                                            $badgeClass = 'badge-success';
                                        elseif ($req->status == 'rejected')
                                            $badgeClass = 'badge-danger';
                                        elseif ($req->status == 'processing')
                                            $badgeClass = 'badge-warning';
                                        elseif (str_contains($req->status, 'approved'))
                                            $badgeClass = 'badge-primary';
                                        elseif ($req->status == 'submitted')
                                            $badgeClass = 'badge-info';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Layout -->
            <div class="d-md-none">
                @foreach ($requests as $req)
                    <div class="card mb-3 {{ $req->is_cito ? 'border-warning' : '' }}"
                        style="{{ $req->is_cito ? 'border-left: 5px solid #ffc107;' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title m-0">
                                    <strong>{{ $req->code }}</strong>
                                    @if($req->is_cito)
                                        <span class="badge badge-danger ml-1">CITO</span>
                                    @endif
                                </h5>
                                <small class="text-muted">{{ $req->created_at->format('Y-m-d') }}</small>
                            </div>

                            <p class="mb-1"><strong>Unit:</strong> {{ $req->unit->name }}</p>
                            <p class="mb-1"><strong>Pemohon:</strong> {{ $req->user->name }}</p>
                            <p class="mb-1"><strong>Perusahaan:</strong> {{ $req->company->name ?? '-' }}</p>
                            @if($req->is_medical)
                                <span class="badge badge-warning">Medis</span>
                            @endif
                            <span class="badge badge-info">{{ ucfirst($req->request_type) }}</span>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <strong>Rp {{ number_format($req->total_amount, 2, ',', '.') }}</strong>
                                @php
                                    $statusLabel = $statusMap[$req->status] ?? ucfirst(str_replace('_', ' ', $req->status));
                                    $badgeClass = 'badge-secondary';

                                    if ($req->status == 'completed')
                                        $badgeClass = 'badge-success';
                                    elseif ($req->status == 'rejected')
                                        $badgeClass = 'badge-danger';
                                    elseif ($req->status == 'processing')
                                        $badgeClass = 'badge-warning';
                                    elseif (str_contains($req->status, 'approved'))
                                        $badgeClass = 'badge-primary';
                                    elseif ($req->status == 'submitted')
                                        $badgeClass = 'badge-info';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('procurement.show', $req->hashid) }}?back_url={{ urlencode(request()->fullUrl()) }}"
                                    class="btn btn-info flex-grow-1">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@stop