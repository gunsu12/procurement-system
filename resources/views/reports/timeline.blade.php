@extends('adminlte::page')

@section('title', 'Laporan Linimasa Persetujuan')

@section('content_header')
<h1>Laporan Linimasa Persetujuan</h1>
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
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reports.timeline') }}" method="GET">
                <div class="row">
                    @if (isset($companies) && $companies->count() > 0)
                        <div class="col-md-2 mb-2">
                            <label>Perusahaan</label>
                            <select name="company_id" class="form-control">
                                <option value="">Semua Perusahaan</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($units->count() > 0)
                        <div class="col-md-2 mb-2">
                            <label>Unit</label>
                            <select name="unit_id" class="form-control" {{ !request('company_id') ? 'disabled' : '' }}>
                                <option value="">Semua Unit</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2 mb-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('reports.timeline') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Average Durations Summary --}}
    @if (!empty($averages))
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Rata-rata Durasi Persetujuan</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Rata-rata Durasi</th>
                                <th>Durasi Min</th>
                                <th>Durasi Maks</th>
                                <th>Jumlah Sampel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($averages as $status => $data)
                                <tr>
                                    <td><span
                                            class="badge bg-info">{{ $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $avg = $data['average'];
                                            if ($avg >= 1440) {
                                                echo floor($avg / 1440) . 'h ' . floor(($avg % 1440) / 60) . 'j';
                                            } elseif ($avg >= 60) {
                                                echo floor($avg / 60) . 'j ' . ($avg % 60) . 'm';
                                            } else {
                                                echo round($avg, 1) . 'm';
                                            }
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $min = $data['min'];
                                            if ($min >= 1440) {
                                                echo floor($min / 1440) . 'h ' . floor(($min % 1440) / 60) . 'j';
                                            } elseif ($min >= 60) {
                                                echo floor($min / 60) . 'j ' . ($min % 60) . 'm';
                                            } else {
                                                echo round($min, 1) . 'm';
                                            }
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $max = $data['max'];
                                            if ($max >= 1440) {
                                                echo floor($max / 1440) . 'h ' . floor(($max % 1440) / 60) . 'j';
                                            } elseif ($max >= 60) {
                                                echo floor($max / 60) . 'j ' . ($max % 60) . 'm';
                                            } else {
                                                echo round($max, 1) . 'm';
                                            }
                                        @endphp
                                    </td>
                                    <td>{{ $data['count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Individual Request Timelines --}}
    @foreach ($requests as $req)
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>
                            <a href="{{ route('procurement.show', $req->hashid) }}?back_url={{ urlencode(request()->fullUrl()) }}"
                                class="text-dark">
                                {{ $req->code }}
                            </a>
                        </strong>
                        @if($req->is_cito)
                            <span class="badge badge-danger">CITO</span>
                        @endif
                    </div>
                    <div>
                        <small class="text-muted">
                            {{ $req->company->name ?? '-' }} | {{ $req->unit->name }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Pemohon:</strong> {{ $req->user->name }}<br>
                        <strong>Status Saat Ini:</strong> <span
                            class="badge bg-secondary">{{ $statusMap[$req->status] ?? $req->status }}</span><br>
                        <strong>Total Jumlah:</strong> Rp {{ number_format($req->total_amount, 2, ',', '.') }}
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Dibuat:</strong> {{ $req->created_at->format('Y-m-d H:i') }}<br>
                        <strong>Total Durasi:</strong>
                        <span class="badge badge-primary">
                            @if ($req->total_duration >= 1440)
                                {{ floor($req->total_duration / 1440) }}h {{ floor(($req->total_duration % 1440) / 60) }}j
                            @elseif ($req->total_duration >= 60)
                                {{ floor($req->total_duration / 60) }}j {{ $req->total_duration % 60 }}m
                            @else
                                {{ $req->total_duration }}m
                            @endif
                        </span>
                    </div>
                </div>

                @if ($req->timeline && count($req->timeline) > 0)
                    <div class="timeline">
                        @foreach ($req->timeline as $index => $item)
                            <div class="time-label">
                                <span class="bg-secondary">
                                    {{ $item['timestamp']->format('Y-m-d H:i') }}
                                </span>
                            </div>
                            <div>
                                <i class="fas fa-check bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        @if ($item['duration_from_previous'])
                                            <i class="fas fa-clock"></i>
                                            @if ($item['duration_from_previous'] >= 1440)
                                                {{ floor($item['duration_from_previous'] / 1440) }} hari
                                                {{ floor(($item['duration_from_previous'] % 1440) / 60) }} jam
                                            @elseif ($item['duration_from_previous'] >= 60)
                                                {{ floor($item['duration_from_previous'] / 60) }} jam
                                                {{ $item['duration_from_previous'] % 60 }} menit
                                            @else
                                                {{ $item['duration_from_previous'] }} menit
                                            @endif
                                        @else
                                            <i class="fas fa-play-circle"></i> Dimulai
                                        @endif
                                    </span>
                                    <h3 class="timeline-header">
                                        <strong>{{ $statusMap[$item['status']] ?? ucfirst(str_replace('_', ' ', $item['status'])) }}</strong>
                                    </h3>
                                    <div class="timeline-body">
                                        <strong>{{ ucfirst($item['action']) }}</strong> oleh {{ $item['user'] }}
                                        @if ($item['note'])
                                            <br><em>Catatan: {{ $item['note'] }}</em>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                @else
                    <p class="text-muted">Tidak ada data linimasa untuk permintaan ini.</p>
                @endif
            </div>
        </div>
    @endforeach

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {{ $requests->withQueryString()->links() }}
    </div>
</div>
@stop

@push('js')
    <script>
        $(document).ready(function () {
            var companySelect = $('select[name="company_id"]');
            var unitSelect = $('select[name="unit_id"]');

            // Initial check
            if (!companySelect.val()) {
                unitSelect.prop('disabled', true);
            }

            companySelect.on('change', function () {
                var companyId = $(this).val();

                if (!companyId) {
                    unitSelect.prop('disabled', true);
                    unitSelect.val('');
                } else {
                    unitSelect.prop('disabled', false);

                    // Fetch units for selected company
                    $.ajax({
                        url: "{{ route('ajax.units') }}",
                        data: {
                            company_id: companyId
                        },
                        success: function (units) {
                            unitSelect.empty();
                            unitSelect.append('<option value="">Semua Unit</option>');

                            $.each(units, function (key, unit) {
                                unitSelect.append('<option value="' + unit.id + '">' + unit.name + '</option>');
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush

@push('css')
    <style>
        .timeline {
            position: relative;
            margin: 0 0 30px 0;
            padding: 0;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #ddd;
            left: 31px;
            margin: 0;
            border-radius: 2px;
        }

        .timeline>div {
            position: relative;
            margin-right: 10px;
            margin-bottom: 15px;
        }

        .timeline>div>.timeline-item {
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #444;
            margin-left: 60px;
            margin-right: 15px;
            padding: 10px;
            position: relative;
        }

        .timeline>div>.fas {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #666;
            background: #d2d6de;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0;
        }

        .timeline>div>.bg-success {
            background-color: #00a65a !important;
            color: #fff;
        }

        .timeline>.time-label>span {
            font-weight: 600;
            padding: 5px;
            display: inline-block;
            background-color: #fff;
            border-radius: 4px;
        }

        .timeline-header {
            margin: 0;
            color: #555;
            font-size: 16px;
            line-height: 1.1;
            margin-bottom: 10px;
        }

        .timeline-body {
            padding: 10px 0 0 0;
        }

        .time {
            color: #999;
            float: right;
            font-size: 12px;
        }
    </style>
@endpush