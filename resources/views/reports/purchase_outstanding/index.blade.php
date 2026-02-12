@extends('adminlte::page')

@section('title', 'Laporan Purchase Outstanding')

@section('content_header')
<h1>Laporan Purchase Outstanding</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.purchase-outstanding.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Mulai Proses</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipe Permintaan</label>
                        <select name="request_type" class="form-control">
                            <option value="">Semua</option>
                            <option value="asset" {{ request('request_type') == 'asset' ? 'selected' : '' }}>Aset
                            </option>
                            <option value="nonaset" {{ request('request_type') == 'nonaset' ? 'selected' : '' }}>Non Aset
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Unit</label>
                        <select name="unit_id" class="form-control">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="is_medical" class="form-control">
                            <option value="">Semua</option>
                            <option value="yes" {{ request('is_medical') == 'yes' ? 'selected' : '' }}>Medis</option>
                            <option value="no" {{ request('is_medical') == 'no' ? 'selected' : '' }}>Non Medis</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Filter Outstanding</label>
                        <select name="outstanding_filter" class="form-control">
                            <option value="all" {{ request('outstanding_filter') == 'all' ? 'selected' : '' }}>Semua
                            </option>
                            <option value="more_than_7" {{ request('outstanding_filter') == 'more_than_7' ? 'selected' : '' }}>Limit > 7 Hari</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" name="preview" value="1" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tampilkan
                        </button>
                        <button type="submit" name="export" value="1" class="btn btn-success ml-2">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <a href="{{ route('reports.purchase-outstanding.index') }}"
                            class="btn btn-default ml-2">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if(isset($results))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Laporan ({{ count($results) }} Item)</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Kode Request</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Unit</th>
                        <th>Kategori</th>
                        <th>Nama Barang</th>
                        <th>Spesifikasi</th>
                        <th>Tgl Proses</th>
                        <th>Outstanding (Hari)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['created_at'] }}</td>
                            <td>{{ $row['unit'] }}</td>
                            <td>
                                <span class="badge {{ $row['category'] == 'Medis' ? 'badge-danger' : 'badge-info' }}">
                                    {{ $row['category'] }}
                                </span>
                            </td>
                            <td>{{ $row['item_name'] }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($row['item_spec'], 30) }}</td>
                            <td>{{ $row['processed_at'] }}</td>
                            <td>
                                <span class="badge {{ $row['outstanding_days'] > 7 ? 'badge-warning' : 'badge-success' }}">
                                    {{ $row['outstanding_days'] }} Hari
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Data tidak ditemukan sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@stop