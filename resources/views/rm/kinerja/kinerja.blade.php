@extends('layout.app')
@section('content')
<style>
@page {
  size: A4 landscape;
  margin: 10mm;
}
.print-only {
  /* transform: scale(1); */
}

@media print {
  @page {
    size: A4 landscape;
    margin: 5mm;
  }
  
  body * { 
    visibility: hidden !important; 
  }
  
  .print-only, .print-only * { 
    visibility: visible !important; 
  }
  
  .print-only {
    position: fixed !important;  /* Gunakan fixed bukan absolute */
    top: 0 !important;
    left: 0 !important;
    width: 200% !important;      /* Perbesar width untuk kompensasi scale */
    height: 200% !important;     /* Perbesar height untuk kompensasi scale */
    transform: scale(0.50) !important;
    transform-origin: top left !important;
    overflow: hidden !important;
  }
  
  .print-only .table-responsive {
    overflow: visible !important;
    width: 100% !important;
  }
  
  table {
    page-break-inside: auto !important;
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 0 !important;
  }
  
  /* Hilangkan margin/padding yang tidak perlu */
  body {
    margin: 0 !important;
    padding: 0 !important;
  }
}
</style>


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
                                            <dd>
                                                <button type="submit" name="tombol" value="filter" class="btn btn-primary" style="margin-top:5px;">Filter</button>
                                                <button onclick="printTable()" class="btn btn-secondary" style="margin-top:5px;">🖨️ Print Halaman</button>
                                            </dd>
                                          </div>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- AWAL DATA PASIEN-->
                {{-- Sebelum tabel: tutup semua tag “non-print” dengan no-print --}}
                <div class="no-print">
                {{-- … semua elemen sebelum tabel (grafik, form, header, dsb) --}}
                </div>

                {{-- Begin print-only wrapper --}}
                <div class="print-only">
                <div class="table-responsive">
                <table class="table table-bordered" style="min-width: 1200px;">
                                <thead>
                                <tr>
                                    <td valign='top' style="center;background-color:rgb(141, 250, 148)" rowspan="3">NO</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148);" rowspan="3">NAMA BANGSAL</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148);" rowspan="3"> T T </td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148);" colspan="4">PASIEN MASUK</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" colspan="11">PASIEN KELUAR</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">LAMA DIRAWAT</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">SISA PASIEN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">HARI PERAWATAN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">BOR %</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">LOS (hari)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">BTO (kali)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="3">TOI (hari)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2" colspan="2">NDR PERMILL</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" rowspan="2" colspan="2">GDR PERMILL</td>
                                </tr>
                                <tr>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" rowspan="2">AWAL</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" colspan="2">MASUK</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" rowspan="2">PINDAHAN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" rowspan="2">DIPINDAHKAN</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" colspan="2">KELUAR HIDUP</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" rowspan="2">APS/PULANG PAKSA</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" rowspan="2">PULANG HARI SAMA <br>(APS/Rujuk/paksa/meninggal)</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" colspan="2">MENINGGAL < 48 JAM</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" colspan="2">MENINGGAL > 48 JAM</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column" colspan="2">TOTAL MATI</td>
                                </tr>
                                <tr>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">L_Masuk</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">P_Masuk</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)">L_Keluar</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">P_Keluar</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">L</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">P</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">L</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">P</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">L</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">P</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">L</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">P</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">L</td>
                                    <td valign='top' style="text-align: center;background-color:rgb(141, 250, 148)" class="col-name wrap-column">P</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td rowspan="4">1</td>
                                    <td style="text-align: center;background-color: #bdd9bf;"> Ruang Bedah (Kerapu)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['kerapu'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienAwal['kerapu'] ?? 0) + ($pasienAwal['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMasuk['kerapu_L'] ?? 0) + ($pasienMasuk['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMasuk['kerapu_P'] ?? 0) + ($pasienMasuk['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPindahan['kerapu'] ?? 0) + ($pasienPindahan['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarPindahan['kerapu'] ?? 0) + ($pasienKeluarPindahan['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarHidup['kerapu_L'] ?? 0) + ($pasienKeluarHidup['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarHidup['kerapu_P'] ?? 0) + ($pasienKeluarHidup['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPulangTidakStandar['kerapu'] ?? 0) + ($pasienPulangTidakStandar['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPulangHariSama['kerapu'] ?? 0) + ($pasienPulangTidakStandar['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48Jam['kerapu_L'] ?? 0) + ($pasienMeninggal48Jam['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48Jam['kerapu_P'] ?? 0) + ($pasienMeninggal48Jam['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48plus['kerapu_L'] ?? 0) + ($pasienMeninggal48plus['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48plus['kerapu_P'] ?? 0) + ($pasienMeninggal48plus['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggalTotal['kerapu_L'] ?? 0) + ($pasienMeninggalTotal['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggalTotal['kerapu_P'] ?? 0) + ($pasienMeninggalTotal['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($lamaDirawat['kerapu'] ?? 0) + ($lamaDirawat['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($sisaPasien['kerapu'] ?? 0) + ($sisaPasien['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($hariPerawatan['kerapu'] ?? 0) + ($hariPerawatan['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($bor['kerapu'] ?? 0) + ($bor['selasar'] ?? 0) }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($los['kerapu'] ?? 0) + ($los['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($bto['kerapu'] ?? 0) + ($bto['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($toi['kerapu'] ?? 0) + ($toi['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($ndr['kerapu_L'] ?? 0) + ($ndr['selasar_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($ndr['kerapu_P'] ?? 0) + ($ndr['selasar_P'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($gdr['kerapu_L'] ?? 0) + ($gdr['selasar_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($gdr['kerapu_P'] ?? 0) + ($gdr['selasar_P'] ?? 0) }}‰</td>
                                </tr>
                                <tr>
                                    <td>Kerapu Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kerapuKelas1'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kerapuKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas1_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas1_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>Kerapu Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kerapuKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kerapuKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kerapuKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kerapuKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kerapuKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kerapuKelas2'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kerapuKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['kerapuKelas2_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kerapuKelas2_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>Kerapu Kelas 3</td>
                                    <td style="text-align: center">{{ ($tempatTidur['kerapuKelas3'] ?? 0) + ($tempatTidur['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienAwal['kerapuKelas3'] ?? 0) + ($pasienAwal['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMasuk['kerapuKelas3_L'] ?? 0) + ($pasienMasuk['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMasuk['kerapuKelas3_P'] ?? 0) + ($pasienMasuk['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienPindahan['kerapuKelas3'] ?? 0) + ($pasienPindahan['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienKeluarPindahan['kerapuKelas3'] ?? 0) + ($pasienKeluarPindahan['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienKeluarHidup['kerapuKelas3_L'] ?? 0) + ($pasienKeluarHidup['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienKeluarHidup['kerapuKelas3_P'] ?? 0) + ($pasienKeluarHidup['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienPulangTidakStandar['kerapuKelas3'] ?? 0) + ($pasienPulangTidakStandar['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienPulangHariSama['kerapuKelas3'] ?? 0) + ($pasienKeluarHidup['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48Jam['kerapuKelas3_L'] ?? 0) + ($pasienMeninggal48Jam['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48Jam['kerapuKelas3_P'] ?? 0) + ($pasienMeninggal48Jam['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48plus['kerapuKelas3_L'] ?? 0) + ($pasienMeninggal48plus['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48plus['kerapuKelas3_P'] ?? 0) + ($pasienMeninggal48plus['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggalTotal['kerapuKelas3_L'] ?? 0) + ($pasienMeninggalTotal['selasar_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggalTotal['kerapuKelas3_P'] ?? 0) + ($pasienMeninggalTotal['selasar_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($lamaDirawat['kerapuKelas3'] ?? 0) + ($lamaDirawat['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($sisaPasien['kerapuKelas3'] ?? 0) + ($sisaPasien['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($hariPerawatan['kerapuKelas3'] ?? 0) + ($hariPerawatan['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($bor['kerapuKelas3'] ?? 0) + ($bor['selasar'] ?? 0) }}%</td>
                                    <td style="text-align: center">{{ ($los['kerapuKelas3'] ?? 0) + ($los['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($bto['kerapuKelas3'] ?? 0) + ($bto['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($toi['kerapuKelas3'] ?? 0) + ($toi['selasar'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($ndr['kerapuKelas3_L'] ?? 0) + ($ndr['selasar_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center">{{ ($ndr['kerapuKelas3_P'] ?? 0) + ($ndr['selasar_P'] ?? 0) }}‰</td>
                                    <td style="text-align: center">{{ ($gdr['kerapuKelas3_L'] ?? 0) + ($gdr['selasar_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center">{{ ($gdr['kerapuKelas3_P'] ?? 0) + ($gdr['selasar_P'] ?? 0) }}‰</td>
                                </tr>
                                <tr>
                                    <td rowspan="4">2</td>
                                    <td style="text-align: center;background-color: #bdd9bf;" > Ruang Penyakit Dalam (Kakap)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['kakap_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['kakap_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['kakap_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['kakap_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangTidakStandar['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangHariSama['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['kakap_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['kakap_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['kakap_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['kakap_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['kakap_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['kakap_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['kakap'] ?? 0 }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['kakap'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['kakap_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['kakap_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['kakap_L'] ?? 0 }}‰</td>           
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['kakap_P'] ?? 0 }}‰</td>           
                                </tr>
                                <tr>
                                    <td>Kakap Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kakapKelas1'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kakapKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas1_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas1_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>Kakap Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kakapKelas2'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kakapKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas2_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas2_P'] ?? 0 }}‰</td>         
                                </tr>
                                <tr>
                                    <td>Kakap Kelas 3</td>
                                    <td style="text-align: center">{{ $tempatTidur['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['kakapKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['kakapKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['kakapKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['kakapKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['kakapKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['kakapKelas3'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['kakapKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas3_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['kakapKelas3_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas3_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['kakapKelas3_P'] ?? 0 }}‰</td>      
                                </tr>
                                <tr>
                                    <td rowspan="4">3</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang Anak (Terakulu)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($tempatTidur['terakulu'] ?? 0) + ($tempatTidur['isolasi'] ?? 0) + ($tempatTidur['inkubator'] ?? 0) + ($tempatTidur['box'] ?? 0) + ($tempatTidur['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienAwal['terakulu'] ?? 0) + ($pasienAwal['isolasi'] ?? 0) + ($pasienAwal['inkubator'] ?? 0) + ($pasienAwal['box'] ?? 0) + ($pasienAwal['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMasuk['terakulu_L'] ?? 0) + ($pasienMasuk['isolasi_L'] ?? 0) + ($pasienMasuk['inkubator_L'] ?? 0) + ($pasienMasuk['box_L'] ?? 0) + ($pasienMasuk['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMasuk['terakulu_P'] ?? 0) + ($pasienMasuk['isolasi_P'] ?? 0) + ($pasienMasuk['inkubator_P'] ?? 0) + ($pasienMasuk['box_P'] ?? 0) + ($pasienMasuk['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPindahan['terakulu'] ?? 0) + ($pasienPindahan['isolasi'] ?? 0) + ($pasienPindahan['inkubator'] ?? 0) + ($pasienPindahan['box'] ?? 0) + ($pasienPindahan['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarPindahan['terakulu'] ?? 0) + ($pasienKeluarPindahan['isolasi'] ?? 0) + ($pasienKeluarPindahan['inkubator'] ?? 0) + ($pasienKeluarPindahan['box'] ?? 0) + ($pasienKeluarPindahan['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarHidup['terakulu_L'] ?? 0) + ($pasienKeluarHidup['isolasi_L'] ?? 0) + ($pasienKeluarHidup['inkubator_L'] ?? 0) + ($pasienKeluarHidup['box_L'] ?? 0) + ($pasienKeluarHidup['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarHidup['terakulu_P'] ?? 0) + ($pasienKeluarHidup['isolasi_P'] ?? 0) + ($pasienKeluarHidup['inkubator_P'] ?? 0) + ($pasienKeluarHidup['box_P'] ?? 0) + ($pasienKeluarHidup['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPulangTidakStandar['terakulu'] ?? 0) + ($pasienPulangTidakStandar['isolasi'] ?? 0) + ($pasienPulangTidakStandar['inkubator'] ?? 0) + ($pasienPulangTidakStandar['box'] ?? 0) + ($pasienPulangTidakStandar['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPulangHariSama['terakulu'] ?? 0) + ($pasienKeluarHidup['isolasi'] ?? 0) + ($pasienKeluarHidup['inkubator'] ?? 0) + ($pasienKeluarHidup['box'] ?? 0) + ($pasienKeluarHidup['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48Jam['terakulu_L'] ?? 0) + ($pasienMeninggal48Jam['isolasi_L'] ?? 0) + ($pasienMeninggal48Jam['inkubator_L'] ?? 0) + ($pasienMeninggal48Jam['box_L'] ?? 0) + ($pasienMeninggal48Jam['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48Jam['terakulu_P'] ?? 0) + ($pasienMeninggal48Jam['isolasi_P'] ?? 0) + ($pasienMeninggal48Jam['inkubator_P'] ?? 0) + ($pasienMeninggal48Jam['box_P'] ?? 0) + ($pasienMeninggal48Jam['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48plus['terakulu_L'] ?? 0) + ($pasienMeninggal48plus['isolasi_L'] ?? 0) + ($pasienMeninggal48plus['inkubator_L'] ?? 0) + ($pasienMeninggal48plus['box_L'] ?? 0) + ($pasienMeninggal48plus['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48plus['terakulu_P'] ?? 0) + ($pasienMeninggal48plus['isolasi_P'] ?? 0) + ($pasienMeninggal48plus['inkubator_P'] ?? 0) + ($pasienMeninggal48plus['box_P'] ?? 0) + ($pasienMeninggal48plus['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggalTotal['terakulu_L'] ?? 0) + ($pasienMeninggalTotal['isolasi_L'] ?? 0) + ($pasienMeninggalTotal['inkubator_L'] ?? 0) + ($pasienMeninggalTotal['box_L'] ?? 0) + ($pasienMeninggalTotal['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggalTotal['terakulu_P'] ?? 0) + ($pasienMeninggalTotal['isolasi_P'] ?? 0) + ($pasienMeninggalTotal['inkubator_P'] ?? 0) + ($pasienMeninggalTotal['box_P'] ?? 0) + ($pasienMeninggalTotal['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($lamaDirawat['terakulu'] ?? 0) + ($lamaDirawat['isolasi'] ?? 0) + ($lamaDirawat['inkubator'] ?? 0) + ($lamaDirawat['box'] ?? 0) + ($lamaDirawat['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($sisaPasien['terakulu'] ?? 0) + ($sisaPasien['isolasi'] ?? 0) + ($sisaPasien['inkubator'] ?? 0) + ($sisaPasien['box'] ?? 0) + ($sisaPasien['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($hariPerawatan['terakulu'] ?? 0) + ($hariPerawatan['isolasi'] ?? 0) + ($hariPerawatan['inkubator'] ?? 0) + ($hariPerawatan['box'] ?? 0) + ($hariPerawatan['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($bor['terakulu'] ?? 0) + ($bor['isolasi'] ?? 0) + ($bor['inkubator'] ?? 0) + ($bor['box'] ?? 0) + ($bor['infant'] ?? 0) }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($los['terakulu'] ?? 0) + ($los['isolasi'] ?? 0) + ($los['inkubator'] ?? 0) + ($los['box'] ?? 0) + ($los['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($bto['terakulu'] ?? 0) + ($bto['isolasi'] ?? 0) + ($bto['inkubator'] ?? 0) + ($bto['box'] ?? 0) + ($bto['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($toi['terakulu'] ?? 0) + ($toi['isolasi'] ?? 0) + ($toi['inkubator'] ?? 0) + ($toi['box'] ?? 0) + ($toi['infant'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($ndr['terakulu_L'] ?? 0) + ($ndr['isolasi_L'] ?? 0) + ($ndr['inkubator_L'] ?? 0) + ($ndr['box_L'] ?? 0) + ($ndr['infant_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($ndr['terakulu_P'] ?? 0) + ($ndr['isolasi_P'] ?? 0) + ($ndr['inkubator_P'] ?? 0) + ($ndr['box_P'] ?? 0) + ($ndr['infant_P'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($gdr['terakulu_L'] ?? 0) + ($gdr['isolasi_L'] ?? 0) + ($gdr['inkubator_L'] ?? 0) + ($gdr['box_L'] ?? 0) + ($gdr['infant_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($gdr['terakulu_P'] ?? 0) + ($gdr['isolasi_P'] ?? 0) + ($gdr['inkubator_P'] ?? 0) + ($gdr['box_P'] ?? 0) + ($gdr['infant_P'] ?? 0) }}‰</td>
                                </tr>
                                <tr>
                                    <td>Terakulu Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['terakuluKelas1'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['terakuluKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas1_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas1_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>Terakulu Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['terakuluKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['terakuluKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['terakuluKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['terakuluKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['terakuluKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['terakuluKelas2'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['terakuluKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['terakuluKelas2_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['terakuluKelas2_P'] ?? 0 }}‰</td>            
                                </tr>
                                <tr>
                                    <td>Terakulu Kelas 3</td>
                                    <td style="text-align: center">{{ ($tempatTidur['terakuluKelas3'] ?? 0) + ($tempatTidur['isolasi'] ?? 0) + ($tempatTidur['inkubator'] ?? 0) + ($tempatTidur['box'] ?? 0) + ($tempatTidur['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienAwal['terakuluKelas3'] ?? 0) + ($pasienAwal['isolasi'] ?? 0) + ($pasienAwal['inkubator'] ?? 0) + ($pasienAwal['box'] ?? 0) + ($pasienAwal['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMasuk['terakuluKelas3_L'] ?? 0) + ($pasienMasuk['isolasi_L'] ?? 0) + ($pasienMasuk['inkubator_L'] ?? 0) + ($pasienMasuk['box_L'] ?? 0) + ($pasienMasuk['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMasuk['terakuluKelas3_P'] ?? 0) + ($pasienMasuk['isolasi_P'] ?? 0) + ($pasienMasuk['inkubator_P'] ?? 0) + ($pasienMasuk['box_P'] ?? 0) + ($pasienMasuk['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienPindahan['terakuluKelas3'] ?? 0) + ($pasienPindahan['isolasi'] ?? 0) + ($pasienPindahan['inkubator'] ?? 0) + ($pasienPindahan['box'] ?? 0) + ($pasienPindahan['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienKeluarPindahan['terakuluKelas3'] ?? 0) + ($pasienKeluarPindahan['isolasi'] ?? 0) + ($pasienKeluarPindahan['inkubator'] ?? 0) + ($pasienKeluarPindahan['box'] ?? 0) + ($pasienKeluarPindahan['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienKeluarHidup['terakuluKelas3_L'] ?? 0) + ($pasienKeluarHidup['isolasi_L'] ?? 0) + ($pasienKeluarHidup['inkubator_L'] ?? 0) + ($pasienKeluarHidup['box_L'] ?? 0) + ($pasienKeluarHidup['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienKeluarHidup['terakuluKelas3_P'] ?? 0) + ($pasienKeluarHidup['isolasi_P'] ?? 0) + ($pasienKeluarHidup['inkubator_P'] ?? 0) + ($pasienKeluarHidup['box_P'] ?? 0) + ($pasienKeluarHidup['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienPulangTidakStandar['terakuluKelas3'] ?? 0) + ($pasienKeluarHidup['isolasi'] ?? 0) + ($pasienKeluarHidup['inkubator'] ?? 0) + ($pasienKeluarHidup['box'] ?? 0) + ($pasienKeluarHidup['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienPulangHariSama['terakuluKelas3'] ?? 0) + ($pasienKeluarHidup['isolasi'] ?? 0) + ($pasienKeluarHidup['inkubator'] ?? 0) + ($pasienKeluarHidup['box'] ?? 0) + ($pasienKeluarHidup['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48Jam['terakuluKelas3_L'] ?? 0) + ($pasienMeninggal48Jam['isolasi_L'] ?? 0) + ($pasienMeninggal48Jam['inkubator_L'] ?? 0) + ($pasienMeninggal48Jam['box_L'] ?? 0) + ($pasienMeninggal48Jam['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48Jam['terakuluKelas3_P'] ?? 0) + ($pasienMeninggal48Jam['isolasi_P'] ?? 0) + ($pasienMeninggal48Jam['inkubator_P'] ?? 0) + ($pasienMeninggal48Jam['box_P'] ?? 0) + ($pasienMeninggal48Jam['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48plus['terakuluKelas3_L'] ?? 0) + ($pasienMeninggal48plus['isolasi_L'] ?? 0) + ($pasienMeninggal48plus['inkubator_L'] ?? 0) + ($pasienMeninggal48plus['box_L'] ?? 0) + ($pasienMeninggal48plus['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggal48plus['terakuluKelas3_P'] ?? 0) + ($pasienMeninggal48plus['isolasi_P'] ?? 0) + ($pasienMeninggal48plus['inkubator_P'] ?? 0) + ($pasienMeninggal48plus['box_P'] ?? 0) + ($pasienMeninggal48plus['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggalTotal['terakuluKelas3_L'] ?? 0) + ($pasienMeninggalTotal['isolasi_L'] ?? 0) + ($pasienMeninggalTotal['inkubator_L'] ?? 0) + ($pasienMeninggalTotal['box_L'] ?? 0) + ($pasienMeninggalTotal['infant_L'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($pasienMeninggalTotal['terakuluKelas3_P'] ?? 0) + ($pasienMeninggalTotal['isolasi_P'] ?? 0) + ($pasienMeninggalTotal['inkubator_P'] ?? 0) + ($pasienMeninggalTotal['box_P'] ?? 0) + ($pasienMeninggalTotal['infant_P'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($lamaDirawat['terakuluKelas3'] ?? 0) + ($lamaDirawat['isolasi'] ?? 0) + ($lamaDirawat['inkubator'] ?? 0) + ($lamaDirawat['box'] ?? 0) + ($lamaDirawat['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($sisaPasien['terakuluKelas3'] ?? 0) + ($sisaPasien['isolasi'] ?? 0) + ($sisaPasien['inkubator'] ?? 0) + ($sisaPasien['box'] ?? 0) + ($sisaPasien['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($hariPerawatan['terakuluKelas3'] ?? 0) + ($hariPerawatan['isolasi'] ?? 0) + ($hariPerawatan['inkubator'] ?? 0) + ($hariPerawatan['box'] ?? 0) + ($hariPerawatan['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($bor['terakuluKelas3'] ?? 0) + ($bor['isolasi'] ?? 0) + ($bor['inkubator'] ?? 0) + ($bor['box'] ?? 0) + ($bor['infant'] ?? 0) }}%</td>
                                    <td style="text-align: center">{{ ($los['terakuluKelas3'] ?? 0) + ($los['isolasi'] ?? 0) + ($los['inkubator'] ?? 0) + ($los['box'] ?? 0) + ($los['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($bto['terakuluKelas3'] ?? 0) + ($bto['isolasi'] ?? 0) + ($bto['inkubator'] ?? 0) + ($bto['box'] ?? 0) + ($bto['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($toi['terakuluKelas3'] ?? 0) + ($toi['isolasi'] ?? 0) + ($toi['inkubator'] ?? 0) + ($toi['box'] ?? 0) + ($toi['infant'] ?? 0) }}</td>
                                    <td style="text-align: center">{{ ($ndr['terakuluKelas3_L'] ?? 0) + ($ndr['isolasi_L'] ?? 0) + ($ndr['inkubator_L'] ?? 0) + ($ndr['box_L'] ?? 0) + ($ndr['infant_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center">{{ ($ndr['terakuluKelas3_P'] ?? 0) + ($ndr['isolasi_P'] ?? 0) + ($ndr['inkubator_P'] ?? 0) + ($ndr['box_P'] ?? 0) + ($ndr['infant_P'] ?? 0) }}‰</td>
                                    <td style="text-align: center">{{ ($gdr['terakuluKelas3_L'] ?? 0) + ($gdr['isolasi_L'] ?? 0) + ($gdr['inkubator_L'] ?? 0) + ($gdr['box_L'] ?? 0) + ($gdr['infant_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center">{{ ($gdr['terakuluKelas3_P'] ?? 0) + ($gdr['isolasi_P'] ?? 0) + ($gdr['inkubator_P'] ?? 0) + ($gdr['box_P'] ?? 0) + ($gdr['infant_P'] ?? 0) }}‰</td>
                                </tr>

                                <tr>
                                    <td rowspan="4">4</td>
                                    <td style="text-align: center;background-color: #bdd9bf;" > Ruang OBSGYN (Balleraja)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['balleraja_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['balleraja_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['balleraja_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['balleraja_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangTidakStandar['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangHariSama['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['balleraja_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['balleraja_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['balleraja_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['balleraja_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['balleraja_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['balleraja_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['balleraja'] ?? 0 }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['balleraja'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['balleraja_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['balleraja_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['balleraja_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['balleraja_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>Balleraja Kelas 1</td>
                                    <td style="text-align: center">{{ $tempatTidur['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas1_L'] ?? 0 }}</td> 
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas1_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas1_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['ballerajaKelas1'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['ballerajaKelas1'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas1_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas1_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas1_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas1_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>Balleraja Kelas 2</td>
                                    <td style="text-align: center">{{ $tempatTidur['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas2_L'] ?? 0 }}</td> 
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas2_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas2_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['ballerajaKelas2'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['ballerajaKelas2'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas2_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas2_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas2_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas2_P'] ?? 0 }}‰</td>            
                                </tr>
                                <tr>
                                    <td>Balleraja Kelas 3</td>
                                    <td style="text-align: center">{{ $tempatTidur['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienAwal['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMasuk['ballerajaKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPindahan['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarPindahan['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienKeluarHidup['ballerajaKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangTidakStandar['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienPulangHariSama['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48Jam['ballerajaKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas3_L'] ?? 0 }}</td> 
                                    <td style="text-align: center">{{ $pasienMeninggal48plus['ballerajaKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas3_L'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $pasienMeninggalTotal['ballerajaKelas3_P'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $lamaDirawat['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $sisaPasien['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $hariPerawatan['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bor['ballerajaKelas3'] ?? 0 }}%</td>
                                    <td style="text-align: center">{{ $los['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $bto['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $toi['ballerajaKelas3'] ?? 0 }}</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas3_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $ndr['ballerajaKelas3_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas3_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center">{{ $gdr['ballerajaKelas3_P'] ?? 0 }}‰</td>            
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang Kohort (Tenggiri)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['tenggiri_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['tenggiri_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['tenggiri_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['tenggiri_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangTidakStandar['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangHariSama['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['tenggiri_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['tenggiri_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['tenggiri_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['tenggiri_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['tenggiri_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['tenggiri_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['tenggiri'] ?? 0 }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['tenggiri'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['tenggiri_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['tenggiri_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['tenggiri_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['tenggiri_P'] ?? 0 }}‰</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang VIP (Barunang) Atas</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['barunang_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['barunang_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['barunang_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['barunang_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangTidakStandar['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangHariSama['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['barunang_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['barunang_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['barunang_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['barunang_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['barunang_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['barunang_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['barunang'] ?? 0 }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['barunang'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['barunang_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['barunang_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['barunang_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['barunang_P'] ?? 0 }}‰</td>            
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang VIP (Lobster) Bawah</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $tempatTidur['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienAwal['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['lobster_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMasuk['lobster_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPindahan['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarPindahan['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['lobster_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienKeluarHidup['lobster_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangTidakStandar['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienPulangHariSama['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['lobster_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48Jam['lobster_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['lobster_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggal48plus['lobster_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['lobster_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $pasienMeninggalTotal['lobster_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $lamaDirawat['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $sisaPasien['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $hariPerawatan['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bor['lobster'] ?? 0 }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $los['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $bto['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $toi['lobster'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['lobster_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $ndr['lobster_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['lobster_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $gdr['lobster_P'] ?? 0 }}‰</td>            
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">Ruang ICU/PICU (Lumba-Lumba)</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($tempatTidur['lumbaLumba'] ?? 0) + ($tempatTidur['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienAwal['lumbaLumba'] ?? 0) + ($pasienAwal['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMasuk['lumbaLumba_L'] ?? 0) + ($pasienMasuk['picu_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMasuk['lumbaLumba_P'] ?? 0) + ($pasienMasuk['picu_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPindahan['lumbaLumba'] ?? 0) + ($pasienPindahan['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarPindahan['lumbaLumba'] ?? 0) + ($pasienKeluarPindahan['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarHidup['lumbaLumba_L'] ?? 0) + ($pasienKeluarHidup['picu_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienKeluarHidup['lumbaLumba_P'] ?? 0) + ($pasienKeluarHidup['picu_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPulangTidakStandar['lumbaLumba'] ?? 0) + ($pasienPulangTidakStandar['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienPulangHariSama['lumbaLumba'] ?? 0) + ($pasienPulangHariSama['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48Jam['lumbaLumba_L'] ?? 0) + ($pasienMeninggal48Jam['picu_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48Jam['lumbaLumba_P'] ?? 0) + ($pasienMeninggal48Jam['picu_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48plus['lumbaLumba_L'] ?? 0) + ($pasienMeninggal48plus['picu_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggal48plus['lumbaLumba_P'] ?? 0) + ($pasienMeninggal48plus['picu_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggalTotal['lumbaLumba_L'] ?? 0) + ($pasienMeninggalTotal['picu_L'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($pasienMeninggalTotal['lumbaLumba_P'] ?? 0) + ($pasienMeninggalTotal['picu_P'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($lamaDirawat['lumbaLumba'] ?? 0) + ($lamaDirawat['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($sisaPasien['lumbaLumba'] ?? 0) + ($sisaPasien['lumbaLumba'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($hariPerawatan['lumbaLumba'] ?? 0) + ($hariPerawatan['lumbaLumba'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($bor['lumbaLumba'] ?? 0) + ($bor['picu'] ?? 0) }}%</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($los['lumbaLumba'] ?? 0) + ($los['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($bto['lumbaLumba'] ?? 0) + ($bto['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($toi['lumbaLumba'] ?? 0) + ($toi['picu'] ?? 0) }}</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($ndr['lumbaLumba_L'] ?? 0) + ($ndr['picu_L'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($ndr['lumbaLumba_P'] ?? 0) + ($ndr['picu_P'] ?? 0) }}‰</td>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($gdr['lumbaLumba_L'] ?? 0) + ($gdr['picu_L'] ?? 0) }}‰</td>            
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ ($gdr['lumbaLumba_P'] ?? 0) + ($gdr['picu_P'] ?? 0) }}‰</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="summary-row">
                                    <td colspan="2" rowspan="2" style="text-align: center;background-color: #f0fff0;"><strong>Jumlah Total</strong></td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $tempatTidur['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $pasienAwal['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMasuk['total_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMasuk['total_P'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $pasienPindahan['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $pasienKeluarPindahan['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienKeluarHidup['total_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienKeluarHidup['total_P'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $pasienPulangTidakStandar['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $pasienPulangHariSama['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMeninggal48Jam['total_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMeninggal48Jam['total_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMeninggal48plus['total_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMeninggal48plus['total_P'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMeninggalTotal['total_L'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $pasienMeninggalTotal['total_P'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $lamaDirawat['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $sisaPasien['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $hariPerawatan['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $bor['total'] ?? 0 }}%</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $los['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $bto['total'] ?? 0 }}</td>
                                    <td rowspan="2" style="text-align: center;background-color: #f0fff0;">{{ $toi['total'] ?? 0 }}</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $ndr['total_L'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $ndr['total_P'] ?? 0 }}‰</td>
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $gdr['total_L'] ?? 0 }}‰</td>            
                                    <td style="text-align: center;background-color: #f0fff0;">{{ $gdr['total_P'] ?? 0 }}‰</td>            
                                </tr>
                                <tr class="summary-row">
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $pasienMasuk['total'] ?? 0 }}</td>
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $pasienKeluarHidup['total'] ?? 0 }}</td>
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $pasienMeninggal48Jam['total'] ?? 0 }}</td>
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $pasienMeninggal48plus['total'] ?? 0 }}</td>
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $pasienMeninggalTotal['total'] ?? 0 }}</td>
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $ndr['total'] ?? 0 }}‰</td>
                                    <td colspan="2" style="text-align: center;background-color: #f0fff0;font-weight: bold">{{ $gdr['total'] ?? 0 }}‰</td>            
                                </tr>
                                </tfoot>
                            </table>
                            <!-- AKHIR DATA PASIEN-->
            </div>
        </div>
        {{-- END print-only wrapper --}}

        {{-- Elemen-elemen lain (grafik, dsb) yang tidak ingin dicetak --}}
        <div class="no-print">
        {{-- Tempatkan grafik, footer, dan komponen lain di sini --}}
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
        {{-- End print-only wrapper --}}
        <div class="no-print">
        {{-- … semua elemen setelah tabel (grafik bawah, footer, dsb) --}}
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

<script>
    function printTable() {
        // Simpan konten asli dan CSS asli
        const originalContent = document.body.innerHTML;
        const originalStyles = document.head.innerHTML;
        
        // Dapatkan content table
        const tableContent = document.querySelector('.print-only').outerHTML;
        
        // Buat CSS khusus untuk print
        const printStyles = `
            <style id="temp-print-styles">
                * {
                    font-family: "Open Sans", sans-serif;
                }
                @page {
                    size: A4 landscape;
                    margin: 5mm;
                }
                @media print {
                    body {
                        margin: 0 !important;
                        padding: 0 !important;
                        font-size: 10px !important;
                    }
                    body * {
                        visibility: hidden !important;
                    }
                    .print-content, .print-content * {
                        visibility: visible !important;
                    }
                    .print-content {
                        position: absolute !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 100% !important;
                    }
                    table {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        table-layout: fixed !important;
                    }
                    th, td {
                        border: 1px solid #000 !important;
                        padding: 2px 4px !important;
                        font-size: 8px !important;
                        vertical-align: top !important;
                    }
                    .wrap-column {
                        word-wrap: break-word !important;
                        word-break: break-word !important;
                        white-space: normal !important;
                        max-width: 100px !important;
                    }
                    .nowrap-column {
                        white-space: nowrap !important;
                        text-align: center !important;
                    }
                    .col-no { width: 3% !important; }
                    .col-name { width: 15% !important; }
                    .col-data { width: 4% !important; }
                }
            </style>
        `;
        
        // Tambahkan CSS print ke head
        document.head.insertAdjacentHTML('beforeend', printStyles);
        
        // Ganti konten body dengan table yang akan di-print
        document.body.innerHTML = `<div class="print-content">${tableContent}</div>`;
        
        // Panggil print
        window.print();
        
        // Kembalikan konten asli setelah print dialog ditutup
        setTimeout(() => {
            document.head.innerHTML = originalStyles;
            document.body.innerHTML = originalContent;
        }, 100);
    }
</script>

{{ $jmlpasien->script() }}
{{ $jmllamapasien->script() }}

@endsection