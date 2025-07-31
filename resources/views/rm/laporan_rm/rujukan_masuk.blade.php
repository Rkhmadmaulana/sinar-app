@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Jumlah Rujukan Masuk</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rujukan-masuk') }}" method="GET" class="mb-4" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_awal" class="mb-0 mr-2">Dari&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
                    </div>
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_akhir" class="mb-0 mr-2">Sampai&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                    </div>
                    <div class="col-auto mb-2">
                        <button type="button" class="btn btn-primary" id="btnFilter">Filter</button>
                    </div>
                    <div class="col-auto mb-2">
                        <a href="{{ route('laporan.rujukan-masuk', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'download_pdf' => true]) }}"
                            class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </form>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
                </div>
            </div>

            <!-- Custom controls for mobile -->
            <div class="mobile-controls-top">
                <div class="mobile-info-container">
                    <div class="mobile-info"></div>
                </div>
                <div class="mobile-controls-row">
                    <div class="mobile-search-container">
                        <input type="text" id="mobileSearch" class="mobile-search" placeholder="Cari...">
                    </div>
                    <div class="mobile-length-container">
                        <select id="mobileLength" class="mobile-length">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                            <option value="100">100 per halaman</option>
                            <option value="-1">Semua</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive" id="tableContainer">
                <table class="table table-bordered table-striped" id="rujukanMasukTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tgl. Registrasi</th>
                            <th>No. Rujuk</th>
                            <th>No. Rawat</th>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Umur</th>
                            <th>Perujuk</th>
                            <th>Alamat Perujuk</th>
                            <th>Dokter Perujuk</th>
                            <th>Diagnosa Awal</th>
                            <th>Poli Rujukan</th>
                            <th>No. Balasan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <!-- Custom mobile pagination -->
            <div class="mobile-controls-bottom">
                <div class="mobile-pagination-wrapper">
                    <div class="mobile-pagination">
                        <!-- Pagination will be inserted here -->
                    </div>
                    <div class="mobile-page-jump">
                        <!-- Page jump will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Statistik Ringkasan -->
            <div class="mt-5">
                <h4>Ringkasan Statistik</h4>
                <div class="row mt-3">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pasien Rujukan Masuk</h5>
                                <h3 class="card-text">{{ $totalPasien }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Berdasarkan Tanggal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Jumlah Pasien Berdasarkan Tanggal Registrasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah Pasien</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pasienPerTanggal as $tanggal => $jumlah)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($tanggal)) }}</td>
                                        <td>{{ $jumlah }}</td>
                                        <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistik Berdasarkan Perujuk -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Jumlah Pasien Berdasarkan Perujuk</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Perujuk</th>
                                        <th>Jumlah Pasien</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pasienPerPerujuk as $perujuk => $jumlah)
                                    <tr>
                                        <td>{{ $perujuk ?: 'Tidak Tercatat' }}</td>
                                        <td>{{ $jumlah }}</td>
                                        <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistik Berdasarkan Diagnosa -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Jumlah Pasien Berdasarkan Diagnosa</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Diagnosa</th>
                                        <th>Jumlah Pasien</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pasienPerDiagnosa as $diagnosa => $jumlah)
                                    <tr>
                                        <td>{{ $diagnosa ?: 'Tidak Tercatat' }}</td>
                                        <td>{{ $jumlah }}</td>
                                        <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistik Berdasarkan Poli -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Jumlah Pasien Berdasarkan Poli Rujukan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Poli</th>
                                        <th>Jumlah Pasien</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pasienPerPoli as $poli => $jumlah)
                                    <tr>
                                        <td>{{ $poli ?: 'Tidak Tercatat' }}</td>
                                        <td>{{ $jumlah }}</td>
                                        <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 768px) {
    .row.align-items-center > div {
        margin-bottom: 10px;
    }
}

/* Custom DataTables styling */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.375rem 0.75rem;
    margin-left: 2px;
    font-size: 14px;
}

.dataTables_wrapper .dataTables_info {
    font-size: 14px;
}

.dataTables_wrapper .dataTables_length select {
    padding: 0.25rem 0.5rem;
    font-size: 14px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #333;
    width: auto;
    min-width: 60px;
    height: auto;
    line-height: normal;
    appearance: menulist; /* Restore default select styling */
    -webkit-appearance: menulist;
    -moz-appearance: menulist;
}

.dataTables_wrapper .dataTables_filter input {
    padding: 0.375rem 0.75rem;
    font-size: 14px;
}

#rujukanMasukTable{
    width:100% !important;
}

/* Loading overlay styles */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-text {
    margin-top: 15px;
    color: #007bff;
    font-weight: bold;
    font-size: 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Table container relative positioning for overlay */
#tableContainer {
    position: relative;
}

