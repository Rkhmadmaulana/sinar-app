<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pasien Meninggal</title>
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
        
        .bangsal-info {
            text-align: center;
            margin: 5px 0 15px 0;
            font-style: italic;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .text-left {
            text-align: left !important;
            padding-left: 3px;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .total-info {
            text-align: right;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .statistics-section {
            margin-top: 20px;
        }
        
        .statistics-title {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            text-align: center;
            text-decoration: underline;
        }
        
        .stat-table {
            margin-bottom: 20px;
        }
        
        .stat-table th {
            background-color: #e9ecef;
            font-size: 8px;
        }
        
        .stat-table td {
            font-size: 8px;
        }
        
        .stat-header {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 10px;
            padding: 5px;
            margin: 15px 0 5px 0;
        }
        
        .signature {
            margin-top: 30px;
            text-align: right;
            margin-right: 50px;
        }
        
        .summary-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            background-color: #f9f9f9;
        }
        
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
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
            LAPORAN PASIEN MENINGGAL
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    @if(!empty($bangsalName))
    <div class="bangsal-info">
        Bangsal: {{ $bangsalName }}
    </div>
    @endif
    
    <div class="summary-box">
        <div class="summary-item"><strong>Total Pasien Meninggal: {{ $totalPasien }}</strong></div>
        <div class="summary-item">Laki-laki: {{ $totalLaki }}</div>
        <div class="summary-item">Perempuan: {{ $totalPerempuan }}</div>
        <div class="summary-item">Meninggal < 48 Jam: {{ $totalMeninggalKurang48 }}</div>
        <div class="summary-item">Meninggal ≥ 48 Jam: {{ $totalMeninggalLebih48 }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No.</th>
                <th style="width: 8%;">Tgl Masuk</th>
                <th style="width: 6%;">No. RM</th>
                <th style="width: 12%;">Nama Pasien</th>
                <th style="width: 5%;">Kode Diagnosa</th>
                <th style="width: 4%;">JK</th>
                <th style="width: 8%;">Tgl Lahir</th>
                <th style="width: 8%;">Tgl Wafat</th>
                <th style="width: 6%;">Umur</th>
                <th style="width: 10%;">Penanggung Jawab</th>
                <th style="width: 10%;">Bangsal</th>
                <th style="width: 6%;">< 48 Jam</th>
                <th style="width: 6%;">≥ 48 Jam</th>
                <th style="width: 13%;">Diagnosa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->no }}</td>
                <td>{{ date('d-m-Y', strtotime($item->tgl_masuk)) }}</td>
                <td>{{ $item->no_rkm_medis }}</td>
                <td class="text-left">{{ $item->nm_pasien }}</td>
                <td>{{ $item->kd_penyakit ?? '-' }}</td>
                <td>{{ $item->jk }}</td>
                <td>{{ $item->tgl_lahir ? date('d-m-Y', strtotime($item->tgl_lahir)) : '-' }}</td>
                <td>{{ date('d-m-Y', strtotime($item->tgl_keluar)) }}</td>
                <td>{{ $item->umur !== null ? $item->umur : '-' }}</td>
                <td class="text-left">{{ $item->png_jawab }}</td>
                <td class="text-left">{{ $item->nm_bangsal }}</td>
                <td>{{ $item->meninggal_kurang_48jam }}</td>
                <td>{{ $item->meninggal_lebih_48jam }}</td>
                <td class="text-left">{{ $item->nm_penyakit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Ringkasan Diagnosa Section on New Page -->
    <div class="page-break">
        <div class="statistics-title">
            RINGKASAN DIAGNOSA PASIEN MENINGGAL
        </div>
        
        <div class="period">
            Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
            @if(!empty($bangsalName))
                <br>Bangsal: {{ $bangsalName }}
            @endif
        </div>
        
        <div style="text-align: center; margin: 20px 0; padding: 10px; background-color: #e9ecef; border: 1px solid #000;">
            <strong>Total Diagnosa: {{ count($totalData) }}</strong>
        </div>
        
        <!-- Ringkasan Diagnosa Table -->
        <table class="stat-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">No</th>
                    <th rowspan="2" style="width: 20%;">Diagnosa</th>
                    <th colspan="2" style="width: 8%;">Jenis Kelamin</th>
                    <th colspan="11" style="width: 44%;">Kelompok Umur</th>
                    <th colspan="2" style="width: 8%;">Meninggal</th>
                </tr>
                <tr>
                    <th style="width: 4%;">L</th>
                    <th style="width: 4%;">P</th>
                    <th style="width: 4%;"><1</th>
                    <th style="width: 4%;"><4</th>
                    <th style="width: 4%;"><9</th>
                    <th style="width: 4%;"><14</th>
                    <th style="width: 4%;"><19</th>
                    <th style="width: 4%;"><44</th>
                    <th style="width: 4%;"><54</th>
                    <th style="width: 4%;"><59</th>
                    <th style="width: 4%;"><69</th>
                    <th style="width: 4%;">≥70</th>
                    <th style="width: 4%;">Null</th>
                    <th style="width: 4%;"><48J</th>
                    <th style="width: 4%;">≥48J</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totalData as $item)
                <tr>
                    <td>{{ $item['no'] }}</td>
                    <td class="text-left">{{ $item['diagnosa'] }}</td>
                    <td>{{ $item['laki'] > 0 ? $item['laki'] : '' }}</td>
                    <td>{{ $item['perempuan'] > 0 ? $item['perempuan'] : '' }}</td>
                    <td>{{ $item['umur_lt_1'] > 0 ? $item['umur_lt_1'] : '' }}</td>
                    <td>{{ $item['umur_lt_4'] > 0 ? $item['umur_lt_4'] : '' }}</td>
                    <td>{{ $item['umur_lt_9'] > 0 ? $item['umur_lt_9'] : '' }}</td>
                    <td>{{ $item['umur_lt_14'] > 0 ? $item['umur_lt_14'] : '' }}</td>
                    <td>{{ $item['umur_lt_19'] > 0 ? $item['umur_lt_19'] : '' }}</td>
                    <td>{{ $item['umur_lt_44'] > 0 ? $item['umur_lt_44'] : '' }}</td>
                    <td>{{ $item['umur_lt_54'] > 0 ? $item['umur_lt_54'] : '' }}</td>
                    <td>{{ $item['umur_lt_59'] > 0 ? $item['umur_lt_59'] : '' }}</td>
                    <td>{{ $item['umur_lt_69'] > 0 ? $item['umur_lt_69'] : '' }}</td>
                    <td>{{ $item['umur_lt_70'] > 0 ? $item['umur_lt_70'] : '' }}</td>
                    <td>{{ $item['umur_null'] > 0 ? $item['umur_null'] : '' }}</td>
                    <td>{{ $item['meninggal_kurang_48jam'] > 0 ? $item['meninggal_kurang_48jam'] : '' }}</td>
                    <td>{{ $item['meninggal_lebih_48jam'] > 0 ? $item['meninggal_lebih_48jam'] : '' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="background-color: #f0f0f0; font-weight: bold;">
                <tr>
                    <td colspan="2" class="text-center">Total</td>
                    <td>{{ collect($totalData)->sum('laki') > 0 ? collect($totalData)->sum('laki') : '' }}</td>
                    <td>{{ collect($totalData)->sum('perempuan') > 0 ? collect($totalData)->sum('perempuan') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_1') > 0 ? collect($totalData)->sum('umur_lt_1') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_4') > 0 ? collect($totalData)->sum('umur_lt_4') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_9') > 0 ? collect($totalData)->sum('umur_lt_9') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_14') > 0 ? collect($totalData)->sum('umur_lt_14') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_19') > 0 ? collect($totalData)->sum('umur_lt_19') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_44') > 0 ? collect($totalData)->sum('umur_lt_44') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_54') > 0 ? collect($totalData)->sum('umur_lt_54') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_59') > 0 ? collect($totalData)->sum('umur_lt_59') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_69') > 0 ? collect($totalData)->sum('umur_lt_69') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_lt_70') > 0 ? collect($totalData)->sum('umur_lt_70') : '' }}</td>
                    <td>{{ collect($totalData)->sum('umur_null') > 0 ? collect($totalData)->sum('umur_null') : '' }}</td>
                    <td>{{ collect($totalData)->sum('meninggal_kurang_48jam') > 0 ? collect($totalData)->sum('meninggal_kurang_48jam') : '' }}</td>
                    <td>{{ collect($totalData)->sum('meninggal_lebih_48jam') > 0 ? collect($totalData)->sum('meninggal_lebih_48jam') : '' }}</td>
                </tr>
            </tfoot>
        </table>
        
        @if(!empty($pasienPerBangsal) && count($pasienPerBangsal) > 1)
        <!-- Statistics by Bangsal -->
        <div class="stat-header">JUMLAH PASIEN BERDASARKAN BANGSAL</div>
        <table class="stat-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Bangsal</th>
                    <th style="width: 25%;">Jumlah Pasien</th>
                    <th style="width: 25%;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pasienPerBangsal as $bangsal => $jumlah)
                <tr>
                    <td class="text-left">{{ $bangsal ?: 'Tidak Tercatat' }}</td>
                    <td>{{ $jumlah }}</td>
                    <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 1) : '0' }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Signature Section -->
    <div class="signature" style="display:none;">
        <p>{{ $hospitalInfo->kota ?? 'Kota' }}, {{ date('d-m-Y') }}</p>
        <p>Mengetahui</p>
        <br><br><br>
        <p style="font-weight: bold;">[Nama Penanggung Jawab]</p>
        <p>Jabatan</p>
    </div>
    
</div>

</body>
</html>