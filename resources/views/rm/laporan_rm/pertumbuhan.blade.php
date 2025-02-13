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
                            <form id="filterForm" action="{{ route('pertumbuhan') }}" method="POST">
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
                    <center>LAPORAN BULANAN<br> PERTUMBUHAN PRODUKTIFITAS<br>{{ $tgllap }}
                    </center>
                    <br>
                    <small > Pasien Bulan Lalu = {{ $dari }} S/D {{ $sampai }}<br> Pasien Bulan Ini = {{ $tgllap }}</small><br>
                    <small style="color:rgb(0, 26, 255);">Rumus Pertumbuhan ((Pasien Bulan Ini - Pasien Bulan Lalu) / Pasien Bulan Lalu) x 100%</small><br><br>
                    <div class="table-responsive">
                        <table style="width:100%;" class="table-bordered">
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Kunjungan Rawat Jalan</th>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Kunjungan IGD</th>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Pasien Rawat Inap</th>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Pemeriksaan Radiologi</th>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Pemeriksaan LAB</th>
                                <!-- <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Operasi</th>
                                <th style="text-align: center;background-color: #bdd9bf;" colspan="2">Rehab Medik</th> -->
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th>
                                <!-- <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Jumlah</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Pertumbuhan</th> -->
                            </tr>
                            <tr>
                                <td style="text-align: center;">{{ $sqlrajal->total }}</td>
                                <td style="text-align: center;">
                                    @if($pertumbuhan_ralan >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_ralan }} %</i> @endif 
                                    @if($pertumbuhan_ralan < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_ralan }} %</i>@endif
                                </td>
                                <td style="text-align: center;">{{ $sqligd->total }}</td>
                                <td style="text-align: center;">
                                    @if($pertumbuhan_igd >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_igd }} %</i> @endif 
                                    @if($pertumbuhan_igd < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_igd }} %</i>@endif
                                </td>
                                <td style="text-align: center;">{{ $sqlranap->total }}</td>
                                <td style="text-align: center;">
                                    @if($pertumbuhan_ranap >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_ranap }} %</i> @endif 
                                    @if($pertumbuhan_ranap < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_ranap }} %</i>@endif
                                </td>
                                <td style="text-align: center;">{{ $sqlrad->total }}</td>
                                <td style="text-align: center;">
                                    @if($pertumbuhan_rad >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_rad }} %</i> @endif 
                                    @if($pertumbuhan_rad < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_rad }} %</i>@endif
                                </td>
                                <td style="text-align: center;">{{ $sqllab->total }}</td>
                                <td style="text-align: center;">
                                    @if($pertumbuhan_lab >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_lab }} %</i> @endif 
                                    @if($pertumbuhan_lab < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_lab }} %</i>@endif
                                </td>
                                <!-- 
                                
                                
                                
                                
                                
                                
                                
                                
                                -->
                            </tr>
                        </table>
                        
                    </div>
                    <br>
                    <small style="color:red;">*Data berdasarkan Tanggal Registrasi (Rajal, Ranap, Rehab Medik)
                        <br>
                        *Data berdasarkan Tanggal Periksa (Radiologi, Laboratorium)
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
