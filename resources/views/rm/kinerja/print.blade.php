@extends('layout.app')
@section('content')
@php
    // Ambil filter tanggal dari query string
    $tgl1 = request()->query('tgl1');
    $tgl2 = request()->query('tgl2');
@endphp

<!DOCTYPE html>
<html>
<head>
    <title>Print Kinerja Pasien ({{ $tgl1 }} s.d. {{ $tgl2 }})</title>
    <style>
        /* Style khusus untuk print */
        @media print {
            .no-print { display: none; }
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; text-align: center; }
        .header-cell { background-color: rgb(141, 250, 148); }
        .group-cell { background-color: #bdd9bf; }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 1rem;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
    </div>

    <h4>Laporan Kinerja Pasien</h4>
    <p>Periode: {{ $tgl1 }} s.d. {{ $tgl2 }}</p>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th rowspan="2" class="header-cell">No</th>
                    <th rowspan="2" class="header-cell">Bangsal / Kelas</th>
                    <th rowspan="2" class="header-cell">TT</th>
                    <th colspan="3" class="header-cell">Pasien Masuk</th>
                    <th colspan="7" class="header-cell">Pasien Keluar</th>
                    <th rowspan="2" class="header-cell">Lama Dirawat</th>
                    <th rowspan="2" class="header-cell">Sisa Pasien</th>
                    <th rowspan="2" class="header-cell">Hari Perawatan</th>
                    <th rowspan="2" class="header-cell">BOR %</th>
                    <th rowspan="2" class="header-cell">LOS</th>
                    <th rowspan="2" class="header-cell">BTO</th>
                    <th rowspan="2" class="header-cell">TOI</th>
                    <th rowspan="2" class="header-cell">NDR ‰</th>
                    <th rowspan="2" class="header-cell">GDR ‰</th>
                </tr>
                <tr>
                    <th class="header-cell">Awal</th>
                    <th class="header-cell">Masuk</th>
                    <th class="header-cell">Pindahan</th>
                    <th class="header-cell">Dipindahkan</th>
                    <th class="header-cell">Keluar Hidup</th>
                    <th class="header-cell">APS/Pulang Paksa</th>
                    <th class="header-cell">Pulang &lt;24 jam</th>
                    <th class="header-cell">Meninggal ≤48 jam</th>
                    <th class="header-cell">Meninggal &gt;48 jam</th>
                    <th class="header-cell">Total Meninggal</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($bangsalList as $kd_bangsal => $alias)
                    @foreach(array_merge([$alias], array_map(fn($k) => $alias . str_replace(' ', '', $k), $kelasList)) as $key)
                        <tr class="{{ $loop->parent->first ? 'group-cell' : '' }}">
                            @if($loop->first)
                                <td rowspan="{{ count($kelasList) + 1 }}">{{ $no++ }}</td>
                            @endif
                            <td>{{ ucfirst(str_replace('kerapu', 'Ruang ' . $alias, $key)) }}</td>
                            <td>{{ $tempatTidur[$key] ?? 0 }}</td>
                            <td>{{ $pasienAwal[$key] ?? 0 }}</td>
                            <td>{{ $pasienMasuk[$key] ?? 0 }}</td>
                            <td>{{ $pasienPindahan[$key] ?? 0 }}</td>
                            <td>{{ $pasienKeluarPindahan[$key] ?? 0 }}</td>
                            <td>{{ $pasienKeluarHidup[$key] ?? 0 }}</td>
                            <td>{{ $pasienPulangTidakStandar[$key] ?? 0 }}</td>
                            <td>{{ $pasienPulangHariSama[$key] ?? 0 }}</td>
                            <td>{{ $pasienMeninggal48Jam[$key] ?? 0 }}</td>
                            <td>{{ $pasienMeninggal48plus[$key] ?? 0 }}</td>
                            <td>{{ $pasienMeninggalTotal[$key] ?? 0 }}</td>
                            <td>{{ $lamaDirawat[$key] ?? 0 }}</td>
                            <td>{{ $sisaPasien[$key] ?? 0 }}</td>
                            <td>{{ $hariPerawatan[$key] ?? 0 }}</td>
                            <td>{{ $bor[$key] ?? 0 }}%</td>
                            <td>{{ $los[$key] ?? 0 }}</td>
                            <td>{{ $bto[$key] ?? 0 }}</td>
                            <td>{{ $toi[$key] ?? 0 }}</td>
                            <td>{{ $ndr[$key] ?? 0 }}‰</td>
                            <td>{{ $gdr[$key] ?? 0 }}‰</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td>{{ $tempatTidur['total'] ?? 0 }}</td>
                    <td>{{ $pasienAwal['total'] ?? 0 }}</td>
                    <td>{{ $pasienMasuk['total'] ?? 0 }}</td>
                    <td>{{ $pasienPindahan['total'] ?? 0 }}</td>
                    <td>{{ $pasienKeluarPindahan['total'] ?? 0 }}</td>
                    <td>{{ $pasienKeluarHidup['total'] ?? 0 }}</td>
                    <td>{{ $pasienPulangTidakStandar['total'] ?? 0 }}</td>
                    <td>{{ $pasienPulangHariSama['total'] ?? 0 }}</td>
                    <td>{{ $pasienMeninggal48Jam['total'] ?? 0 }}</td>
                    <td>{{ $pasienMeninggal48plus['total'] ?? 0 }}</td>
                    <td>{{ $pasienMeninggalTotal['total'] ?? 0 }}</td>
                    <td>{{ $lamaDirawat['total'] ?? 0 }}</td>
                    <td>{{ $sisaPasien['total'] ?? 0 }}</td>
                    <td>{{ $hariPerawatan['total'] ?? 0 }}</td>
                    <td>{{ $bor['total'] ?? 0 }}%</td>
                    <td>{{ $los['total'] ?? 0 }}</td>
                    <td>{{ $bto['total'] ?? 0 }}</td>
                    <td>{{ $toi['total'] ?? 0 }}</td>
                    <td>{{ $ndr['total'] ?? 0 }}‰</td>
                    <td>{{ $gdr['total'] ?? 0 }}‰</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
@endsection
