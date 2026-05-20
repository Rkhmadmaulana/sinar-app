@extends('layout.app')
@section('content')
<style>
    th,
    td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .table-laporan {
    width: 100%;
    border-collapse: collapse;
    }

    .table-laporan th,
    .table-laporan td {
        padding: 8px;
        text-align: center;
        border: 1px solid #ddd;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <form id="filterForm" action="{{ route('kunjunganrajal') }}" method="POST">
                                @csrf
                                <div class="row clearfix">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <dt>Dari Tanggal</dt>
                                                <dd>
                                                    @if (isset($tgl1))
                                                        <input type="date" value="{{ $tgl1 }}"
                                                            class="form-control" name="tgl1">
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
                                                        <input type="date" value="{{ $tgl2 }}"
                                                            class="form-control" name="tgl2">
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
                                                <button type="submit" name="tombol" value="filter"
                                                    class="btn btn-primary">Filter</button>
                                                <button type="submit" name="download_pdf" value="1"
                                                    class="btn btn-danger"><i class="bx bx-download"></i> Download PDF</button>
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
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <center>LAPORAN BULANAN<br>JUMLAH PENGUNJUNG DAN KUNJUNGAN RAWAT JALAN <br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi</small><br><br>
                    <div class="table-responsive">
                        <table style="width:100%;" class="table-bordered">
                            <tr>
                                <th style="text-align: center;background-color: #F47174;" colspan="9">
                                    Pengunjung</th>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="9">
                                    Kunjungan</th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #F47174;">Anggota</th>
                                <th style="text-align: center;background-color: #F47174;">PNS</th>
                                <th style="text-align: center;background-color: #F47174;">Keluarga</th>
                                <th style="text-align: center;background-color: #F47174;">Siswa Dikbang</th>
                                <th style="text-align: center;background-color: #F47174;">Siswa Diktuk </th>
                                <th style="text-align: center;background-color: #F47174;">Mandiri</th>
                                <th style="text-align: center;background-color: #F47174;">BPJS</th>
                                <th style="text-align: center;background-color: #F47174;">Lainnya</th>
                                <th style="text-align: center;background-color: #F47174;">Total</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Anggota</th>
                                <th style="text-align: center;background-color: #bdd9bf;">PNS</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Keluarga</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Siswa Dikbang</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Siswa Diktuk </th>
                                <th style="text-align: center;background-color: #bdd9bf;">Mandiri</th>
                                <th style="text-align: center;background-color: #bdd9bf;">BPJS</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Lainnya</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Total</th>
                            </tr>
                            <tr>
                                <th style="text-align: center;">{{ $anggotapolri->anggota_polri }}</th>
                                <th style="text-align: center;">{{ $anggotapns->anggota_pns }}</th>
                                <th style="text-align: center;">{{ $anggotakelpolri->anggota_kel_polri }}</th>
                                <th style="text-align: center;">{{ $dikbang->siswa_dikbang }}</th>
                                <th style="text-align: center;">{{ $diktuk->siswa_diktuk }} </th>
                                <th style="text-align: center;">{{ $pasien_umum->pasienumum }}</th>
                                <th style="text-align: center;">{{ $total_pengunjung_bpjs }}</th>
                                <th style="text-align: center;">{{ $pasien_other->pasienother }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_pengunjung }}</th>
                                <th style="text-align: center;">{{ $anggotapolri->kunjungan_anggota_polri }}</th>
                                <th style="text-align: center;">{{ $anggotapns->kunjungan_anggota_pns }}</th>
                                <th style="text-align: center;">{{ $anggotakelpolri->kunjungan_kel_polri }}</th>
                                <th style="text-align: center;">{{ $dikbang->kunjungan_siswa_dikbang }}</th>
                                <th style="text-align: center;">{{ $diktuk->kunjungan_siswa_diktuk }} </th>
                                <th style="text-align: center;">{{ $pasien_umum->kunjungan_pasienumum }}</th>
                                <th style="text-align: center;">{{ $total_kunjungan_bpjs }}</th>
                                <th style="text-align: center;">{{ $pasien_other->kunjungan_pasienother }}</th>
                                <th style="text-align: center;background-color: #bdd9bf;">{{ $total_kunjungan }}</th>
                            </tr>
                        </table>
                    </div>
                </div>
                <!--TAMBAHAN GAWIANKU-->
                <div class="card mt-4">
                    <div class="card-body table-responsive">
                        <table class="table-laporan">
                        <div class="card-header bg-info text-white">
                            <strong>Laporan Hasil Rawat Jalan Tahun {{ $tahun }}</strong>
                        </div>
                            <thead class="table-light text-center">
                            <tr style="background:#17a2b8; color:white;">
                                <th>Hasil Pelayanan</th>
                                <th>Jumlah Pasien</th>
                                <th>Persentase</th>
                            </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Sembuh</td>
                                    <td class="text-end">{{ $sembuhRalan ?? 0 }}</td>
                                    <td class="text-end">{{ $persenSembuhRalan ?? 0 }}%</td>
                                </tr>

                                <tr>
                                    <td>Rujuk ke Rawat Inap</td>
                                    <td class="text-end">{{ $ranapRalan ?? 0 }}</td>
                                    <td class="text-end">{{ $persenRanapRalan ?? 0 }}%</td>
                                </tr>

                                <tr>
                                    <td>Lainnya</td>
                                    <td class="text-end">{{ $lainnyaRalan ?? 0 }}</td>
                                    <td class="text-end">{{ $persenLainnyaRalan ?? 0 }}%</td>
                                </tr>
                            </tbody>

                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td class="text-center">TOTAL</td>
                                    <td class="text-end">{{ $totalRalan ?? 0 }}</td>
                                    <td class="text-end">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>



            </div>
        </div>
    </div>
</div>

@endsection
