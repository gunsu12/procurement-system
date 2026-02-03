@extends('adminlte::page')

@section('title', 'Dokumentasi Business Process')

@section('content_header')
<h1>Dokumentasi Business Process</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Introduction -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Pengenalan</h3>
        </div>
        <div class="card-body">
            <p>Sistem Procurement BROS Hospital dirancang untuk mengelola proses pengadaan barang dan jasa secara
                terstruktur dengan approval workflow yang jelas berdasarkan role dan tipe permohonan.</p>
        </div>
    </div>

    <!-- Roles & Permissions -->
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users"></i> Role & Permissions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Deskripsi</th>
                            <th>Akses Cross-Company</th>
                            <th>Permissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-primary">Unit</span></td>
                            <td>Pembuat permohonan pengadaan</td>
                            <td><i class="fas fa-times text-danger"></i> Tidak</td>
                            <td>
                                <ul class="mb-0">
                                    <li>Membuat permohonan</li>
                                    <li>Edit permohonan (status: submitted)</li>
                                    <li>Melihat data unit sendiri</li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="table-success">
                            <td><span class="badge badge-success">Manager</span></td>
                            <td>Approver level 1 - Unit Manager</td>
                            <td><i class="fas fa-check text-success"></i> <strong>Ya</strong> (jika designated approver)
                            </td>
                            <td>
                                <ul class="mb-0">
                                    <li>Approve/Reject permohonan</li>
                                    <li>Reject item tertentu</li>
                                    <li>Melihat data dari company manapun (jika approver unit)</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">Budgeting</span></td>
                            <td>Approver level 2 - Tim Budgeting</td>
                            <td><i class="fas fa-times text-danger"></i> Tidak</td>
                            <td>
                                <ul class="mb-0">
                                    <li>Approve/Reject permohonan</li>
                                    <li>Melihat data company sendiri</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-info">Director Company</span></td>
                            <td>Approver level 3 - Direktur Perusahaan</td>
                            <td><i class="fas fa-times text-danger"></i> Tidak</td>
                            <td>
                                <ul class="mb-0">
                                    <li>Approve/Reject permohonan (Full Chain)</li>
                                    <li>Melihat data company sendiri</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-secondary">Holding Roles</span></td>
                            <td>Finance Manager, Finance Director, General Director Holding</td>
                            <td><i class="fas fa-check text-success"></i> Ya</td>
                            <td>
                                <ul class="mb-0">
                                    <li>Approve/Reject permohonan (Full Chain)</li>
                                    <li>Melihat data semua company</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-dark">Purchasing</span></td>
                            <td>Tim Purchasing - Eksekutor pengadaan</td>
                            <td><i class="fas fa-times text-danger"></i> Tidak</td>
                            <td>
                                <ul class="mb-0">
                                    <li>Process permohonan yang sudah approved</li>
                                    <li>Toggle check items</li>
                                    <li>Complete permohonan</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-danger">Super Admin</span></td>
                            <td>Administrator sistem</td>
                            <td><i class="fas fa-check text-success"></i> Ya</td>
                            <td>
                                <ul class="mb-0">
                                    <li>Full access ke semua fitur</li>
                                    <li>Manage master data</li>
                                    <li>View activity logs</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Approval Workflow -->
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-project-diagram"></i> Approval Workflow</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Sistem memiliki 2 workflow berbeda berdasarkan tipe dan nilai permohonan:</p>

            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-fast-forward text-primary"></i> Short Chain</h5>
                    <p class="text-sm"><strong>Kondisi:</strong> Tipe Nonaset & Total > Rp 1.000.000</p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <ol class="mb-0">
                                <li><strong>Submitted</strong> → <span class="badge badge-success">Manager</span></li>
                                <li><strong>Approved by Manager</strong> → <span
                                        class="badge badge-warning">Budgeting</span></li>
                                <li><strong>Approved by Budgeting</strong> → <span
                                        class="badge badge-dark">Purchasing</span></li>
                                <li><strong>Processing</strong> → <span class="badge badge-dark">Purchasing</span></li>
                                <li><strong>Completed</strong> ✅</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5><i class="fas fa-route text-danger"></i> Full Chain</h5>
                    <p class="text-sm"><strong>Kondisi:</strong> Tipe Aset</p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <ol class="mb-0 text-sm">
                                <li><strong>Submitted</strong> → <span class="badge badge-success">Manager</span></li>
                                <li><strong>Approved by Manager</strong> → <span
                                        class="badge badge-warning">Budgeting</span></li>
                                <li><strong>Approved by Budgeting</strong> → <span class="badge badge-info">Director
                                        Company</span></li>
                                <li><strong>Approved by Dir Company</strong> → <span
                                        class="badge badge-secondary">Finance Mgr Holding</span></li>
                                <li><strong>Approved by Fin Mgr Holding</strong> → <span
                                        class="badge badge-secondary">Finance Dir Holding</span></li>
                                <li><strong>Approved by Fin Dir Holding</strong> → <span
                                        class="badge badge-secondary">General Dir Holding</span></li>
                                <li><strong>Approved by Gen Dir Holding</strong> → <span
                                        class="badge badge-dark">Purchasing</span></li>
                                <li><strong>Processing</strong> → <span class="badge badge-dark">Purchasing</span></li>
                                <li><strong>Completed</strong> ✅</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Flow Guide -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-check"></i> Alur Penggunaan User</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Panduan step-by-step untuk setiap role dalam menggunakan sistem:</p>

            <!-- Unit User Flow -->
            <div class="card card-outline card-primary collapsed-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <span class="badge badge-primary">1</span> Alur Penggunaan - <strong>UNIT</strong> (Pembuat
                        Permohonan)
                    </h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-primary">Membuat Permohonan Baru</span>
                        </div>
                        <div>
                            <i class="fas fa-plus bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 1:</strong> Klik "Buat Permohonan"</h3>
                                <div class="timeline-body">
                                    <p>Dari sidebar, pilih menu <strong>Pengadaan → Buat Permohonan</strong> atau klik
                                        tombol "Buat Permohonan" di halaman Permohonan.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-edit bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 2:</strong> Isi Form Permohonan</h3>
                                <div class="timeline-body">
                                    <ul>
                                        <li><strong>Catatan:</strong> Deskripsi kebutuhan</li>
                                        <li><strong>Tipe:</strong> Pilih Aset atau Nonaset</li>
                                        <li><strong>Kategori:</strong> Medis atau Non Medis</li>
                                        <li><strong>CITO:</strong> Centang jika urgent</li>
                                        <li><strong>Dokumen:</strong> Upload dokumen pendukung (opsional)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-list bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3:</strong> Tambah Item</h3>
                                <div class="timeline-body">
                                    <p>Klik tombol <strong>"Tambah Item"</strong> dan isi:</p>
                                    <ul>
                                        <li>Nama barang</li>
                                        <li>Spesifikasi</li>
                                        <li>Jumlah & Satuan</li>
                                        <li>Estimasi harga</li>
                                        <li>Informasi anggaran (opsional)</li>
                                    </ul>
                                    <p class="text-muted"><small>Ulangi untuk menambah item lainnya.</small></p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-paper-plane bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 4:</strong> Submit Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Review semua data, pastikan sudah benar, lalu klik <strong>"Submit
                                            Permohonan"</strong>.</p>
                                    <div class="alert alert-info">
                                        <i class="icon fas fa-info"></i> Setelah submit, permohonan akan masuk ke
                                        approval workflow dan tidak dapat diedit lagi kecuali ditolak.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-eye bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 5:</strong> Monitor Status</h3>
                                <div class="timeline-body">
                                    <p>Pantau status permohonan di halaman <strong>Permohonan</strong>. Anda akan
                                        melihat status terkini seperti:</p>
                                    <ul>
                                        <li><span class="badge badge-info">Submitted</span> - Menunggu approval manager
                                        </li>
                                        <li><span class="badge badge-primary">Approved by Manager</span> - Sudah
                                            disetujui manager</li>
                                        <li><span class="badge badge-danger">Rejected</span> - Ditolak (dapat diedit
                                            ulang)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manager User Flow -->
            <div class="card card-outline card-success collapsed-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <span class="badge badge-success">2</span> Alur Penggunaan - <strong>MANAGER</strong> (Approver
                        Level 1)
                    </h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-success">Review & Approve Permohonan</span>
                        </div>
                        <div>
                            <i class="fas fa-list bg-green"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 1:</strong> Buka Daftar Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Dari halaman <strong>Permohonan</strong>, sistem akan otomatis filter permohonan
                                        dengan status <span class="badge badge-info">Submitted</span> yang menunggu
                                        approval Anda.</p>
                                    <div class="alert alert-success">
                                        <i class="icon fas fa-globe"></i> <strong>Cross-Company Access:</strong> Anda
                                        akan melihat permohonan dari company manapun selama Anda adalah designated
                                        approver untuk unit tersebut.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-search bg-green"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 2:</strong> Review Detail Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Klik <strong>"Lihat"</strong> pada permohonan untuk melihat:</p>
                                    <ul>
                                        <li>Informasi pemohon & unit</li>
                                        <li>Daftar item yang diminta</li>
                                        <li>Total nilai pengadaan</li>
                                        <li>Dokumen pendukung (jika ada)</li>
                                        <li>Status CITO (jika urgent)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-ban bg-orange"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3a:</strong> Reject Item (Opsional)</h3>
                                <div class="timeline-body">
                                    <p>Jika ada item tertentu yang tidak sesuai, Anda dapat menolak item tersebut tanpa
                                        menolak seluruh permohonan:</p>
                                    <ul>
                                        <li>Klik ikon ❌ pada item yang ingin ditolak</li>
                                        <li>Item yang ditolak akan tetap tercatat tapi tidak diproses</li>
                                        <li>Permohonan tetap bisa di-approve untuk item lainnya</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-check bg-green"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3b:</strong> Approve Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Jika semua sesuai:</p>
                                    <ol>
                                        <li>Scroll ke section <strong>"Aksi"</strong></li>
                                        <li>Isi catatan/alasan approval</li>
                                        <li>Klik tombol <strong class="text-success">"Setujui / Proses"</strong></li>
                                    </ol>
                                    <p>Status akan berubah menjadi <span class="badge badge-primary">Approved by
                                            Manager</span> dan diteruskan ke Budgeting.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-times bg-red"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3c:</strong> Reject Seluruh Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Jika permohonan tidak sesuai:</p>
                                    <ol>
                                        <li>Scroll ke section <strong>"Aksi"</strong></li>
                                        <li>Isi alasan penolakan dengan jelas</li>
                                        <li>Klik tombol <strong class="text-danger">"Tolak"</strong></li>
                                    </ol>
                                    <p>Status akan berubah menjadi <span class="badge badge-danger">Rejected</span> dan
                                        pemohon dapat edit ulang.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budgeting User Flow -->
            <div class="card card-outline card-warning collapsed-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <span class="badge badge-warning">3</span> Alur Penggunaan - <strong>BUDGETING</strong>
                        (Approver Level 2)
                    </h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-warning">Verifikasi Anggaran</span>
                        </div>
                        <div>
                            <i class="fas fa-list bg-yellow"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 1:</strong> Buka Daftar Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Sistem otomatis filter permohonan dengan status <span
                                            class="badge badge-primary">Approved by Manager</span> yang menunggu
                                        verifikasi budgeting.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-search bg-yellow"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 2:</strong> Verifikasi Anggaran</h3>
                                <div class="timeline-body">
                                    <p>Review permohonan dengan fokus pada:</p>
                                    <ul>
                                        <li>Ketersediaan budget</li>
                                        <li>Kesesuaian nominal dengan rencana anggaran</li>
                                        <li>Informasi budget yang dicantumkan pemohon</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-check bg-yellow"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3:</strong> Approve atau Reject</h3>
                                <div class="timeline-body">
                                    <p><strong>Jika Budget Tersedia:</strong></p>
                                    <ul>
                                        <li>Isi catatan verifikasi</li>
                                        <li>Klik <strong class="text-success">"Setujui / Proses"</strong></li>
                                        <li>Status: <span class="badge badge-primary">Approved by Budgeting</span></li>
                                    </ul>
                                    <p><strong>Jika Budget Tidak Tersedia:</strong></p>
                                    <ul>
                                        <li>Isi alasan penolakan (misal: "Budget tahun ini sudah terpakai")</li>
                                        <li>Klik <strong class="text-danger">"Tolak"</strong></li>
                                    </ul>
                                    <div class="alert alert-info">
                                        <i class="icon fas fa-route"></i> <strong>Next Step:</strong> Setelah approve,
                                        permohonan akan diteruskan ke:
                                        <ul class="mb-0">
                                            <li><strong>Short Chain:</strong> Langsung ke Purchasing (jika nonaset <
                                                    1jt)</li>
                                            <li><strong>Full Chain:</strong> Ke Director Company (jika aset atau >= 1jt)
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchasing User Flow -->
            <div class="card card-outline card-dark collapsed-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <span class="badge badge-dark">4</span> Alur Penggunaan - <strong>PURCHASING</strong>
                        (Eksekutor)
                    </h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-dark">Proses Pengadaan</span>
                        </div>
                        <div>
                            <i class="fas fa-list bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 1:</strong> Buka Permohonan yang Sudah Approved
                                </h3>
                                <div class="timeline-body">
                                    <p>Filter permohonan berdasarkan:</p>
                                    <ul>
                                        <li><span class="badge badge-primary">Approved by Gen Dir Holding</span> (Full
                                            Chain)</li>
                                        <li><span class="badge badge-primary">Approved by Budgeting</span> (Short Chain)
                                        </li>
                                        <li><span class="badge badge-warning">Processing</span> (sedang diproses)</li>
                                    </ul>
                                    <p>Gunakan filter <strong>Tipe: Medis/Non Medis</strong> sesuai spesialisasi Anda.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-play bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 2:</strong> Mulai Proses Pengadaan</h3>
                                <div class="timeline-body">
                                    <p>Klik <strong>"Lihat"</strong> pada permohonan, lalu:</p>
                                    <ol>
                                        <li>Review semua item yang diminta</li>
                                        <li>Isi catatan proses (misal: "Mulai proses tender")</li>
                                        <li>Klik <strong>"Setujui / Proses"</strong></li>
                                    </ol>
                                    <p>Status akan berubah menjadi <span class="badge badge-warning">Processing</span>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-check-square bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3:</strong> Check Item yang Sudah Dibeli</h3>
                                <div class="timeline-body">
                                    <p>Saat proses pengadaan berjalan:</p>
                                    <ul>
                                        <li>Klik checkbox di sebelah item yang sudah dibeli/diterima</li>
                                        <li>Item yang di-check akan ditandai sebagai selesai</li>
                                        <li>Berguna untuk tracking progress pembelian</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-flag-checkered bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 4:</strong> Complete Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Setelah semua item selesai dibeli dan diterima:</p>
                                    <ol>
                                        <li>Pastikan semua item sudah di-check</li>
                                        <li>Isi catatan completion (misal: "Semua barang sudah diterima unit")</li>
                                        <li>Klik <strong class="text-success">"Setujui / Proses"</strong></li>
                                    </ol>
                                    <p>Status akan berubah menjadi <span class="badge badge-success">Completed</span> ✅
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Director & Holding Roles -->
            <div class="card card-outline card-info collapsed-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <span class="badge badge-info">5</span> Alur Penggunaan - <strong>DIRECTOR & HOLDING
                            ROLES</strong> (Approver Tingkat Tinggi)
                    </h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <p><strong>Roles yang termasuk:</strong></p>
                    <ul>
                        <li><span class="badge badge-info">Director Company</span> - Direktur Perusahaan</li>
                        <li><span class="badge badge-secondary">Finance Manager Holding</span></li>
                        <li><span class="badge badge-secondary">Finance Director Holding</span></li>
                        <li><span class="badge badge-secondary">General Director Holding</span></li>
                    </ul>

                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-info">Strategic Approval</span>
                        </div>
                        <div>
                            <i class="fas fa-list bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 1:</strong> Review Permohonan</h3>
                                <div class="timeline-body">
                                    <p>Sistem otomatis filter permohonan yang memerlukan approval Anda berdasarkan
                                        status dan workflow chain:</p>
                                    <ul>
                                        <li><strong>Director Company:</strong> Status <span
                                                class="badge badge-primary">Approved by Budgeting</span></li>
                                        <li><strong>Finance Mgr Holding:</strong> Status <span
                                                class="badge badge-primary">Approved by Dir Company</span></li>
                                        <li><strong>Finance Dir Holding:</strong> Status <span
                                                class="badge badge-primary">Approved by Fin Mgr Holding</span></li>
                                        <li><strong>General Dir Holding:</strong> Status <span
                                                class="badge badge-primary">Approved by Fin Dir Holding</span></li>
                                    </ul>
                                    <div class="alert alert-warning">
                                        <i class="icon fas fa-info"></i> <strong>Catatan:</strong> Director Company
                                        hanya mereview permohonan Full Chain (Aset atau >= Rp 1jt)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-search bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 2:</strong> Strategic Review</h3>
                                <div class="timeline-body">
                                    <p>Review dengan fokus pada:</p>
                                    <ul>
                                        <li>Justifikasi kebutuhan</li>
                                        <li>Nilai investasi vs benefit</li>
                                        <li>Alignment dengan strategi perusahaan</li>
                                        <li>Cash flow & financial impact</li>
                                        <li>History approval sebelumnya</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-check bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><strong>Step 3:</strong> Approve atau Reject</h3>
                                <div class="timeline-body">
                                    <p>Sama seperti approver lainnya:</p>
                                    <ul>
                                        <li>Isi catatan/pertimbangan keputusan</li>
                                        <li>Klik <strong class="text-success">"Setujui / Proses"</strong> untuk approve
                                        </li>
                                        <li>Atau klik <strong class="text-danger">"Tolak"</strong> jika tidak sesuai
                                        </li>
                                    </ul>
                                    <p>Permohonan akan diteruskan ke approver level berikutnya dalam chain.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Features -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-star"></i> Fitur Khusus</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-globe"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Manager Cross-Company</span>
                            <span class="info-box-number">Manager bisa approve request dari company manapun jika mereka
                                adalah designated approver (approval_by) untuk unit tersebut.</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-ban"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Item Rejection</span>
                            <span class="info-box-number">Manager dapat menolak item tertentu dari permohonan tanpa
                                harus menolak seluruh permohonan.</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-fire"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">CITO Priority</span>
                            <span class="info-box-number">Permohonan dapat ditandai sebagai CITO (urgent) untuk
                                prioritas lebih tinggi.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Types -->
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Tipe Permohonan</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                        <th>Workflow</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge badge-primary">Aset</span></td>
                        <td>Barang yang akan menjadi aset perusahaan (inventaris)</td>
                        <td><strong>Full Chain</strong> (semua nominal)</td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-warning">Nonaset < 1jt</span>
                        </td>
                        <td>Barang habis pakai dengan total < Rp 1.000.000</td>
                        <td><strong>Short Chain</strong></td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-danger">Nonaset >= 1jt</span></td>
                        <td>Barang habis pakai dengan total >= Rp 1.000.000</td>
                        <td><strong>Full Chain</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status List -->
    <div class="card card-dark card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tasks"></i> Daftar Status</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Status Approval</h5>
                    <ul>
                        <li><span class="badge badge-info">Submitted</span> - Permohonan baru dibuat</li>
                        <li><span class="badge badge-primary">Approved by Manager</span> - Disetujui manager</li>
                        <li><span class="badge badge-primary">Approved by Budgeting</span> - Disetujui budgeting</li>
                        <li><span class="badge badge-primary">Approved by Dir Company</span> - Disetujui direktur</li>
                        <li><span class="badge badge-primary">Approved by Fin Mgr Holding</span> - Disetujui fin mgr
                            holding</li>
                        <li><span class="badge badge-primary">Approved by Fin Dir Holding</span> - Disetujui fin dir
                            holding</li>
                        <li><span class="badge badge-primary">Approved by Gen Dir Holding</span> - Disetujui gen dir
                            holding</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Status Eksekusi</h5>
                    <ul>
                        <li><span class="badge badge-warning">Processing</span> - Sedang diproses purchasing</li>
                        <li><span class="badge badge-success">Completed</span> - Selesai</li>
                        <li><span class="badge badge-danger">Rejected</span> - Ditolak</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ / Important Notes -->
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Catatan Penting</h3>
        </div>
        <div class="card-body">
            <div class="callout callout-warning">
                <h5><i class="icon fas fa-info"></i> Manager Cross-Company Access</h5>
                <p>Sejak update terbaru, Manager dapat melihat dan approve permohonan dari company manapun, selama
                    mereka adalah designated approver (approval_by) untuk unit yang terkait. Ini memungkinkan
                    fleksibilitas dalam struktur organisasi.</p>
            </div>

            <div class="callout callout-info">
                <h5><i class="icon fas fa-coins"></i> Threshold Rp 1.000.000</h5>
                <p>Nilai Rp 1.000.000 adalah threshold untuk menentukan workflow. Permohonan nonaset dengan total di
                    bawah threshold ini akan menggunakan Short Chain untuk mempercepat proses.</p>
            </div>

            <div class="callout callout-danger">
                <h5><i class="icon fas fa-ban"></i> Edit Permission</h5>
                <p>Permohonan hanya dapat diedit oleh pembuat (unit) dan hanya ketika status masih "Submitted". Setelah
                    masuk ke approval workflow, permohonan tidak dapat diedit lagi.</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-title {
        font-weight: bold;
    }

    .callout {
        border-left-width: 5px;
    }

    .table-responsive {
        font-size: 0.9rem;
    }

    ul {
        padding-left: 20px;
    }

    /* Timeline styling */
    .timeline {
        position: relative;
        margin: 0 0 30px 0;
        padding: 0;
        list-style: none;
    }

    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }

    .timeline>div {
        margin-bottom: 15px;
        position: relative;
    }

    .timeline>div>.timeline-item {
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        border-radius: 3px;
        margin-top: 0;
        margin-left: 60px;
        margin-right: 15px;
        padding: 0;
        position: relative;
    }

    .timeline>div>.fa,
    .timeline>div>.fas,
    .timeline>div>.far,
    .timeline>div>.fab,
    .timeline>div>.fal,
    .timeline>div>.fad,
    .timeline>div>.svg-inline--fa,
    .timeline>div>.ion {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #666;
        background: #d2d6de;
        border-radius: 50%;
        text-align: center;
        left: 16px;
        top: 0;
    }

    .timeline>div>.timeline-item>.time {
        color: #999;
        float: right;
        padding: 10px;
        font-size: 12px;
    }

    .timeline>div>.timeline-item>.timeline-header {
        margin: 0;
        color: #555;
        border-bottom: 1px solid #f4f4f4;
        padding: 10px;
        font-size: 16px;
        line-height: 1.1;
    }

    .timeline>div>.timeline-item>.timeline-body,
    .timeline>div>.timeline-item>.timeline-footer {
        padding: 10px;
    }

    .timeline>.time-label>span {
        font-weight: 600;
        padding: 5px;
        display: inline-block;
        background-color: #fff;
        border-radius: 4px;
    }

    /* Collapsed card styling */
    .collapsed-card .card-body {
        display: none;
    }

    .collapsed-card .fa-plus:before {
        content: "\f067";
    }

    .card:not(.collapsed-card) .fa-plus:before {
        content: "\f068";
    }
</style>
@stop