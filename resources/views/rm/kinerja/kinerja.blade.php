@extends('layout.app')
@section('content')

@php
$id_user = session()->get('id_user');
$nik = session()->get('nik');
$level = session()->get('level');

$user = DB::table('user_dashboard')
    ->select('*') // Gunakan select untuk memilih kolom
    ->where('id_user', $id_user) // Gunakan where untuk parameter
    ->first();

@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Keluar</small><br><br>
    <div class="row">
        <div class="col-md-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <form id="filterForm" action="{{ route('kinerja') }}" method="POST"> 
                                @csrf
                                    <div class="row clearfix">
                                        <div class="col-md-6"><br>
                                          <div class="form-group">
                                            <div class="form-line">
                                              <dt>Dari Tanggal</dt>
                                              <dd>
                                                @if(isset($tgl1))
                                                <input type="date" value="{{ $tgl1 }}" class="form-control" name="tgl1">
                                                @else
                                                <input type="date" class="form-control" name="tgl1">
                                                @endif
                                              </dd>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-md-6"><br>
                                          <div class="form-group">
                                            <div class="form-line">
                                              <dt>Sampai Tanggal</dt>
                                              <dd>
                                                @if(isset($tgl2))
                                                <input type="date" value="{{ $tgl2 }}" class="form-control" name="tgl2">
                                                @else
                                                <input type="date" class="form-control" name="tgl2">
                                                @endif
                                              </dd>
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-md-12">
                                          <div class="form-group">
                                            <dd><button type="submit" name="tombol" value="filter" class="btn btn-primary" style="margin-top:5px;">Filter</button></dd>
                                          </div>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- AWAL DATA PASIEN-->
                <div class="table-responsive">
                <table class="table table-bordered" style="min-width: 1200px;">
                                <tr>
                                    <td valign='top' style="center;background-color:rgb(141, 250, 148)" rowspan="2">NO</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148);" rowspan="2">NAMA BANGSAL</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148);" rowspan="2"> T T </td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148);" colspan="3">PASIEN MASUK</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" colspan="5">PASIEN KELUAR</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">LAMA DIRAWAT</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">SISA PASIEN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">HARI PERAWATAN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">BOR %</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">LOS (hari)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">BTO (kali)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">TOI (hari)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">NDR PERMILL</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2">GDR PERMILL</td>
                                </tr>
                                <tr>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">AWAL</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">MASUK</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">PINDAHAN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">DIPINDAHKAN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">KELUAR HIDUP</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">KELUAR < 48 JAM</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">KELUAR > 48 JAM</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">TOTAL MATI</td>
                                </tr>
                                <tr>
                                    <td rowspan="4">1</td>
                                    <td style="text-align: center;background-color: #bdd9bf;"> Ruang Bedah (Kerapu)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['kerapu'] ?? 0 }}</td>    
                                </tr>
                                <tr>
                                    <td>Kerapu Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas1'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Kerapu Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas2'] ?? 0 }}</td>         
                                </tr>
                                <tr>
                                    <td>Kerapu Kelas 3</td>
                                    <td style="text-align: center">{{ $tempatTidur['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas3'] ?? 0 }}</td>          
                                </tr>
                                <tr>
                                    <td rowspan="4">2</td>
                                    <td style="text-align: center;background-color: #bdd9bf;" > Ruang Penyakit Dalam (Kakap)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['kakap'] ?? 0 }}</td>           
                                </tr>
                                <tr>
                                    <td>Kakap Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas1'] ?? 0 }}</td>           
                                </tr>
                                <tr>
                                    <td>Kakap Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas2'] ?? 0 }}</td>          
                                </tr>
                                <tr>
                                    <td>Kakap Kelas 3</td>
                                    <td style="text-align: center">{{ $tempatTidur['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas3'] ?? 0 }}</td>           
                                </tr>
                                <tr>
                                    <td rowspan="4">3</td>
                                    <td style="text-align: center;background-color: #bdd9bf;" > Ruang Anak (Terakulu)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['terakulu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['terakulu'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Terakulu Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas1'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Terakulu Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas2'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Terakulu Kelas 3</td>
                                    <td style="text-align: center">{{ $tempatTidur['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas3'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td rowspan="4">4</td>
                                    <td style="text-align: center;background-color: #bdd9bf;" > Ruang OBSGYN (Balleraja)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['balleraja'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Balleraja Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas1'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Balleraja Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas2'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>Balleraja Kelas 3</td>
                                    <td style="text-align: center">{{ $tempatTidur['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $los['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas3'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang Kohort (Tenggiri)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['tenggiri'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang VIP (Barunang) Atas</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['barunang'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang VIP (Lobster) Bawah</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['lobster'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang ICU (Lumba-Lumba)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['lumbaLumba'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['lumbaLumba'] ?? 0 }}</td>            
                                </tr>
                                <tr>
                                    <td></td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">Jumlah Total</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $tempatTidur['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienAwal['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienMasuk['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienPindahan['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienKeluarPindahan['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienKeluarHidup['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienMeninggal48Jam['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienMeninggal48plus['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $pasienMeninggalTotal['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $lamaDirawat['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $sisaPasien['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $hariPerawatan['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $bor['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $los['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $bto['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $toi['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $ndr['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color:rgb(91, 245, 101);">{{ $gdr['total'] ?? 0 }}</td>            
                                </tr>
                            </table>
                            <!-- AKHIR DATA PASIEN-->
            </div>
        </div>
    </div> 
</div>
<div class="card-table-wrapper">
    
</div>
{{-- Hapus kondisi login, langsung tampilkan  --}}
<div class="container-xxl flex-grow-1 container-p-y mt-4">
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Setting Jumlah Bed</h5>
                        </div>
                        <?php
                            $sqlkmrdewasa = DB::select("select jumlah  from jumlah_kamar where kamar='kamar_dewasa' ");
                            $sqlkmrbayi = DB::select("select jumlah from jumlah_kamar where kamar='kamar_bayi' ");
                            
                            $jmlkmrdewasa = $sqlkmrdewasa[0]->jumlah ?? 0;
                            $jmlkmrbayi = $sqlkmrbayi[0]->jumlah ?? 0;
                        ?>
                        <form id="filterForm" action="{{ route('setjumlahbed') }}" method="POST"> 
                            @csrf
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Jumlah Bed Dewasa</dt>
                                            <dd>
                                                <input type="number" value="{{ $jmlkmrdewasa }}" class="form-control" name="bed_dewasa" placeholder="Jumlah Bed Dewasa" required>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Jumlah Bed Bayi</dt>
                                            <dd>
                                                <input type="number" value="{{ $jmlkmrbayi }}" class="form-control" name="bed_bayi" placeholder="Jumlah Bed Bayi" required>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <dd><button type="submit" name="tombol" value="Submit" class="btn btn-primary" style="margin-top:5px;">Update</button> </dd>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row"> 
        <div class="col-md-9 col-lg-9 col-xl-9 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body"><br>
                    {!! $jmlpasien->container() !!} 
                </div>
            </div>
        </div> 
        <div class="col-md-3 col-lg-3 col-xl-3 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body" >
                    <h5 class="card-title" >Total Pasien (Hidup + Mati)</h5>
                    Dewasa: {{ $jmldewasa->total ?? '0' }}<br>
                    Bayi: {{ $jmlbayi->total ?? '0' }}<br>
                    <br>
                    <h5 class="card-title" >Rata-rata pasien dirawat 1 hari</h5>
                    Dewasa: {{ $pasienper1haridewasa ?? '0' }}<br>
                    Bayi: {{ $pasienper1haribayi ?? '0' }}<br>
                </div>
            </div>
        </div>
        
        <div class="col-md-9 col-lg-9 col-xl-9 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Grafik Lama Rawat</h5>
                    
                    @if(isset($jmllamapasien))
                        {!! $jmllamapasien->container() !!}
                    @else
                        <p>Grafik jmllamapasien tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-lg-3 col-xl-3 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Hari Perawatan</h5>
                    Dewasa: {{ $jumlah_rawat_dewasa->total_lama ?? '0' }}<br>
                    Bayi: {{ $jumlah_rawat_bayi->total_lama ?? '0' }}<br>
                    <br>
                    <h5 class="card-title">LOS</h5>
                
                    Dewasa: {{ $los_dewasa ?? '0' }}<br>
                    Bayi: {{ $los_bayi ?? '0' }}<br>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <table style="width:100%;" class="table-bordered">
                        <tr>
                            <th valign='top' style="text-align: center ;background-color: #bdd9bf;" colspan="4" >Data Pasien (Mati)</th>
                        </tr>
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;" colspan="2">Total Dewasa (Mati):</th>
                            <td valign='top' style="text-align: center" colspan="2">{{ $jmldewasamati->total ?? '0' }}</td>
                        </tr>
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;" colspan="2">Total Bayi (Mati):</th>
                            <td valign='top' style="text-align: center" colspan="2">{{ $jmlbayimati->total ?? '0' }}</td>
                        </tr>
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Dewasa (Mati > 2 Hari):</th>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Bayi (Mati > 2 Hari):</th>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Dewasa (Mati < 2 Hari):</th>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Bayi (Mati < 2 Hari):</th>  
                        </tr>
                        <tr>
                            <td valign='top' style="text-align: center">{{ $pasienmatidewasalebih2hari ?? '0' }}</td>
                            <td valign='top' style="text-align: center">{{ $pasienmatibayilebih2hari ?? '0' }}</td>
                            <td valign='top' style="text-align: center">{{ $pasienmatidewasakurang2hari ?? '0' }}</td>
                            <td valign='top' style="text-align: center">{{ $pasienmatibayikurang2hari ?? '0' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <table style="width:100%;" class="table-bordered">
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;" colspan="4">Data / Parameter</th>
                        </tr>
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Bed Dewasa:</th>
                            <td valign='top' style="text-align: center">{{ $jmlkamardewasa }}</td>
                        </tr>
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Bed Bayi:</th>
                            <td valign='top' style="text-align: center">{{ $jmlkamarbayi }}</td>
                        </tr>
                        <tr>
                            <th valign='top' style="text-align: center;background-color: #bdd9bf;">Periode hari:</th>
                            <td valign='top' style="text-align: center">{{ $days ?? '0' }}</td>
                        </tr> 
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Bed Occupancy Rate (BOR) </h5>
                    Dewasa: ({{ $jumlah_rawat_dewasa->total_lama ?? '0'}} / ({{ $jmlkamardewasa }} x {{ $days }})) x 100% = {{ $bor_dewasa?? '0' }}<br>
                    Bayi: ({{ $jumlah_rawat_bayi->total_lama ?? '0' }} / ({{ $jmlkamarbayi }} x {{ $days }})) x 100% = {{ $bor_bayi?? '0' }}<br>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Length Of Stay (Avg LOS) </h5>
                    Dewasa: {{ $los_dewasa ?? '0' }} / {{ $jmldewasa->total ?? '0'}} = {{ $alos_dewasa?? '0' }}<br>
                    Bayi: {{ $los_bayi ?? '0' }} / {{ $jmlbayi->total ?? '0'}} = {{ $alos_bayi?? '0' }}<br>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Bed Turn Over (BTO)</h5>
                    Dewasa:  {{ $jmldewasa->total?? '0' }} / {{ $jmlkamardewasa }} = {{ $bto_dewasa?? '0' }}<br>
                    Bayi:  {{ $jmlbayi->total?? '0' }} / {{ $jmlkamarbayi }} = {{ $bto_bayi?? '0' }}<br>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Turn Over Interval (TOI) </h5>
                    Dewasa: (({{ $jmlkamardewasa }} x  {{ $days?? '0' }} ) - {{ $jumlah_rawat_dewasa->total_lama ?? '0' }}) / {{ $jmldewasa->total }} = {{ $toi_dewasa?? '0' }} <br>
                    Bayi: (({{ $jmlkamarbayi }} x  {{ $days?? '0' }}) - {{ $jumlah_rawat_bayi->total_lama ?? '0' }}) / {{ $jmlbayi->total }} = {{ $toi_bayi?? '0' }} <br>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Nett Death Rate (NDR) </h5>
                    Dewasa:  ({{ $pasienmatidewasalebih2hari ?? '0' }} / {{ $jmldewasa->total ?? '0' }}) x 1000% = {{ $ndr_dewasa ?? '0' }}<br>
                    Bayi: ({{ $pasienmatibayilebih2hari ?? '0' }} / {{ $jmlbayi->total ?? '0' }}) x 1000% = {{ $ndr_bayi ?? '0' }}<br>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-6 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Gross Death Rate (GDR) </h5>
                    Dewasa:  ({{ $jmldewasamati->total ?? '0' }} / {{ $jmldewasa->total ?? '0' }}) x 1000% = {{ $gdr_dewasa ?? '0' }}<br>
                    Bayi: ({{ $jmlbayimati->total ?? '0' }} / {{ $jmlbayi->total ?? '0' }}) x 1000% = {{ $gdr_bayi ?? '0' }}<br>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ddssd -->
<script src="{{ $jmlpasien->cdn() }}"></script>
<script src="{{ $jmllamapasien->cdn() }}"></script>


{{ $jmlpasien->script() }}
{{ $jmllamapasien->script() }}

@endsection