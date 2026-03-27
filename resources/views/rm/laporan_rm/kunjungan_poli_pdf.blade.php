<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kunjungan Poli</title>
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
            padding: 4px 6px;
            text-align: center;
        }
        th {
            background-color: #343a40;
            color: #fff;
            font-weight: bold;
        }
        .section-header {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: left;
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
    </style>
</head>
<body>

<div class="header">
    @if($hospitalInfo)
        <h1>{{ $hospitalInfo->nama_instansi ?? 'Rumah Sakit' }}</h1>
        <p>{{ $hospitalInfo->alamat_instansi ?? '' }}</p>
    @endif
    <h2>DATA KUNJUNGAN POLI BERDASARKAN KASUS / PENYAKIT</h2>
    <p>Tahun: {{ implode(', ', $years) }}</p>
    <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
</div>

<div class="info-box">
    <strong>Penyakit/Kasus yang ditampilkan:</strong><br>
    @foreach($penyakit as $p)
        {{ $p['nama'] }} ({{ $p['kode_icd'] }})@if(!$loop->last), @endif
    @endforeach
</div>

{{-- TABEL RAWAT JALAN --}}
<h3 style="font-size:11px; margin:15px 0 5px;">RAWAT JALAN</h3>
<table>
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Kasus/Penyakit<br><small>(Kode ICD-10)</small></th>
            @foreach($years as $year)
                <th colspan="2">{{ $year }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($years as $year)
                <th>Pasien Baru</th>
                <th>Kunjungan</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($rawatJalanData as $id => $data)
            <tr>
                <td>{{ $no++ }}</td>
                <td style="text-align:left;">{{ $data['nama'] }}<br><small>{{ $data['kode_icd'] }}</small></td>
                @foreach($years as $year)
                    @php $yd = $data['years'][$year] ?? ['pasien_baru'=>0,'kunjungan'=>0]; @endphp
                    <td>{{ $yd['pasien_baru'] }}</td>
                    <td>{{ $yd['kunjungan'] }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">JUMLAH</td>
            @foreach($years as $year)
                @php $tj = $rawatJalanTotals[$year] ?? ['pasien_baru'=>0,'kunjungan'=>0]; @endphp
                <td>{{ $tj['pasien_baru'] }}</td>
                <td>{{ $tj['kunjungan'] }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

{{-- TABEL RAWAT INAP --}}
<h3 style="font-size:11px; margin:15px 0 5px;">RAWAT INAP</h3>
<table>
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Kasus/Penyakit<br><small>(Kode ICD-10)</small></th>
            @foreach($years as $year)
                <th colspan="2">{{ $year }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($years as $year)
                <th>Jml Pasien</th>
                <th>Keluar Meninggal</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($rawatInapData as $id => $data)
            <tr>
                <td>{{ $no++ }}</td>
                <td style="text-align:left;">{{ $data['nama'] }}<br><small>{{ $data['kode_icd'] }}</small></td>
                @foreach($years as $year)
                    @php $yi = $data['years'][$year] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0]; @endphp
                    <td>{{ $yi['jumlah_pasien'] }}</td>
                    <td>{{ $yi['keluar_meninggal'] }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">JUMLAH</td>
            @foreach($years as $year)
                @php $ti = $rawatInapTotals[$year] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0]; @endphp
                <td>{{ $ti['jumlah_pasien'] }}</td>
                <td>{{ $ti['keluar_meninggal'] }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

<div class="footer">
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
