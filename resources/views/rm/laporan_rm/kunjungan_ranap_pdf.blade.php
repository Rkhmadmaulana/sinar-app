<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pasien Rawat Ranap</title>
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
            background-color: #bdd9bf;
            color: #333;
            font-weight: bold;
        }
        .total-cell {
            background-color: #F47174;
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
    </style>
</head>
<body>

<div class="header">
    @if($hospitalInfo)
        <h1>{{ $hospitalInfo->nama_instansi ?? 'Rumah Sakit' }}</h1>
        <p>{{ $hospitalInfo->alamat_instansi ?? '' }}</p>
    @endif
    <h2>LAPORAN BULANAN</h2>
    <h2>JUMLAH KUNJUNGAN RAWAT INAP</h2>
    <p>{{ $tgllap }}</p>
    <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
</div>

<div class="info-box">
    <strong>Keterangan:</strong><br>
    <small>*Data dibawah ini berdasarkan Tanggal Keluar</small>
</div>

{{-- TABEL KUNJUNGAN RAWAT INAP --}}
<table>
    <tr>
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
    <tr>
        <td>{{ $anggotapolri->anggota_polri }}</td>
        <td>{{ $anggotapns->anggota_pns }}</td>
        <td>{{ $anggotakelpolri->anggota_kel_polri }}</td>
        <td>{{ $dikbang->siswa_dikbang }}</td>
        <td>{{ $diktuk->siswa_diktuk }}</td>
        <td>{{ $pasien_umum->pasienumum }}</td>
        <td>{{ $total_pengunjung_bpjs }}</td>
        <td>{{ $pasien_other->pasienother }}</td>
        <td class="total-cell">{{ $total_pengunjung }}</td>
    </tr>
</table>

<div class="footer">
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
