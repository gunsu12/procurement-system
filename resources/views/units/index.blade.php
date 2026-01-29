@extends('adminlte::page')

@section('title', 'Units')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Units</h1>
    <a href="{{ route('units.create') }}" class="btn btn-primary">Add Unit</a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form action="{{ route('units.index') }}" method="GET" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search by name or code..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="company_id">Company</label>
                        <select name="company_id" id="company_id" class="form-control">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" 
                                    {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="division_id">Division</label>
                        <select name="division_id" id="division_id" class="form-control" 
                                {{ !request('company_id') ? 'disabled' : '' }}>
                            <option value="">All Divisions</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" 
                                    {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="approval_by">Approver</label>
                        <input type="hidden" name="approval_by" id="approval_by" value="{{ request('approval_by') }}">
                        <div class="input-group">
                            <input type="text" class="form-control" id="approval_by_display" 
                                   value="{{ request('approval_by') ? \App\Models\User::find(request('approval_by'))->name : '' }}" 
                                   placeholder="All Approvers" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#approverModal">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('approval_by'))
                                    <button type="button" class="btn btn-outline-danger" id="clearApprover">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('units.index') }}" class="btn btn-default">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
                        <th>Division</th>
                        <th>Company</th>
                        <th>Approver</th>
                        <th width="150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($units->count() > 0)
                        @foreach ($units as $unit)
                            <tr>
                                <td>{{ $unit->id }}</td>
                                <td>{{ $unit->code }}</td>
                                <td>{{ $unit->name }}</td>
                                <td>{{ $unit->division->name }}</td>
                                <td>{{ $unit->company->name ?? '-' }}</td>
                                <td>{{ $unit->approver->name ?? '-' }}</td>
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
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No units found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $units->links() }}
        </div>
    </div>
</div>

<!-- Approver Selection Modal -->
<div class="modal fade" id="approverModal" tabindex="-1" role="dialog" aria-labelledby="approverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approverModalLabel">Select Approver</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="userSearch" class="form-control" placeholder="Search by name, email, or role...">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Company</th>
                                <th width="80px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @foreach($users as $user)
                                <tr class="user-row" 
                                    data-id="{{ $user->id }}" 
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}"
                                    data-company="{{ $user->company->name ?? '' }}">
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge badge-info">{{ $user->role }}</span></td>
                                    <td>{{ $user->company->name ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary select-user" data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                            <i class="fas fa-check"></i> Select
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // When company changes, fetch divisions
    $('#company_id').on('change', function() {
        const companyId = $(this).val();
        const divisionSelect = $('#division_id');
        
        if (!companyId) {
            divisionSelect.prop('disabled', true);
            divisionSelect.html('<option value="">All Divisions</option>');
            return;
        }
        
        // Fetch divisions for selected company
        $.ajax({
            url: '/api/divisions',
            method: 'GET',
            data: { company_id: companyId },
            success: function(data) {
                divisionSelect.prop('disabled', false);
                divisionSelect.html('<option value="">All Divisions</option>');
                
                data.forEach(function(division) {
                    divisionSelect.append(
                        `<option value="${division.id}">${division.name}</option>`
                    );
                });
            },
            error: function() {
                divisionSelect.prop('disabled', true);
                divisionSelect.html('<option value="">Error loading divisions</option>');
            }
        });
    });

    // Approver Modal: Search functionality
    $('#userSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.user-row').each(function() {
            const name = $(this).data('name').toLowerCase();
            const email = $(this).data('email').toLowerCase();
            const role = $(this).data('role').toLowerCase();
            const company = $(this).data('company').toLowerCase();
            
            const matches = name.includes(searchTerm) || 
                          email.includes(searchTerm) || 
                          role.includes(searchTerm) ||
                          company.includes(searchTerm);
            
            $(this).toggle(matches);
        });
    });

    // Approver Modal: Select user
    $('.select-user').on('click', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#approval_by').val(userId);
        $('#approval_by_display').val(userName);
        $('#approverModal').modal('hide');
    });

    // Clear approver selection
    $('#clearApprover').on('click', function() {
        $('#approval_by').val('');
        $('#approval_by_display').val('');
        // Optionally submit form to refresh
        // $('#filterForm').submit();
    });

    // Reset search when modal is closed
    $('#approverModal').on('hidden.bs.modal', function() {
        $('#userSearch').val('');
        $('.user-row').show();
    });
});
</script>
@stop
