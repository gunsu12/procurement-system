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
            <form action="{{ route('procurement.index') }}" method="GET" class="form-inline">
                @if ($units->count() > 0)
                    <label class="mr-2">Unit:</label>
                    <select name="unit_id" class="form-control mr-3">
                        <option value="">All Units</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <label class="mr-2">Status:</label>
                <select name="status" class="form-control mr-3">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>

                <label class="mr-2">Start Date:</label>
                <input type="date" name="start_date" class="form-control mr-3" value="{{ request('start_date') }}">

                <label class="mr-2">End Date:</label>
                <input type="date" name="end_date" class="form-control mr-3" value="{{ request('end_date') }}">

                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('procurement.index') }}" class="btn btn-secondary ml-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
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
                            <td>{{ $req->unit->name }}</td>
                            <td>{{ $req->user->name }}</td>
                            <td><small>{{ Str::limit($req->notes, 50) }}</small></td>
                            <td class="text-right">Rp {{ number_format($req->total_amount, 2, ',', '.') }}</td>
                            <td><span class="badge bg-secondary">{{ $req->status }}</span></td>
                            <td>
                                <a href="{{ route('procurement.show', $req->hashid) }}" class="btn btn-sm btn-info">View</a>
                                @if($req->status == 'submitted' && $req->user_id == Auth::id())
                                    <a href="{{ route('procurement.edit', $req->hashid) }}"
                                        class="btn btn-sm btn-warning">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $requests->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@stop