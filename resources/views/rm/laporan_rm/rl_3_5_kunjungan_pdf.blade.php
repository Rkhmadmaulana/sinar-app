{{-- FILE: resources/views/rm/laporan_rm/rl_3_5_kunjungan_pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RL 3.5 - Rekapitulasi Kunjungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 10px;
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
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        
        .hospital-address {
            font-size: 11px;
            margin: 2px 0;
        }
        
        .hospital-contact {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            text-decoration: underline;
        }
        
        .period {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .text-left {
            text-align: left !important;
            padding-left: 5px;
        }
        
        .total-row {
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
            FORMULIR RL 3.5 - REKAPITULASI KUNJUNGAN
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    <div class="no-break">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No</th>
                    <th rowspan="2" style="width: 35%;">Jenis Kegiatan</th>
                    <th colspan="2">Kunjungan Pasien<br>Dalam Kab/Kota</th>
                    <th colspan="2">Kunjungan Pasien<br>Luar Kab/Kota</th>
                    <th rowspan="2" style="width: 12%;">Total<br>Kunjungan</th>
                </tr>
                <tr>
                    <th style="width: 12%;">Laki-laki</th>
                    <th style="width: 12%;">Perempuan</th>
                    <th style="width: 12%;">Laki-laki</th>
                    <th style="width: 12%;">Perempuan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDalamL = 0;
                    $totalDalamP = 0;
                    $totalLuarL = 0;
                    $totalLuarP = 0;
                    $totalSemua = 0;
                @endphp
                @foreach($jenisKegiatan as $index => $jenis)
                    @php
                        $dalam_L = $jenis['data']['Dalam_L'] ?? 0;
                        $dalam_P = $jenis['data']['Dalam_P'] ?? 0;
                        $luar_L = $jenis['data']['Luar_L'] ?? 0;
                        $luar_P = $jenis['data']['Luar_P'] ?? 0;
                        $total = $dalam_L + $dalam_P + $luar_L + $luar_P;
                        
                        $totalDalamL += $dalam_L;
                        $totalDalamP += $dalam_P;
                        $totalLuarL += $luar_L;
                        $totalLuarP += $luar_P;
                        $totalSemua += $total;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">{{ $jenis['nama'] }}</td>
                        <td>{{ $dalam_L > 0 ? number_format($dalam_L, 0, ',', '.') : '-' }}</td>
                        <td>{{ $dalam_P > 0 ? number_format($dalam_P, 0, ',', '.') : '-' }}</td>
                        <td>{{ $luar_L > 0 ? number_format($luar_L, 0, ',', '.') : '-' }}</td>
                        <td>{{ $luar_P > 0 ? number_format($luar_P, 0, ',', '.') : '-' }}</td>
                        <td>{{ $total > 0 ? number_format($total, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
                
                <tr class="total-row">
                    <td colspan="2">TOTAL</td>
                    <td>{{ number_format($totalDalamL, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalDalamP, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalLuarL, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalLuarP, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalSemua, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>