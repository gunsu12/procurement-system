@extends('adminlte::page')

@section('title', isset($unit) ? 'Edit Unit' : 'Create Unit')

@section('content_header')
<h1>{{ isset($unit) ? 'Edit Unit' : 'Create Unit' }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <form action="{{ isset($unit) ? route('units.update', $unit) : route('units.store') }}" method="POST">
                @csrf
                @if(isset($unit))
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Unit Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name', $unit->name ?? '') }}" placeholder="Enter unit name"
                            required>
                        @error('name')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="code">Unit Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                            name="code" value="{{ old('code', $unit->code ?? '') }}" placeholder="Enter unit code"
                            required>
                        @error('code')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="division_id">Division</label>
                        <select name="division_id" id="division_id"
                            class="form-control @error('division_id') is-invalid @enderror" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ (old('division_id', $unit->division_id ?? '') == $division->id) ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division_id')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_id">Company</label>
                        <select name="company_id" id="company_id"
                            class="form-control @error('company_id') is-invalid @enderror" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ (old('company_id', $unit->company_id ?? '') == $company->id) ? 'selected' : '' }}>
                                    {{ $company->name }} {{ $company->is_holding ? '(Holding)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="approval_by">Approver</label>
                        <input type="hidden" name="approval_by" id="approval_by"
                            value="{{ old('approval_by', $unit->approval_by ?? '') }}">
                        <div class="input-group">
                            <input type="text" class="form-control @error('approval_by') is-invalid @enderror"
                                id="approval_by_display"
                                value="{{ old('approval_by') ? \App\Models\User::find(old('approval_by'))->name : ($unit->approver->name ?? '') }}"
                                placeholder="Select Approver" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" data-toggle="modal"
                                    data-target="#approverModal">
                                    <i class="fas fa-search"></i> Browse
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="clearApprover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @error('approval_by')
                            <span class="error invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('units.index') }}" class="btn btn-default">Cancel</a>
                    <button type="submit"
                        class="btn btn-primary float-right">{{ isset($unit) ? 'Update' : 'Submit' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('units.approver_modal')
@stop