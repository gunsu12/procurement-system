@extends('adminlte::page')

@section('title', 'Edit Request')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Edit Procurement Request: {{ $procurement->code }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('procurement.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Back</a></li>
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
                        <label>Supporting Documents</label>
                        {{-- Show Legacy Document --}}
                        @if($procurement->document_path)
                            <div class="mb-2">
                                <span class="badge badge-secondary">Legacy Document</span>
                                <a href="{{ asset('storage/' . $procurement->document_path) }}" target="_blank" class="btn btn-xs btn-info">View</a>
                            </div>
                        @endif

                        {{-- Show New Documents --}}
                        @if($procurement->documents->count() > 0)
                            <div class="mb-3">
                                <label>Existing Documents:</label>
                                <ul class="list-unstyled">
                                    @foreach($procurement->documents as $doc)
                                        <li class="mb-1">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">
                                                <i class="fas fa-file"></i> {{ $doc->file_name }}
                                            </a>
                                            <small class="text-muted">({{ number_format($doc->file_size / 1024, 0) }} KB)</small>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <label>Upload New Documents</label>
                        <input type="file" name="document[]" class="form-control" multiple>
                        <small class="text-muted">Allowed types: pdf, doc, docx, xls, xlsx, jpg, png. Max: 10MB per file. You can select multiple files.</small>
                    </div>

                    <h4>Items</h4>
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Spec</th>
                                <th width="100px">Qty</th>
                                <th>Est. Price</th>
                                <th width="100px">Unit</th>
                                <th>Subtotal</th>
                                <th>Budget Info</th>
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
                        <a href="{{ route('procurement.show', $procurement->hashid) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
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
        });
    </script>
@stop
