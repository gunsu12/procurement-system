@extends('adminlte::page')

@section('title', 'Detail Permohonan')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Detail Permohonan: {{ $procurement->code }}</h1>
    @php
        $backUrl = request('back_url') ? urldecode(request('back_url')) : (url()->previous() == url()->current() ? route('procurement.index') : url()->previous());
    @endphp
    <a href="{{ $backUrl }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
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
                    <h3 class="card-title">Detail</h3>
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
                                'cancelled' => 'secondary',
                            ];
                            $statusColor = $statusColors[$procurement->status] ?? 'secondary';
                        @endphp
                        <span
                            class="badge badge-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $procurement->status)) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4 col-md-3">Perusahaan</dt>
                        <dd class="col-sm-8 col-md-9">{{ $procurement->company->name ?? '-' }}</dd>

                        <dt class="col-sm-4 col-md-3">Unit</dt>
                        <dd class="col-sm-8 col-md-9">{{ $procurement->unit->name }}</dd>

                        <dt class="col-sm-4 col-md-3">Pemohon</dt>
                        <dd class="col-sm-8 col-md-9">{{ $procurement->user->name }}</dd>

                        <dt class="col-sm-4 col-md-3">Tanggal</dt>
                        <dd class="col-sm-8 col-md-9">{{ $procurement->created_at->format('d F Y H:i') }}</dd>

                        <dt class="col-sm-4 col-md-3">Tipe Permohonan</dt>
                        <dd class="col-sm-8 col-md-9">
                            <span class="badge badge-{{ $procurement->request_type == 'aset' ? 'success' : 'info' }}">
                                {{ ucfirst($procurement->request_type) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4 col-md-3">Kategori</dt>
                        <dd class="col-sm-8 col-md-9">
                            @if ($procurement->is_medical)
                                <span class="badge badge-warning">Medis</span>
                            @else
                                <span class="badge badge-secondary">Non Medis</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 col-md-3">Total Pengajuan</dt>
                        <dd class="col-sm-8 col-md-9">
                            <span class="text-success font-weight-bold" style="font-size: 1.1em;">
                                Rp {{ number_format($procurement->total_amount, 2, ',', '.') }}
                            </span>
                        </dd>

                        @if ($procurement->notes)
                            <dt class="col-sm-4 col-md-3">Catatan</dt>
                            <dd class="col-sm-8 col-md-9">{{ $procurement->notes }}</dd>
                        @endif
                    </dl>

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
                                <p class="mb-2"><strong>Dokumen Pendukung:</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Nama File</th>
                                                <th style="width: 100px;">Ukuran</th>
                                                <th style="width: 120px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Legacy Document --}}
                                            @if ($procurement->document_path)
                                                @php
                                                    $ext = strtolower(
                                                        pathinfo($procurement->document_path, PATHINFO_EXTENSION),
                                                    );
                                                    $icon = 'fa-file';
                                                    if (in_array($ext, ['pdf'])) {
                                                        $icon = 'fa-file-pdf text-danger';
                                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                                        $icon = 'fa-file-word text-primary';
                                                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                                        $icon = 'fa-file-excel text-success';
                                                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                        $icon = 'fa-file-image text-purple';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center"><i class="fas {{ $icon }}"></i>
                                                    </td>
                                                    <td>
                                                        {{ basename($procurement->document_path) }}
                                                        <span class="badge badge-secondary ml-1">Legacy</span>
                                                    </td>
                                                    <td>-</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-info btn-view-document"
                                                            data-url="{{ Storage::disk('s3')->temporaryUrl($procurement->document_path, now()->addMinutes(20)) }}"
                                                            data-type="{{ $ext }}" title="Lihat">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="{{ Storage::disk('s3')->temporaryUrl($procurement->document_path, now()->addMinutes(20)) }}"
                                                            class="btn btn-xs btn-secondary" download title="Unduh">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        @if ($procurement->user_id == Auth::id() && $procurement->status == 'submitted')
                                                            <button type="button" class="btn btn-xs btn-danger btn-delete-document"
                                                                data-url="{{ route('procurement.legacy-document.delete', $procurement->hashid) }}"
                                                                title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- New Documents --}}
                                            @foreach ($procurement->documents as $doc)
                                                @php
                                                    $ext = strtolower(
                                                        pathinfo($doc->file_name, PATHINFO_EXTENSION),
                                                    );
                                                    $icon = 'fa-file';
                                                    if (in_array($ext, ['pdf'])) {
                                                        $icon = 'fa-file-pdf text-danger';
                                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                                        $icon = 'fa-file-word text-primary';
                                                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                                        $icon = 'fa-file-excel text-success';
                                                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                        $icon = 'fa-file-image text-purple';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center"><i class="fas {{ $icon }}"></i>
                                                    </td>
                                                    <td>{{ $doc->file_name }}</td>
                                                    <td>{{ number_format($doc->file_size / 1024, 0) }} KB</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-info btn-view-document"
                                                            data-url="{{ Storage::disk('s3')->temporaryUrl($doc->file_path, now()->addMinutes(20)) }}"
                                                            data-type="{{ $doc->mime_type ?? $ext }}" title="Lihat">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="{{ Storage::disk('s3')->temporaryUrl($doc->file_path, now()->addMinutes(20)) }}"
                                                            class="btn btn-xs btn-secondary" download title="Unduh">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        @if ($procurement->user_id == Auth::id() && $procurement->status == 'submitted')
                                                            <button type="button" class="btn btn-xs btn-danger btn-delete-document"
                                                                data-url="{{ route('procurement.documents.delete', $doc->id) }}"
                                                                title="Hapus">
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
                        Daftar Barang
                        @if ($procurement->status == 'processing')
                            <span class="badge badge-info ml-2">
                                <i class="fas fa-clipboard-check"></i>
                                {{ $procurement->items->where('is_checked', true)->count() }} /
                                {{ $procurement->items->count() }} Diperiksa
                            </span>
                        @endif
                    </h5>
                    <!-- Desktop View (Table) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    @if ($procurement->status == 'processing' && Auth::user()->role == 'purchasing')
                                        <th width="50">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </th>
                                    @endif
                                    <th>Nama Barang</th>
                                    <th>Spesifikasi</th>
                                    <th>Jumlah</th>
                                    <th>Est. Harga</th>
                                    <th>Satuan</th>
                                    <th>Subtotal</th>
                                    <th>Anggaran</th>
                                    @if (
                                            $procurement->status == 'submitted' &&
                                            Auth::user()->role == 'manager' &&
                                            $procurement->unit->approval_by == Auth::user()->id
                                        )
                                        <th class="text-center" style="width: 80px;">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($procurement->items as $item)
                                    <tr class="{{ $item->is_checked ? 'table-success' : ($item->is_rejected ? 'table-danger' : '') }}"
                                        id="item-row-{{ $item->id }}">
                                        @if ($procurement->status == 'processing' && Auth::user()->role == 'purchasing')
                                            <td class="text-center" style="vertical-align: middle;">
                                                <button type="button"
                                                    class="btn {{ $item->is_checked ? 'btn-success' : 'btn-default' }} toggle-check-btn"
                                                    data-item-id="{{ $item->id }}"
                                                    style="width: 28px; height: 28px; padding: 0; border-radius: 4px; border: 2px solid #28a745; transition: all 0.2s;"
                                                    title="{{ $item->is_checked ? 'Hapus centang' : 'Centang item' }}">
                                                    <i class="fas fa-check"
                                                        style="{{ $item->is_checked ? '' : 'display: none;' }} font-size: 0.8rem;"></i>
                                                </button>
                                            </td>
                                        @endif
                                        <td>
                                            {{ $item->name }}
                                            @if ($item->is_checked)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ $item->checkedBy->name ?? '-' }}
                                                    <br>
                                                    <i class="fas fa-clock"></i>
                                                    {{ $item->checked_at ? $item->checked_at->format('d M Y H:i') : '-' }}
                                                </small>
                                            @endif
                                            @if ($item->is_rejected)
                                                <div class="rejection-info">
                                                    <br>
                                                    <small class="text-danger">
                                                        <i class="fas fa-times-circle"></i> Ditolak
                                                        @if ($item->rejection_note)
                                                            <br>
                                                            Catatan: {{ $item->rejection_note }}
                                                        @endif
                                                    </small>
                                                </div>
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
                                        @if (
                                                $procurement->status == 'submitted' &&
                                                Auth::user()->role == 'manager' &&
                                                $procurement->unit->approval_by == Auth::user()->id
                                            )
                                            <td class="text-center">
                                                @if (!$item->is_rejected)
                                                    <button type="button" class="btn btn-xs btn-danger btn-reject-item"
                                                        data-item-id="{{ $item->id }}" title="Tolak Item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-xs btn-secondary btn-cancel-reject-item"
                                                        data-item-id="{{ $item->id }}" title="Batal Tolak">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
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
                                    <td
                                        colspan="{{ $procurement->status == 'submitted' && Auth::user()->role == 'manager' ? 3 : 2 }}">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mobile View (Cards) -->
                    <div class="d-md-none">
                        @foreach ($procurement->items as $item)
                            <div class="card mb-3 {{ $item->is_checked ? 'border-success' : ($item->is_rejected ? 'border-danger' : '') }}"
                                id="mobile-item-row-{{ $item->id }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title font-weight-bold mb-0">{{ $item->name }}</h5>
                                        @if ($procurement->status == 'processing' && Auth::user()->role == 'purchasing')
                                            <button type="button"
                                                class="btn {{ $item->is_checked ? 'btn-success' : 'btn-default' }} toggle-check-btn btn-sm ml-2"
                                                data-item-id="{{ $item->id }}"
                                                style="width: 30px; height: 30px; border-radius: 4px; border: 2px solid #28a745;"
                                                title="{{ $item->is_checked ? 'Hapus centang' : 'Centang item' }}">
                                                <i class="fas fa-check"
                                                    style="{{ $item->is_checked ? '' : 'display: none;' }}"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if ($item->specification)
                                        <p class="text-muted small mb-2"><i class="fas fa-info-circle mr-1"></i>
                                            {{ $item->specification }}</p>
                                    @endif

                                    <div class="row small mb-2">
                                        <div class="col-6">
                                            <span class="text-muted">Jumlah:</span><br>
                                            <strong>{{ $item->quantity }} {{ $item->unit }}</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span class="text-muted">Est. Harga:</span><br>
                                            <strong>Rp
                                                {{ number_format($item->estimated_price, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                        <span>Subtotal:</span>
                                        <strong class="text-primary">Rp
                                            {{ number_format($item->subtotal, 2, ',', '.') }}</strong>
                                    </div>

                                    @if ($item->budget_info)
                                        <div class="mt-2 text-muted small">
                                            <i class="fas fa-coins mr-1"></i> Anggaran: {{ $item->budget_info }}
                                        </div>
                                    @endif

                                    @if ($item->is_checked)
                                        <div class="alert alert-success p-2 mt-2 mb-0 small">
                                            <i class="fas fa-user mr-1"></i> {{ $item->checkedBy->name ?? '-' }}
                                            <span class="float-right"><i class="fas fa-clock mr-1"></i>
                                                {{ $item->checked_at ? $item->checked_at->format('d M H:i') : '-' }}</span>
                                        </div>
                                    @endif

                                    @if ($item->is_rejected)
                                        <div class="alert alert-danger p-2 mt-2 mb-0 small">
                                            <strong><i class="fas fa-times-circle mr-1"></i> Ditolak</strong>
                                            @if ($item->rejection_note)
                                                <div class="mt-1 border-top border-danger pt-1">
                                                    {{ $item->rejection_note }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if (
                                            $procurement->status == 'submitted' &&
                                            Auth::user()->role == 'manager' &&
                                            $procurement->unit->approval_by == Auth::user()->id
                                        )
                                        <div class="mt-3 text-right border-top pt-2">
                                            @if (!$item->is_rejected)
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-reject-item btn-block"
                                                    data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-times mr-1"></i> Tolak Item
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary btn-cancel-reject-item btn-block"
                                                    data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-undo mr-1"></i> Batal Tolak
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="card bg-light border-info">
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-info">Total Pengajuan</h6>
                                <h5 class="mb-0 font-weight-bold">Rp
                                    {{ number_format($procurement->total_amount, 2, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" data-toggle="collapse"
                    data-target="#historyCollapse" aria-expanded="false" aria-controls="historyCollapse"
                    style="cursor: pointer;">
                    <h3 class="card-title">Riwayat Persetujuan</h3>
                    <i class="fas fa-chevron-right d-md-none pull-right" id="historyIcon"></i>
                </div>
                <div class="collapse" id="historyCollapse">
                    <div class="timeline timeline-inverse">
                        @foreach ($procurement->logs as $log)
                            @php
                                $icon = 'fa-info';
                                $bg = 'bg-secondary';
                                if (stripos($log->action, 'submit') !== false) {
                                    $icon = 'fa-paper-plane';
                                    $bg = 'bg-primary';
                                } elseif (stripos($log->action, 'approve') !== false) {
                                    $icon = 'fa-check';
                                    $bg = 'bg-success';
                                } elseif (stripos($log->action, 'reject') !== false) {
                                    $icon = 'fa-times';
                                    $bg = 'bg-danger';
                                } elseif (stripos($log->action, 'process') !== false) {
                                    $icon = 'fa-cog';
                                    $bg = 'bg-warning';
                                }
                            @endphp
                            <div>
                                <i class="fas {{ $icon }} {{ $bg }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i>
                                        {{ $log->created_at->format('d M H:i') }}</span>
                                    <h3 class="timeline-header border-0">
                                        <a href="#">{{ $log->user->name }}</a>
                                        <span class="text-muted"
                                            style="font-size: 0.9em;">({{ str_replace('_', ' ', $log->user->role) }})</span>
                                        <div class="mt-1">
                                            <span
                                                class="badge badge-{{ str_replace('bg-', '', $bg) }}">{{ ucfirst($log->action) }}</span>
                                        </div>
                                    </h3>
                                    @if ($log->note)
                                        <div class="timeline-body p-2 bg-light border rounded mt-2" style="font-size: 0.9em;">
                                            {{ $log->note }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @media (min-width: 768px) {
                #historyCollapse.collapse {
                    display: block !important;
                    height: auto !important;
                    visibility: visible !important;
                }
            }
        </style>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aksi</h3>
                </div>
                <div class="card-body">
                    @php
                        $user = Auth::user();
                        $canApprove = false;
                        $role = $user->role;
                        $status = $procurement->status;

                        if ($procurement->request_type == 'nonaset' && $procurement->total_amount > 1000000) {
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

                        $holdingRoles = [
                            'finance_manager_holding',
                            'finance_director_holding',
                            'general_director_holding',
                            'purchasing',
                            'super_admin',
                        ];

                        if (isset($flow[$status]) && ($flow[$status] == $role || $role == 'super_admin')) {
                            if (in_array($role, $holdingRoles)) {
                                $canApprove = true;
                            } else {
                                // Manager can approve from any company if they are the approver
                                if ($role == 'manager') {
                                    $canApprove =
                                        $procurement->unit && $procurement->unit->approval_by == $user->id;
                                } else {
                                    // Non-holding roles (except manager) must be in the same company
                                    if ($user->company_id == $procurement->company_id) {
                                        $canApprove = true;
                                    }
                                }
                            }
                        }
                    @endphp


                    @if ($procurement->status == 'submitted' && $procurement->user_id == Auth::id())
                        <a href="{{ route('procurement.edit', $procurement->hashid) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> Edit Permohonan
                        </a>
                    @endif

                    @can('cancel', $procurement)
                        <form id="form-cancel-procurement" action="{{ route('procurement.cancel', $procurement->hashid) }}"
                            method="POST">
                            @csrf
                            <button type="button" class="btn btn-danger w-100 mb-3" id="btn-cancel-procurement">
                                <i class="fas fa-ban"></i> Batalkan Permohonan
                            </button>
                        </form>
                    @endcan

                    @if ($canApprove)
                        <form action="{{ route('procurement.approve', $procurement->hashid) }}" method="POST" class="mb-2">
                            @csrf
                            <div class="form-group mb-2">
                                <textarea name="note" class="form-control" placeholder="Alasan/Catatan" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Setujui / Proses</button>
                        </form>

                        <form action="{{ route('procurement.reject', $procurement->hashid) }}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <textarea name="note" class="form-control" placeholder="Alasan Penolakan"
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Tolak</button>
                        </form>
                    @else
                        <p class="text-muted">Tidak ada aksi yang tersedia untuk Anda.</p>
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
                <h5 class="modal-title" id="documentPreviewModalLabel">Pratinjau Dokumen</h5>
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

@section('css')
@stop

@push('js')
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function () {
            // History Collapse Icon Toggle
            $('#historyCollapse').on('show.bs.collapse', function () {
                $('#historyIcon').removeClass('fa-chevron-right').addClass('fa-chevron-down');
            });
            $('#historyCollapse').on('hide.bs.collapse', function () {
                $('#historyIcon').removeClass('fa-chevron-down').addClass('fa-chevron-right');
            });

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

                if (type.includes('image') || ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(type
                    .split('/').pop())) {
                    content = `<img src="${url}" class="img-fluid" style="max-height: 100%; width: auto;">`;
                } else if (type.includes('pdf')) {
                    content = `<embed src="${url}" type="application/pdf" width="100%" height="100%">`;
                } else if (['xls', 'xlsx'].includes(type.split('/').pop()) || type.includes('excel') || type
                    .includes('spreadsheet')) {
                    content =
                        `<div id="excel-preview-container" style="background: white; padding: 20px; width: 100%; height: 100%; overflow: auto;">
                                                                                                                                                              <div class="text-center p-3"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Memuat Spreadsheet...</div>
                                                                                                                                                           </div>`;

                    // Fetch and render Excel file
                    fetch(url)
                        .then(res => res.arrayBuffer())
                        .then(ab => {
                            const wb = XLSX.read(ab, {
                                type: 'array'
                            });
                            const wsname = wb.SheetNames[0];
                            const ws = wb.Sheets[wsname];
                            const html = XLSX.utils.sheet_to_html(ws, {
                                id: 'excel-table',
                                editable: false
                            });

                            $('#excel-preview-container').html(
                                `
                                                                                                                                                            <div class="d-flex justify-content-between mb-2">
                                                                                                                                                                <h5 class="text-dark">Sheet: ${wsname}</h5>
                                                                                                                                                                <a href="${url}" class="btn btn-sm btn-primary" download>Unduh Asli</a>
                                                                                                                                                            </div>
                                                                                                                                                            <div class="table-responsive bg-white">
                                                                                                                                                                ${html}
                                                                                                                                                            </div>
                                                                                                                                                        `
                            );

                            // Basic styling for the generated table
                            $('#excel-table').addClass(
                                'table table-bordered table-striped table-sm text-dark');
                        })
                        .catch(err => {
                            console.error(err);
                            $('#excel-preview-container').html(
                                `
                                                                                                                                                            <div class="text-center text-danger p-5">
                                                                                                                                                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                                                                                                                                                <h4>Gagal memuat file Excel</h4>
                                                                                                                                                                <p>${err.message}</p>
                                                                                                                                                                <a href="${url}" class="btn btn-primary mt-2" download>Unduh File</a>
                                                                                                                                                            </div>
                                                                                                                                                        `
                            );
                        });

                } else {
                    content =
                        `
                                                                                                                                                                                            <div class="text-center text-white p-5">
                                                                                                                                                                                                <i class="fas fa-file-download fa-5x mb-4 text-muted"></i>
                                                                                                                                                                                                <h4>Pratinjau tidak tersedia</h4>
                                                                                                                                                                                                <p class="mb-4">Tipe file ini tidak dapat ditampilkan pratinjau secara langsung.</p>
                                                                                                                                                                                                <a href="${url}" class="btn btn-primary" download>
                                                                                                                                                                                                    <i class="fas fa-download mr-1"></i> Unduh File
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
                    title: 'Hapus Dokumen?',
                    text: "Tindakan ini tidak dapat dibatalkan!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
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
                                        'Terhapus!',
                                        response.success,
                                        'success'
                                    );
                                    row.fadeOut(300, function () {
                                        $(this).remove();
                                        if ($('table tr.btn-delete-document')
                                            .length === 0 && $('#itemsTable tr')
                                                .length > 0) {
                                            // Handle empty state
                                        }
                                    });
                                } else {
                                    Swal.fire('Error!', response.error ||
                                        'Gagal menghapus', 'error');
                                    btn.prop('disabled', false);
                                }
                            },
                            error: function (xhr) {
                                let msg = 'Gagal menghapus dokumen';
                                if (xhr.responseJSON && xhr.responseJSON.error) msg =
                                    xhr.responseJSON.error;
                                Swal.fire('Error!', msg, 'error');
                                btn.prop('disabled', false);
                            }
                        });
                    }
                });
            });

            // Cancel Procurement Handler
            $('#btn-cancel-procurement').on('click', function () {
                Swal.fire({
                    title: 'Batalkan Permohonan?',
                    text: "Apakah Anda yakin ingin membatalkan permohonan ini? Tindakan ini tidak dapat dibatalkan!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, batalkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.value) {
                        $('#form-cancel-procurement').submit();
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
                    url: '{{ url('/procurement/items') }}/' + itemId + '/toggle-check',
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
                                button.attr('title', 'Hapus centang');
                                row.addClass('table-success');

                                // Update item name cell with check info
                                const nameCell = row.find('td:eq(1)');
                                const itemName = nameCell.contents().filter(function () {
                                    return this.nodeType === 3;
                                }).text().trim();

                                nameCell.html(
                                    `
                                                                                                                                                                                    ${itemName}
                                                                                                                                                                                    <br>
                                                                                                                                                                                    <small class="text-muted">
                                                                                                                                                                                        <i class="fas fa-user"></i> ${response.checked_by}
                                                                                                                                                                                        <br>
                                                                                                                                                                                        <i class="fas fa-clock"></i> ${response.checked_at}
                                                                                                                                                                                    </small>
                                                                                                                                                                                `
                                );
                            } else {
                                button.removeClass('btn-success').addClass('btn-default');
                                button.find('i').hide();
                                button.attr('title', 'Centang item');
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
                            toastr.success(response.is_checked ? 'Item berhasil dicentang' :
                                'Item berhasil dihapus centangnya');
                        }

                        // Re-enable button
                        button.prop('disabled', false);
                    },
                    error: function (xhr) {
                        let errorMessage = 'Gagal mengubah status centang item';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        toastr.error(errorMessage);

                        // Re-enable button
                        button.prop('disabled', false);
                    }
                });
            });

            // Reject Item Handler
            $(document).on('click', '.btn-reject-item', function () {
                let btn = $(this);
                let itemId = btn.data('item-id');
                let row = $('#item-row-' + itemId);

                Swal.fire({
                    title: 'Tolak Item',
                    input: 'textarea',
                    inputPlaceholder: 'Alasan penolakan...',
                    inputAttributes: {
                        'aria-label': 'Alasan penolakan'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    confirmButtonColor: '#d33',
                    showLoaderOnConfirm: true,
                    preConfirm: (note) => {
                        return $.ajax({
                            url: '{{ url('/procurement/items') }}/' + itemId +
                                '/reject',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                note: note
                            }
                        }).catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error.responseJSON.error || error.responseText}`
                            );
                            throw error; // Prevent modal from closing
                        })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.value.success === true) {
                        toastr.success('Item ditolak. Memuat ulang...');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                });
            });

            // Cancel Reject Item Handler
            $(document).on('click', '.btn-cancel-reject-item', function () {
                let btn = $(this);
                let itemId = btn.data('item-id');
                let row = $('#item-row-' + itemId);

                $.ajax({
                    url: '{{ url('/procurement/items') }}/' + itemId + '/cancel-reject',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        toastr.success('Penolakan dibatalkan. Memuat ulang...');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON.error || 'Gagal');
                    }
                });
            });

            function updateCheckCounter() {
                const totalItems = {{ $procurement->items->count() }};
                const checkedItems = $('.toggle-check-btn.btn-success').length;
                $('.badge.badge-info').html(
                    `
                                                                                                                                                                                                        <i class="fas fa-clipboard-check"></i>
                                                                                                                                                                                                        ${checkedItems} / ${totalItems} Diperiksa
                                                                                                                                                                                                    `
                );
            }
        });
    </script>
@endpush
@stop