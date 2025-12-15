@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RL 3.7 Rekapitulasi Kegiatan Pelayanan Neonatal, Bayi, dan Balita</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rl37') }}" method="GET" class="mb-4">
                <div class="row align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_awal" class="mb-0 mr-2">Dari&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal ?? now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_akhir" class="mb-0 mr-2">Sampai&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir ?? now()->endOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>

            <div class="mb-3">
                <a href="{{ route('laporan.rl37') }}?download_pdf=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="text-center thead-light">
                        <tr>
                            <th rowspan="3">No.</th>
                            <th rowspan="3">Jenis Kegiatan</th>
                            <th colspan="10">Rujukan</th>
                            <th colspan="3">Non Rujukan</th>
                            <th rowspan="3">Dirujuk</th>
                        </tr>
                        <tr>
                            <th colspan="7">Medis</th>
                            <th colspan="3">Non Medis</th>
                            <th rowspan="2">Jlh Hidup</th>
                            <th rowspan="2">Jlh Mati</th>
                            <th rowspan="2">Total</th>
                        </tr>
                        <tr>
                            <th>RS</th>
                            <th>Bidan</th>
                            <th>Puskes</th>
                            <th>Faskes</th>
                            <th>Hidup</th>
                            <th>Mati</th>
                            <th>Total</th>
                            <th>Hidup</th>
                            <th>Mati</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kategori as $item)
                            @if(isset($item['is_header']) && $item['is_header'])
                                <tr class="table-secondary">
                                    <td colspan="17"><strong>{{ $item['kode'] }}. {{ $item['nama'] }}</strong></td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-center">{{ $item['kode'] }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    <!-- RS -->
                                    <td class="text-center">
                                        @if($item['data']['rs'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&sumber=rs&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['rs'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Bidan -->
                                    <td class="text-center">
                                        @if($item['data']['bidan'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&sumber=bidan&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['bidan'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Puskes -->
                                    <td class="text-center">
                                        @if($item['data']['puskes'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&sumber=puskes&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['puskes'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Faskes -->
                                    <td class="text-center">
                                        @if($item['data']['faskes'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&sumber=faskes&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['faskes'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Hidup Medis -->
                                    <td class="text-center">
                                        @if($item['data']['hidup_medis'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&status=hidup&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['hidup_medis'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Mati Medis -->
                                    <td class="text-center">
                                        @if($item['data']['mati_medis'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&status=mati&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['mati_medis'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Total Medis -->
                                    <td class="text-center bg-light">
                                        @if($item['data']['total_medis'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=rujukan&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                <strong>{{ $item['data']['total_medis'] }}</strong>
                                            </a>
                                        @else
                                            <strong>0</strong>
                                        @endif
                                    </td>
                                    <!-- Hidup Non Medis -->
                                    <td class="text-center">
                                        @if($item['data']['hidup_non_medis'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=non_rujukan&status=hidup&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['hidup_non_medis'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Mati Non Medis -->
                                    <td class="text-center">
                                        @if($item['data']['mati_non_medis'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=non_rujukan&status=mati&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['mati_non_medis'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Total Non Medis -->
                                    <td class="text-center bg-light">
                                        @if($item['data']['total_non_medis'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=non_rujukan&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                <strong>{{ $item['data']['total_non_medis'] }}</strong>
                                            </a>
                                        @else
                                            <strong>0</strong>
                                        @endif
                                    </td>
                                    <!-- Hidup Non Rujuk -->
                                    <td class="text-center">
                                        @if($item['data']['hidup_non_rujuk'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=non_rujukan&status=hidup&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['hidup_non_rujuk'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Mati Non Rujuk -->
                                    <td class="text-center">
                                        @if($item['data']['mati_non_rujuk'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=non_rujukan&status=mati&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['mati_non_rujuk'] }}
                                            </a>
                                        @else
                                             
                                        @endif
                                    </td>
                                    <!-- Total Non Rujuk -->
                                    <td class="text-center bg-light">
                                        @if($item['data']['total_non_rujuk'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&rujukan_type=non_rujukan&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                <strong>{{ $item['data']['total_non_rujuk'] }}</strong>
                                            </a>
                                        @else
                                            <strong>0</strong>
                                        @endif
                                    </td>
                                    <!-- Dirujuk -->
                                    <td class="text-center">
                                        @if($item['data']['dirujuk'] > 0)
                                            <a style="color:black;" href="{{ route('laporan.rl37.detail') }}?kategori={{ $item['kode'] }}&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" target="_blank">
                                                {{ $item['data']['dirujuk'] }}
                                            </a>
                                        @else
                                             
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