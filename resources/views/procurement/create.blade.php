@extends('adminlte::page')

@section('title', 'Buat Permohonan')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Buat Permohonan Pengadaan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="btn btn-default btn-sm"><i
                        class="fas fa-arrow-left"></i> Kembali ke Dashboard</a></li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement.store') }}" method="POST" id="createForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                <div class="form-group mb-3">
                    <label>Catatan / Notes</label>
                    <textarea name="notes" class="form-control" rows="3"
                        placeholder="Catatan tambahan untuk permohonan ini (opsional)"></textarea>
                </div>

                @if(in_array(Auth::user()->role, ['super_admin', 'purchasing', 'finance_manager_holding', 'finance_director_holding', 'general_director_holding']))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Unit Pelaksana (Requesting Unit) <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-control select2" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->company->name ?? '' }} - {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih unit yang melakukan pengajuan ini</small>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Tipe Permohonan <span class="text-danger">*</span></label>
                            <select name="request_type" class="form-control" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="aset">Aset</option>
                                <option value="nonaset">Non Aset</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Kategori</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_medical" id="isMedical"
                                    value="1">
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
                            <input class="form-check-input" type="checkbox" name="is_cito" id="isCito" value="1">
                            <label class="form-check-label" for="isCito">
                                <strong>Tandai sebagai CITO (Urgent)</strong>
                            </label>
                        </div>
                        <div class="form-group" id="citoReasonGroup" style="display: none;">
                            <label>Alasan CITO <span class="text-danger">*</span></label>
                            <textarea name="cito_reason" id="citoReason" class="form-control" rows="3"
                                placeholder="Jelaskan alasan mengapa permohonan ini bersifat urgent/cito..."></textarea>
                            <small class="text-muted">Wajib diisi jika permohonan ditandai sebagai CITO</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Dokumen Pendukung</label>
                    <input type="file" name="document[]" class="form-control" multiple>
                    <small class="text-muted">Tipe yang diizinkan: pdf, doc, docx, xls, xlsx, jpg, png. Maks: 10MB per
                        file.
                        Anda dapat memilih beberapa file.</small>
                </div>
                <h4>Daftar Barang</h4>
                <div class="card bg-light shadow-sm mb-3">
                    <div class="card-body p-2">
                        <!-- Header (Desktop Only) -->
                        <div class="d-none d-md-flex row border-bottom pb-2 mb-2 font-weight-bold text-center">
                            <div class="col-md-2">Nama Barang</div>
                            <div class="col-md-2">Spesifikasi</div>
                            <div class="col-md-1">Jumlah</div>
                            <div class="col-md-1">Satuan</div>
                            <div class="col-md-2">Est. Harga</div>
                            <div class="col-md-2">Subtotal</div>
                            <div class="col-md-1">Anggaran</div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-success" id="addItem"
                                    title="Tambah Barang"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>

                        <!-- Items List Container -->
                        <div id="itemsList">
                            <!-- Item Template (Initial Row) -->
                            <div class="item-row mb-3 mb-md-0 border-bottom border-md-0 pb-3 pb-md-0">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-2 mb-2 mb-md-0">
                                        <label class="d-md-none small text-muted">Nama Barang</label>
                                        <input type="text" name="items[0][name]" class="form-control"
                                            placeholder="Nama Barang" required>
                                    </div>
                                    <div class="col-12 col-md-2 mb-2 mb-md-0">
                                        <label class="d-md-none small text-muted">Spesifikasi</label>
                                        <input type="text" name="items[0][specification]" class="form-control"
                                            placeholder="Spesifikasi">
                                    </div>
                                    <div class="col-6 col-md-1 mb-2 mb-md-0">
                                        <label class="d-md-none small text-muted">Jumlah</label>
                                        <input type="number" name="items[0][quantity]" class="form-control item-qty"
                                            value="1" min="1" required>
                                    </div>
                                    <div class="col-6 col-md-1 mb-2 mb-md-0">
                                        <label class="d-md-none small text-muted">Satuan</label>
                                        <input type="text" name="items[0][unit]" class="form-control"
                                            placeholder="Satuan" required>
                                    </div>
                                    <div class="col-12 col-md-2 mb-2 mb-md-0">
                                        <label class="d-md-none small text-muted">Est. Harga</label>
                                        <input type="number" name="items[0][estimated_price]"
                                            class="form-control item-price" step="0.01" value="0" required>
                                    </div>
                                    <div class="col-12 col-md-2 mb-2 mb-md-0 text-right">
                                        <div class="d-flex justify-content-between d-md-block">
                                            <span class="d-md-none font-weight-bold">Subtotal:</span>
                                            <span class="item-subtotal font-weight-bold">Rp 0,00</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-1 mb-2 mb-md-0">
                                        <label class="d-md-none small text-muted">Info Anggaran</label>
                                        <input type="text" name="items[0][budget_info]" class="form-control"
                                            placeholder="Info">
                                    </div>
                                    <div class="col-12 col-md-1 text-center">
                                        <button type="button"
                                            class="btn btn-danger btn-sm btn-block remove-row d-md-none"><i
                                                class="fas fa-trash"></i> Hapus</button>
                                        <button type="button"
                                            class="btn btn-danger btn-sm remove-row d-none d-md-inline-block"><i
                                                class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Add Button -->
                        <div class="d-md-none mt-3">
                            <button type="button" class="btn btn-success btn-block btn-add-item">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>
                        </div>

                        <!-- Total Footer -->
                        <div class="row mt-3 border-top pt-3">
                            <div class="col-12 text-right">
                                <h5 class="font-weight-bold mb-0">Total Pengajuan: <span id="totalAmount"
                                        class="text-primary">Rp 0,00</span></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('procurement.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Kirim Permohonan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    let itemIndex = 1;

    // Calculate total
    function calculateTotal() {
        let grandTotal = 0;
        $('#itemsList .item-row').each(function () {
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

    // Add item row
    $('#addItem, .btn-add-item').click(function () {
        let html = `
        <div class="item-row mb-3 mb-md-0 border-bottom border-md-0 pb-3 pb-md-0">
            <div class="row align-items-center">
                <div class="col-12 col-md-2 mb-2 mb-md-0">
                    <label class="d-md-none small text-muted">Nama Barang</label>
                    <input type="text" name="items[${itemIndex}][name]" class="form-control" placeholder="Nama Barang" required>
                </div>
                <div class="col-12 col-md-2 mb-2 mb-md-0">
                    <label class="d-md-none small text-muted">Spesifikasi</label>
                    <input type="text" name="items[${itemIndex}][specification]" class="form-control" placeholder="Spesifikasi">
                </div>
                <div class="col-6 col-md-1 mb-2 mb-md-0">
                    <label class="d-md-none small text-muted">Jumlah</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" value="1" min="1" required>
                </div>
                <div class="col-6 col-md-1 mb-2 mb-md-0">
                    <label class="d-md-none small text-muted">Satuan</label>
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control" placeholder="Satuan" required>
                </div>
                <div class="col-12 col-md-2 mb-2 mb-md-0">
                    <label class="d-md-none small text-muted">Est. Harga</label>
                    <input type="number" name="items[${itemIndex}][estimated_price]" class="form-control item-price" step="0.01" value="0" required>
                </div>
                <div class="col-12 col-md-2 mb-2 mb-md-0 text-right">
                    <div class="d-flex justify-content-between d-md-block">
                        <span class="d-md-none font-weight-bold">Subtotal:</span>
                        <span class="item-subtotal font-weight-bold">Rp 0,00</span>
                    </div>
                </div>
                <div class="col-12 col-md-1 mb-2 mb-md-0">
                    <label class="d-md-none small text-muted">Info Anggaran</label>
                    <input type="text" name="items[${itemIndex}][budget_info]" class="form-control" placeholder="Info">
                </div>
                <div class="col-12 col-md-1 text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-block remove-row d-md-none"><i class="fas fa-trash"></i> Hapus</button>
                    <button type="button" class="btn btn-danger btn-sm remove-row d-none d-md-inline-block"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>`;
        $('#itemsList').append(html);
        itemIndex++;
        calculateTotal();
    });

    // Remove row
    $(document).on('click', '.remove-row', function () {
        $(this).closest('.item-row').remove();
        calculateTotal();
    });

    // Auto calculate on input change
    $(document).on('input', '.item-price, .item-qty', function () {
        calculateTotal();
    });

    // CITO checkbox toggle
    $('#isCito').change(function () {
        if ($(this).is(':checked')) {
            $('#citoReasonGroup').slideDown();
            $('#citoReason').prop('required', true);
        } else {
            $('#citoReasonGroup').slideUp();
            $('#citoReason').prop('required', false).val('');
        }
    });

    // Form validation
    $('#createForm').submit(function (e) {
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
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
    });
</script>
@stop