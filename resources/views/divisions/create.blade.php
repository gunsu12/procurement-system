@extends('adminlte::page')

@section('title', isset($division) ? 'Edit Division' : 'Create Division')

@section('content_header')
<h1>{{ isset($division) ? 'Edit Division' : 'Create Division' }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <form action="{{ isset($division) ? route('divisions.update', $division) : route('divisions.store') }}"
                method="POST">
                @csrf
                @if(isset($division))
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Division Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name', $division->name ?? '') }}"
                            placeholder="Enter division name" required>
                        @error('name')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('divisions.index') }}" class="btn btn-default">Cancel</a>
                    <button type="submit"
                        class="btn btn-primary float-right">{{ isset($division) ? 'Update' : 'Submit' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop