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
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
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
        pageLength: 15,
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
        responsive: false
    });

    //table.on('draw', function() {
    //    $('#rujukanMasukTable').attr('style', 'width: 100% !important');
    //});

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
        
        // Reload table with new parameters
        table.ajax.reload();
        
        // Update PDF download link
        var pdfUrl = "{{ route('laporan.rujukan-masuk') }}" + 
                     "?tanggal_awal=" + tanggalAwal + 
                     "&tanggal_akhir=" + tanggalAkhir + 
                     "&download_pdf=true";
        $('.btn-danger').attr('href', pdfUrl);
        
        // Update period display
        var startDate = new Date(tanggalAwal).toLocaleDateString('id-ID');
        var endDate = new Date(tanggalAkhir).toLocaleDateString('id-ID');
        $('h5').first().text('Periode: ' + startDate + ' s/d ' + endDate);
        
        // Optionally reload the page to update statistics
        setTimeout(function() {
            window.location.replace("{{ route('laporan.rujukan-masuk') }}" + 
                                   "?tanggal_awal=" + tanggalAwal + 
                                   "&tanggal_akhir=" + tanggalAkhir);
        }, 500);
    });
    
    // Auto-reload table when dates change (optional)
    $('#tanggal_awal, #tanggal_akhir').change(function() {
        var tanggalAwal = $('#tanggal_awal').val();
        var tanggalAkhir = $('#tanggal_akhir').val();
        
        if (tanggalAwal && tanggalAkhir && tanggalAwal <= tanggalAkhir) {
            table.ajax.reload();
        }
    });
});
</script>
@endpush