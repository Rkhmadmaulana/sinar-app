<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penyakit Terbanyak</title>
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
    </style>
</head>
<body>

<div class="header">
    @if($hospitalInfo)
        <h1>{{ $hospitalInfo->nama_instansi ?? 'Rumah Sakit' }}</h1>
        <p>{{ $hospitalInfo->alamat_instansi ?? '' }}</p>
    @endif
    <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
</div>

{{-- TABEL PENYAKIT TERBANYAK RAWAT INAP --}}
<div class="section-title">LAPORAN BULANAN<br>{{ $limit_penyakit ?? 10 }} JENIS PENYAKIT PASIEN TERBANYAK RAWAT INAP<br>{{ $tgllap }}</div>
<div class="info-box">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien Ranap</div>
<table>
    <tr>
        <th>Penyakit</th>
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
        $dataRanap = [];
        foreach ($diagnosa as $a) {
            // Anggota
            $anggota = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND pasien_polri.golongan_polri = '1'
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_anggota = $anggota[0]->total ?? 0;

            // PNS
            $pns = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '2' OR pasien_polri.golongan_polri = '7' OR pasien_polri.golongan_polri = '8' OR pasien_polri.golongan_polri = '10')
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_pns = $pns[0]->total ?? 0;

            // Keluarga
            $kel = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '9' OR pasien_polri.golongan_polri = '3')
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_kel = $kel[0]->total ?? 0;

            // Dikbang
            $dikbang = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '4' OR pasien_polri.golongan_polri = '6')
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_dikbang = $dikbang[0]->total ?? 0;

            // Diktuk
            $diktuk = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND pasien_polri.golongan_polri = '5'
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_diktuk = $diktuk[0]->total ?? 0;

            // Umum
            $umum = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj='UMU'", [$a->kode, $tgl1, $tgl2]);
            $val_umum = $umum[0]->total ?? 0;

            // BPJS
            $bpj = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_bpj = $bpj[0]->total - $val_anggota - $val_pns - $val_dikbang - $val_diktuk - $val_kel;

            // Other
            $other = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ranap'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj!='UMU' AND reg_periksa.kd_pj!='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_other = $other[0]->total ?? 0;

            $dataRanap[] = [
                'nama' => $a->nama,
                'kode' => $a->kode,
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
    @foreach ($dataRanap as $row)
    <tr>
        <td style="text-align: left;">{{ $row['nama'] }} - ({{ $row['kode'] }})</td>
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

<div class="page-break"></div>

{{-- TABEL PENYAKIT TERBANYAK RAWAT JALAN --}}
<div class="section-title">LAPORAN BULANAN<br>{{ $limit_penyakit ?? 10 }} JENIS PENYAKIT PASIEN TERBANYAK RAWAT JALAN<br>{{ $tgllap }}</div>
<div class="info-box">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien Ralan</div>
<table>
    <tr>
        <th>Penyakit</th>
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
        $dataRalan = [];
        foreach ($diagnosa_ralan as $a) {
            // Anggota
            $anggota = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND pasien_polri.golongan_polri = '1'
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_anggota = $anggota[0]->total ?? 0;

            // PNS
            $pns = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '2' OR pasien_polri.golongan_polri = '7' OR pasien_polri.golongan_polri = '8' OR pasien_polri.golongan_polri = '10')
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_pns = $pns[0]->total ?? 0;

            // Keluarga
            $kel = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '9' OR pasien_polri.golongan_polri = '3')
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_kel = $kel[0]->total ?? 0;

            // Dikbang
            $dikbang = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND (pasien_polri.golongan_polri = '4' OR pasien_polri.golongan_polri = '6')
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_dikbang = $dikbang[0]->total ?? 0;

            // Diktuk
            $diktuk = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                JOIN pasien_polri ON pasien_polri.no_rkm_medis = reg_periksa.no_rkm_medis
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND pasien_polri.golongan_polri = '5'
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_diktuk = $diktuk[0]->total ?? 0;

            // Umum
            $umum = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj='UMU'", [$a->kode, $tgl1, $tgl2]);
            $val_umum = $umum[0]->total ?? 0;

            // BPJS
            $bpj = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_bpj = $bpj[0]->total - $val_anggota - $val_pns - $val_dikbang - $val_diktuk - $val_kel;

            // Other
            $other = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien
                JOIN reg_periksa ON diagnosa_pasien.no_rawat = reg_periksa.no_rawat
                WHERE diagnosa_pasien.kd_penyakit = ?
                AND reg_periksa.status_lanjut = 'Ralan'
                AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
                AND reg_periksa.kd_pj!='UMU' AND reg_periksa.kd_pj!='BPJ'", [$a->kode, $tgl1, $tgl2]);
            $val_other = $other[0]->total ?? 0;

            $dataRalan[] = [
                'nama' => $a->nama,
                'kode' => $a->kode,
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
    @foreach ($dataRalan as $row)
    <tr>
        <td style="text-align: left;">{{ $row['nama'] }} - ({{ $row['kode'] }})</td>
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

<div class="footer">
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
