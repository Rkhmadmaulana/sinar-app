<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Ibu dan Bayi</title>
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
        <h3>LAPORAN</h3>
        <h3>IBU DAN BAYI</h3>
    </div>

    <p class="keterangan">*Data dibawah ini berdasarkan Tanggal Registrasi</p>

    <table>
        <thead>
            <tr>
                <th>Bayi Lahir Hidup</th>
                <th>Bayi Lahir Mati</th>
                <th>Bayi Ranap Mati</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $bayilahir->total }}</td>
                <td>{{ $bayimati->total }}</td>
                <td>{{ $bayimatiranap->total }}</td>
                <td class="total-col">{{ $total_lahirmati }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Berat Bayi Lahir >= 2500 Gr</th>
                <th>Berat Bayi Lahir <= 2500 Gr</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $bayi25->total }}</td>
                <td>{{ $bayi24->total }}</td>
                <td class="total-col">{{ $total_berat }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
