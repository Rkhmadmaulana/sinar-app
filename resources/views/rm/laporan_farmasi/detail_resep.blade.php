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
                            <form id="filterForm" action="{{ route('detailresep') }}" method="POST">
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
                                                <button type="submit" name="tombol" value="filter"class="btn btn-primary">Filter</button>
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
        @include('rm.laporan_farmasi.layout.menu_farmasi')
    </div>

    <br>

    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <center>LAPORAN<br>KELENGKAPAN RESEP <br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br>
                    <div class="table-responsive">
                        <table id="kelengkapan" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">No. Resep</th>
                                    <th style="text-align: center;">Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detail_resep  as $a)
                                    <tr>
                                        <td >{{ $a->no_resep }}</td>
                                        <td style="text-align: center;">
                                            <a href="{{route('modalfarmasi', ['id' => $a->no_resep])}}" id="openModal" class="btn btn-primary openModal" data-toggle="modal"
                                            data-target="#resepModal">Detail</a>  
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

<div class="modal fade" id="resepModal" tabindex="-1" role="dialog" aria-labelledby="resepModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resepModalLabel">Detail Resep</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var resepModal = document.getElementById("resepModal");
        
        resepModal.addEventListener("hidden.bs.modal", function () {
            document.getElementById("modal-body-content").innerHTML = "Loading...";
            location.reload(); // Refresh halaman setelah modal ditutup
        });
    });
</script>

@endsection