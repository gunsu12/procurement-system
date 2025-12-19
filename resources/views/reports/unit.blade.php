@extends('adminlte::page')

@section('title', 'Unit Reports')

@section('content_header')
<h1>Unit Procurement Report</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Requests</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    @if(isset($companies) && $companies->count() > 0)
                        <div class="col-md-3 mb-2">
                            <label>Company</label>
                            <select name="company_id" class="form-control">
                                <option value="">All Companies</option>
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
                            <option value="">All Units</option>
                            @foreach ($units->groupBy('company.name') as $companyName => $companyUnits)
                                <optgroup label="{{ $companyName ?: 'No Company' }}">
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

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Company</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Manager Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td>{{ $req->code }}</td>
                                <td>{{ $req->company->name ?? '-' }}</td>
                                <td>{{ $req->unit->name }}</td>
                                <td>{{ $req->status }}</td>
                                <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                <td>{{ number_format($req->manager_nominal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop