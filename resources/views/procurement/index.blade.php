@extends('adminlte::page')

@section('title', 'Permohonan Pengadaan')

@section('content_header')
<h1>Permohonan Pengadaan</h1>
@stop

@section('content')
<div class="container-fluid">
    @if (Auth::user()->role == 'unit')
        <a href="{{ route('procurement.create') }}" class="btn btn-primary mb-3">Buat Permohonan</a>
    @endif

    <div class="card mb-3">
        <div class="card-header d-md-none" data-toggle="collapse" data-target="#filterCollapse" style="cursor:pointer">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        
        <div id="filterCollapse" class="collapse d-md-block">
            <div class="card-body">
                <form action="{{ route('procurement.index') }}" method="GET">
                    <div class="row">
                        @if (isset($companies) && $companies->count() > 0)
                            <div class="col-md-3 mb-2">
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
                            <div class="col-md-3 mb-2">
                                <label>Unit</label>
                                @php
                                    $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];
                                    $isHoldingRole = in_array(Auth::user()->role, $holdingRoles);
                                    // Disable unit select only for holding roles when no company is selected
                                    $disableUnit = $isHoldingRole && !request('company_id');
                                @endphp
                                <select name="unit_id" class="form-control" {{ $disableUnit ? 'disabled' : '' }}>
                                    <option value="">Semua Unit</option>
                                    @foreach ($units->groupBy('company.name') as $companyName => $companyUnits)
                                        <optgroup label="{{ $companyName ?: 'No Company' }}">
                                            @foreach ($companyUnits as $u)
                                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-2 mb-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label>Tipe</label>
                            <select name="is_medical" class="form-control">
                                <option value="">Semua Tipe</option>
                                <option value="1" {{ request('is_medical') == '1' ? 'selected' : '' }}>Medis</option>
                                <option value="0" {{ request('is_medical') == '0' ? 'selected' : '' }}>Non Medis</option>
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
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('procurement.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            <tr class="{{ $req->is_cito ? 'table-warning' : '' }}">
                                <td>
                                    {{ $req->code }} <br>
                                    @if($req->is_cito)
                                        <span class="badge badge-danger">CITO</span>
                                    @endif
                                    @if($req->is_medical)
                                        <span class="badge badge-warning">Medical</span>
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
                                        $badgeColor = 'bg-info'; // Default for submitted
                                        if (str_contains($req->status, 'approved')) {
                                            $badgeColor = 'badge-primary';
                                        } elseif ($req->status === 'processing') {
                                            $badgeColor = 'badge-warning';
                                        } elseif ($req->status === 'rejected') {
                                            $badgeColor = 'bg-danger';
                                        } elseif ($req->status === 'completed') {
                                            $badgeColor = 'bg-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('procurement.show', $req->hashid) }}"
                                        class="btn btn-sm btn-info">Lihat</a>
                                    @if($req->status == 'submitted' && $req->user_id == Auth::id())
                                        <a href="{{ route('procurement.edit', $req->hashid) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Layout -->
            <div class="d-md-none">
                @foreach ($requests as $req)
                    <div class="card mb-3 {{ $req->is_cito ? 'border-warning' : '' }}" style="{{ $req->is_cito ? 'border-left: 5px solid #ffc107;' : '' }}">
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
                                <span class="badge badge-warning">Medical</span>
                            @endif
                            <span class="badge badge-info">{{ ucfirst($req->request_type) }}</span>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <strong>Rp {{ number_format($req->total_amount, 2, ',', '.') }}</strong>
                                @php
                                    $badgeColor = 'bg-info';
                                    if (str_contains($req->status, 'approved')) {
                                        $badgeColor = 'badge-primary';
                                    } elseif ($req->status === 'processing') {
                                        $badgeColor = 'badge-warning';
                                    } elseif ($req->status === 'rejected') {
                                        $badgeColor = 'bg-danger';
                                    } elseif ($req->status === 'completed') {
                                        $badgeColor = 'bg-success';
                                    }
                                @endphp
                                <span class="badge {{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('procurement.show', $req->hashid) }}" class="btn btn-info flex-grow-1 mr-2">Lihat</a>
                                @if($req->status == 'submitted' && $req->user_id == Auth::id())
                                    <a href="{{ route('procurement.edit', $req->hashid) }}" class="btn btn-warning flex-grow-1">Edit</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $requests->withQueryString()->links() }}
            </div>
        </div>
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
                var currentUnit = "{{ request('unit_id') }}";

                if (!companyId) {
                    unitSelect.prop('disabled', true);
                    unitSelect.empty();
                    unitSelect.append('<option value="">Semua Unit</option>');
                    return;
                } else {
                    unitSelect.prop('disabled', false);
                }

                $.ajax({
                    url: "{{ route('ajax.units') }}",
                    data: {
                        company_id: companyId
                    },
                    success: function (units) {
                        unitSelect.empty();
                        unitSelect.append('<option value="">Semua Unit</option>');

                        $.each(units, function (key, unit) {
                            var selected = (unit.id == currentUnit) ? 'selected' : '';
                            unitSelect.append('<option value="' + unit.id + '" ' + selected + '>' + unit.name + '</option>');
                        });
                    }
                });
            });

            // Remove empty query parameters before form submission
            $('form[action="{{ route('procurement.index') }}"]').on('submit', function(e) {
                // Get all form inputs
                $(this).find('input, select, textarea').each(function() {
                    var $input = $(this);
                    var value = $input.val();
                    
                    // Remove input if value is empty or null
                    if (value === '' || value === null) {
                        $input.prop('disabled', true);
                    }
                });
                
                // Form will submit without disabled (empty) fields
                // Re-enable them after a short delay to avoid issues
                setTimeout(function() {
                    $('form input, form select, form textarea').prop('disabled', false);
                }, 100);
            });
        });
    </script>
@endpush