@extends('adminlte::page')

@section('title', 'Divisions')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Divisions</h1>
    <a href="{{ route('divisions.create') }}" class="btn btn-primary">Add Division</a>
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
                        <th>Company</th>
                        <th>Created At</th>
                        <th width="150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($divisions as $division)
                        <tr>
                            <td>{{ $division->id }}</td>
                            <td>{{ $division->name }}</td>
                            <td>{{ $division->company->name ?? '-' }}</td>
                            <td>{{ $division->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('divisions.edit', $division) }}"
                                    class="btn btn-xs btn-default text-primary mx-1" title="Edit">
                                    <i class="fa fa-lg fa-fw fa-pen"></i>
                                </a>
                                <form action="{{ route('divisions.destroy', $division) }}" method="POST"
                                    style="display:inline">
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
            {{ $divisions->links() }}
        </div>
    </div>
</div>
@stop