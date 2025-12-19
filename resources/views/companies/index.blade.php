@extends('adminlte::page')

@section('title', 'Companies')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Companies</h1>
    <a href="{{ route('companies.create') }}" class="btn btn-primary">Add Company</a>
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Holding?</th>
                        <th>Created At</th>
                        <th width="150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companies as $company)
                        <tr>
                            <td>{{ $company->id }}</td>
                            <td>{{ $company->code }}</td>
                            <td>{{ $company->name }}</td>
                            <td>
                                @if($company->is_holding)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>{{ $company->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('companies.edit', $company) }}"
                                    class="btn btn-xs btn-default text-primary mx-1" title="Edit">
                                    <i class="fa fa-lg fa-fw fa-pen"></i>
                                </a>
                                <form action="{{ route('companies.destroy', $company) }}" method="POST"
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
            {{ $companies->links() }}
        </div>
    </div>
</div>
@stop