@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Jumlah Rujukan Masuk</h3>
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
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

            {{-- Menggunakan Komponen DataTable --}}
            @include('layout.datatable', [
                'tableId' => 'rujukanMasukTable',
                'searchPlaceholder' => 'Cari rujukan masuk...',
                'columns' => [
                    ['data' => 'DT_RowIndex', 'title' => 'No.'],
                    ['data' => 'tgl_registrasi', 'title' => 'Tgl. Registrasi'],
                    ['data' => 'no_rujuk', 'title' => 'No. Rujuk'],
                    ['data' => 'no_rawat', 'title' => 'No. Rawat'],
                    ['data' => 'no_rkm_medis', 'title' => 'No. RM'],
                    ['data' => 'nm_pasien', 'title' => 'Nama Pasien'],
                    ['data' => 'umur', 'title' => 'Umur'],
                    ['data' => 'perujuk', 'title' => 'Perujuk'],
                    ['data' => 'alamat_perujuk', 'title' => 'Alamat Perujuk'],
                    ['data' => 'dokter_perujuk', 'title' => 'Dokter Perujuk'],
                    ['data' => 'diagnosa', 'title' => 'Diagnosa Awal'],
                    ['data' => 'nm_poli', 'title' => 'Poli Rujukan'],
                    ['data' => 'no_balasan', 'title' => 'No. Balasan'],
                    ['data' => 'keterangan', 'title' => 'Keterangan']
                ]
            ])

            {{-- Statistik Ringkasan --}}
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

                {{-- Statistik Berdasarkan Tanggal --}}
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

                {{-- Statistik Berdasarkan Perujuk --}}
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

                {{-- Statistik Berdasarkan Diagnosa --}}
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

                {{-- Statistik Berdasarkan Poli --}}
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

{{-- Custom styles untuk responsive form --}}
@push('styles')
<style>
@media (max-width: 768px) {
    .row.align-items-center > div {
        margin-bottom: 10px;
    }
}
</style>
@endpush

{{-- JavaScript untuk inisialisasi DataTable --}}
@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi DataTable menggunakan komponen reusable
    var table = initCustomDataTable('rujukanMasukTable', {
        ajax: {
            url: "{{ route('laporan.rujukan-masuk') }}",
            data: function(d) {
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
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
        order: [[1, 'desc']] // Sort by tanggal registrasi descending
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
        
        // Update export URL
        const exportBtn = $('.dt_custom_n_export_container a');
        const baseUrl = "{{ route('laporan.rujukan-masuk') }}";
        const newUrl = baseUrl + "?tanggal_awal=" + tanggalAwal + "&tanggal_akhir=" + tanggalAkhir + "&download_pdf=true";
        exportBtn.attr('href', newUrl);
        
        // Reload table with new parameters
        table.ajax.reload();
    });
});
</script>
@endpush