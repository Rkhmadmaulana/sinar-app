<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pasien Rawat Jalan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 14px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 12px;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #343a40;
            color: #fff;
            font-weight: bold;
        }
        th.pengunjung-header {
            background-color: #F47174;
            color: #333;
        }
        th.kunjungan-header {
            background-color: #bdd9bf;
            color: #333;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
        }
        .info-box {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            background: #f8f9fa;
        }
        .info-box strong {
            margin-right: 5px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 15px 0 5px;
            padding: 5px;
            background-color: #17a2b8;
            color: white;
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
    <h2>JUMLAH PENGUNJUNG DAN KUNJUNGAN RAWAT JALAN</h2>
    <p>{{ $tgllap }}</p>
    <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
</div>

<div class="info-box">
    <strong>Keterangan:</strong><br>
    <small>*Data dibawah ini berdasarkan Tanggal Registrasi</small>
</div>

{{-- TABEL PENGUNJUNG DAN KUNJUNGAN --}}
<table>
    <tr>
        <th style="text-align: center;" class="pengunjung-header" colspan="9">Pengunjung</th>
        <th style="text-align: center;" class="kunjungan-header" colspan="9">Kunjungan</th>
    </tr>
    <tr>
        <th class="pengunjung-header">Anggota</th>
        <th class="pengunjung-header">PNS</th>
        <th class="pengunjung-header">Keluarga</th>
        <th class="pengunjung-header">Siswa Dikbang</th>
        <th class="pengunjung-header">Siswa Diktuk</th>
        <th class="pengunjung-header">Mandiri</th>
        <th class="pengunjung-header">BPJS</th>
        <th class="pengunjung-header">Lainnya</th>
        <th class="pengunjung-header">Total</th>
        <th class="kunjungan-header">Anggota</th>
        <th class="kunjungan-header">PNS</th>
        <th class="kunjungan-header">Keluarga</th>
        <th class="kunjungan-header">Siswa Dikbang</th>
        <th class="kunjungan-header">Siswa Diktuk</th>
        <th class="kunjungan-header">Mandiri</th>
        <th class="kunjungan-header">BPJS</th>
        <th class="kunjungan-header">Lainnya</th>
        <th class="kunjungan-header">Total</th>
    </tr>
    <tr>
        <td>{{ $anggotapolri->anggota_polri }}</td>
        <td>{{ $anggotapns->anggota_pns }}</td>
        <td>{{ $anggotakelpolri->anggota_kel_polri }}</td>
        <td>{{ $dikbang->siswa_dikbang }}</td>
        <td>{{ $diktuk->siswa_diktuk }}</td>
        <td>{{ $pasien_umum->pasienumum }}</td>
        <td>{{ $total_pengunjung_bpjs }}</td>
        <td>{{ $pasien_other->pasienother }}</td>
        <td style="background-color: #F47174; font-weight: bold;">{{ $total_pengunjung }}</td>
        <td>{{ $anggotapolri->kunjungan_anggota_polri }}</td>
        <td>{{ $anggotapns->kunjungan_anggota_pns }}</td>
        <td>{{ $anggotakelpolri->kunjungan_kel_polri }}</td>
        <td>{{ $dikbang->kunjungan_siswa_dikbang }}</td>
        <td>{{ $diktuk->kunjungan_siswa_diktuk }}</td>
        <td>{{ $pasien_umum->kunjungan_pasienumum }}</td>
        <td>{{ $total_kunjungan_bpjs }}</td>
        <td>{{ $pasien_other->kunjungan_pasienother }}</td>
        <td style="background-color: #bdd9bf; font-weight: bold;">{{ $total_kunjungan }}</td>
    </tr>
</table>

{{-- TABEL HASIL RAWAT JALAN --}}
<div class="section-title">Laporan Hasil Rawat Jalan Tahun {{ $tahun }}</div>
<table>
    <thead>
        <tr style="background-color: #17a2b8; color: white;">
            <th>Hasil Pelayanan</th>
            <th>Jumlah Pasien</th>
            <th>Persentase</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: left;">Sembuh</td>
            <td>{{ $sembuhRalan ?? 0 }}</td>
            <td>{{ $persenSembuhRalan ?? 0 }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Rujuk ke Rawat Inap</td>
            <td>{{ $ranapRalan ?? 0 }}</td>
            <td>{{ $persenRanapRalan ?? 0 }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Lainnya</td>
            <td>{{ $lainnyaRalan ?? 0 }}</td>
            <td>{{ $persenLainnyaRalan ?? 0 }}%</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td style="text-align: center;">TOTAL</td>
            <td>{{ $totalSemuaRalan ?? 0 }}</td>
            <td>100%</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
