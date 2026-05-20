<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Rujukan</title>
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
            padding: 4px 2px;
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
        
        .bg-light {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature {
            margin-top: 50px;
            text-align: right;
            margin-right: 50px;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-row">
            {{-- Kiri: Logo --}}
            <div class="logo-section">
                @if($hospitalInfo && $hospitalInfo->logo)
                    <img src="data:image/png;base64,{{ base64_encode($hospitalInfo->logo) }}" 
                        alt="Logo" class="logo" style="width: 60px; height: 60px; border: none;">
                @else
                    <div class="logo">LOGO</div>
                @endif
            </div>

            {{-- Tengah: Info RS --}}
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

            {{-- Kanan: Spacer kosong --}}
            <div class="logo-section">
                <!-- Kosong tapi ukurannya sama dengan logo kiri -->
            </div>
        </div>
        
        <div class="report-title">
            LAPORAN REKAPITULASI KEGIATAN PELAYANAN RUJUKAN
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    <div class="no-break">
        <table>
            <thead>
                <tr>
                    <th rowspan="3" style="width: 5%;">No.</th>
                    <th rowspan="3" style="width: 15%;">Jenis Spesialisasi</th>
                    <th colspan="8">Rujukan Masuk</th>
                    <th colspan="4">Dirujuk Keluar</th>
                </tr>
                <tr>
                    <th colspan="4">Diterima Dari</th>
                    <th colspan="4">Dikembalikan Ke</th>
                    <th rowspan="2">Pasien Rujukan</th>
                    <th rowspan="2">Pasien Datang Sendiri</th>
                    <th rowspan="2">Total Dirujuk Keluar</th>
                    <th rowspan="2">Diterima Kembali</th>
                </tr>
                <tr>
                    <th style="width: 8%;">Puskesmas</th>
                    <th style="width: 8%;">RS Lain</th>
                    <th style="width: 8%;">Faskes Lain</th>
                    <th style="width: 8%;">Total Rujukan Masuk</th>
                    <th style="width: 8%;">Puskesmas</th>
                    <th style="width: 8%;">RS Asal</th>
                    <th style="width: 8%;">Faskes Asal</th>
                    <th style="width: 8%;">Total Rujukan Masuk Dikembalikan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($spesialisasi as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $item['nama'] }}</td>
                    <!-- Diterima Dari -->
                    <td>{{ $item['data']['diterima_dari']['puskesmas']['value'] ?? 0 }}</td>
                    <td>{{ $item['data']['diterima_dari']['rs_lain']['value'] ?? 0 }}</td>
                    <td>{{ $item['data']['diterima_dari']['faskes_lain']['value'] ?? 0 }}</td>
                    <td class="bg-light">{{ $item['data']['diterima_dari']['all']['value'] ?? 0 }}</td>
                    <!-- Dikembalikan Ke -->
                    <td>{{ $item['data']['dikembalikan_ke']['puskesmas']['value'] ?? 0 }}</td>
                    <td>{{ $item['data']['dikembalikan_ke']['rs_asal']['value'] ?? 0 }}</td>
                    <td>{{ $item['data']['dikembalikan_ke']['faskes_asal']['value'] ?? 0 }}</td>
                    <td class="bg-light">{{ ($item['data']['dikembalikan_ke']['puskesmas']['value'] ?? 0) + ($item['data']['dikembalikan_ke']['rs_asal']['value'] ?? 0) + ($item['data']['dikembalikan_ke']['faskes_asal']['value'] ?? 0) }}</td>
                    <!-- Dirujuk Keluar -->
                    <td>{{ $item['data']['dirujuk_keluar']['all']['value'] ?? 0 }}</td>
                    <td>-</td>
                    <td class="bg-light">{{ $item['data']['dirujuk_keluar']['all']['value'] ?? 0 }}</td>
                    <td>-</td>
                </tr>
                @endforeach
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="2">TOTAL</td>
                    <!-- Total Diterima Dari -->
                    <td>{{ $totalData['diterima_dari']['puskesmas']['value'] ?? 0 }}</td>
                    <td>{{ $totalData['diterima_dari']['rs_lain']['value'] ?? 0 }}</td>
                    <td>{{ $totalData['diterima_dari']['faskes_lain']['value'] ?? 0 }}</td>
                    <td>{{ $totalData['diterima_dari']['all']['value'] ?? 0 }}</td>
                    <!-- Total Dikembalikan Ke -->
                    <td>{{ $totalData['dikembalikan_ke']['puskesmas']['value'] ?? 0 }}</td>
                    <td>{{ $totalData['dikembalikan_ke']['rs_asal']['value'] ?? 0 }}</td>
                    <td>{{ $totalData['dikembalikan_ke']['faskes_asal']['value'] ?? 0 }}</td>
                    <td>{{ ($totalData['dikembalikan_ke']['puskesmas']['value'] ?? 0) + ($totalData['dikembalikan_ke']['rs_asal']['value'] ?? 0) + ($totalData['dikembalikan_ke']['faskes_asal']['value'] ?? 0) }}</td>
                    <!-- Total Dirujuk Keluar -->
                    <td>{{ $totalData['dirujuk_keluar']['all']['value'] ?? 0 }}</td>
                    <td>-</td>
                    <td>{{ $totalData['dirujuk_keluar']['all']['value'] ?? 0 }}</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="signature" style="display:none;">
        <p>{{ $hospitalInfo->kabupaten ?? 'Kotabaru' }}, {{ date('d F Y') }}</p>
        <p>Kepala Bagian Medical Record</p>
        <br><br><br>
        <p>_________________________</p>
        <p>NIP. </p>
    </div>
</body>
</html>