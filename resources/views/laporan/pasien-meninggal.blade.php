@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                    
                    <div class="card-tools">
                        <form action="{{ route('laporan.pasien-meninggal') }}" method="GET" class="form-inline">
                            <div class="input-group mr-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
                            </div>
                            
                            <div class="input-group mr-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
                            </div>
                            
                            <div class="input-group mr-2">
                                <select name="bangsal" class="form-control">
                                    <option value="">Semua Bangsal</option>
                                    @foreach($daftarBangsal as $b)
                                        <option value="{{ $b->kd_bangsal }}" {{ $bangsal == $b->kd_bangsal ? 'selected' : '' }}>
                                            {{ $b->nm_bangsal }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(count($data) > 0)
                        <div class="mb-3">
                            <a href="{{ route('laporan.pasien-meninggal.pdf', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'bangsal' => $bangsal]) }}" 
                               class="btn btn-danger" target="_blank">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </a>
                            <a href="{{ route('laporan.pasien-meninggal.excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'bangsal' => $bangsal]) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Download Excel
                            </a>
                        </div>
                        
                        <h5>Tabel Data Pasien Meninggal</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Rekam Medis</th>
                                        <th>Nama Pasien</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Umur</th>
                                        <th>Penanggung Jawab</th>
                                        <th>Meninggal < 48 Jam</th>
                                        <th>Meninggal >= 48 Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            <td>{{ $item->no }}</td>
                                            <td>{{ $item->tgl_masuk }}</td>
                                            <td>{{ $item->no_rkm_medis }}</td>
                                            <td>{{ $item->nm_pasien }}</td>
                                            <td>{{ $item->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                            <td>{{ $item->tgl_lahir }}</td>
                                            <td>{{ $item->umur !== null ? $item->umur : '-' }}</td>
                                            <td>{{ $item->png_jawab }}</td>
                                            <td class="text-center">{{ $item->meninggal_kurang_48jam }}</td>
                                            <td class="text-center">{{ $item->meninggal_lebih_48jam }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="3">Total Pasien</th>
                                        <th colspan="7">{{ count($data) }} Pasien</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <h5 class="mt-4">Tabel Ringkasan Diagnosa</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Diagnosa</th>
                                        <th colspan="2" class="text-center">Jenis Kelamin</th>
                                        <th colspan="11" class="text-center">Kelompok Umur</th>
                                        <th colspan="2" class="text-center">Meninggal</th>
                                    </tr>
                                    <tr>
                                        <th>L</th>
                                        <th>P</th>
                                        <th><1</th>
                                        <th><4</th>
                                        <th><9</th>
                                        <th><14</th>
                                        <th><19</th>
                                        <th><44</th>
                                        <th><54</th>
                                        <th><59</th>
                                        <th><69</th>
                                        <th>≥70</th>
                                        <th>Null</th>
                                        <th><48 Jam</th>
                                        <th>≥48 Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($totalData as $item)
                                        <tr>
                                            <td>{{ $item['no'] }}</td>
                                            <td>{{ $item['diagnosa'] }}</td>
                                            <td class="text-center">{{ $item['laki'] }}</td>
                                            <td class="text-center">{{ $item['perempuan'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_1'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_4'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_9'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_14'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_19'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_44'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_54'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_59'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_69'] }}</td>
                                            <td class="text-center">{{ $item['umur_lt_70'] }}</td>
                                            <td class="text-center">{{ $item['umur_null'] }}</td>
                                            <td class="text-center">{{ $item['meninggal_kurang_48jam'] }}</td>
                                            <td class="text-center">{{ $item['meninggal_lebih_48jam'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Silahkan pilih rentang tanggal dan klik tombol Tampilkan untuk melihat data pasien meninggal.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection