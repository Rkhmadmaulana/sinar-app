<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penyakit Menular</title>
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
        .disease-name {
            background-color: #bdd9bf;
            text-align: left;
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
    <h2>PENYAKIT MENULAR DI RSUD PANGERAN JAYA SUMITRA</h2>
    <p>{{ $tgllap }}</p>
    <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
</div>

<div class="info-box">
    <strong>Keterangan:</strong><br>
    <small>*Data dibawah ini berdasarkan Tanggal Registrasi</small>
</div>

{{-- TABEL PENYAKIT MENULAR --}}
<table>
    <tr>
        <th>Nama Penyakit</th>
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
        <td class="disease-name">HIV</td>
        <td>{{ $anggotahiv->hiv }}</td>
        <td>{{ $pnshiv->hiv }}</td>
        <td>{{ $kelpolrihiv->hiv }}</td>
        <td>{{ $dikbanghiv->hiv }}</td>
        <td>{{ $diktukhiv->hiv }}</td>
        <td>{{ $umumhiv->hiv }}</td>
        <td>{{ $bpjshiv }}</td>
        <td>{{ $otherhiv->hiv }}</td>
        <td class="total-cell">{{ $total_hiv }}</td>
    </tr>
    <tr>
        <td class="disease-name">Tuberkulosis (TB)</td>
        <td>{{ $anggotatb->tb }}</td>
        <td>{{ $pnstb->tb }}</td>
        <td>{{ $kelpolritb->tb }}</td>
        <td>{{ $dikbangtb->tb }}</td>
        <td>{{ $diktuktb->tb }}</td>
        <td>{{ $umumtb->tb }}</td>
        <td>{{ $bpjstb }}</td>
        <td>{{ $othertb->tb }}</td>
        <td class="total-cell">{{ $total_tb }}</td>
    </tr>
    <tr>
        <td class="disease-name">Malaria</td>
        <td>{{ $anggotamalaria->malaria }}</td>
        <td>{{ $pnsmalaria->malaria }}</td>
        <td>{{ $kelpolrimalaria->malaria }}</td>
        <td>{{ $dikbangmalaria->malaria }}</td>
        <td>{{ $diktukmalaria->malaria }}</td>
        <td>{{ $umummalaria->malaria }}</td>
        <td>{{ $bpjsmalaria }}</td>
        <td>{{ $othermalaria->malaria }}</td>
        <td class="total-cell">{{ $total_malaria }}</td>
    </tr>
    <tr>
        <td class="disease-name">DBD</td>
        <td>{{ $anggotadbd->dbd }}</td>
        <td>{{ $pnsdbd->dbd }}</td>
        <td>{{ $kelpolridbd->dbd }}</td>
        <td>{{ $dikbangdbd->dbd }}</td>
        <td>{{ $diktukdbd->dbd }}</td>
        <td>{{ $umumdbd->dbd }}</td>
        <td>{{ $bpjsdbd }}</td>
        <td>{{ $otherdbd->dbd }}</td>
        <td class="total-cell">{{ $total_dbd }}</td>
    </tr>
    <tr>
        <td class="disease-name">PMS</td>
        <td>{{ $anggotapms->pms }}</td>
        <td>{{ $pnspms->pms }}</td>
        <td>{{ $kelpolripms->pms }}</td>
        <td>{{ $dikbangpms->pms }}</td>
        <td>{{ $diktukpms->pms }}</td>
        <td>{{ $umumpms->pms }}</td>
        <td>{{ $bpjspms }}</td>
        <td>{{ $otherpms->pms }}</td>
        <td class="total-cell">{{ $total_pms }}</td>
    </tr>
    <tr>
        <td class="disease-name">Hepatitis</td>
        <td>{{ $anggotahepatitis->hepatitis }}</td>
        <td>{{ $pnshepatitis->hepatitis }}</td>
        <td>{{ $kelpolrihepatitis->hepatitis }}</td>
        <td>{{ $dikbanghepatitis->hepatitis }}</td>
        <td>{{ $diktukhepatitis->hepatitis }}</td>
        <td>{{ $umumhepatitis->hepatitis }}</td>
        <td>{{ $bpjshepatitis }}</td>
        <td>{{ $otherhepatitis->hepatitis }}</td>
        <td class="total-cell">{{ $total_hepatitis }}</td>
    </tr>
    <tr>
        <td class="disease-name">Covid</td>
        <td>{{ $anggotacovid->covid }}</td>
        <td>{{ $pnscovid->covid }}</td>
        <td>{{ $kelpolricovid->covid }}</td>
        <td>{{ $dikbangcovid->covid }}</td>
        <td>{{ $diktukcovid->covid }}</td>
        <td>{{ $umumcovid->covid }}</td>
        <td>{{ $bpjscovid }}</td>
        <td>{{ $othercovid->covid }}</td>
        <td class="total-cell">{{ $total_covid }}</td>
    </tr>
</table>

<div class="footer">
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
