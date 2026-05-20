@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RL 3.15 Rekapitulasi Kegiatan Pelayanan Kesehatan Jiwa</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rl315') }}" method="GET" class="mb-4">
                <div class="row align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_awal" class="mb-0 mr-2">Dari&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" 
                               value="{{ $tanggalAwal ?? now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_akhir" class="mb-0 mr-2">Sampai&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                               value="{{ $tanggalAkhir ?? now()->endOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>

            <div class="mb-3">
                <a href="{{ route('laporan.rl315') }}?download_pdf=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <!--
                <a href="{{ route('laporan.rl315') }}?download_excel=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
                -->
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 8%">No.</th>
                            <th style="width: 50%">Jenis Kegiatan</th>
                            <th class="text-center" style="width: 14%">Laki-laki</th>
                            <th class="text-center" style="width: 14%">Perempuan</th>
                            <th class="text-center" style="width: 14%">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $no => $item)
                            @if($no == 99)
                                <tr class="table-warning font-weight-bold">
                                    <td class="text-center">{{ $no }}</td>
                                    <td><strong>{{ $item['nama'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['laki'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['perempuan'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['jumlah'] }}</strong></td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-center">{{ $no }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    <td class="text-center">
                                        @if($item['laki'] > 0)
                                            <a href="{{ route('laporan.rl315.detail') }}?kategori={{ $no }}&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                               target="_blank" style="color: #000;">
                                                {{ $item['laki'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['perempuan'] > 0)
                                            <a href="{{ route('laporan.rl315.detail') }}?kategori={{ $no }}&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                               target="_blank" style="color: #000;">
                                                {{ $item['perempuan'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['jumlah'] > 0)
                                            <a href="{{ route('laporan.rl315.detail') }}?kategori={{ $no }}&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                               target="_blank" style="color: #000;">
                                                {{ $item['jumlah'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection