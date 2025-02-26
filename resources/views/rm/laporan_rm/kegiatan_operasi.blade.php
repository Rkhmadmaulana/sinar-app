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
                            <form id="filterForm" action="{{ route('operasi') }}" method="POST">
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
                    <center>LAPORAN BULANAN<br> JUMLAH PASIEN OPERASI<br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi</small><br><br>
                    <div class="table-responsive">
                        <table style="width:100%;" class="table-bordered">
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Ruangan</th>
                                <th style="text-align: center;background-color: #bdd9bf;">BPJS</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Umum</th>
                                <th style="text-align: center;background-color: #bdd9bf;">Total</th>
                                @foreach ($op  as $a)
                                <tr>
                                    <td style="text-align: center;background-color: #bdd9bf;">{{ $a->jenis_op }}</td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        
                                        $query_result = DB::select("SELECT COUNT(*) as total FROM reg_periksa AS a
                                                                    INNER JOIN kamar_inap AS b ON b.no_rawat = a.no_rawat
                                                                    INNER JOIN booking_operasi AS c ON c.no_rawat = b.no_rawat
                                                                    INNER JOIN paket_operasi AS d ON d.kode_paket = c.kode_paket
                                                                    WHERE a.tgl_registrasi BETWEEN ? AND ?
                                                                    AND c.tanggal BETWEEN ? AND ?
                                                                    AND a.kd_pj='BPJ' AND (c.status='Proses Operasi' OR c.status='Selesai')", [ $tgl1, $tgl2, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $anggota = $query_result[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $anggota }}
                                    </td>
                                    <td style="text-align: center;">  
                                        @php
                                        // Query SQL dipisahkan dari Blade
                                        $query_result2 = DB::select("SELECT COUNT(*) as total FROM reg_periksa AS a
                                                                    INNER JOIN kamar_inap AS b ON b.no_rawat = a.no_rawat
                                                                    INNER JOIN booking_operasi AS c ON c.no_rawat = b.no_rawat
                                                                    INNER JOIN paket_operasi AS d ON d.kode_paket = c.kode_paket
                                                                    WHERE a.tgl_registrasi BETWEEN ? AND ?
                                                                    AND c.tanggal BETWEEN ? AND ?
                                                                    AND a.kd_pj='PJ2' AND (c.status='Proses Operasi' OR c.status='Selesai')", [ $tgl1, $tgl2, $tgl1, $tgl2]);
                                        // Mengambil nilai total dari hasil query
                                        $pns = $query_result2[0]->total ?? 0; // Jika tidak ada hasil, defaultnya adalah 0
                                        @endphp
                                        {{ $pns }}
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
