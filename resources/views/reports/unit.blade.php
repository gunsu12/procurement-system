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
                    <div class="col-md-4">
                        <select name="unit_id" class="form-control">
                            <option value="">All Units</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
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
@stop
