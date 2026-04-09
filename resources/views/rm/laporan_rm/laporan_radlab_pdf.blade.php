<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Radiologi & Laboratorium</title>
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
            padding: 8px;
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
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN BULANAN</h3>
        <h3>RADIOLOGI & LABORATORIUM</h3>
        <p>{{ $tgllap }}</p>
    </div>

    <p class="keterangan">*Data dibawah ini berdasarkan Tanggal Registrasi</p>

    <table>
        <thead>
            <tr>
                <th colspan="2">Pemeriksaan</th>
            </tr>
            <tr>
                <th>Radiologi</th>
                <th>Laboratorium</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $totalRadiologi }}</td>
                <td>{{ $totalLab }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
