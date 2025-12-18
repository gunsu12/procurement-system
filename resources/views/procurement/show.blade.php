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
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Unit:</strong> {{ $procurement->unit->name }}</p>
                        </div>
                        <div class="col-md-6">
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

                    @if ($procurement->document_path)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p class="mb-2"><strong>Supporting Document:</strong></p>
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                    data-target="#documentModal">
                                    <i class="fas fa-eye"></i> View Document
                                </button>
                                <a href="{{ asset('storage/' . $procurement->document_path) }}" target="_blank"
                                    class="btn btn-sm btn-secondary">
                                    <i class="fas fa-file-download"></i> Download
                                </a>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <h5 class="mb-3">Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
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
                                    <tr>
                                        <td>{{ $item->name }}</td>
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
                                    <td colspan="4" class="text-right"><strong>Total Pengajuan:</strong></td>
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
                        // Check if current user can approve based on status map (re-using logic or simple check)
                        $canApprove = false;
                        $role = $user->role;
                        $status = $procurement->status;

                        // Map status to required role
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

                        if (isset($flow[$status]) && $flow[$status] == $role) {
                            // Validation for manager/unit scope
                            if ($role == 'manager' && $user->unit_id != $procurement->unit_id) {
                                $canApprove = false;
                            } else {
                                $canApprove = true;
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

@if ($procurement->document_path)
    <div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Supporting Document:
                        {{ basename($procurement->document_path) }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="height: 80vh; overflow-y: auto;">
                    @php
                        $extension = pathinfo($procurement->document_path, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg']);
                    @endphp

                    @if ($isImage)
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $procurement->document_path) }}" class="img-fluid" alt="Document">
                        </div>
                    @else
                        <iframe src="{{ asset('storage/' . $procurement->document_path) }}" width="100%" height="100%"
                            frameborder="0"></iframe>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
@stop