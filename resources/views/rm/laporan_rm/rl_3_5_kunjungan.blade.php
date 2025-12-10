@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RL 3.5 - Rekapitulasi Kunjungan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rl-3-5-kunjungan') }}" method="GET" class="mb-4">
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

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ isset($tanggalAwal) ? date('d-m-Y', strtotime($tanggalAwal)) : now()->startOfMonth()->format('d-m-Y') }} 
                        s/d {{ isset($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : now()->endOfMonth()->format('d-m-Y') }}</h5>
                </div>
            </div>

            <div class="mb-3">
                <a href="{{ route('laporan.rl-3-5-kunjungan') }}?download_pdf=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ route('laporan.rl-3-5-kunjungan') }}?download_excel=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="text-center">
                        <tr class="bg-primary text-white">
                            <th rowspan="2" style="vertical-align: middle;">No</th>
                            <th rowspan="2" style="vertical-align: middle;">Jenis Kegiatan</th>
                            <th colspan="2">Kunjungan Pasien<br>Dalam Kab/Kota</th>
                            <th colspan="2">Kunjungan Pasien<br>Luar Kab/Kota</th>
                            <th rowspan="2" style="vertical-align: middle;">Total<br>Kunjungan</th>
                        </tr>
                        <tr class="bg-primary text-white">
                            <th>Laki-laki</th>
                            <th>Perempuan</th>
                            <th>Laki-laki</th>
                            <th>Perempuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalDalamL = 0;
                            $totalDalamP = 0;
                            $totalLuarL = 0;
                            $totalLuarP = 0;
                            $totalSemua = 0;
                        @endphp
                        @foreach($jenisKegiatan as $index => $jenis)
                            @php
                                $dalam_L = $jenis['data']['Dalam_L'] ?? 0;
                                $dalam_P = $jenis['data']['Dalam_P'] ?? 0;
                                $luar_L = $jenis['data']['Luar_L'] ?? 0;
                                $luar_P = $jenis['data']['Luar_P'] ?? 0;
                                $total = $dalam_L + $dalam_P + $luar_L + $luar_P;
                                
                                $totalDalamL += $dalam_L;
                                $totalDalamP += $dalam_P;
                                $totalLuarL += $luar_L;
                                $totalLuarP += $luar_P;
                                $totalSemua += $total;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $jenis['nama'] }}</td>
                                <td class="text-center">{{ $dalam_L > 0 ? number_format($dalam_L, 0, ',', '.') : '-' }}</td>
                                <td class="text-center">{{ $dalam_P > 0 ? number_format($dalam_P, 0, ',', '.') : '-' }}</td>
                                <td class="text-center">{{ $luar_L > 0 ? number_format($luar_L, 0, ',', '.') : '-' }}</td>
                                <td class="text-center">{{ $luar_P > 0 ? number_format($luar_P, 0, ',', '.') : '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-warning font-weight-bold">
                            <td colspan="2" class="text-center">TOTAL</td>
                            <td class="text-center">{{ number_format($totalDalamL, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($totalDalamP, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($totalLuarL, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($totalLuarP, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($totalSemua, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection