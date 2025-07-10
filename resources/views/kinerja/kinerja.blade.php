@extends('layout.app')
@section('content')
@include('layout.header')
@include('layout.sidebar')

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
                                        <div class="col-md-4">
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
                                        <div class="col-md-4">
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
                                        <div class="col-md-4">
                                            <div class="form-group">
                                              <div class="form-line">
                                                <dt>Kelas Kamar</dt>
                                                <dd>
                                                    <select name="kelas"  class="form-control" style="width:100%">
                                                      <option value="" @if(isset($kelaskamar) && $kelaskamar == "") selected @endif>Semua</option>
                                                      <option value="Kelas 1" @if(isset($kelaskamar) && $kelaskamar == "Kelas 1") selected @endif>Kelas 1</option>
                                                      <option value="Kelas 2" @if(isset($kelaskamar) && $kelaskamar == "Kelas 2") selected @endif>Kelas 2</option>
                                                      <option value="Kelas 3" @if(isset($kelaskamar) && $kelaskamar == "Kelas 3") selected @endif>Kelas 3</option>
                                                      <option value="Kelas Utama" @if(isset($kelaskamar) && $kelaskamar == "Kelas Utama") selected @endif>Kelas Utama</option>
                                                      <option value="Kelas VIP" @if(isset($kelaskamar) && $kelaskamar == "Kelas VIP") selected @endif>Kelas VIP</option>
                                                      <option value="Kelas VVIP" @if(isset($kelaskamar) && $kelaskamar == "Kelas VVIP") selected @endif>Kelas VVIP</option>
                                                    </select>
                                                </dd>
                                              </div>
                                            </div>
                                          </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-md-12">
                                          <div class="form-group">
                                            <dd><button type="submit" name="tombol" value="filter" class="btn btn-primary">Filter</button></dd>
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
</div>
{{-- Hapus kondisi login, langsung tampilkan semua --}}
<div class="container-xxl flex-grow-1 container-p-y">
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
                                            <dd><button type="submit" name="tombol" value="Submit" class="btn btn-primary">Update</button> </dd>
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
                <div class="card-body">
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
                    <h5 class="card-title">Grafik Lama Rawat11</h5>
                    
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

<script src="{{ $jmlpasien->cdn() }}"></script>
<script src="{{ $jmllamapasien->cdn() }}"></script>


{{ $jmlpasien->script() }}
{{ $jmllamapasien->script() }}

@include('layout.footer')