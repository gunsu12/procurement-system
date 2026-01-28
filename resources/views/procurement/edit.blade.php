@extends('adminlte::page')

@section('title', 'Edit Permohonan')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Edit Permohonan Pengadaan: {{ $procurement->code }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></li>
        </ol>
    </div>
</div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('procurement.update', $procurement->hashid) }}" method="POST" id="editForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                    
                    <div class="form-group mb-3">
                        <label>Catatan / Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Catatan tambahan untuk permohonan ini (opsional)">{{ old('notes', $procurement->notes) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Tipe Permohonan <span class="text-danger">*</span></label>
                                <select name="request_type" class="form-control" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="aset" {{ old('request_type', $procurement->request_type) == 'aset' ? 'selected' : '' }}>Aset</option>
                                    <option value="nonaset" {{ old('request_type', $procurement->request_type) == 'nonaset' ? 'selected' : '' }}>Non Aset</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Kategori</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_medical" id="isMedical"
                                        value="1" {{ old('is_medical', $procurement->is_medical) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isMedical">
                                        Medis
                                    </label>
                                </div>
                                <small class="text-muted">Centang jika permohonan ini untuk keperluan medis</small>
                            </div>
                        </div>
                    </div>

                    <div class="card card-warning mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Permohonan CITO (Urgent)</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_cito" id="isCito"
                                    value="1" {{ old('is_cito', $procurement->is_cito) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isCito">
                                    <strong>Tandai sebagai CITO (Urgent)</strong>
                                </label>
                            </div>
                            <div class="form-group" id="citoReasonGroup" style="{{ old('is_cito', $procurement->is_cito) ? '' : 'display: none;' }}">
                                <label>Alasan CITO <span class="text-danger">*</span></label>
                                <textarea name="cito_reason" id="citoReason" class="form-control" rows="3"
                                    placeholder="Jelaskan alasan mengapa permohonan ini bersifat urgent/cito...">{{ old('cito_reason', $procurement->cito_reason) }}</textarea>
                                <small class="text-muted">Wajib diisi jika permohonan ditandai sebagai CITO</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Dokumen Pendukung</label>
                        
                        @if ($procurement->document_path || $procurement->documents->count() > 0)
                            <div class="table-responsive mb-3">
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
                                                $ext = strtolower(pathinfo($procurement->document_path, PATHINFO_EXTENSION));
                                                $icon = 'fa-file';
                                                if (in_array($ext, ['pdf'])) $icon = 'fa-file-pdf text-danger';
                                                elseif (in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                                elseif (in_array($ext, ['xls', 'xlsx'])) $icon = 'fa-file-excel text-success';
                                                elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'fa-file-image text-purple';
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
                                                        data-url="{{ asset('storage/' . $procurement->document_path) }}"
                                                        data-type="{{ $ext }}"
                                                        title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ asset('storage/' . $procurement->document_path) }}" 
                                                       class="btn btn-xs btn-secondary" download title="Unduh">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    @if($procurement->user_id == Auth::id() && $procurement->status == 'submitted')
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
                                        @foreach($procurement->documents as $doc)
                                            @php
                                                $ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                                                $icon = 'fa-file';
                                                if (in_array($ext, ['pdf'])) $icon = 'fa-file-pdf text-danger';
                                                elseif (in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                                elseif (in_array($ext, ['xls', 'xlsx'])) $icon = 'fa-file-excel text-success';
                                                elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'fa-file-image text-purple';
                                            @endphp
                                            <tr>
                                                <td class="text-center"><i class="fas {{ $icon }}"></i></td>
                                                <td>{{ $doc->file_name }}</td>
                                                <td>{{ number_format($doc->file_size / 1024, 0) }} KB</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-info btn-view-document"
                                                        data-url="{{ asset('storage/' . $doc->file_path) }}"
                                                        data-type="{{ $doc->mime_type ?? $ext }}"
                                                        title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" 
                                                       class="btn btn-xs btn-secondary" download title="Unduh">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    @if($procurement->user_id == Auth::id() && $procurement->status == 'submitted')
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
                        @endif

                        <label>Unggah Dokumen Baru</label>
                        <input type="file" name="document[]" class="form-control" multiple>
                        <small class="text-muted">Tipe yang diizinkan: pdf, doc, docx, xls, xlsx, jpg, png. Maks: 10MB per file. Anda dapat memilih beberapa file.</small>
                    </div>

                    <h4>Daftar Barang</h4>
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Spesifikasi</th>
                                <th width="100px">Jumlah</th>
                                <th>Est. Harga</th>
                                <th width="100px">Satuan</th>
                                <th>Subtotal</th>
                                <th>Info Anggaran</th>
                                <th><button type="button" class="btn btn-sm btn-success" id="addItem">+</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(old('items', $procurement->items) as $index => $item)
                            <tr>
                                <td><input type="text" name="items[{{ $index }}][name]" class="form-control" value="{{ $item['name'] ?? '' }}" required></td>
                                <td><input type="text" name="items[{{ $index }}][specification]" class="form-control" value="{{ $item['specification'] ?? '' }}"></td>
                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control item-qty" value="{{ $item['quantity'] ?? '' }}" min="1" required></td>
                                <td><input type="number" name="items[{{ $index }}][estimated_price]" class="form-control item-price" step="0.01" value="{{ $item['estimated_price'] ?? '' }}" required></td>
                                <td><input type="text" name="items[{{ $index }}][unit]" class="form-control" value="{{ $item['unit'] ?? '' }}" placeholder="Pcs/Set" required></td>
                                <td class="item-subtotal text-right">Rp 0</td>
                                <td><input type="text" name="items[{{ $index }}][budget_info]" class="form-control" value="{{ $item['budget_info'] ?? '' }}"></td>
                                <td>
                                    @if($index > 0)
                                        <button type="button" class="btn btn-danger btn-sm remove-row">x</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="5" class="text-right"><strong>Total Pengajuan:</strong></td>
                                <td colspan="3"><strong id="totalAmount">Rp 0</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('procurement.show', $procurement->hashid) }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Perbarui Permohonan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Document Preview Modal --}}
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" role="dialog" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document" style="height: 95vh;">
            <div class="modal-content h-100">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentPreviewModalLabel">Pratinjau Dokumen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center" id="documentPreviewContainer" style="overflow: auto;">
                    {{-- Content will be loaded here --}}
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function () {
             // Document Preview Handler
             $('.btn-view-document').on('click', function() {
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
                } else {
                    content = `
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
            $('.btn-delete-document').on('click', function() {
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
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Terhapus!',
                                        response.success,
                                        'success'
                                    );
                                    row.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    Swal.fire('Error!', response.error || 'Gagal menghapus', 'error');
                                    btn.prop('disabled', false);
                                }
                            },
                            error: function(xhr) {
                                let msg = 'Gagal menghapus dokumen';
                                if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                                Swal.fire('Error!', msg, 'error');
                                btn.prop('disabled', false);
                            }
                        });
                    }
                });
            });
        });

        let itemIndex = {{ count(old('items', $procurement->items)) }};

        // Calculate total
        function calculateTotal() {
            let grandTotal = 0;
            $('#itemsTable tbody tr').each(function () {
                let qty = parseFloat($(this).find('.item-qty').val()) || 0;
                let price = parseFloat($(this).find('.item-price').val()) || 0;
                let subtotal = qty * price;

                $(this).find('.item-subtotal').text('Rp ' + subtotal.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                grandTotal += subtotal;
            });

            $('#totalAmount').text('Rp ' + grandTotal.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }

        // Initialize total
        $(document).ready(function () {
            calculateTotal();
        });

        // Add item row
        $('#addItem').click(function () {
            let html = `<tr>
            <td><input type="text" name="items[${itemIndex}][name]" class="form-control" required></td>
            <td><input type="text" name="items[${itemIndex}][specification]" class="form-control"></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" value="1" min="1" required></td>
            <td><input type="number" name="items[${itemIndex}][estimated_price]" class="form-control item-price" step="0.01" value="0" required></td>
            <td><input type="text" name="items[${itemIndex}][unit]" class="form-control" placeholder="Pcs/Set" required></td>
            <td class="item-subtotal text-right">Rp 0</td>
            <td><input type="text" name="items[${itemIndex}][budget_info]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">x</button></td>
        </tr>`;
            $('#itemsTable tbody').append(html);
            itemIndex++;
            calculateTotal();
        });

        // Remove row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            calculateTotal();
        });

        // Auto calculate on input change
        $(document).on('input', '.item-price, .item-qty', function () {
            calculateTotal();
        });

        // CITO checkbox toggle
        $('#isCito').change(function() {
            if ($(this).is(':checked')) {
                $('#citoReasonGroup').slideDown();
                $('#citoReason').prop('required', true);
            } else {
                $('#citoReasonGroup').slideUp();
                $('#citoReason').prop('required', false).val('');
            }
        });

        // Form validation
        $('#editForm').submit(function(e) {
            if ($('#isCito').is(':checked') && !$('#citoReason').val().trim()) {
                e.preventDefault();
                alert('Alasan CITO wajib diisi jika permohonan ditandai sebagai CITO!');
                $('#citoReason').focus();
                return false;
            }

            // Prevent double submit
            let btn = $(this).find('button[type="submit"]');
            if (btn.prop('disabled')) {
                e.preventDefault();
                return false;
            }
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memperbarui...');
        });
    </script>
@stop
