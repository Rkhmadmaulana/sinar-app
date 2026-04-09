<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pertumbuhan</title>
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
            margin-bottom: 10px;
        }
        .keterangan-biru {
            color: rgb(0, 26, 255);
        }
        .keterangan-merah {
            color: red;
        }
        .trending-up {
            color: rgb(9, 255, 0);
        }
        .trending-down {
            color: rgb(255, 0, 0);
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN BULANAN</h3>
        <h3>PERTUMBUHAN PRODUKTIFITAS</h3>
        <p>{{ $tgllap }}</p>
    </div>

    <p class="keterangan">Pasien Bulan Lalu = {{ $dari }} S/D {{ $sampai }}<br>
    Pasien Bulan Ini = {{ $tgllap }}</p>
    <p class="keterangan-biru">Rumus Pertumbuhan ((Pasien Bulan Ini - Pasien Bulan Lalu) / Pasien Bulan Lalu) x 100%</p>

    <table>
        <thead>
            <tr>
                <th colspan="2">Kunjungan Rawat Jalan</th>
                <th colspan="2">Kunjungan IGD</th>
                <th colspan="2">Pasien Rawat Inap</th>
                <th colspan="2">Pemeriksaan Radiologi</th>
                <th colspan="2">Pemeriksaan LAB</th>
            </tr>
            <tr>
                <th>Jumlah</th>
                <th>Pertumbuhan</th>
                <th>Jumlah</th>
                <th>Pertumbuhan</th>
                <th>Jumlah</th>
                <th>Pertumbuhan</th>
                <th>Jumlah</th>
                <th>Pertumbuhan</th>
                <th>Jumlah</th>
                <th>Pertumbuhan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $sqlrajal->total }}</td>
                <td>
                    @if($pertumbuhan_ralan >= 0)
                        <span class="trending-up">{{ $pertumbuhan_ralan }} %</span>
                    @else
                        <span class="trending-down">{{ $pertumbuhan_ralan }} %</span>
                    @endif
                </td>
                <td>{{ $sqligd->total }}</td>
                <td>
                    @if($pertumbuhan_igd >= 0)
                        <span class="trending-up">{{ $pertumbuhan_igd }} %</span>
                    @else
                        <span class="trending-down">{{ $pertumbuhan_igd }} %</span>
                    @endif
                </td>
                <td>{{ $sqlranap->total }}</td>
                <td>
                    @if($pertumbuhan_ranap >= 0)
                        <span class="trending-up">{{ $pertumbuhan_ranap }} %</span>
                    @else
                        <span class="trending-down">{{ $pertumbuhan_ranap }} %</span>
                    @endif
                </td>
                <td>{{ $sqlrad->total }}</td>
                <td>
                    @if($pertumbuhan_rad >= 0)
                        <span class="trending-up">{{ $pertumbuhan_rad }} %</span>
                    @else
                        <span class="trending-down">{{ $pertumbuhan_rad }} %</span>
                    @endif
                </td>
                <td>{{ $sqllab->total }}</td>
                <td>
                    @if($pertumbuhan_lab >= 0)
                        <span class="trending-up">{{ $pertumbuhan_lab }} %</span>
                    @else
                        <span class="trending-down">{{ $pertumbuhan_lab }} %</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <p class="keterangan-merah">*Data berdasarkan Tanggal Registrasi (Rajal, Ranap, Rehab Medik)<br>
    *Data berdasarkan Tanggal Periksa (Radiologi, Laboratorium)</p>
</body>
</html>
