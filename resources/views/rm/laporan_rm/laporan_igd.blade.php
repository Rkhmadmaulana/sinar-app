@extends('layout.app')
@section('content')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

{{-- jQuery (WAJIB sebelum DataTables) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<style>
    th,
    td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <form id="filterForm" action="{{ route('igd') }}" method="POST">
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
                                            <dd><button type="submit" name="tombol" value="filter"
                                                    class="btn btn-primary">Filter</button></dd>
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
                    <center>LAPORAN BULANAN TAK KIRAIN SAMPEAN CUAYO<br> REKAPITULASI PASIEN IGD<br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi</small><br><br>
                    <div class="table-responsive">
                        <table style="width:100%;" class="table-bordered">
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Jenis Kasus</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Anggota</th>
                                <th style="text-align: center;background-color: #bdd9bf;">PNS</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Keluarga</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Siswa Dikbang</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Siswa Diktuk </th>
                                <th style="text-align: center;background-color: #bdd9bf;">Mandiri</th>
                                <th style="text-align: center;background-color: #bdd9bf;">BPJS</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Lainnya</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Total</th>
                                @foreach ($igd  as $a)
                                <tr>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $a->kasus }}</td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_result = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND pasien_polri.golongan_polri = '1'
                                                                    AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $anggota = $query_result[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $anggota }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_result2 = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND ( pasien_polri.golongan_polri = '2' OR  pasien_polri.golongan_polri = '7'  OR  pasien_polri.golongan_polri = '8' OR  pasien_polri.golongan_polri = '10')
                                                                    AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $pns = $query_result2[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $pns }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_result3 = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND ( pasien_polri.golongan_polri = '9' OR  pasien_polri.golongan_polri = '3' )
                                                                    AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $kel_anggota = $query_result3[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $kel_anggota }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php 
                                        // Query SQL dipisahkan dari Blade
                                        $query_result4 = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND ( pasien_polri.golongan_polri = '4' OR  pasien_polri.golongan_polri = '6' )
                                                                    AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $dikbang = $query_result4[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $dikbang }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_result5 = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND pasien_polri.golongan_polri = '5'
                                                                    AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $diktuk = $query_result5[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $diktuk }}
                                    </td>
                                    
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_umum = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND reg_periksa.kd_pj='UMU'", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $pasien_umum = $query_umum[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $pasien_umum }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_bpj = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND reg_periksa.kd_pj='BPJ' ", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $pasien_bpj = $query_bpj[0]->total - $anggota - $pns - $dikbang - $diktuk -  $kel_anggota; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $pasien_bpj }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_other = DB::select("SELECT COUNT(*) as total FROM reg_periksa 
                                                                    JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                                                                    JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus 
                                                                    WHERE master_triase_macam_kasus.macam_kasus = ? 
                                                                    AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                                                                    AND reg_periksa.kd_pj!='UMU' AND reg_periksa.kd_pj!='BPJ' ", [$a->kasus, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $pasien_other = $query_other[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $pasien_other }}
                                    </td>
                                    <td style="text-align: center;background-color: #F47174;">{{ $a->total }}</td>
                                </tr>
                                @endforeach
                        </table>
                    </div>
                    </br>
                    </br>
                    </br>
                    
                        <div class="alert alert-danger py-2 mb-3">
                            <small>
                                <strong>* Perhatian:</strong>
                                Filter pada tabel berikut hanya mengikuti <b>TAHUN</b> dari tanggal awal. <br/>
                                Pilih <b>TANGGAL</b> di "Dari Tanggal" dan langsung klik filter untuk dibaca <b>TAHUN</b>-nya saja,,
                            </small>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <strong>Layanan Lanjutan Pasien IGD – Tahun {{ $tahun }}</strong>
                            </div>

                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width:50%">Jenis Layanan</th>
                                            <th style="width:25%">Jumlah Pasien</th>
                                            <th style="width:25%">Persentase</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>Pulang Sembuh</td>
                                            <td class="text-end">{{ $pulang ?? 0 }}</td>
                                            <td class="text-end">{{ $persenPulang ?? 0 }}%</td>
                                        </tr>

                                        <tr>
                                            <td>Rujuk Rawat Inap (RRI)</td>
                                            <td class="text-end">{{ $rri ?? 0 }}</td>
                                            <td class="text-end">{{ $persenRri ?? 0 }}%</td>
                                        </tr>

                                        <tr>
                                            <td>Rujuk FKTL Lain</td>
                                            <td class="text-end">{{ $rujukKeluar ?? 0 }}</td>
                                            <td class="text-end">{{ $persenRujuk ?? 0 }}%</td>
                                        </tr>

                                        <tr>
                                            <td>Meninggal di IGD</td>
                                            <td class="text-end">{{ $meninggalIgd ?? 0 }}</td>
                                            <td class="text-end">{{ $persenMeninggalIgd ?? 0 }}%</td>
                                        </tr>

                                        <tr>
                                            <td>Lainnya</td>
                                            <td class="text-end">{{ $lainnya ?? 0 }}</td>
                                            <td class="text-end">{{ $persenLainnya ?? 0 }}%</td>
                                        </tr>
                                    </tbody>

                                    <tfoot class="table-secondary fw-bold">
                                        <tr>
                                            <td class="text-center">TOTAL</td>
                                            <td class="text-end">{{ $total ?? 0 }}</td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    <div class="table-responsive">


                        TESSSSS CHUGG
                        
                        <div class="text-center mb-3">
                            <h5 class="fw-bold mb-1">
                                Distribusi Kunjungan Pasien IGD & PONEK
                            </h5>
                            <div class="text-muted">
                                Berdasarkan Rekap Bulanan Tahun {{ $tahun }}
                            </div>
                        </div>
                        <table class="table table-bordered table-striped mt-4">
                            <thead class="table-dark">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">IGD</th>
                                    <th class="text-center">% IGD</th>
                                    <th class="text-center">PONEK</th>
                                    <th class="text-center">% PONEK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i=1; $i<=12; $i++)
                                @php
                                    $igd   = $data[$i]->igd   ?? 0;
                                    $ponek = $data[$i]->ponek ?? 0;

                                    $persenIgd   = $totalIgd > 0   ? round(($igd / $totalIgd) * 100, 2) : 0;
                                    $persenPonek = $totalPonek > 0 ? round(($ponek / $totalPonek) * 100, 2) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $bulan[$i] }}</td>
                                    <td class="text-end">{{ $igd }}</td>
                                    <td class="text-end">{{ $persenIgd }}%</td>
                                    <td class="text-end">{{ $ponek }}</td>
                                    <td class="text-end">{{ $persenPonek }}%</td>
                                </tr>
                                @endfor
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr style="background:#f8d7da;font-weight:bold;">
                                    <td class="text-center">TOTAL</td>
                                    <td class="text-end">{{ $totalIgd }}</td>
                                    <td class="text-end">100%</td>
                                    <td class="text-end">{{ $totalPonek }}</td>
                                    <td class="text-end">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
