@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Users</h1>
    <div class="d-flex align-items-center">
        <form action="{{ route('users.index') }}" method="GET" class="form-inline mr-3">
            <select name="company_id" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Filter by Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <button type="button" class="btn btn-secondary mr-2" data-toggle="modal" data-target="#syncModal">
            <i class="fas fa-sync mr-1"></i> Sync Users
        </button>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Add User</a>
    </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- Search Form --}}
        <form action="{{ route('users.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by name, email, or username..." value="{{ request('search') }}">
                        <input type="hidden" name="company_id" value="{{ request('company_id') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('users.index', ['company_id' => request('company_id')]) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>

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
                        <th>Email</th>
                        <th>Role</th>
                        <th>Unit</th>
                        <th>Company</th>
                        <th width="150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-info">{{ $user->role }}</span></td>
                            <td>{{ $user->unit->name ?? 'N/A' }}</td>
                            <td>{{ $user->company->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-xs btn-default text-primary mx-1"
                                    title="Edit">
                                    <i class="fa fa-lg fa-fw fa-pen"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
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
            {{ $users->links() }}
        </div>
    </div>

    {{-- Sync Modal --}}
    <div class="modal fade" id="syncModal" tabindex="-1" role="dialog" aria-labelledby="syncModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="syncModalLabel">Sync Users from HRS</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-info" id="btnFetchHrs">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh Data from HRS
                        </button>
                        <span id="fetchLoading" class="ml-2" style="display:none;">
                            <i class="fas fa-spinner fa-spin"></i> Fetching...
                        </span>
                        <span id="fetchStatus" class="ml-2 text-muted"></span>
                    </div>

                    <div class="mb-2">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by NIK or Name..."
                            style="display:none;">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-striped" id="syncTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                                    <th>NIK</th>
                                    <th>Name</th>
                                    <th>Unit</th>
                                    <th>Site</th>
                                </tr>
                            </thead>
                            <tbody id="syncTableBody">
                                {{-- Rows will be populated via JS --}}
                            </tbody>
                        </table>

                        {{-- Pagination Controls --}}
                        <div id="paginationControls" class="d-flex justify-content-between align-items-center mt-2"
                            style="display:none !important;">
                            <div>
                                Showing <span id="pageStart">0</span> to <span id="pageEnd">0</span> of <span
                                    id="totalItems">0</span> entries
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="prevPage"
                                    disabled>Previous</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="nextPage"
                                    disabled>Next</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <form action="{{ route('users.sync') }}" method="POST" id="syncForm">
                            @csrf
                            <button type="submit" class="btn btn-primary" id="btnConfirmSync" disabled>
                                <i class="fas fa-sync mr-1"></i> Sync Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @stop

        @section('js')
        <script>
            $(document).ready(function () {
                let employeesData = [];
                let filteredData = [];
                let currentPage = 1;
                const itemsPerPage = 10;

                // Auto-fetch when modal opens
                $('#syncModal').on('shown.bs.modal', function () {
                    // Auto-fetch only if data hasn't been loaded yet
                    if (employeesData.length === 0) {
                        $('#btnFetchHrs').trigger('click');
                    }
                });

                function renderTable() {
                    const start = (currentPage - 1) * itemsPerPage;
                    const end = start + itemsPerPage;
                    const pageItems = filteredData.slice(start, end);
                    const $tbody = $('#syncTableBody');

                    $tbody.empty();

                    pageItems.forEach(function (emp) {
                        const isChecked = emp.selected ? 'checked' : '';
                        var row = `<tr>
                        <td class="text-center">
                            <input type="checkbox" class="emp-checkbox" value="${emp.nik}" ${isChecked}>
                        </td>
                        <td>${emp.nik}</td>
                        <td>${emp.full_name}</td>
                        <td>${emp.unit_name}</td>
                        <td>${emp.site_code}</td>
                    </tr>`;
                        $tbody.append(row);
                    });

                    // Update Pagination Info
                    $('#pageStart').text(filteredData.length > 0 ? start + 1 : 0);
                    $('#pageEnd').text(Math.min(end, filteredData.length));
                    $('#totalItems').text(filteredData.length);

                    // Update Buttons
                    $('#prevPage').prop('disabled', currentPage === 1);
                    $('#nextPage').prop('disabled', end >= filteredData.length);

                    // Update Select All Checkbox state for current page
                    const allSelected = pageItems.every(emp => emp.selected);
                    $('#selectAll').prop('checked', pageItems.length > 0 && allSelected);
                }

                // Search Event
                $('#searchInput').on('keyup', function () {
                    const term = $(this).val().toLowerCase();
                    filteredData = employeesData.filter(emp =>
                        (emp.nik && emp.nik.toLowerCase().includes(term)) ||
                        (emp.full_name && emp.full_name.toLowerCase().includes(term))
                    );
                    currentPage = 1;
                    renderTable();
                });

                // Fetch Data
                $('#btnFetchHrs').click(function () {
                    var $btn = $(this);
                    var $loading = $('#fetchLoading');
                    var $status = $('#fetchStatus');
                    var $table = $('#syncTable');
                    var $pagination = $('#paginationControls');
                    var $searchInput = $('#searchInput');
                    var $btnConfirm = $('#btnConfirmSync');

                    $btn.prop('disabled', true);
                    $loading.show();
                    $status.text('');
                    $table.hide();
                    $pagination.hide();
                    $searchInput.hide();
                    $btnConfirm.prop('disabled', true);

                    $.ajax({
                        url: "{{ env('HRS_BASE_URL') }}/sync/employees",
                        method: "GET",
                        headers: {
                            'x-api-key': "{{ env('HRS_API_KEY') }}",
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            $loading.hide();
                            $btn.prop('disabled', false);

                            if (response.length > 0) {
                                // Initialize data with 'selected' property
                                employeesData = response.map(emp => ({ ...emp, selected: false }));
                                filteredData = employeesData; // Initialize filtered data
                                currentPage = 1;

                                $status.text('Found ' + response.length + ' employees.');
                                $table.show();
                                $pagination.css('display', 'flex'); // Force flex display
                                $searchInput.show();
                                renderTable();
                                $btnConfirm.prop('disabled', false);
                            } else {
                                $status.text('No employees found.');
                                employeesData = [];
                                filteredData = [];
                                renderTable();
                            }
                        },
                        error: function (xhr) {
                            $loading.hide();
                            $btn.prop('disabled', false);
                            $status.text('Error: ' + (xhr.responseJSON?.error || xhr.statusText || 'Unknown error'));
                            console.error(xhr);
                        }
                    });
                });

                // Pagination Events
                $('#prevPage').click(function () {
                    if (currentPage > 1) {
                        currentPage--;
                        renderTable();
                    }
                });

                $('#nextPage').click(function () {
                    if ((currentPage * itemsPerPage) < filteredData.length) {
                        currentPage++;
                        renderTable();
                    }
                });

                // Checkbox Events
                // Handle Select All (Current Page)
                $('#selectAll').change(function () {
                    const isChecked = $(this).is(':checked');
                    const start = (currentPage - 1) * itemsPerPage;
                    const end = start + itemsPerPage;

                    // Update state in data source for current page items (from filteredData)
                    for (let i = start; i < end && i < filteredData.length; i++) {
                        filteredData[i].selected = isChecked;
                    }
                    renderTable();
                });

                // Handle Individual Checkbox
                $(document).on('change', '.emp-checkbox', function () {
                    const nik = $(this).val();
                    const isChecked = $(this).is(':checked');
                    const emp = employeesData.find(e => e.nik === nik);
                    if (emp) {
                        emp.selected = isChecked;
                    }

                    // Update Select All Checkbox logic
                    const start = (currentPage - 1) * itemsPerPage;
                    const end = start + itemsPerPage;
                    const pageItems = filteredData.slice(start, end);
                    const allSelected = pageItems.every(e => e.selected);
                    $('#selectAll').prop('checked', allSelected);
                });

                // Sync Submit
                $('#btnConfirmSync').click(function (e) {
                    e.preventDefault();

                    // Collect selected employees with full data
                    const selectedEmployees = employeesData.filter(emp => emp.selected);

                    if (selectedEmployees.length === 0) {
                        alert('Please select at least one user to sync.');
                        return;
                    }

                    if (!confirm(`Are you sure you want to sync ${selectedEmployees.length} selected users?`)) {
                        return;
                    }

                    // Add selected employees data to form
                    const $form = $('#syncForm');

                    // Remove existing hidden inputs if any
                    $form.find('input[name="employees_data"]').remove();

                    // Send full employee data as JSON
                    $form.append(`<input type="hidden" name="employees_data" value='${JSON.stringify(selectedEmployees)}'>`);

                    // Show loading state
                    var $btn = $(this);
                    $btn.prop('disabled', true);
                    $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Syncing...');

                    $form.submit();
                });
            });
        </script>
        @stop