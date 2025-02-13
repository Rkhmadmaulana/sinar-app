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
                            <form id="filterForm" action="{{ route('penyakitmenular') }}" method="POST">
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
                    <center>LAPORAN BULANAN<br>PENYAKIT MENULAR DI RSUD PANGERAN JAYA SUMITRA<br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi</small><br><br>
                    <div class="table-responsive">
                        <table style="width:100%;" class="table-bordered">
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Nama Penyakit</th>
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
                                <th style="text-align: center;background-color: #bdd9bf;">HIV</th>
                                <th style="text-align: center;">{{ $anggotahiv->hiv }}</th>
                                <th style="text-align: center;">{{ $pnshiv->hiv }}</th>
                                <th style="text-align: center;">{{ $kelpolrihiv->hiv }}</th>
                                <th style="text-align: center;">{{ $dikbanghiv->hiv }}</th>
                                <th style="text-align: center;">{{ $diktukhiv->hiv }}</th>
                                <th style="text-align: center;">{{ $umumhiv->hiv }}</th>
                                <th style="text-align: center;">{{ $bpjshiv }}</th>
                                <th style="text-align: center;">{{ $otherhiv->hiv }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_hiv }} </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Tuberkulosis (TB)</th>
                                <th style="text-align: center;">{{ $anggotatb->tb }}</th>
                                <th style="text-align: center;">{{ $pnstb->tb }}</th>
                                <th style="text-align: center;">{{ $kelpolritb->tb }}</th>
                                <th style="text-align: center;">{{ $dikbangtb->tb }}</th>
                                <th style="text-align: center;">{{ $diktuktb->tb }}</th>
                                <th style="text-align: center;">{{ $umumtb->tb }}</th>
                                <th style="text-align: center;">{{ $bpjstb }}</th>
                                <th style="text-align: center;">{{ $othertb->tb }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_tb }} </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Malaria</th>
                                <th style="text-align: center;">{{ $anggotamalaria->malaria }}</th>
                                <th style="text-align: center;">{{ $pnsmalaria->malaria }}</th>
                                <th style="text-align: center;">{{ $kelpolrimalaria->malaria }}</th>
                                <th style="text-align: center;">{{ $dikbangmalaria->malaria }}</th>
                                <th style="text-align: center;">{{ $diktukmalaria->malaria }}</th>
                                <th style="text-align: center;">{{ $umummalaria->malaria }}</th>
                                <th style="text-align: center;">{{ $bpjsmalaria }}</th>
                                <th style="text-align: center;">{{ $othermalaria->malaria }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_malaria }} </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">DBD</th>
                                <th style="text-align: center;">{{ $anggotadbd->dbd }}</th>
                                <th style="text-align: center;">{{ $pnsdbd->dbd }}</th>
                                <th style="text-align: center;">{{ $kelpolridbd->dbd }}</th>
                                <th style="text-align: center;">{{ $dikbangdbd->dbd }}</th>
                                <th style="text-align: center;">{{ $diktukdbd->dbd }}</th>
                                <th style="text-align: center;">{{ $umumdbd->dbd }}</th>
                                <th style="text-align: center;">{{ $bpjsdbd }}</th>
                                <th style="text-align: center;">{{ $otherdbd->dbd }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_dbd }} </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">PMS</th>
                                <th style="text-align: center;">{{ $anggotapms->pms }}</th>
                                <th style="text-align: center;">{{ $pnspms->pms }}</th>
                                <th style="text-align: center;">{{ $kelpolripms->pms }}</th>
                                <th style="text-align: center;">{{ $dikbangpms->pms }}</th>
                                <th style="text-align: center;">{{ $diktukpms->pms }}</th>
                                <th style="text-align: center;">{{ $umumpms->pms }}</th>
                                <th style="text-align: center;">{{ $bpjspms }}</th>
                                <th style="text-align: center;">{{ $otherpms->pms }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_pms }} </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Hepatitis</th>
                                <th style="text-align: center;">{{ $anggotahepatitis->hepatitis }}</th>
                                <th style="text-align: center;">{{ $pnshepatitis->hepatitis }}</th>
                                <th style="text-align: center;">{{ $kelpolrihepatitis->hepatitis }}</th>
                                <th style="text-align: center;">{{ $dikbanghepatitis->hepatitis }}</th>
                                <th style="text-align: center;">{{ $diktukhepatitis->hepatitis }}</th>
                                <th style="text-align: center;">{{ $umumhepatitis->hepatitis }}</th>
                                <th style="text-align: center;">{{ $bpjshepatitis }}</th>
                                <th style="text-align: center;">{{ $otherhepatitis->hepatitis }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_hepatitis }} </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;background-color: #bdd9bf;">Covid</th>
                                <th style="text-align: center;">{{ $anggotacovid->covid }}</th>
                                <th style="text-align: center;">{{ $pnscovid->covid }}</th>
                                <th style="text-align: center;">{{ $kelpolricovid->covid }}</th>
                                <th style="text-align: center;">{{ $dikbangcovid->covid }}</th>
                                <th style="text-align: center;">{{ $diktukcovid->covid }}</th>
                                <th style="text-align: center;">{{ $umumcovid->covid }}</th>
                                <th style="text-align: center;">{{ $bpjscovid }}</th>
                                <th style="text-align: center;">{{ $othercovid->covid }}</th>
                                <th style="text-align: center;background-color: #F47174;">{{ $total_covid }} </th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