<!--    DATA KEMATIAN PASIEN IGD DAN PONEK    -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <strong>Data Kematian Pasien IGD & PONEK Tahun {{ $tahun }}</strong>
                        </div>

                        @php
                            function sortLink($label, $column, $sort, $order) {
                                $newOrder = ($sort === $column && $order === 'asc') ? 'desc' : 'asc';
                                $icon = '';

                                if ($sort === $column) {
                                    $icon = $order === 'asc' ? '↑' : '↓';
                                }

                                return '<a href="?sort='.$column.'&order='.$newOrder.'">'.$label.' '.$icon.'</a>';
                            }
                        @endphp

                        <div class="card-body table-responsive">
                        <table id="tabelKematian" class="table table-bordered table-striped table-sm">
                                <thead class="text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>No. RM</th>
                                        <th>Nama Pasien</th>
                                        <th>Alamat</th>
                                        <th>Unit</th>
                                        <th>ICD 1</th>
                                        <th>ICD 2</th>
                                        <th>ICD 3</th>
                                        <th>ICD 4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataKematian as $i => $row)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $row->no_rkm_medis }}</td>
                                            <td>{{ $row->nm_pasien }}</td>
                                            <td style="max-width:200px; white-space:normal;">
                                                {{ $row->alamat }}
                                            </td>
                                            <td class="text-center">
                                                @if ($row->kd_poli == 'PNK')
                                                    PONEK
                                                @else
                                                    IGD
                                                @endif
                                            </td>
                                            <td>{{ $row->icd1 ?? 'TAD' }}</td>
                                            <td>{{ $row->icd2 ?? 'TAD' }}</td>
                                            <td>{{ $row->icd3 ?? 'TAD' }}</td>
                                            <td>{{ $row->icd4 ?? 'TAD' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                Tidak ada data kematian pada tahun {{ $tahun }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <script>
                        $(document).ready(function () {
                            $('#tabelKematian').DataTable({
                                pageLength: 10,
                                lengthMenu: [10, 25, 50, 100],
                                ordering: true,
                                searching: true,
                                info: true,
                                language: {
                                    search: "Cari:",
                                    lengthMenu: "Tampilkan _MENU_ data",
                                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                                    paginate: {
                                        previous: "Sebelumnya",
                                        next: "Berikutnya"
                                    },
                                    zeroRecords: "Data tidak ditemukan"
                                }
                            });
                        });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
