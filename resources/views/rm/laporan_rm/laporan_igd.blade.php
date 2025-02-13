@extends('layout.app')
@section('content')
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
                    <center>LAPORAN BULANAN<br> REKAPITULASI PASIEN IGD<br>{{ $tgllap }}
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
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
