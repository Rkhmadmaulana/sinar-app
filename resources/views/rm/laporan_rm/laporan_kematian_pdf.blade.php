<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kematian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #bdd9bf;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 5px 0;
        }
        .keterangan {
            font-size: 10px;
            color: red;
            margin-bottom: 10px;
        }
        .total-col {
            background-color: #F47174;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN BULANAN</h3>
        <h3>REKAPITULASI KEMATIAN</h3>
        <p>{{ $tgllap }}</p>
    </div>

    <p class="keterangan">*Data dibawah ini berdasarkan Tanggal Registrasi</p>

    <table>
        <thead>
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
        </thead>
        <tbody>
            <tr>
                <td>{{ $anggota->total }}</td>
                <td>{{ $pns->total }}</td>
                <td>{{ $keluarga->total }}</td>
                <td>{{ $dikbang->total }}</td>
                <td>{{ $diktuk->total }}</td>
                <td>{{ $umum->total }}</td>
                <td>{{ $bpjs }}</td>
                <td>{{ $lainnya->total }}</td>
                <td class="total-col">{{ $total }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Rawat Inap</th>
                <th>IGD</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $ranap->total2 }}</td>
                <td>{{ $igd->total2 }}</td>
                <td class="total-col">{{ $total2 }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
