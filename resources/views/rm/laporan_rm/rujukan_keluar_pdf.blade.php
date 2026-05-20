<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rujukan Keluar</title>
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
        
        .search-info {
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
            font-size: 9px;
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
            LAPORAN JUMLAH RUJUKAN KELUAR
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    @if(!empty($keyword))
    <div class="search-info">
        Pencarian: "{{ $keyword }}"
    </div>
    @endif
    
    <div class="total-info">
        Total Data: {{ $totalPasien }} pasien
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No.</th>
                <th style="width: 8%;">Tgl. Rujuk</th>
                <th style="width: 6%;">Jam</th>
                <th style="width: 10%;">No. Rawat</th>
                <th style="width: 6%;">No. RM</th>
                <th style="width: 12%;">Nama Pasien</th>
                <th style="width: 12%;">Tempat Rujuk</th>
                <th style="width: 12%;">Diagnosa</th>
                <th style="width: 10%;">Dokter Perujuk</th>
                <th style="width: 8%;">Kategori</th>
                <th style="width: 6%;">Ambulance</th>
                <th style="width: 7%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ date('d-m-Y', strtotime($item->tgl_rujuk)) }}</td>
                <td>{{ $item->jam }}</td>
                <td class="text-left">{{ $item->no_rawat }}</td>
                <td>{{ $item->no_rkm_medis }}</td>
                <td class="text-left">{{ $item->nm_pasien }}</td>
                <td class="text-left">{{ $item->rujuk_ke }}</td>
                <td class="text-left">{{ $item->keterangan_diagnosa }}</td>
                <td class="text-left">{{ $item->nm_dokter }}</td>
                <td>{{ $item->kat_rujuk }}</td>
                <td>{{ $item->ambulance }}</td>
                <td class="text-left">{{ $item->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Statistics Section on New Page -->
    <div class="page-break">
        <div class="statistics-title">
            RINGKASAN STATISTIK RUJUKAN KELUAR
        </div>
        
        <div class="period">
            Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
            @if(!empty($keyword))
                <br>Pencarian: "{{ $keyword }}"
            @endif
        </div>
        
        <div style="text-align: center; margin: 20px 0; padding: 10px; background-color: #e9ecef; border: 1px solid #000;">
            <strong>Total Pasien Rujukan Keluar: {{ $totalPasien }}</strong>
        </div>
        
        <!-- Statistics by Date -->
        <div class="stat-header">1. JUMLAH PASIEN BERDASARKAN TANGGAL RUJUK</div>
        <table class="stat-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Tanggal</th>
                    <th style="width: 20%;">Jumlah Pasien</th>
                    <th style="width: 20%;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pasienPerTanggal as $tanggal => $jumlah)
                <tr>
                    <td>{{ date('d-m-Y', strtotime($tanggal)) }}</td>
                    <td>{{ $jumlah }}</td>
                    <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Statistics by Destination -->
        <div class="stat-header">2. JUMLAH PASIEN BERDASARKAN TEMPAT RUJUK</div>
        <table class="stat-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Tempat Rujuk</th>
                    <th style="width: 20%;">Jumlah Pasien</th>
                    <th style="width: 20%;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pasienPerTempatRujuk as $tempatRujuk => $jumlah)
                <tr>
                    <td class="text-left">{{ $tempatRujuk ?: 'Tidak Tercatat' }}</td>
                    <td>{{ $jumlah }}</td>
                    <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Statistics by Diagnosis -->
        <div class="stat-header">3. JUMLAH PASIEN BERDASARKAN DIAGNOSA</div>
        <table class="stat-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Diagnosa</th>
                    <th style="width: 20%;">Jumlah Pasien</th>
                    <th style="width: 20%;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pasienPerDiagnosa as $diagnosa => $jumlah)
                <tr>
                    <td class="text-left">{{ $diagnosa ?: 'Tidak Tercatat' }}</td>
                    <td>{{ $jumlah }}</td>
                    <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Statistics by Doctor -->
        <div class="stat-header">4. JUMLAH PASIEN BERDASARKAN DOKTER PERUJUK</div>
        <table class="stat-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Dokter Perujuk</th>
                    <th style="width: 20%;">Jumlah Pasien</th>
                    <th style="width: 20%;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pasienPerDokter as $dokter => $jumlah)
                <tr>
                    <td class="text-left">{{ $dokter ?: 'Tidak Tercatat' }}</td>
                    <td>{{ $jumlah }}</td>
                    <td>{{ $totalPasien > 0 ? number_format(($jumlah / $totalPasien) * 100, 2) : 0 }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="signature" style="display: none;">
        <p>{{ $hospitalInfo->kabupaten ?? 'Kotabaru' }}, {{ date('d F Y') }}</p>
        <p>Kepala Bagian Medical Record</p>
        <br><br><br>
        <p>_________________________</p>
        <p>NIP. </p>
    </div>
</body>
</html>