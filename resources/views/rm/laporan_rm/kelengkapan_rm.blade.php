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
                            <form id="filterForm" action="{{ route('kelengkapan') }}" method="POST">
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
                    <center>LAPORAN<br>KELENGKAPAN REKAM MEDIS PASIEN RAWAT INAP <br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br>
                    <div class="table-responsive">
                        <table id="kelengkapan" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th >No. Rawat</th>
                                    <th >No. RM</th>
                                    <th >Nama Pasien</th>
                                    <th >Status</th>
                                    <th >Verifikasi (L / TL)</th>
                                    <th >Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nmr_rwt  as $a)
                                    <tr>
                                        <td >{{ $a->no_rawat }}</td>
                                        <td style="text-align: center;">
                                            {{ $a->no_rkm_medis }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $a->nm_pasien }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $a->status_lanjut }}
                                        </td>
                                        <td style="text-align: center;">
                                        <?php
            if ($a) { ?>
                    <a href="#"
                        class="btn btn-success">Lengkap</a>
                        <a href="#"
                        class="btn btn-danger">Tidak Lengkap</a>

                    <?php  } else { ?>
                    <strong>
                        <center><span style="color: green;">✓ Lengkap </span></center>
                    </strong>
                    <?php } ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{route('modalrm', ['id' => $a->no_rawat])}}" id="openModal" class="btn btn-primary openModal" data-toggle="modal"
                                            data-target="#ermModal">Detail</a>
                                            
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>

<div class="modal fade" id="ermModal" tabindex="-1" role="dialog" aria-labelledby="ermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ermModalLabel">Detail Laporan RM</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                Loading...
            </div>
        </div>
    </div>
</div>

@endsection