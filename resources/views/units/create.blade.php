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
@stop