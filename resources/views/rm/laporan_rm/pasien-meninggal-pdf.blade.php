<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pasien Meninggal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9pt;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 16pt;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 11pt;
            margin-bottom: 15px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PASIEN MENINGGAL</h1>
        <div class="subtitle">Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</div>
    </div>
    
    <h3>Data Pasien Meninggal</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Masuk</th>
                <th>Rekam Medis</th>
                <th>Nama Pasien</th>
                <th>JK</th>
                <th>Tgl Lahir</th>
                <th>Umur</th>
                <th>Penanggung Jawab</th>
                <th>Meninggal<br>< 48 Jam</th>
                <th>Meninggal<br>>= 48 Jam</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->no }}</td>
                    <td>{{ $item->tgl_masuk }}</td>
                    <td>{{ $item->no_rkm_medis }}</td>
                    <td>{{ $item->nm_pasien }}</td>
                    <td>{{ $item->jk }}</td>
                    <td>{{ $item->tgl_lahir }}</td>
                    <td>{{ $item->umur !== null ? $item->umur : '-' }}</td>
                    <td>{{ $item->png_jawab }}</td>
                    <td class="text-center">{{ $item->meninggal_kurang_48jam }}</td>
                    <td class="text-center">{{ $item->meninggal_lebih_48jam }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total Pasien</th>
                <th colspan="7">{{ count($data) }} Pasien</th>
            </tr>
        </tfoot>
    </table>
    
    <div class="page-break"></div>
    
    <h3>Ringkasan Diagnosa</h3>
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Diagnosa</th>
                <th colspan="2" class="text-center">Jenis Kelamin</th>
                <th colspan="11" class="text-center">Kelompok Umur</th>
                <th colspan="2" class="text-center">Meninggal</th>
            </tr>
            <tr>
                <th>L</th>
                <th>P</th>
                <th><1</th>
                <th><4</th>
                <th><9</th>
                <th><14</th>
                <th><19</th>
                <th><44</th>
                <th><54</th>
                <th><59</th>
                <th><69</th>
                <th>≥70</th>
                <th>Null</th>
                <th><48 Jam</th>
                <th>≥48 Jam</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totalData as $item)
                <tr>
                    <td>{{ $item['no'] }}</td>
                    <td>{{ $item['diagnosa'] }}</td>
                    <td class="text-center">{{ $item['laki'] }}</td>
                    <td class="text-center">{{ $item['perempuan'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_1'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_4'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_9'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_14'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_19'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_44'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_54'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_59'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_69'] }}</td>
                    <td class="text-center">{{ $item['umur_lt_70'] }}</td>
                    <td class="text-center">{{ $item['umur_null'] }}</td>
                    <td class="text-center">{{ $item['meninggal_kurang_48jam'] }}</td>
                    <td class="text-center">{{ $item['meninggal_lebih_48jam'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">Total</td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('laki') }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('perempuan') }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_1') > 0 ? collect($totalData)->sum('umur_lt_1') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_4') > 0 ? collect($totalData)->sum('umur_lt_4') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_9') > 0 ? collect($totalData)->sum('umur_lt_9') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_14') > 0 ? collect($totalData)->sum('umur_lt_14') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_19') > 0 ? collect($totalData)->sum('umur_lt_19') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_44') > 0 ? collect($totalData)->sum('umur_lt_44') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_54') > 0 ? collect($totalData)->sum('umur_lt_54') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_59') > 0 ? collect($totalData)->sum('umur_lt_59') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_69') > 0 ? collect($totalData)->sum('umur_lt_69') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_lt_70') > 0 ? collect($totalData)->sum('umur_lt_70') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('umur_null') > 0 ? collect($totalData)->sum('umur_null') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('meninggal_kurang_48jam') > 0 ? collect($totalData)->sum('meninggal_kurang_48jam') : '' }}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ collect($totalData)->sum('meninggal_lebih_48jam') > 0 ? collect($totalData)->sum('meninggal_lebih_48jam') : '' }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>