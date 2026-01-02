@extends('adminlte::page')

@section('title', 'Procurement Requests')

@section('content_header')
<h1>Procurement Requests</h1>
@stop

@section('content')
<div class="container-fluid">
    @if (Auth::user()->role == 'unit')
        <a href="{{ route('procurement.create') }}" class="btn btn-primary mb-3">Create Request</a>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('procurement.index') }}" method="GET">
                <div class="row">
                    @if (isset($companies) && $companies->count() > 0)
                        <div class="col-md-3 mb-2">
                            <label>Company</label>
                            <select name="company_id" class="form-control">
                                <option value="">All Companies</option>
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
                            <select name="unit_id" class="form-control" {{ !request('company_id') ? 'disabled' : '' }}>
                                <option value="">All Units</option>
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
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label>Type</label>
                        <select name="is_medical" class="form-control">
                            <option value="">All Types</option>
                            <option value="1" {{ request('is_medical') == '1' ? 'selected' : '' }}>Medis</option>
                            <option value="0" {{ request('is_medical') == '0' ? 'selected' : '' }}>Non Medis</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label>End Date</label>
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Unit</th>
                            <th>Requester</th>
                            <th>Notes</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            <tr class="{{ $req->is_cito ? 'table-warning' : '' }}">
                                <td>
                                    {{ $req->code }}
                                    @if($req->is_cito)
                                        <span class="badge badge-danger">CITO</span>
                                    @endif
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
                                        class="btn btn-sm btn-info">View</a>
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
                    unitSelect.append('<option value="">All Units</option>');
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
                        unitSelect.append('<option value="">All Units</option>');

                        $.each(units, function (key, unit) {
                            var selected = (unit.id == currentUnit) ? 'selected' : '';
                            unitSelect.append('<option value="' + unit.id + '" ' + selected + '>' + unit.name + '</option>');
                        });
                    }
                });
            });
        });
    </script>
@endpush