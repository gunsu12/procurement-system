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
                                
                                {{-- Legacy Document --}}
                                @if ($procurement->document_path)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $procurement->document_path) }}" target="_blank"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-file"></i> View Legacy Document ({{ basename($procurement->document_path) }})
                                        </a>
                                        <a href="{{ asset('storage/' . $procurement->document_path) }}" target="_blank"
                                                class="btn btn-sm btn-secondary ml-1" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                    </div>
                                @endif

                                {{-- New Documents --}}
                                @if ($procurement->documents->count() > 0)
                                    <div class="list-group">
                                        @foreach($procurement->documents as $doc)
                                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                                <div>
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-primary">
                                                        <i class="fas fa-file-alt mr-2"></i> {{ $doc->file_name }}
                                                    </a>
                                                    <small class="text-muted ml-2">({{ number_format($doc->file_size / 1024, 0) }} KB)</small>
                                                </div>
                                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-secondary" download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
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



@push('js')
    <script>
        $(document).ready(function () {
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