<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RL 3.7 Neonatal, Bayi, dan Balita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 8px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .logo-section {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            text-align: center;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            border: 1px solid #ccc;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #666;
        }
        
        .hospital-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .hospital-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        
        .hospital-address {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .hospital-contact {
            font-size: 9px;
            margin: 2px 0;
        }
        
        .report-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
            text-decoration: underline;
        }
        
        .period {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            font-size: 9px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .text-left {
            text-align: left !important;
            padding-left: 3px;
        }
        
        .bg-light {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .category-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-row">
            <div class="logo-section">
                @if($hospitalInfo && $hospitalInfo->logo)
                    <img src="data:image/png;base64,{{ base64_encode($hospitalInfo->logo) }}" 
                        alt="Logo" class="logo" style="width: 60px; height: 60px; border: none;">
                @else
                    <div class="logo">LOGO</div>
                @endif
            </div>

            <div class="hospital-info" style="width: calc(100% - 160px);">
                <h1 class="hospital-name">
                    {{ $hospitalInfo->nama_instansi ?? 'RUMAH SAKIT UMUM DAERAH' }}
                </h1>
                <div class="hospital-address">
                    {{ $hospitalInfo->alamat_instansi ?? '' }}
                </div>
                <div class="hospital-contact">
                    @if($hospitalInfo && ($hospitalInfo->kontak || $hospitalInfo->email))
                        @if($hospitalInfo->kontak)
                            Telp: {{ $hospitalInfo->kontak }}
                        @endif
                        @if($hospitalInfo->kontak && $hospitalInfo->email) | @endif
                        @if($hospitalInfo->email)
                            Email: {{ $hospitalInfo->email }}
                        @endif
                    @endif
                </div>
            </div>

            <div class="logo-section"></div>
        </div>
        
        <div class="report-title">
            FORMULIR RL 3.7<br>
            REKAPITULASI KEGIATAN PELAYANAN NEONATAL, BAYI, DAN BALITA
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    <div class="no-break">
        <table>
            <thead>
                <tr>
                    <th rowspan="3" style="width: 4%;">No.</th>
                    <th rowspan="3" style="width: 18%;">Jenis Kegiatan</th>
                    <th colspan="10">Rujukan</th>
                    <th colspan="3">Non Rujukan</th>
                    <th rowspan="3" style="width: 4%;">Dirujuk</th>
                </tr>
                <tr>
                    <th colspan="7">Medis</th>
                    <th colspan="3">Non Medis</th>
                    <th rowspan="2">Hidup</th>
                    <th rowspan="2">Mati</th>
                    <th rowspan="2">Total</th>
                </tr>
                <tr>
                    <th style="width: 4%;">RS</th>
                    <th style="width: 4%;">Bidan</th>
                    <th style="width: 4%;">Puskes</th>
                    <th style="width: 4%;">Faskes</th>
                    <th style="width: 4%;">Hidup</th>
                    <th style="width: 4%;">Mati</th>
                    <th style="width: 5%;">Total</th>
                    <th style="width: 4%;">Hidup</th>
                    <th style="width: 4%;">Mati</th>
                    <th style="width: 5%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kategori as $item)
                    @if(isset($item['is_header']) && $item['is_header'])
                        <tr class="category-row">
                            <td colspan="17" class="text-left"><strong>{{ $item['kode'] }}. {{ $item['nama'] }}</strong></td>
                        </tr>
                    @else
                        <tr>
                            <td>{{ $item['kode'] }}</td>
                            <td class="text-left">{{ $item['nama'] }}</td>
                            <td>{{ $item['data']['rs'] }}</td>
                            <td>{{ $item['data']['bidan'] }}</td>
                            <td>{{ $item['data']['puskes'] }}</td>
                            <td>{{ $item['data']['faskes'] }}</td>
                            <td>{{ $item['data']['hidup_medis'] }}</td>
                            <td>{{ $item['data']['mati_medis'] }}</td>
                            <td class="bg-light">{{ $item['data']['total_medis'] }}</td>
                            <td>{{ $item['data']['hidup_non_medis'] }}</td>
                            <td>{{ $item['data']['mati_non_medis'] }}</td>
                            <td class="bg-light">{{ $item['data']['total_non_medis'] }}</td>
                            <td>{{ $item['data']['hidup_non_rujuk'] }}</td>
                            <td>{{ $item['data']['mati_non_rujuk'] }}</td>
                            <td class="bg-light">{{ $item['data']['total_non_rujuk'] }}</td>
                            <td>{{ $item['data']['dirujuk'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>