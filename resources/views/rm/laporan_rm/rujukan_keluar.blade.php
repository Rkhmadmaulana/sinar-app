@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Jumlah Rujukan Keluar</h3>
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('laporan.rujukan-keluar') }}" method="GET" class="mb-4" id="filterForm">
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
                        <a href="{{ route('laporan.rujukan-keluar', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'download_pdf' => true]) }}"
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
                'tableId' => 'rujukanKeluarTable',
                'searchPlaceholder' => 'Cari rujukan keluar...',
                'columns' => [
                    ['data' => 'DT_RowIndex', 'title' => 'No.'],
                    ['data' => 'tgl_rujuk', 'title' => 'Tgl. Rujuk'],
                    ['data' => 'jam', 'title' => 'Jam Rujuk'],
                    ['data' => 'no_rujuk', 'title' => 'No. Rujuk'],
                    ['data' => 'no_rawat', 'title' => 'No. Rawat'],
                    ['data' => 'no_rkm_medis', 'title' => 'No. RM'],
                    ['data' => 'nm_pasien', 'title' => 'Nama Pasien'],
                    ['data' => 'rujuk_ke', 'title' => 'Tempat Rujuk'],
                    ['data' => 'keterangan_diagnosa', 'title' => 'Diagnosa'],
                    ['data' => 'nm_dokter', 'title' => 'Dokter Perujuk'],
                    ['data' => 'kat_rujuk', 'title' => 'Kategori Rujuk'],
                    ['data' => 'ambulance', 'title' => 'Ambulance'],
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
                                <h5 class="card-title">Total Pasien Rujukan Keluar</h5>
                                <h3 class="card-text">{{ $totalPasien }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Statistik Berdasarkan Tanggal --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Jumlah Pasien Berdasarkan Tanggal Rujuk</h5>
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

                {{-- Statistik Berdasarkan Tempat Rujuk --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Jumlah Pasien Berdasarkan Tempat Rujuk</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Tempat Rujuk</th>
                                        <th>Jumlah Pasien</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pasienPerTempatRujuk as $tempatRujuk => $jumlah)
                                    <tr>
                                        <td>{{ $tempatRujuk ?: 'Tidak Tercatat' }}</td>
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
    var table = initCustomDataTable('rujukanKeluarTable', {
        ajax: {
            url: "{{ route('laporan.rujukan-keluar') }}",
            data: function(d) {
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'tgl_rujuk', name: 'rujuk.tgl_rujuk'},
            {data: 'jam', name: 'rujuk.jam'},
            {data: 'no_rujuk', name: 'rujuk.no_rujuk'},
            {data: 'no_rawat', name: 'rujuk.no_rawat'},
            {data: 'no_rkm_medis', name: 'reg_periksa.no_rkm_medis'},
            {data: 'nm_pasien', name: 'pasien.nm_pasien'},
            {data: 'rujuk_ke', name: 'rujuk.rujuk_ke'},
            {data: 'keterangan_diagnosa', name: 'rujuk.keterangan_diagnosa'},
            {data: 'nm_dokter', name: 'dokter.nm_dokter'},
            {data: 'kat_rujuk', name: 'rujuk.kat_rujuk'},
            {data: 'ambulance', name: 'rujuk.ambulance'},
            {data: 'keterangan', name: 'rujuk.keterangan'}
        ],
        order: [[1, 'desc']] // Sort by tanggal rujuk descending
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
        
        window.location.href = "{{ route('laporan.rujukan-keluar') }}" +
            "?tanggal_awal=" + tanggalAwal +
            "&tanggal_akhir=" + tanggalAkhir;
    });
});
</script>
@endpush