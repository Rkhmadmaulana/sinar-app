<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kematian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #bdd9bf;
            font-weight: bold;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h3 {
            margin: 3px 0;
            font-size: 14px;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
        }
        .keterangan {
            font-size: 9px;
            color: red;
            margin-bottom: 8px;
        }
        .total-col {
            background-color: #F47174;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin: 10px 0 5px 0;
            padding: 5px;
            background-color: #e9ecef;
            border: 1px solid #999;
        }
        .diagnosa-table td:nth-child(2) {
            text-align: left;
            max-width: 200px;
            font-size: 10px;
            white-space: pre-line;
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

    @if(!empty($diagnosaKematian) && count($diagnosaKematian) > 0)
    <div class="section-title">{{ $limitDiagnosaKematian }} PENYAKIT PENYEBAB KEMATIAN PASIEN RAWAT INAP</div>
    <table class="diagnosa-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Diagnosa</th>
                <th>Jumlah</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diagnosaKematian as $idx => $row)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $row->nm_penyakit }}@if(strpos($row->kd_penyakit, ',') === false) ({{ $row->kd_penyakit }})@endif</td>
                <td>{{ $row->total }}</td>
                <td>{{ $totalSemuaDiagnosa > 0 ? number_format($row->total / $totalSemuaDiagnosa * 100, 1) : '0' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;">
                <td colspan="2">Jumlah</td>
                <td class="total-col">{{ $totalSemuaDiagnosa }}</td>
                <td>100%</td>
            </tr>
        </tfoot>
    </table>
    @endif
</body>
</html>
