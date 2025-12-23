<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RL 3.19 Rekapitulasi Cara Bayar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 9px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .logo-section {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
            text-align: center;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            border: 1px solid #ccc;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
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
            font-size: 9px;
            margin: 2px 0;
        }
        
        .hospital-contact {
            font-size: 8px;
            margin: 2px 0;
        }
        
        .report-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }
        
        .period {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            font-size: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: left;
            font-size: 8px;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #ffff99;
            font-weight: bold;
        }
        
        .subtotal-row {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        
        .notes {
            margin-top: 15px;
            font-size: 7px;
            page-break-inside: avoid;
        }
        
        .notes h6 {
            font-size: 9px;
            margin-bottom: 8px;
        }
        
        .notes ol {
            margin-left: 15px;
            padding-left: 0;
        }
        
        .notes li {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-row">
            <div class="logo-section">
                @if($hospitalInfo && $hospitalInfo->logo)
                    <img src="data:image/png;base64,{{ base64_encode($hospitalInfo->logo) }}" 
                         alt="Logo" style="width: 50px; height: 50px;">
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
            FORMULIR RL 3.19<br>
            REKAPITULASI CARA BAYAR
        </div>
    </div>
    
    <div class="period">
        Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 24%;">Cara Pembayaran</th>
                <th colspan="2" style="width: 24%;">Pasien Rawat Inap</th>
                <th rowspan="2" style="width: 12%;">Jumlah Pasien<br>Rawat Jalan</th>
                <th colspan="3" style="width: 36%;">Jumlah Pasien Rawat Jalan</th>
            </tr>
            <tr>
                <th style="width: 12%;">Jumlah Pasien<br>Keluar</th>
                <th style="width: 12%;">Jumlah Lama<br>Dirawat</th>
                <th style="width: 12%;">Laboratorium</th>
                <th style="width: 12%;">Radiologi</th>
                <th style="width: 12%;">Lain-lain</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $no => $item)
                @if($no == 99)
                    <tr class="total-row">
                        <td class="text-center">{{ $item['no'] }}</td>
                        <td><strong>{{ $item['nama'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['ranap_pasien'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['ranap_lama'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_total'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_lab'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_rad'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_lain'] }}</strong></td>
                    </tr>
                @elseif($no == 2 || $no == 4)
                    <tr class="subtotal-row">
                        <td class="text-center">{{ $item['no'] }}</td>
                        <td><strong>{{ $item['nama'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['ranap_pasien'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['ranap_lama'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_total'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_lab'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_rad'] }}</strong></td>
                        <td class="text-center"><strong>{{ $item['rajal_lain'] }}</strong></td>
                    </tr>
                @else
                    <tr>
                        <td class="text-center">{{ $item['no'] }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td class="text-center">{{ $item['ranap_pasien'] }}</td>
                        <td class="text-center">{{ $item['ranap_lama'] }}</td>
                        <td class="text-center">{{ $item['rajal_total'] }}</td>
                        <td class="text-center">{{ $item['rajal_lab'] }}</td>
                        <td class="text-center">{{ $item['rajal_rad'] }}</td>
                        <td class="text-center">{{ $item['rajal_lain'] }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!--
    <div class="notes">
        <h6>Catatan Pengisian Formulir RL 3.19:</h6>
        <ol>
            <li>Formulir rekapitulasi Cara Bayar dilaporkan tahunan dengan data yang bersumber dari unit pembayaran.</li>
            <li>Pasien Rawat Inap terdiri dari:
                <ul style="list-style-type: lower-alpha; margin-left: 15px;">
                    <li>Jumlah Pasien Keluar diisi dengan jumlah pasien rawat inap yang sudah keluar, baik hidup maupun mati, selama periode satu tahun, sesuai cara pembayarannya.</li>
                    <li>Jumlah Lama Dirawat diisi dengan jumlah lama dirawat seluruh pasien rawat inap yang sudah keluar, baik hidup maupun mati, selama periode satu tahun, sesuai cara pembayarannya.</li>
                </ul>
            </li>
            <li>Jumlah Pasien Rawat Jalan merupakan penjumlahan jumlah pasien yang keluar dari laboratorium, radiologi, dan lainnya selama periode satu tahun, sesuai cara pembayarannya. Pasien poliklinik dan pasien rawat darurat yang tidak lanjut ke rawat inap masuk dalam kolom lain-lain.</li>
            <li>Membayar Sendiri diisi dengan jumlah pasien yang membayar sendiri baik dengan tunai maupun non tunai tanpa adanya peran serta dari pihak ke-3.</li>
            <li>Asuransi terdiri dari Asuransi JKN (BPJS Kesehatan), Asuransi Pemerintah Daerah (Jamkesda), Asuransi Pemerintah Lainnya, dan Asuransi Swasta.</li>
            <li>Keringanan (Cost Sharing) diisi dengan jumlah pasien yang membayar dengan mendapatkan keringanan dari Rumah Sakit.</li>
            <li>Gratis berarti pasien dibebaskan dari pembayaran dan rumah sakit tidak menagihkan atau tidak menambah biaya lain secara apapun ke pihak manapun.</li>
        </ol>
    </div>
    -->
</body>
</html>