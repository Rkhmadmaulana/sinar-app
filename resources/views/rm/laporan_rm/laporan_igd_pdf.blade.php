<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan IGD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 12px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 11px;
        }
        .header p {
            margin: 2px 0;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px 5px;
            text-align: center;
            font-size: 8px;
        }
        th {
            background-color: #bdd9bf;
            color: #333;
            font-weight: bold;
        }
        .total-cell {
            background-color: #F47174;
            font-weight: bold;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 5px;
            padding: 5px;
            background-color: #17a2b8;
            color: white;
        }
        .section-title-green {
            background-color: #28a745;
        }
        .section-title-primary {
            background-color: #007bff;
        }
        .info-box {
            margin-bottom: 8px;
            font-size: 8px;
            color: #666;
        }
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
        }
        .page-break {
            page-break-before: always;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .table-secondary {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<div class="header">
    @if($hospitalInfo)
        <h1>{{ $hospitalInfo->nama_instansi ?? 'Rumah Sakit' }}</h1>
        <p>{{ $hospitalInfo->alamat_instansi ?? '' }}</p>
    @endif
    <h2>LAPORAN BULANAN</h2>
    <h2>REKAPITULASI PASIEN IGD</h2>
    <p>{{ $tgllap }}</p>
    <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
</div>

<div class="info-box">*Data dibawah ini berdasarkan Tanggal Registrasi</div>

{{-- TABEL 1: REKAPITULASI PASIEN IGD --}}
<table>
    <tr>
        <th>Jenis Kasus</th>
        <th>Anggota</th>
        <th>PNS</th>
        <th>Keluarga</th>
        <th>Siswa Dikbang</th>
        <th>Siswa Diktuk</th>
        <th>Mandiri</th>
        <th>BPJS</th>
        <th>Lainnya</th>
        <th>Total</th>
    </tr>
    @php
        $dataIgd = [];
        foreach ($igd as $a) {
            // Anggota
            $anggota = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND pasien_polri.golongan_polri = '1'
                AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_anggota = $anggota[0]->total ?? 0;

            // PNS
            $pns = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '2' OR pasien_polri.golongan_polri = '7' OR pasien_polri.golongan_polri = '8' OR pasien_polri.golongan_polri = '10')
                AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_pns = $pns[0]->total ?? 0;

            // Keluarga
            $kel = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '9' OR pasien_polri.golongan_polri = '3')
                AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_kel = $kel[0]->total ?? 0;

            // Dikbang
            $dikbang = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '4' OR pasien_polri.golongan_polri = '6')
                AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_dikbang = $dikbang[0]->total ?? 0;

            // Diktuk
            $diktuk = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND pasien_polri.golongan_polri = '5'
                AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_diktuk = $diktuk[0]->total ?? 0;

            // Umum
            $umum = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj='UMU'", [$a->kasus, $tgl1, $tgl2]);
            $val_umum = $umum[0]->total ?? 0;

            // BPJS
            $bpj = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_bpj = $bpj[0]->total - $val_anggota - $val_pns - $val_dikbang - $val_diktuk - $val_kel;

            // Other
            $other = DB::select("SELECT COUNT(*) as total FROM reg_periksa
                JOIN data_triase_igd ON data_triase_igd.no_rawat = reg_periksa.no_rawat
                JOIN master_triase_macam_kasus ON master_triase_macam_kasus.kode_kasus = data_triase_igd.kode_kasus
                WHERE master_triase_macam_kasus.macam_kasus = ?
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj!='UMU' AND reg_periksa.kd_pj!='BPJ'", [$a->kasus, $tgl1, $tgl2]);
            $val_other = $other[0]->total ?? 0;

            $dataIgd[] = [
                'kasus' => $a->kasus,
                'anggota' => $val_anggota,
                'pns' => $val_pns,
                'keluarga' => $val_kel,
                'dikbang' => $val_dikbang,
                'diktuk' => $val_diktuk,
                'umum' => $val_umum,
                'bpj' => $val_bpj,
                'other' => $val_other,
                'total' => $a->total
            ];
        }
    @endphp
    @foreach ($dataIgd as $row)
    <tr>
        <td style="text-align: left; background-color: #bdd9bf;">{{ $row['kasus'] }}</td>
        <td>{{ $row['anggota'] }}</td>
        <td>{{ $row['pns'] }}</td>
        <td>{{ $row['keluarga'] }}</td>
        <td>{{ $row['dikbang'] }}</td>
        <td>{{ $row['diktuk'] }}</td>
        <td>{{ $row['umum'] }}</td>
        <td>{{ $row['bpj'] }}</td>
        <td>{{ $row['other'] }}</td>
        <td class="total-cell">{{ $row['total'] }}</td>
    </tr>
    @endforeach
