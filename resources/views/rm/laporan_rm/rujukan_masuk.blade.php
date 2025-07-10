@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Jumlah Rujukan Masuk</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rujukan-masuk') }}" method="GET" class="mb-4">
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
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Cari..." name="keyword" value="{{ $keyword }}" form="searchForm">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit" form="searchForm">Cari</button>
                        </div>
                    </div>
                    <form id="searchForm" action="{{ route('laporan.rujukan-masuk') }}" method="GET">
                        <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                        <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
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
                            <th>Kategori Rujuk</th>
                            <th>No. Balasan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $key => $item)
                        <tr>
                            <td>{{ ($data->currentPage() - 1) * $data->perPage() + $key + 1 }}</td>
                            <td>{{ date('d-m-Y', strtotime($item->tgl_registrasi)) }}</td>
                            <td>{{ $item->no_rujuk }}</td>
                            <td>{{ $item->no_rawat }}</td>
                            <td>{{ $item->no_rkm_medis }}</td>
                            <td>{{ $item->nm_pasien }}</td>
                            <td>{{ $item->umur }}</td>
                            <td>{{ $item->perujuk }}</td>
                            <td>{{ $item->alamat_perujuk }}</td>
                            <td>{{ $item->dokter_perujuk }}</td>
                            <td>{{ $item->kd_penyakit }} - {{ $item->nm_penyakit }}</td>
                            <td>{{ $item->nm_poli }}</td>
                            <td>{{ $item->kategori_rujuk }}</td>
                            <td>{{ $item->no_balasan }}</td>
                            <td>{{ $item->keterangan }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 d-flex justify-content-center">
                {{ $data->appends(['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'keyword' => $keyword])->links() }}
            </div>

            <!-- Statistik Ringkasan -->
            <div class="mt-5">
                <h4>Ringkasan Statistik {{ !empty($keyword) ? '(Hasil Pencarian)' : '' }}</h4>
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

/* Mengurangi ukuran tombol pagination */
.pagination {
    font-size: 14px;
}

.page-link {
    padding: 0.375rem 0.75rem;
}

.pagination svg {
    width: 20px;
    height: 20px;
}
</style>
@endpush