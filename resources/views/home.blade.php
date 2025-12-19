@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard Statistics</h1>
@stop

@section('content')
<div class="row">
    @php
        $colors = [
            'submitted' => 'info',
            'approved_by_manager' => 'primary',
            'approved_by_budgeting' => 'primary',
            'approved_by_dir_company' => 'primary',
            'approved_by_fin_mgr_holding' => 'warning',
            'approved_by_fin_dir_holding' => 'warning',
            'approved_by_gen_dir_holding' => 'warning',
            'processing' => 'indigo',
            'completed' => 'success',
            'rejected' => 'danger'
        ];

        $icons = [
            'submitted' => 'fas fa-file-import',
            'approved_by_manager' => 'fas fa-user-check',
            'approved_by_budgeting' => 'fas fa-coins',
            'approved_by_dir_company' => 'fas fa-building',
            'approved_by_fin_mgr_holding' => 'fas fa-file-invoice-dollar',
            'approved_by_fin_dir_holding' => 'fas fa-file-invoice-dollar',
            'approved_by_gen_dir_holding' => 'fas fa-file-invoice-dollar',
            'processing' => 'fas fa-sync',
            'completed' => 'fas fa-check-circle',
            'rejected' => 'fas fa-times-circle'
        ];
    @endphp

    @foreach($allStatuses as $status => $label)
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-{{ $colors[$status] ?? 'secondary' }}">
                <div class="inner">
                    <h3>{{ $stats[$status] ?? 0 }}</h3>
                    <p>{{ $label }}</p>
                </div>
                <div class="icon">
                    <i class="{{ $icons[$status] ?? 'fas fa-info-circle' }}"></i>
                </div>
                <a href="{{ route('procurement.index', ['status' => $status]) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Quick Access</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('procurement.create') }}" class="btn btn-app bg-success">
                    <i class="fas fa-plus"></i> New Request
                </a>
                <a href="{{ route('procurement.index') }}" class="btn btn-app bg-info">
                    <i class="fas fa-list"></i> View All Requests
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-app bg-warning">
                    <i class="fas fa-chart-pie"></i> Reports
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .icon i {
        font-size: 50px;
    }
</style>
@stop