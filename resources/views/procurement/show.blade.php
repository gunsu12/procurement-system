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
                            <span class="badge badge-primary">{{ $procurement->status }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Unit:</strong> {{ $procurement->unit->name }}</p>
                        <p><strong>Requester:</strong> {{ $procurement->user->name }}</p>
                        <p><strong>Date:</strong> {{ $procurement->created_at }}</p>
                        <p><strong>Manager Nominal:</strong> {{ number_format($procurement->manager_nominal) }}</p>
                        @if ($procurement->document_path)
                            <p><strong>Supporting Document:</strong>
                                <a href="{{ asset('storage/' . $procurement->document_path) }}" target="_blank"
                                    class="btn btn-sm btn-info">
                                    <i class="fas fa-file-download"></i> Download / View
                                </a>
                            </p>
                        @endif

                        <h5>Items</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Spec</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($procurement->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->specification }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->budget_info }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

                        @if ($canApprove)
                            <form action="{{ route('procurement.approve', $procurement->id) }}" method="POST"
                                class="mb-2">
                                @csrf
                                <div class="form-group mb-2">
                                    <textarea name="note" class="form-control" placeholder="Reason/Note" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Approve / Progress</button>
                            </form>

                            <form action="{{ route('procurement.reject', $procurement->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-2">
                                    <textarea name="note" class="form-control" placeholder="Rejection Reason" required></textarea>
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
@stop
