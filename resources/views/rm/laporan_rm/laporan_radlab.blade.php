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
                                <form id="filterForm" action="{{ route('laporan_radlab') }}" method="POST">
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
                        <center>LAPORAN BULANAN<br>RADIOLOGI & LABORATORIUM <br>{{ $tgllap }}
                        </center>
                        <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi</small><br><br>
                        <div class="table-responsive">
                            <table style="width:100%;" class="table-bordered">
                                <tr>
                                    <th style="text-align: center;background-color: #e8e9ea;" colspan="9">
                                        Pemeriksaan</th>
                                <tr>
                                    <th style="text-align: center;background-color: #F47174;">Radiologi</th>
                                    <th style="text-align: center;background-color: #bdd9bf;">Laboratorium</th>
                                </tr>
                                <tr>

                                    <td style="text-align: center;">{{ $totalRadiologi }}</td>
                                    <td style="text-align: center;">{{ $totalLab }}</td>

                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