/* Blur effect when loading */
.table-blur {
    filter: blur(2px);
    opacity: 0.5;
    pointer-events: none;
}

/* Hide default processing indicator */
#rujukanMasukTable_processing {
    display: none !important;
}

/* Custom pagination */
.dataTables_wrapper .dataTables_paginate {
    display: flex;
    align-items: center;
    margin-top: 15px;
}

.page-jump {
    margin: 0 15px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-jump input {
    width: 60px;
    padding: 5px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
    -moz-appearance: textfield; /* Firefox */
}

/* Hide spinner arrows for Chrome, Safari, Edge */
.page-jump input::-webkit-outer-spin-button,
.page-jump input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.page-jump label {
    margin: 0;
    font-size: 14px;
    color: #666;
}

/* Mobile Controls Styling - Top */
.mobile-controls-top {
    display: none;
    margin-bottom: 15px;
}

.mobile-info-container {
    text-align: center;
    margin-bottom: 10px;
}

.mobile-info {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.mobile-controls-row {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

.mobile-search-container {
    width: 100%;
    max-width: 300px;
}

.mobile-search {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.mobile-search:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.mobile-length-container {
    width: 100%;
    max-width: 200px;
}

.mobile-length {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    text-align-last: center;
}

.mobile-length:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

/* Mobile Controls Styling - Bottom */
.mobile-controls-bottom {
    display: none;
    margin-top: 20px;
}

.mobile-pagination-wrapper {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.mobile-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 5px;
}

.mobile-pagination .paginate_button {
    display: inline-block;
    padding: 8px 12px;
    margin: 2px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    min-width: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.mobile-pagination .paginate_button:hover:not(.disabled):not(.current) {
    background: #e9ecef;
    border-color: #007bff;
}

.mobile-pagination .paginate_button.current {
    background: #007bff;
    color: white;
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

.mobile-pagination .paginate_button.disabled {
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.5;
    background: #f8f9fa;
}

.mobile-page-jump {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #495057;
    font-weight: 500;
}

.mobile-page-jump input {
    width: 60px;
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.mobile-page-jump input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.mobile-page-jump input::-webkit-outer-spin-button,
.mobile-page-jump input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.mobile-page-jump input[type=number] {
    -moz-appearance: textfield;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .mobile-controls-row {
        gap: 15px;
    }
    
    .mobile-search,
    .mobile-length {
        padding: 12px 15px;
        font-size: 16px; /* Prevent zoom on iOS */
    }
    
    .mobile-pagination .paginate_button {
        padding: 10px 14px;
        font-size: 15px;
        min-width: 44px; /* Better touch target */
    }
    
    .mobile-pagination-wrapper {
        padding: 20px 15px;
    }
}

/* Media Queries */
@media (max-width: 768px) {
    /* Hide default DataTables controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_paginate,
    .dataTables_wrapper .dataTables_info {
        display: none !important;
    }
    
    /* Show mobile controls */
    .mobile-controls-top,
    .mobile-controls-bottom {
        display: block !important;
    }
    
    /* Adjust table container */
    #tableContainer {
        margin: 15px 0;
    }
    
    .row.align-items-center > div {
        margin-bottom: 10px;
    }
    
    /* Make sure table is scrollable */
    .table-responsive#tableContainer {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    
    /* Add some spacing for better mobile experience */
    .card-body {
        padding: 15px;
    }
}

@media (min-width: 769px) {
    /* Hide mobile controls on desktop */
    .mobile-controls-top,
    .mobile-controls-bottom {
        display: none !important;
    }
}

</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Function to show loading overlay
    function showLoading() {
        if ($('#loadingOverlay').length === 0) {
            $('#tableContainer').append(`
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Memuat data...</div>
                </div>
            `);
        }
        $('#rujukanMasukTable').addClass('table-blur');
        $('#loadingOverlay').show();
    }

    // Function to hide loading overlay
    function hideLoading() {
        $('#rujukanMasukTable').removeClass('table-blur');
        $('#loadingOverlay').hide();
    }

    var table = $('#rujukanMasukTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('laporan.rujukan-masuk') }}",
            data: function(d) {
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                hideLoading();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'tgl_registrasi', name: 'reg_periksa.tgl_registrasi'},
            {data: 'no_rujuk', name: 'rujuk_masuk.no_rujuk'},
            {data: 'no_rawat', name: 'reg_periksa.no_rawat'},
            {data: 'no_rkm_medis', name: 'reg_periksa.no_rkm_medis'},
            {data: 'nm_pasien', name: 'pasien.nm_pasien'},
            {data: 'umur', name: 'umur', orderable: false},
            {data: 'perujuk', name: 'rujuk_masuk.perujuk'},
            {data: 'alamat_perujuk', name: 'rujuk_masuk.alamat'},
            {data: 'dokter_perujuk', name: 'rujuk_masuk.dokter_perujuk'},
            {data: 'diagnosa', name: 'diagnosa', orderable: false},
            {data: 'nm_poli', name: 'poliklinik.nm_poli'},
            {data: 'no_balasan', name: 'rujuk_masuk.no_balasan'},
            {data: 'keterangan', name: 'rujuk_masuk.keterangan'}
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        language: {
            processing: "Memuat data...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        scrollX: false,
        responsive: false,
        drawCallback: function(settings) {
            var api = this.api();

            if ($(window).width() > 768) {
                var pagination = $(api.table().container()).find('.dataTables_paginate');
                
                // Remove existing page jump if exists
                pagination.find('.page-jump').remove();
                    
                // Add page jump input
                var pageInfo = api.page.info();
                if (pageInfo.pages > 1) {
                    var pageJump = $(`
                        <div class="page-jump">
                            <label>Halaman:</label>
                            <input type="text" class="page-input" value="${pageInfo.page + 1}" inputmode="numeric">
                            <label>dari ${pageInfo.pages}</label>
                        </div>
                    `);
                    
                    // Insert page jump at the end, before "Next" button
                    var nextButton = pagination.find('.paginate_button.next');
                    if (nextButton.length > 0) {
                        pageJump.insertBefore(nextButton);
                    } else {
                        // If no next button, append to the end
                        pagination.append(pageJump);
                    }
                    
                    // Handle page input events
                    pageJump.find('.page-input').on('keydown', function(e) {
                        // Allow: backspace, delete, tab, escape, enter
                        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
                            // Allow: Ctrl+A, Command+A
                            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
                            // Allow: Ctrl+C, Command+C
                            (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) || 
                            // Allow: Ctrl+V, Command+V
                            (e.keyCode === 86 && (e.ctrlKey === true || e.metaKey === true)) ||
                            // Allow: Ctrl+X, Command+X
                            (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                            // Allow: home, end, left, right, down, up
                            (e.keyCode >= 35 && e.keyCode <= 40)) {
                            // Let it happen, don't do anything
                            return;
                        }
                        // Ensure that it is a number and stop the keypress
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }
                    }).on('keypress', function(e) {
                        if (e.which === 13) { // Enter key
                            var pageNumber = parseInt($(this).val());
                            if (pageNumber >= 1 && pageNumber <= pageInfo.pages) {
                                api.page(pageNumber - 1).draw('page');
                            } else {
                                $(this).val(pageInfo.page + 1);
                                alert('Nomor halaman tidak valid. Masukkan angka antara 1 dan ' + pageInfo.pages);
                                return false;
                            }
                        }
                    }).on('blur', function() {
                        var pageNumber = parseInt($(this).val());
                        if (isNaN(pageNumber) || pageNumber < 1 || pageNumber > pageInfo.pages) {
                            $(this).val(pageInfo.page + 1);
                        } else {
                            api.page(pageNumber - 1).draw('page');
                        }
                    }).on('input', function() {
                        // Remove any non-numeric characters
                        var value = $(this).val().replace(/\D/g, '');
                        $(this).val(value);
                    });
                }
            } else {
                // Mobile pagination
                updateMobilePagination(api);
                updateMobileInfo(api);
            }
        }
    });

     // Function to update mobile pagination
    function updateMobilePagination(api) {
        var pageInfo = api.page.info();
        var paginationHtml = '';
        
        // Previous button
        if (pageInfo.page > 0) {
            paginationHtml += '<a href="#" class="paginate_button previous" data-page="' + (pageInfo.page - 1) + '">‹</a>';
        } else {
            paginationHtml += '<span class="paginate_button disabled">‹</span>';
        }
        
        // Page numbers (show max 5 pages)
        var startPage = Math.max(0, pageInfo.page - 2);
        var endPage = Math.min(pageInfo.pages - 1, pageInfo.page + 2);
        
        // Adjust for mobile - show fewer pages if needed
        if ($(window).width() <= 576) {
            startPage = Math.max(0, pageInfo.page - 1);
            endPage = Math.min(pageInfo.pages - 1, pageInfo.page + 1);
        }

        if (startPage > 0) {
            paginationHtml += '<a href="#" class="paginate_button" data-page="0">1</a>';
            if (startPage > 1) {
                paginationHtml += '<span class="paginate_button disabled">...</span>';
            }
        }
        
        for (var i = startPage; i <= endPage; i++) {
            if (i === pageInfo.page) {
                paginationHtml += '<span class="paginate_button current">' + (i + 1) + '</span>';
            } else {
                paginationHtml += '<a href="#" class="paginate_button" data-page="' + i + '">' + (i + 1) + '</a>';
            }
        }
        
        if (endPage < pageInfo.pages - 1) {
            if (endPage < pageInfo.pages - 2) {
                paginationHtml += '<span class="paginate_button disabled">...</span>';
            }
            paginationHtml += '<a href="#" class="paginate_button" data-page="' + (pageInfo.pages - 1) + '">' + pageInfo.pages + '</a>';
        }
        
        // Next button
        if (pageInfo.page < pageInfo.pages - 1) {
            paginationHtml += '<a href="#" class="paginate_button next" data-page="' + (pageInfo.page + 1) + '">›</a>';
        } else {
            paginationHtml += '<span class="paginate_button disabled">›</span>';
        }
        
        $('.mobile-pagination').html(paginationHtml);
        
        // Page jump
        var pageJumpHtml = `
            <span>Halaman:</span>
            <input type="number" class="mobile-page-input" value="${pageInfo.page + 1}" min="1" max="${pageInfo.pages}">
            <span>dari ${pageInfo.pages}</span>
        `;
        $('.mobile-page-jump').html(pageJumpHtml);
    }
    
    // Function to update mobile info
    function updateMobileInfo(api) {
        var pageInfo = api.page.info();
        var infoText = '';
        
        if (pageInfo.recordsTotal > 0) {
            infoText = `${pageInfo.start + 1}-${pageInfo.end} dari ${pageInfo.recordsTotal} data`;
        } else {
            infoText = 'Tidak ada data';
        }
        
        $('.mobile-info').text(infoText);
    }

    // Mobile pagination click events
    $(document).on('click', '.mobile-pagination .paginate_button:not(.disabled):not(.current)', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page !== undefined) {
            table.page(page).draw('page');
        }
    });

    // Mobile page jump
    $(document).on('change keypress', '.mobile-page-input', function(e) {
        if (e.type === 'keypress' && e.which !== 13) return;
        
        var pageNumber = parseInt($(this).val());
        var pageInfo = table.page.info();
        
        if (pageNumber >= 1 && pageNumber <= pageInfo.pages) {
            table.page(pageNumber - 1).draw('page');
        } else {
            $(this).val(pageInfo.page + 1);
            alert('Nomor halaman tidak valid. Masukkan angka antara 1 dan ' + pageInfo.pages);
        }
    });

    // Mobile search
    var mobileSearchDelay = null;
    $('#mobileSearch').on('input', function() {
        var keyword = this.value;
        clearTimeout(mobileSearchDelay);
        mobileSearchDelay = setTimeout(function() {
            table.search(keyword).draw();
        }, 500);
    });

    // Mobile length change
    $('#mobileLength').on('change', function() {
        table.page.len($(this).val()).draw();
    });

    // Sync desktop and mobile controls
    table.on('search.dt', function() {
        var searchValue = table.search();
        $('#mobileSearch').val(searchValue);
        $('input[type="search"]').val(searchValue);
    });

    table.on('length.dt', function() {
        var length = table.page.len();
        $('#mobileLength').val(length);
        $('.dataTables_length select').val(length);
    });

     // Handle window resize
    $(window).on('resize', function() {
        if (table) {
            table.draw();
        }
    });

    // Debounce search
    var searchDelay = null;
    $('#rujukanMasukTable_filter input')
        .off() // Remove default event
        .on('input', function() {
            var keyword = this.value;
            clearTimeout(searchDelay);
            searchDelay = setTimeout(function() {
                table.search(keyword).draw();
            }, 500); // 500ms delay
        });

    $('#rujukanMasukTable').on('draw.dt', function() {
        var api = $('#rujukanMasukTable').DataTable();
        var pageInfo = api.page.info();
        
        // Update page input value when page changes
        $('.page-input').val(pageInfo.page + 1);
    });


    // Filter button click event
    $('#btnFilter').click(function() {
        var tanggalAwal = $('#tanggal_awal').val();
        var tanggalAkhir = $('#tanggal_akhir').val();
        
        if (!tanggalAwal || !tanggalAkhir) {
            alert('Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu');
            return;
        }
        
        if (tanggalAwal > tanggalAkhir) {
            alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
            return;
        }
        
        window.location.href = "{{ route('laporan.rujukan-masuk') }}" +
            "?tanggal_awal=" + tanggalAwal +
            "&tanggal_akhir=" + tanggalAkhir;
    });
    
});
</script>
@endpush