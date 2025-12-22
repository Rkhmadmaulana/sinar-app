<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RL 3.4 - Rekapitulasi Pengunjung</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 11px;
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
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .text-left {
            text-align: left !important;
            padding-left: 10px;
        }
        
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        
        .notes h4 {
            margin-top: 0;
            font-size: 12px;
        }
        
        .notes ol {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .notes li {
            margin-bottom: 5px;
            font-size: 10px;
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
            FORMULIR RL 3.4 - REKAPITULASI PENGUNJUNG
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th style="width: 50%;">Jenis Pengunjung</th>
                <th style="width: 40%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td class="text-left">Pengunjung Baru</td>
                <td>{{ number_format($pengunjungBaru, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td class="text-left">Pengunjung Lama</td>
                <td>{{ number_format($pengunjungLama, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">TOTAL</td>
                <td>{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>