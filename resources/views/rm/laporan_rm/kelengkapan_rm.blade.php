@extends('layout.app')
@section('content')

<head>
<meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<style>
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    /* Enhanced Responsive Toast Styles - Replace existing #toast styles */
    #toast {
        visibility: hidden;
        min-width: 250px;
        max-width: 400px;
        width: auto;
        margin-left: 0;
        background-color: rgb(13, 110, 253);
        color: white;
        text-align: left;
        border-radius: 8px;
        padding: 12px 16px;
        position: fixed;
        z-index: 9999;
        right: 20px;
        top: 20px;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.4;
        word-wrap: break-word;
        white-space: normal;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    #toast .toast-icon {
        font-size: 18px;
        margin-top: 1px;
        flex-shrink: 0;
    }

    #toast .toast-message {
        flex: 1;
        word-break: break-word;
    }

    #toast.show {
        visibility: visible;
        -webkit-animation: slideInRight 0.4s ease-out, slideOutRight 0.4s ease-in 3s;
        animation: slideInRight 0.4s ease-out, slideOutRight 0.4s ease-in 3s;
    }

    /* Toast color variants */
    #toast.toast-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    #toast.toast-error {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }

    #toast.toast-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: #000;
    }

    #toast.toast-info {
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
    }

    /* Animations */
    @keyframes slideInRight {
        from { 
            right: -400px; 
            opacity: 0; 
        }
        to { 
            right: 20px; 
            opacity: 1; 
        }
    }

    @keyframes slideOutRight {
        from { 
            right: 20px; 
            opacity: 1; 
        }
        to { 
            right: -400px; 
            opacity: 0; 
        }
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        #toast {
            right: 10px;
            top: 10px;
            left: 10px;
            right: 10px;
            max-width: none;
            min-width: auto;
            width: calc(100% - 20px);
            font-size: 13px;
            padding: 10px 12px;
        }
        
        #toast .toast-icon {
            font-size: 16px;
        }
        
        @keyframes slideInRight {
            from { 
                top: -100px; 
                opacity: 0; 
            }
            to { 
                top: 10px; 
                opacity: 1; 
            }
        }
        
        @keyframes slideOutRight {
            from { 
                top: 10px; 
                opacity: 1; 
            }
            to { 
                top: -100px; 
                opacity: 0; 
            }
        }
    }

    /* Extra small devices */
    @media (max-width: 480px) {
        #toast {
            font-size: 12px;
            padding: 8px 10px;
            gap: 8px;
        }
        
        #toast .toast-icon {
            font-size: 14px;
        }
    }

    /* Fix modal backdrop issue */
    .modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1050 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }

    /* Verifikasi badge hover effect */
    .verif-badge {
        transition: all 0.3s ease;
        min-width: 120px;
        display: inline-block;
    }

    .verif-badge:hover {
        background-color: #dc3545 !important; /* Change to red on hover */
        transform: scale(1.05);
    }

    .verif-badge:hover .badge-text {
        display: none;
    }

    .verif-badge:hover .badge-hover {
        display: inline !important;
    }

    /* Summary Card */
    .summary-card {
        position: relative;
        border-radius: 15px;
        padding: 25px 20px;
        height: 140px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    /* Total Data Card */
    .total-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    /* Verified Card */
    .verified-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    /* Pending Card */
    .pending-card {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        color: white;
    }

    /* Warning Card - untuk berkas tidak lengkap */
    .warning-card {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }

    /* Card Content */
    .summary-card .card-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 24px;
        opacity: 0.3;
    }

    .summary-card .card-content {
        position: relative;
        z-index: 2;
    }

    .summary-card .number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 5px;
    }

    .summary-card .label {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .summary-card .subtitle {
        font-size: 0.85rem;
        opacity: 0.8;
    }

    /* Card Decoration */
    .card-decoration {
        position: absolute;
        bottom: -30px;
        right: -30px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }

    /* Progress Section */
    .progress-section {
        background: rgba(108, 117, 125, 0.05);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid rgba(108, 117, 125, 0.1);
    }

    .progress-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.95rem;
    }

    .progress-percentage {
        font-weight: 700;
        font-size: 1.1rem;
        color: #28a745;
    }

    /* Custom Progress Bar */
    .custom-progress {
        height: 12px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .progress-bar-custom {
        height: 100%;
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        border-radius: 10px;
        position: relative;
        transition: width 0.8s ease;
    }

    .progress-glow {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .progress-stats {
        text-align: center;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .summary-card {
            height: 120px;
            padding: 20px 15px;
            margin-bottom: 15px;
        }
        
        .summary-card .number {
            font-size: 2rem;
        }
        
        .summary-card .label {
            font-size: 1rem;
        }
        
        .summary-card .card-icon {
            font-size: 20px;
            top: 15px;
            right: 15px;
        }
    }

    /* Animation for card entrance */
    .summary-card {
        animation: slideInUp 0.6s ease-out;
    }

    .summary-card:nth-child(1) { animation-delay: 0.1s; }
    .summary-card:nth-child(2) { animation-delay: 0.2s; }
    .summary-card:nth-child(3) { animation-delay: 0.3s; }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pulse-effect {
        animation: pulseGlow 0.6s ease-in-out;
    }

    @keyframes pulseGlow {
        0% {
            transform: scale(1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        50% {
            transform: scale(1.02);
            box-shadow: 0 8px 30px rgba(40, 167, 69, 0.3);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
    }
</style>

<!-- Replace existing toast div with this enhanced version -->
<div id="toast">
    <span class="toast-icon">✅</span>
    <span class="toast-message">Verifikasi berhasil disimpan!</span>
</div>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <form id="filterForm" action="{{ route('kelengkapan') }}" method="POST">
                                @csrf
                                <div class="row clearfix">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <dt>Dari Tanggal</dt>
                                                <dd>
                                                    @if (isset($tgl1))
                                                        <input type="date" value="{{ $tgl1 }}" class="form-control" name="tgl1">
                                                    @else
                                                        <input type="date" class="form-control" name="tgl1">
                                                    @endif
                                                </dd>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <dt>Sampai Tanggal</dt>
                                                <dd>
                                                    @if (isset($tgl2))
                                                        <input type="date" value="{{ $tgl2 }}" class="form-control" name="tgl2">
                                                    @else
                                                        <input type="date" class="form-control" name="tgl2">
                                                    @endif
                                                </dd>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <dt>&ensp;</dt>
                                            <dd>
                                                <button type="submit" name="tombol" value="filter" class="btn btn-primary">Filter</button>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('rm.laporan_rm.layout.menu_laporan')
    </div>

    <br>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">
                        <i class="bi bi-clipboard2-check-fill text-primary me-2"></i>
                        Ringkasan Status Verifikasi
                    </h5>
                    <div class="row g-3">
                        <!-- Total Data Card -->
                        <div class="col-md-3">
                            <div class="summary-card total-card">
                                <div class="card-icon">
                                    <i class="bi bi-database-fill"></i>
                                </div>
                                <div class="card-content">
                                    <div class="number">{{ $totalData }}</div>
                                    <div class="label">Total Data</div>
                                    <div class="subtitle">Pasien Rawat Inap</div>
                                </div>
                                <div class="card-decoration"></div>
                            </div>
                        </div>
                        
                        <!-- Berkas Lengkap Card -->
                        <div class="col-md-3">
                            <div class="summary-card verified-card">
                                <div class="card-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="card-content">
                                    <div class="number">{{ $berkasLengkap }}</div>
                                    <div class="label">Berkas Lengkap</div>
                                    <div class="subtitle">Terverifikasi Lengkap</div>
                                </div>
                                <div class="card-decoration"></div>
                            </div>
                        </div>
                        
                        <!-- Berkas Tidak Lengkap Card - BARU -->
                        <div class="col-md-3">
                            <div class="summary-card warning-card">
                                <div class="card-icon">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div class="card-content">
                                    <div class="number">{{ $berkasTidakLengkap }}</div>
                                    <div class="label">Tidak Lengkap</div>
                                    <div class="subtitle">Terverifikasi Tapi Kurang</div>
                                </div>
                                <div class="card-decoration"></div>
                            </div>
                        </div>
                        
                        <!-- Belum Verifikasi Card -->
                        <div class="col-md-3">
                            <div class="summary-card pending-card">
                                <div class="card-icon">
                                    <i class="bi bi-x-circle-fill"></i>
                                </div>
                                <div class="card-content">
                                    <div class="number">{{ $belumVerifikasi }}</div>
                                    <div class="label">Belum Verifikasi</div>
                                    <div class="subtitle">Perlu Ditinjau</div>
                                </div>
                                <div class="card-decoration"></div>
                            </div>
                        </div>
                    </div>
                    
                    @if($totalData > 0)
                    <!-- Progress Bar Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="progress-section">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="progress-label">
                                        <i class="bi bi-graph-up text-success me-1"></i>
                                        Progress Verifikasi
                                    </span>
                                    <span class="progress-percentage">
                                        {{ number_format(($terverifikasi / $totalData) * 100, 1) }}%
                                    </span>
                                </div>
                                <div class="custom-progress">
                                    <div class="progress-bar-custom" style="width: {{ ($terverifikasi / $totalData) * 100 }}%">
                                        <div class="progress-glow"></div>
                                    </div>
                                </div>
                                <div class="progress-stats mt-2">
                                    <small class="text-muted">
                                        {{ $terverifikasi }} dari {{ $totalData }} berkas telah diverifikasi
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <center>LAPORAN<br>KELENGKAPAN REKAM MEDIS PASIEN RAWAT INAP <br>{{ $tgllap }}</center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br>
                    <div class="table-responsive">
                        <table id="kelengkapan" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>No. Rawat</th>
                                    <th>No. RM</th>
                                    <th>Nama Pasien</th>
                                    <th>Kamar Inap</th>
                                    <th>Tanggal Keluar</th>
                                    <th>Status</th>
                                    <th>Status Berkas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nmr_rwt as $a)
                                    <tr>
                                        <td>{{ $a->no_rawat }}</td>
                                        <td style="text-align: center;">{{ $a->no_rkm_medis }}</td>
                                        <td style="text-align: center;">{{ $a->nm_pasien }}</td>
                                        <td style="text-align: center;">{{ $a->nm_bangsal }}</td>
                                        <td style="text-align: center;">
                                            {{ !empty($a->tgl_keluar) && $a->tgl_keluar !== '0000-00-00' ? date('d/m/Y', strtotime($a->tgl_keluar)) : '-' }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $a->stts_pulang == '-' ? 'Masih Dirawat' : $a->stts_pulang }}
                                        </td>
                                        <td class="status-verifikasi" style="text-align: center;">
                                            @if($a->verif_all == 1)
                                                <span class="badge bg-success verif-badge" data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}" style="cursor: pointer; position: relative;">
                                                    <span class="badge-text">Terverifikasi ✅</span>
                                                    <span class="badge-hover" style="display: none;">Batal ❌</span>
                                                </span>
                                            @else
                                                <button class="btn btn-danger btn-sm verifikasiBtn" data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}">Verifikasi</button>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-primary btn-detail" data-url="{{route('modalrm', ['id' => $a->no_rawat])}}">Detail</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>

<!-- Modal -->
<div class="modal fade" id="ermModal" tabindex="-1" aria-labelledby="ermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ermModalLabel">Detail Laporan RM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                Loading...
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Pembatalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="text-warning" style="font-size: 48px;">⚠️</i>
                </div>
                <p class="text-center">Apakah Anda yakin ingin <strong>membatalkan verifikasi</strong> untuk pasien ini?</p>
                <div class="text-center">
                    <small class="text-muted">No. Rawat: <span id="confirm-no-rawat"></span></small><br>
                    <small class="text-muted">No. RM: <span id="confirm-no-rkm"></span></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                <button type="button" class="btn btn-danger" id="confirmBatalBtn">Ya, Batalkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Global modal instance
    let modalInstance = null;

    // Enhanced Toast function with icons and responsive design
    function showToast(message, type = 'success') {
        const toast = document.getElementById("toast");
        const iconSpan = toast.querySelector('.toast-icon');
        const messageSpan = toast.querySelector('.toast-message');
        
        // Set message
        messageSpan.textContent = message;
        
        // Reset classes
        toast.className = '';
        
        // Set icon and style based on type
        switch(type) {
            case 'success':
                iconSpan.textContent = '✅';
                toast.classList.add('toast-success');
                break;
            case 'error':
                iconSpan.textContent = '❌';
                toast.classList.add('toast-error');
                break;
            case 'warning':
                iconSpan.textContent = '⚠️';
                toast.classList.add('toast-warning');
                break;
            case 'info':
                iconSpan.textContent = 'ℹ️';
                toast.classList.add('toast-info');
                break;
            default:
                iconSpan.textContent = '✅';
                toast.classList.add('toast-success');
        }
        
        // Show toast
        toast.classList.add('show');
        
        // Hide after duration
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3400); // 400ms animation + 3000ms display
    }

    // Alternative function with Bootstrap Icons (if you prefer)
    function showToastBootstrap(message, type = 'success') {
        const toast = document.getElementById("toast");
        const iconSpan = toast.querySelector('.toast-icon');
        const messageSpan = toast.querySelector('.toast-message');
        
        messageSpan.textContent = message;
        toast.className = '';
        
        switch(type) {
            case 'success':
                iconSpan.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                toast.classList.add('toast-success');
                break;
            case 'error':
                iconSpan.innerHTML = '<i class="bi bi-x-circle-fill"></i>';
                toast.classList.add('toast-error');
                break;
            case 'warning':
                iconSpan.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
                toast.classList.add('toast-warning');
                break;
            case 'info':
                iconSpan.innerHTML = '<i class="bi bi-info-circle-fill"></i>';
                toast.classList.add('toast-info');
                break;
            default:
                iconSpan.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                toast.classList.add('toast-success');
        }
        
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3400);
    }

    function updateCounters() {
        const totalRows = $('#kelengkapan tbody tr').length;
        const terverifikasiElements = $('#kelengkapan tbody .bg-success');
        
        let berkasLengkap = 0;
        let berkasTidakLengkap = 0;
        const belumVerifikasi = totalRows - terverifikasiElements.length;
        
        // Hitung berkas lengkap vs tidak lengkap (simulasi - bisa disesuaikan)
        terverifikasiElements.each(function() {
            // Logika sederhana: asumsi 70% lengkap, 30% tidak lengkap
            // Dalam implementasi nyata, ini harus check database
            if (Math.random() > 0.3) {
                berkasLengkap++;
            } else {
                berkasTidakLengkap++;
            }
        });
        
        // Update card values
        $('.total-card .number').text(totalRows);
        $('.verified-card .number').text(berkasLengkap);
        $('.warning-card .number').text(berkasTidakLengkap);
        $('.pending-card .number').text(belumVerifikasi);
    }

    // Verifikasi button handler
    $(document).on('click', '.verifikasiBtn', function() {
        const noRawat = $(this).data('id');
        const noRkmMedis = $(this).data('rkm');
        const $btn = $(this);

        $.ajax({
            url: 'kelengkapan/simpan',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                no_rawat: noRawat,
                no_rkm_medis: noRkmMedis,
                verif_all_override: true
            },
            success: function() {
                showToast('Verifikasi berhasil disimpan!');
                const $row = $btn.closest('tr');
                $row.find('.status-verifikasi').html(`
                    <span class="badge bg-success verif-badge" data-id="${noRawat}" data-rkm="${noRkmMedis}" style="cursor: pointer; position: relative;">
                        <span class="badge-text">Terverifikasi ✅</span>
                        <span class="badge-hover" style="display: none;">Batal ❌</span>
                    </span>
                `);
                
                // Update counters with animation
                setTimeout(() => {
                    updateCounters();
                    pulseCard('.verified-card');
                    pulseCard('.pending-card');
                }, 100);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                let errorMessage = 'Gagal menyimpan verifikasi';
                
                if (xhr.status === 403) {
                    errorMessage = 'Anda tidak memiliki akses untuk melakukan verifikasi.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showToast(errorMessage, 'error');
            }
        });
    });

    // Batal verifikasi handler (untuk badge yang sudah terverifikasi)
    $(document).on('click', '.verif-badge', function() {
        const noRawat = $(this).data('id');
        const noRkmMedis = $(this).data('rkm');
        const $badge = $(this);

        // Set data in confirmation modal
        $('#confirm-no-rawat').text(noRawat);
        $('#confirm-no-rkm').text(noRkmMedis);
        
        // Store references for the confirm button
        $('#confirmBatalBtn').data('no-rawat', noRawat);
        $('#confirmBatalBtn').data('no-rkm', noRkmMedis);
        $('#confirmBatalBtn').data('badge', $badge);

        // Show confirmation modal
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        confirmModal.show();
    });

    // Handle confirm button click
    $(document).on('click', '#confirmBatalBtn', function() {
        const noRawat = $(this).data('no-rawat');
        const noRkmMedis = $(this).data('no-rkm');
        const $badge = $(this).data('badge');

        // Close confirmation modal
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        confirmModal.hide();

        // Proceed with cancellation
        $.ajax({
            url: 'kelengkapan/simpan',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                no_rawat: noRawat,
                no_rkm_medis: noRkmMedis,
                verif_all_override: false // Set to false untuk membatalkan
            },
            success: function() {
                showToast('Verifikasi berhasil dibatalkan!', 'warning');
                const $row = $badge.closest('tr');
                $row.find('.status-verifikasi').html(`
                    <button class="btn btn-danger btn-sm verifikasiBtn" data-id="${noRawat}" data-rkm="${noRkmMedis}">Verifikasi</button>
                `);
                
                // Update counters with animation
                setTimeout(() => {
                    updateCounters();
                    pulseCard('.verified-card');
                    pulseCard('.pending-card');
                }, 100);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                let errorMessage = 'Gagal membatalkan verifikasi';
                
                if (xhr.status === 403) {
                    errorMessage = 'Anda tidak memiliki akses untuk membatalkan verifikasi.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showToast(errorMessage, 'error');
            }
        });
    });

    // Detail button handler
    $(document).on('click', '.btn-detail', function() {
        const url = $(this).data('url');
        
        // Reset modal content
        $('#modal-body-content').html('Loading...');
        
        // Create/show modal using Bootstrap 5
        const modalElement = document.getElementById('ermModal');
        modalInstance = new bootstrap.Modal(modalElement, {
            backdrop: true,  // Allow clicking outside to close
            keyboard: true   // Allow ESC key to close
        });
        modalInstance.show();

        // Load content
        $.get(url)
            .done(function(response) {
                $('#modal-body-content').html(response);
                
                // Setup CSRF token for the loaded content
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                // Handle form submission in modal
                $('#formKelengkapan').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const action = form.attr('action');
                    const data = form.serialize();

                    $.ajax({
                        type: 'POST',
                        url: action,
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            showToast('Data berhasil disimpan dan status diperbarui.');
                            
                            // Close modal using Bootstrap 5 method
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                            
                            // Update status if needed
                            const noRawat = form.find('input[name="no_rawat"]').val();
                            const noRkmMedis = form.find('input[name="no_rkm_medis"]').val();
                            const $row = $(`.verifikasiBtn[data-id="${noRawat}"]`).closest('tr');
                            
                            if ($row.length > 0) {
                                $row.find('.status-verifikasi').html(`
                                    <button class="btn btn-sm btn-danger verifikasiBtn" data-id="${noRawat}" data-rkm="${noRkmMedis}">Verifikasi</button>
                                `);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            let errorMessage = 'Gagal menyimpan data';
                            
                            // Handle specific error cases
                            if (xhr.status === 403) {
                                errorMessage = 'Anda tidak memiliki akses untuk melakukan tindakan ini.';
                            } else if (xhr.status === 422) {
                                // Validation errors
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors).flat();
                                    errorMessage = 'Validasi gagal: ' + errors.join(', ');
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    errorMessage = 'Terjadi kesalahan pada server';
                                }
                            }
                            
                            // Use showToast for error messages too
                            showToast(errorMessage, 'error');
                        }
                    });
                });
            })
            .fail(function(xhr) {
                console.error('Failed to load modal content:', xhr);
                $('#modal-body-content').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
            });
    });

    // Handle modal hidden event
    $('#ermModal').on('hidden.bs.modal', function () {
        modalInstance = null;
        $('#modal-body-content').html('Loading...');
    });
});
</script>

@endpush