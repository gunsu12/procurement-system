@extends('adminlte::page')

@section('title', 'Reports')

@section('content_header')
<h1>Reports Overview</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="list-group">
                <a href="{{ route('reports.unit') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-building mr-2"></i> Procurement Request Report per Unit
                </a>
                <a href="{{ route('reports.outstanding') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-clock mr-2"></i> Outstanding Purchasing (> 7 Days)
                </a>
                <a href="{{ route('reports.timeline') }}" class="list-group-item list-group-item-action">
                    <i class="fas fa-history mr-2"></i> Approval Timeline Analysis
                </a>
            </div>
        </div>
    </div>
</div>
@stop