</table>

<!--<div class="page-break"></div>-->

{{-- TABEL 2: LAYANAN LANJUTAN PASIEN IGD --}}
<div class="section-title section-title-primary">Layanan Lanjutan Pasien IGD – Tahun {{ $tahun }}</div>
<table>
    <thead>
        <tr>
            <th style="width:50%">Jenis Layanan</th>
            <th style="width:25%">Jumlah Pasien</th>
            <th style="width:25%">Persentase</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: left;">Pulang Sembuh</td>
            <td class="text-end">{{ $pulang ?? 0 }}</td>
            <td class="text-end">{{ $persenPulang ?? 0 }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Rujuk Rawat Inap (RRI)</td>
            <td class="text-end">{{ $rri ?? 0 }}</td>
            <td class="text-end">{{ $persenRri ?? 0 }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Rujuk FKTL Lain</td>
            <td class="text-end">{{ $rujukKeluar ?? 0 }}</td>
            <td class="text-end">{{ $persenRujuk ?? 0 }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Meninggal di IGD</td>
            <td class="text-end">{{ $meninggalIgd ?? 0 }}</td>
            <td class="text-end">{{ $persenMeninggalIgd ?? 0 }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Lainnya</td>
            <td class="text-end">{{ $lainnya ?? 0 }}</td>
            <td class="text-end">{{ $persenLainnya ?? 0 }}%</td>
        </tr>
    </tbody>
    <tfoot class="table-secondary fw-bold">
        <tr>
            <td class="text-center">TOTAL</td>
            <td class="text-end">{{ $total ?? 0 }}</td>
            <td class="text-end">100%</td>
        </tr>
    </tfoot>
</table>

{{-- TABEL 3: DISTRIBUSI KUNJUNGAN PASIEN IGD & PONEK --}}
<div class="section-title" style="margin-top: 20px;">Distribusi Kunjungan Pasien IGD & PONEK<br>Berdasarkan Rekap Bulanan Tahun {{ $tahun }}</div>
<table>
    <thead>
        <tr>
            <th>Bulan</th>
            <th>IGD</th>
            <th>% IGD</th>
            <th>PONEK</th>
            <th>% PONEK</th>
        </tr>
    </thead>
    <tbody>
        @for ($i=1; $i<=12; $i++)
        @php
            $igdVal = $data[$i]->igd ?? 0;
            $ponekVal = $data[$i]->ponek ?? 0;
            $persenIgd = $totalIgd > 0 ? round(($igdVal / $totalIgd) * 100, 2) : 0;
            $persenPonek = $totalPonek > 0 ? round(($ponekVal / $totalPonek) * 100, 2) : 0;
        @endphp
        <tr>
            <td style="text-align: left;">{{ $bulan[$i] }}</td>
            <td class="text-end">{{ $igdVal }}</td>
            <td class="text-end">{{ $persenIgd }}%</td>
            <td class="text-end">{{ $ponekVal }}</td>
            <td class="text-end">{{ $persenPonek }}%</td>
        </tr>
        @endfor
    </tbody>
    <tfoot class="table-secondary fw-bold">
        <tr>
            <td class="text-center">TOTAL</td>
            <td class="text-end">{{ $totalIgd }}</td>
            <td class="text-end">100%</td>
            <td class="text-end">{{ $totalPonek }}</td>
            <td class="text-end">100%</td>
        </tr>
    </tfoot>
</table>

{{-- TABEL 4: 10 BESAR PENYAKIT IGD --}}
<div class="section-title section-title-green" style="margin-top: 20px;">10 Besar Penyakit IGD Tahun {{ $tahun }}</div>
<table>
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:15%">Kode ICD</th>
            <th>Nama Penyakit</th>
            <th style="width:20%">Jumlah Kasus</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topPenyakit as $i => $row)
        <tr>
            <td class="text-center">{{ $i+1 }}</td>
            <td class="text-center">{{ $row->kd_penyakit }}</td>
            <td style="text-align: left;">{{ $row->nm_penyakit }}</td>
            <td class="text-end">{{ $row->jumlah_kasus }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
