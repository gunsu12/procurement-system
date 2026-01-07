@extends('adminlte::page')

@section('title', 'Units')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Units</h1>
    <div class="d-flex align-items-center">
        <form action="{{ route('units.index') }}" method="GET" class="form-inline mr-3">
            <select name="company_id" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Filter by Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('units.create') }}" class="btn btn-primary">Add Unit</a>
    </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Division</th>
                        <th>Company</th>
                        <th width="150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td>{{ $unit->id }}</td>
                            <td>{{ $unit->name }}</td>
                            <td>{{ $unit->division->name }}</td>
                            <td>{{ $unit->company->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('units.edit', $unit) }}" class="btn btn-xs btn-default text-primary mx-1"
                                    title="Edit">
                                    <i class="fa fa-lg fa-fw fa-pen"></i>
                                </a>
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-default text-danger mx-1" title="Delete"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-lg fa-fw fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $units->links() }}
        </div>
    </div>
</div>
@stop