@extends('adminlte::page')

@section('title', 'Laporan Pembelian Barang')

@section('content_header')
<h1>Laporan Pembelian Barang</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header">Filter Laporan</div>
        <div class="card-body">
            <form action="{{ route('reports.purchase') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Tipe Pengajuan</label>
                        <select name="request_type" class="form-control">
                            <option value="">Semua Tipe</option>
                            <option value="aset" {{ request('request_type') == 'aset' ? 'selected' : '' }}>Aset</option>
                            <option value="nonaset" {{ request('request_type') == 'nonaset' ? 'selected' : '' }}>Non Aset
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Unit Pemohon</label>
                        <select name="unit_id" class="form-control">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Status Pembelian</label>
                        <select name="is_checked" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_checked') == '1' ? 'selected' : '' }}>Sudah Beli</option>
                            <option value="0" {{ request('is_checked') === '0' ? 'selected' : '' }}>Belum Beli</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('reports.purchase') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>

                    <button type="submit" name="export" value="excel" class="btn btn-success float-right">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Kode Pengajuan</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Tgl Approve Budgeting</th>
                            <th>Unit Pemohon</th>
                            <th>Item Name</th>
                            <th>Item Spec</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Budget (Est.)</th>
                            <th>Status Beli</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php
                                $req = $item->procurementRequest;
                                $log = $req->logs->first(function ($l) {
                                    return $l->status_after === 'approved_by_budgeting';
                                });
                                $approveDate = $log ? $log->created_at->format('d M Y H:i') : '-';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('procurement.show', $req->hashid) }}" target="_blank">
                                        {{ $req->code }}
                                    </a>
                                </td>
                                <td>{{ $req->created_at->format('d M Y') }}</td>
                                <td>{{ $approveDate }}</td>
                                <td>{{ $req->unit->name ?? '-' }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ Str::limit($item->specification, 30) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit }}</td>
                                <td class="text-right">{{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($item->is_checked)
                                        <span class="badge badge-success">Sudah Beli</span>
                                        <br><small>{{ $item->checked_at ? $item->checked_at->format('d/m/y') : '' }}</small>
                                    @else
                                        <span class="badge badge-warning">Belum Beli</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
@stop

@section('css')

@stop

@section('js')
<script>
    console.log('Report loaded');
</script>
@stop