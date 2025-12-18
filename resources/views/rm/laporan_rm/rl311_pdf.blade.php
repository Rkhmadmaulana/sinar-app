<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RL 3.11 Kegiatan Pelayanan Gigi dan Mulut</title>
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
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            background-color: #ffff99;
            font-weight: bold;
        }
        
        .notes {
            margin-top: 20px;
            font-size: 9px;
            page-break-inside: avoid;
        }
        
        .notes h6 {
            font-size: 11px;
            margin-bottom: 10px;
        }
        
        .notes ol {
            margin-left: 20px;
            padding-left: 0;
        }
        
        .notes li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-row">
            <div class="logo-section">
                @if($hospitalInfo && $hospitalInfo->logo)
                    <img src="data:image/png;base64,{{ base64_encode($hospitalInfo->logo) }}" 
                         alt="Logo" style="width: 60px; height: 60px;">
                @else
                    <div class="logo">LOGO</div>
                @endif
            </div>

            <div class="hospital-info">
                <h1 class="hospital-name">
                    {{ $hospitalInfo->nama_instansi ?? 'RUMAH SAKIT' }}
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
            FORMULIR RL 3.11<br>
            REKAPITULASI KEGIATAN PELAYANAN GIGI DAN MULUT
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">No.</th>
                <th style="width: 70%;">Jenis Kegiatan</th>
                <th style="width: 20%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $no => $item)
                @if($no == 99)
                    <tr class="total-row">
                        <td class="text-center">{{ $no }}</td>
                        <td><strong>{{ $item['nama'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['jumlah'] }}</strong></td>
                    </tr>
                @else
                    <tr>
                        <td class="text-center">{{ $no }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td class="text-center">{{ $item['jumlah'] }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!--
    <div class="notes">
        <h6>Catatan Pengisian Formulir RL 3.11:</h6>
        <ol>
            <li>Data yang dilaporkan merupakan jumlah kegiatan pelayanan sesuai dengan jenis kegiatan pelayanan yang diberikan kepada pasien pada saat kunjungan di Poliklinik Gigi dan Mulut di RS.</li>
            <li>Tumpatan merupakan semua tumpatan yang bersifat permanen baik amalgam maupun sintetik.</li>
            <li>Pengobatan pulpa merupakan semua tindakan yang dimaksudkan untuk pengobatan pulpa secara langsung, termasuk pemberian eugenol, pulp capping, prosedur dalam mummifikasi, dan exterpasi (semua tindakan dalam endodontic).</li>
            <li>Pencabutan merupakan semua tindakan pencabutan gigi secara biasa, bukan tindakan yang digolongkan tindakan operatif.</li>
        </ol>
    </div>
    -->
</body>
</html>