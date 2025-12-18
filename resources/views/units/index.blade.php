@extends('adminlte::page')

@section('title', 'Units')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Units</h1>
    <a href="{{ route('units.create') }}" class="btn btn-primary">Add Unit</a>
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

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Division</th>
                    <th width="150px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($units as $unit)
                    <tr>
                        <td>{{ $unit->id }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->division->name }}</td>
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
        <div class="mt-3">
            {{ $units->links() }}
        </div>
    </div>
</div>
@stop