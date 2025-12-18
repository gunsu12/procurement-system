@extends('adminlte::page')

@section('title', 'Outstanding Reports')

@section('content_header')
    <h1>Outstanding Purchasing (> 7 Days)</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Delayed Requests</h3>
        </div>
        <div class="card-body">
            <p class="text-danger">Requests not completed/rejected created more than 7 days ago.</p>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Unit</th>
                        <th>Current Status</th>
                        <th>Created Date</th>
                        <th>Days Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->code }}</td>
                        <td>{{ $req->unit->name }}</td>
                        <td>{{ $req->status }}</td>
                        <td>{{ $req->created_at->format('Y-m-d') }}</td>
                        <td>{{ $req->created_at->diffInDays(now()) }} days</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
