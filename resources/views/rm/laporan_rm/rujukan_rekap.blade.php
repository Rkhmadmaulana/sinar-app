@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rekapitulasi Kegiatan Pelayanan Rujukan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rujukan-rekap') }}" method="GET" class="mb-4">
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
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ isset($tanggalAwal) ? date('d-m-Y', strtotime($tanggalAwal)) : now()->startOfMonth()->format('d-m-Y') }} s/d {{ isset($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : now()->endOfMonth()->format('d-m-Y') }}</h5>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Jenis Spesialisasi</th>
                            <th colspan="6">Rujukan Masuk</th>
                            <th colspan="4">Dirujuk Keluar</th>
                        </tr>
                        <tr>
                            <!-- Rujukan Masuk -->
                            <th colspan="3">Diterima Dari</th>
                            <th colspan="3">Dikembalikan Ke</th>
                            
                            <!-- Dirujuk Keluar -->
                            <th>Pasien Rujukan</th>
                            <th>Pasien Datang Sendiri</th>
                            <th>Total Dirujuk Keluar</th>
                            <th>Diterima Kembali</th>
                        </tr>
                        <tr>
                            <!-- Headers for bottom row -->
                            <th></th>
                            <th></th>
                            
                            <!-- Diterima Dari -->
                            <th>Puskesmas</th>
                            <th>RS Lain</th>
                            <th>Faskes Lain</th>
                            
                            <!-- Dikembalikan Ke -->
                            <th>Puskesmas</th>
                            <th>RS Asal</th>
                            <th>Faskes Asal</th>
                            
                            <!-- Dirujuk Keluar headers -->
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spesialisasi as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item['nama'] }}</td>
                            
                            <!-- Rujukan Masuk - Diterima Dari -->
                            <td class="text-center">
                                @if(($item['data']['diterima_dari']['puskesmas']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=diterima_dari&source=puskesmas&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}&spec_key={{ $item['key'] }}" 
                                    target="_blank">
                                        {{ $item['data']['diterima_dari']['puskesmas']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($item['data']['diterima_dari']['rs_lain']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=diterima_dari&source=rs_lain&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}&spec_key={{ $item['key'] }}" 
                                    target="_blank">
                                        {{ $item['data']['diterima_dari']['rs_lain']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($item['data']['diterima_dari']['faskes_lain']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=diterima_dari&source=faskes_lain&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}&spec_key={{ $item['key'] }}" 
                                    target="_blank">
                                        {{ $item['data']['diterima_dari']['faskes_lain']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Rujukan Masuk - Dikembalikan Ke -->
                            <td class="text-center">
                                @if(($item['data']['dikembalikan_ke']['puskesmas']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=dikembalikan_ke&source=puskesmas&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}&spec_key={{ $item['key'] }}" 
                                    target="_blank">
                                        {{ $item['data']['dikembalikan_ke']['puskesmas']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($item['data']['dikembalikan_ke']['rs_asal']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=dikembalikan_ke&source=rs_asal&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}&spec_key={{ $item['key'] }}" 
                                    target="_blank">
                                        {{ $item['data']['dikembalikan_ke']['rs_asal']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($item['data']['dikembalikan_ke']['faskes_asal']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=dikembalikan_ke&source=faskes_asal&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}&spec_key={{ $item['key'] }}" 
                                    target="_blank">
                                        {{ $item['data']['dikembalikan_ke']['faskes_asal']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            
                            <!-- Dirujuk Keluar (kosong) -->
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                        @endforeach
                        
                        <!-- Total row -->
                        <tr class="font-weight-bold">
                            <td colspan="2" class="text-center">TOTAL</td>
                            
                            <!-- Total row - Diterima Dari -->
                            <td class="text-center">
                                @if(($totalData['diterima_dari']['puskesmas']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=diterima_dari&source=puskesmas&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                    target="_blank">
                                        {{ $totalData['diterima_dari']['puskesmas']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($totalData['diterima_dari']['rs_lain']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=diterima_dari&source=rs_lain&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                    target="_blank">
                                        {{ $totalData['diterima_dari']['rs_lain']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($totalData['diterima_dari']['faskes_lain']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=diterima_dari&source=faskes_lain&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                    target="_blank">
                                        {{ $totalData['diterima_dari']['faskes_lain']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Total row - Dikembalikan Ke -->
                            <td class="text-center">
                                @if(($totalData['dikembalikan_ke']['puskesmas']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=dikembalikan_ke&source=puskesmas&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                    target="_blank">
                                        {{ $totalData['dikembalikan_ke']['puskesmas']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($totalData['dikembalikan_ke']['rs_asal']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=dikembalikan_ke&source=rs_asal&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                    target="_blank">
                                        {{ $totalData['dikembalikan_ke']['rs_asal']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($totalData['dikembalikan_ke']['faskes_asal']['value'] ?? 0) > 0)
                                    <a style="color:black;" href="{{ route('laporan.rujukan-rekap.detail') }}?category=dikembalikan_ke&source=faskes_asal&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                    target="_blank">
                                        {{ $totalData['dikembalikan_ke']['faskes_asal']['value'] ?? 0 }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            
                            <!-- Dirujuk Keluar (kosong) -->
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection