@extends('adminlte::page')

@section('title', 'Request Details')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Request Details: {{ $procurement->code }}</h1>
    <a href="{{ url()->previous() == url()->current() ? route('procurement.index') : url()->previous() }}"
        class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Details</h3>
                    <div class="card-tools">
                        @php
                            $statusColors = [
                                'submitted' => 'info',
                                'approved_by_manager' => 'primary',
                                'approved_by_budgeting' => 'primary',
                                'approved_by_dir_company' => 'primary',
                                'approved_by_fin_mgr_holding' => 'primary',
                                'approved_by_fin_dir_holding' => 'primary',
                                'approved_by_gen_dir_holding' => 'primary',
                                'processing' => 'warning',
                                'completed' => 'success',
                                'rejected' => 'danger',
                            ];
                            $statusColor = $statusColors[$procurement->status] ?? 'secondary';
                        @endphp
                        <span
                            class="badge badge-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $procurement->status)) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Company:</strong> {{ $procurement->company->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Unit:</strong> {{ $procurement->unit->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Requester:</strong> {{ $procurement->user->name }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Date:</strong>
                                {{ $procurement->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Total Pengajuan:</strong>
                                <span class="badge badge-success" style="font-size: 1.1em;">
                                    Rp {{ number_format($procurement->total_amount, 2, ',', '.') }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if ($procurement->notes)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p class="mb-2"><strong>Catatan:</strong></p>
                                <p class="text-muted">{{ $procurement->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Tipe Permohonan:</strong>
                                <span
                                    class="badge badge-{{ $procurement->request_type == 'aset' ? 'success' : 'info' }}">
                                    {{ ucfirst($procurement->request_type) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Kategori:</strong>
                                @if ($procurement->is_medical)
                                    <span class="badge badge-warning">Medis</span>
                                @else
                                    <span class="badge badge-secondary">Non Medis</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($procurement->is_cito)
                        <div class="alert alert-warning border-left-warning mb-3" style="border-left: 4px solid #ffc107;">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> PERMOHONAN CITO
                                (URGENT)</h5>
                            <hr>
                            <p class="mb-0"><strong>Alasan CITO:</strong></p>
                            <p class="mb-0">{{ $procurement->cito_reason }}</p>
                        </div>
                    @endif

                    @if ($procurement->document_path || $procurement->documents->count() > 0)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p class="mb-2"><strong>Supporting Documents:</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>File Name</th>
                                                <th style="width: 100px;">Size</th>
                                                <th style="width: 120px;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Legacy Document --}}
                                            @if ($procurement->document_path)
                                                @php
                                                    $ext = strtolower(pathinfo($procurement->document_path, PATHINFO_EXTENSION));
                                                    $icon = 'fa-file';
                                                    if (in_array($ext, ['pdf']))
                                                        $icon = 'fa-file-pdf text-danger';
                                                    elseif (in_array($ext, ['doc', 'docx']))
                                                        $icon = 'fa-file-word text-primary';
                                                    elseif (in_array($ext, ['xls', 'xlsx']))
                                                        $icon = 'fa-file-excel text-success';
                                                    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                                        $icon = 'fa-file-image text-purple';
                                                @endphp
                                                <tr>
                                                    <td class="text-center"><i class="fas {{ $icon }}"></i></td>
                                                    <td>
                                                        {{ basename($procurement->document_path) }}
                                                        <span class="badge badge-secondary ml-1">Legacy</span>
                                                    </td>
                                                    <td>-</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-info btn-view-document"
                                                            data-url="{{ Storage::disk('s3')->temporaryUrl($procurement->document_path, now()->addMinutes(20)) }}"
                                                            data-type="{{ $ext }}" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="{{ Storage::disk('s3')->temporaryUrl($procurement->document_path, now()->addMinutes(20)) }}"
                                                            class="btn btn-xs btn-secondary" download title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        @if($procurement->user_id == Auth::id() && $procurement->status == 'submitted')
                                                            <button type="button" class="btn btn-xs btn-danger btn-delete-document"
                                                                data-url="{{ route('procurement.legacy-document.delete', $procurement->hashid) }}"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- New Documents --}}
                                            @foreach($procurement->documents as $doc)
                                                @php
                                                    $ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                                                    $icon = 'fa-file';
                                                    if (in_array($ext, ['pdf']))
                                                        $icon = 'fa-file-pdf text-danger';
                                                    elseif (in_array($ext, ['doc', 'docx']))
                                                        $icon = 'fa-file-word text-primary';
                                                    elseif (in_array($ext, ['xls', 'xlsx']))
                                                        $icon = 'fa-file-excel text-success';
                                                    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                                        $icon = 'fa-file-image text-purple';
                                                @endphp
                                                <tr>
                                                    <td class="text-center"><i class="fas {{ $icon }}"></i></td>
                                                    <td>{{ $doc->file_name }}</td>
                                                    <td>{{ number_format($doc->file_size / 1024, 0) }} KB</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-info btn-view-document"
                                                            data-url="{{ Storage::disk('s3')->temporaryUrl($doc->file_path, now()->addMinutes(20)) }}"
                                                            data-type="{{ $doc->mime_type ?? $ext }}" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="{{ Storage::disk('s3')->temporaryUrl($doc->file_path, now()->addMinutes(20)) }}"
                                                            class="btn btn-xs btn-secondary" download title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        @if($procurement->user_id == Auth::id() && $procurement->status == 'submitted')
                                                            <button type="button" class="btn btn-xs btn-danger btn-delete-document"
                                                                data-url="{{ route('procurement.documents.delete', $doc->id) }}"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <h5 class="mb-3">
                        Items
                        @if($procurement->status == 'processing')
                            <span class="badge badge-info ml-2">
                                <i class="fas fa-clipboard-check"></i>
                                {{ $procurement->items->where('is_checked', true)->count() }} /
                                {{ $procurement->items->count() }} Checked
                            </span>
                        @endif
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    @if($procurement->status == 'processing' && Auth::user()->role == 'purchasing')
                                        <th width="50">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </th>
                                    @endif
                                    <th>Name</th>
                                    <th>Spec</th>
                                    <th>Qty</th>
                                    <th>Est. Price</th>
                                    <th>Unit</th>
                                    <th>Subtotal</th>
                                    <th>Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($procurement->items as $item)
                                    <tr class="{{ $item->is_checked ? 'table-success' : '' }}"
                                        id="item-row-{{ $item->id }}">
                                        @if($procurement->status == 'processing' && Auth::user()->role == 'purchasing')
                                            <td class="text-center" style="vertical-align: middle;">
                                                <button type="button"
                                                    class="btn {{ $item->is_checked ? 'btn-success' : 'btn-default' }} toggle-check-btn"
                                                    data-item-id="{{ $item->id }}"
                                                    style="width: 28px; height: 28px; padding: 0; border-radius: 4px; border: 2px solid #28a745; transition: all 0.2s;"
                                                    title="{{ $item->is_checked ? 'Uncheck item' : 'Check item' }}">
                                                    <i class="fas fa-check"
                                                        style="{{ $item->is_checked ? '' : 'display: none;' }} font-size: 0.8rem;"></i>
                                                </button>
                                            </td>
                                        @endif
                                        <td>
                                            {{ $item->name }}
                                            @if($item->is_checked)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ $item->checkedBy->name ?? '-' }}
                                                    <br>
                                                    <i class="fas fa-clock"></i>
                                                    {{ $item->checked_at ? $item->checked_at->format('d M Y H:i') : '-' }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $item->specification ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-right">Rp
                                            {{ number_format($item->estimated_price, 2, ',', '.') }}
                                        </td>
                                        <td>{{ $item->unit }}</td>
                                        <td class="text-right">Rp
                                            {{ number_format($item->subtotal, 2, ',', '.') }}
                                        </td>
                                        <td>{{ $item->budget_info ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-info">
                                <tr>
                                    <td colspan="{{ $procurement->status == 'processing' && Auth::user()->role == 'purchasing' ? 5 : 4 }}"
                                        class="text-right"><strong>Total Pengajuan:</strong></td>
                                    <td class="text-right"><strong>Rp
                                            {{ number_format($procurement->total_amount, 2, ',', '.') }}</strong>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Approval History</h3>
                </div>
                <div class="card-body">
                    <ul>
                        @foreach ($procurement->logs as $log)
                            <li>
                                <strong>{{ $log->user->name }}</strong> ({{ $log->user->role }}) -
                                {{ $log->action }}
                                <br>
                                <small>{{ $log->created_at }}</small>
                                @if ($log->note)
                                    <p class="text-muted">{{ $log->note }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    @php
                        $user = Auth::user();
                        $canApprove = false;
                        $role = $user->role;
                        $status = $procurement->status;

                        if ($procurement->request_type == 'nonaset' && $procurement->total_amount < 1000000) {
                            $flow = [
                                'submitted' => 'manager',
                                'approved_by_manager' => 'budgeting',
                                'approved_by_budgeting' => 'purchasing',
                                'processing' => 'purchasing',
                            ];
                        } else {
                            $flow = [
                                'submitted' => 'manager',
                                'approved_by_manager' => 'budgeting',
                                'approved_by_budgeting' => 'director_company',
                                'approved_by_dir_company' => 'finance_manager_holding',
                                'approved_by_fin_mgr_holding' => 'finance_director_holding',
                                'approved_by_fin_dir_holding' => 'general_director_holding',
                                'approved_by_gen_dir_holding' => 'purchasing',
                                'processing' => 'purchasing',
                            ];
                        }

                        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

                        if (isset($flow[$status]) && ($flow[$status] == $role || $role == 'super_admin')) {
                            if (in_array($role, $holdingRoles)) {
                                $canApprove = true;
                            } else {
                                // Non-holding roles must be in the same company
                                if ($user->company_id == $procurement->company_id) {
                                    if ($role == 'manager') {
                                        $canApprove = ($user->unit_id == $procurement->unit_id);
                                    } else {
                                        $canApprove = true;
                                    }
                                }
                            }
                        }
                    @endphp

                    @if ($procurement->status == 'submitted' && $procurement->user_id == Auth::id())
                        <a href="{{ route('procurement.edit', $procurement->hashid) }}" class="btn btn-warning w-100 mb-3">
                            <i class="fas fa-edit"></i> Edit Request
                        </a>
                    @endif

                    @if ($canApprove)
                        <form action="{{ route('procurement.approve', $procurement->hashid) }}" method="POST" class="mb-2">
                            @csrf
                            <div class="form-group mb-2">
                                <textarea name="note" class="form-control" placeholder="Reason/Note" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Approve / Progress</button>
                        </form>

                        <form action="{{ route('procurement.reject', $procurement->hashid) }}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <textarea name="note" class="form-control" placeholder="Rejection Reason"
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Reject</button>
                        </form>
                    @else
                        <p class="text-muted">No actions available for you.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>




{{-- Document Preview Modal --}}
<div class="modal fade" id="documentPreviewModal" tabindex="-1" role="dialog"
    aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="height: 95vh;">
        <div class="modal-content h-100">
            <div class="modal-header">
                <h5 class="modal-title" id="documentPreviewModalLabel">Document Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center"
                id="documentPreviewContainer" style="overflow: auto;">
                {{-- Content will be loaded here --}}
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function () {
            // Document Preview Handler
            $('.btn-view-document').on('click', function () {
                const btn = $(this);
                const url = btn.data('url');
                const type = String(btn.data('type')).toLowerCase();
                const container = $('#documentPreviewContainer');
                const modal = $('#documentPreviewModal');

                container.empty();
                modal.modal('show');

                let content = '';

                if (type.includes('image') || ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(type.split('/').pop())) {
                    content = `<img src="${url}" class="img-fluid" style="max-height: 100%; width: auto;">`;
                } else if (type.includes('pdf')) {
                    content = `<embed src="${url}" type="application/pdf" width="100%" height="100%">`;
                } else if (['xls', 'xlsx'].includes(type.split('/').pop()) || type.includes('excel') || type.includes('spreadsheet')) {
                    content = `<div id="excel-preview-container" style="background: white; padding: 20px; width: 100%; height: 100%; overflow: auto;">
                                      <div class="text-center p-3"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading Spreadsheet...</div>
                                   </div>`;

                    // Fetch and render Excel file
                    fetch(url)
                        .then(res => res.arrayBuffer())
                        .then(ab => {
                            const wb = XLSX.read(ab, { type: 'array' });
                            const wsname = wb.SheetNames[0];
                            const ws = wb.Sheets[wsname];
                            const html = XLSX.utils.sheet_to_html(ws, { id: 'excel-table', editable: false });

                            $('#excel-preview-container').html(`
                                    <div class="d-flex justify-content-between mb-2">
                                        <h5 class="text-dark">Sheet: ${wsname}</h5>
                                        <a href="${url}" class="btn btn-sm btn-primary" download>Download Original</a>
                                    </div>
                                    <div class="table-responsive bg-white">
                                        ${html}
                                    </div>
                                `);

                            // Basic styling for the generated table
                            $('#excel-table').addClass('table table-bordered table-striped table-sm text-dark');
                        })
                        .catch(err => {
                            console.error(err);
                            $('#excel-preview-container').html(`
                                    <div class="text-center text-danger p-5">
                                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                        <h4>Failed to load Excel file</h4>
                                        <p>${err.message}</p>
                                        <a href="${url}" class="btn btn-primary mt-2" download>Download File</a>
                                    </div>
                                `);
                        });

                } else {
                    content = `
                                                                    <div class="text-center text-white p-5">
                                                                        <i class="fas fa-file-download fa-5x mb-4 text-muted"></i>
                                                                        <h4>Preview not available</h4>
                                                                        <p class="mb-4">This file type cannot be previewed directly.</p>
                                                                        <a href="${url}" class="btn btn-primary" download>
                                                                            <i class="fas fa-download mr-1"></i> Download File
                                                                        </a>
                                                                    </div>
                                                                `;
                }

                container.html(content);
            });

            // Document Delete Handler
            $('.btn-delete-document').on('click', function () {
                const btn = $(this);
                const url = btn.data('url');
                const row = btn.closest('tr');

                Swal.fire({
                    title: 'Delete Document?',
                    text: "This action cannot be undone!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        btn.prop('disabled', true);

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.success,
                                        'success'
                                    );
                                    row.fadeOut(300, function () {
                                        $(this).remove();
                                        if ($('table tr.btn-delete-document').length === 0 && $('#itemsTable tr').length > 0) {
                                            // Handle empty state
                                        }
                                    });
                                } else {
                                    Swal.fire('Error!', response.error || 'Failed to delete', 'error');
                                    btn.prop('disabled', false);
                                }
                            },
                            error: function (xhr) {
                                let msg = 'Failed to delete document';
                                if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                                Swal.fire('Error!', msg, 'error');
                                btn.prop('disabled', false);
                            }
                        });
                    }
                });
            });

            // Handle checklist toggle
            $('.toggle-check-btn').on('click', function () {
                const button = $(this);
                const itemId = button.data('item-id');
                const row = $('#item-row-' + itemId);

                // Disable button during request
                button.prop('disabled', true);

                $.ajax({
                    url: '{{ url("/procurement/items") }}/' + itemId + '/toggle-check',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            // Update button appearance
                            if (response.is_checked) {
                                button.removeClass('btn-default').addClass('btn-success');
                                button.find('i').show();
                                button.attr('title', 'Uncheck item');
                                row.addClass('table-success');

                                // Update item name cell with check info
                                const nameCell = row.find('td:eq(1)');
                                const itemName = nameCell.contents().filter(function () {
                                    return this.nodeType === 3;
                                }).text().trim();

                                nameCell.html(`
                                                            ${itemName}
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-user"></i> ${response.checked_by}
                                                                <br>
                                                                <i class="fas fa-clock"></i> ${response.checked_at}
                                                            </small>
                                                        `);
                            } else {
                                button.removeClass('btn-success').addClass('btn-default');
                                button.find('i').hide();
                                button.attr('title', 'Check item');
                                row.removeClass('table-success');

                                // Remove check info from item name cell
                                const nameCell = row.find('td:eq(1)');
                                const itemName = nameCell.contents().filter(function () {
                                    return this.nodeType === 3;
                                }).text().trim();
                                nameCell.text(itemName);
                            }

                            // Update the badge counter
                            updateCheckCounter();

                            // Show success toast
                            toastr.success(response.is_checked ? 'Item checked successfully' : 'Item unchecked successfully');
                        }

                        // Re-enable button
                        button.prop('disabled', false);
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to toggle item check';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        toastr.error(errorMessage);

                        // Re-enable button
                        button.prop('disabled', false);
                    }
                });
            });

            function updateCheckCounter() {
                const totalItems = {{ $procurement->items->count() }};
                const checkedItems = $('.toggle-check-btn.btn-success').length;
                $('.badge.badge-info').html(`
                                                                                <i class="fas fa-clipboard-check"></i>
                                                                                ${checkedItems} / ${totalItems} Checked
                                                                            `);
            }
        });
    </script>
@endpush
@stop