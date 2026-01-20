<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERM - Tata Tertib ICU</title>
    <link rel="icon" href="{{ asset('public/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('public/img/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700"
        rel="stylesheet">
    <link href="{{ asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}"
        rel="stylesheet">
    <link href="{{ asset('public/vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('public/css/style.css') }}" rel="stylesheet">

    <style>
        table td,
        table th {
            padding: 5px;
        }

        .sub-table th {
            background-color: #FFFAF8;
            padding: 8px;
            width: 30%;
            vertical-align: top;
        }

        .sub-table td {
            padding: 8px;
            vertical-align: top;
        }

        .content-section {
            margin: 15px 0;
            text-align: justify;
            line-height: 1.6;
        }

        .content-section h6 {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .content-section ol {
            margin-left: 20px;
            margin-top: 10px;
        }

        .content-section ol li {
            margin-bottom: 8px;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #fafafa;
        }

        .signature-box h6 {
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .signature-image {
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            max-width: 250px;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-image img {
            max-width: 100%;
            max-height: 200px;
            height: auto;
        }

        .no-signature {
            color: #999;
            font-style: italic;
            padding: 30px 0;
        }

        .info-text {
            margin: 10px 0;
            line-height: 1.6;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <h5 style="color:BLUE;">ERM Ranap - Tata Tertib ICU</h5>
    <div class="table-responsive">
        <table id="erm" class="table table-bordered table-striped" style="width:100%;">
            <thead>
                <tr>
                    <th>Riwayat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table table-bordered" style="width:100%;">

                            {{-- Data Pasien --}}
                            <tr>
                                <td style="width: 20%;">No Rawat</td>
                                <td style="width: 1%;">:</td>
                                <td>{{ $row->no_rawat }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Registrasi</td>
                                <td>:</td>
                                <td>{{ $row->tgl_registrasi }} | {{ $row->jam_reg }}</td>
                            </tr>
                            <tr>
                                <td>Nama Pasien</td>
                                <td>:</td>
                                <td>{{ $row->nm_pasien }}</td>
                            </tr>

                            {{-- Tata Tertib ICU --}}
                            <tr>
                                <td>Tata Tertib ICU</td>
                                <td>:</td>
                                <td>
                                    @if ($tatatertib)
                                        {{-- Informasi Tata Tertib --}}
                                        <div class="content-section">
                                            <h6>TATA TERTIB WAKTU BERKUNJUNG KELUARGA DI RUANG ICU</h6>



                                            <h6>Pengertian:</h6>
                                            <p>Pengaturan waktu berkunjung keluarga untuk pasien ICU sehubungan dengan
                                                ruang intensif merupakan ruang steril yang penuh dengan tindakan dalam
                                                merawat pasien.</p>

                                            <h6>Pelaksanaan:</h6>
                                            <ol>
                                                <li>Jam besuk: <strong><u>16.00 – 21.00 WITA</u></strong></li>
                                                <li>Pengunjung harus melepas alas kaki sebelum masuk ruang ICU.</li>
                                                <li>Setiap pengunjung wajib memakai APD dan mencuci tangan sebelum dan
                                                    sesudah bertemu pasien.</li>
                                                <li>Pengunjung maksimal 2 (dua) orang.</li>
                                                <li>Di luar jam besuk pasien dilarang menerima tamu kecuali kondisi
                                                    khusus.</li>
                                                <li>Perawat ICU dapat memanggil keluarga di luar jam besuk jika
                                                    diperlukan.</li>
                                                <li>Pasien tertentu tidak diperkenankan menerima kunjungan.</li>
                                                <li>Bayi dan anak-anak dilarang masuk ruang ICU.</li>
                                                <li>Pengecualian pasien anak boleh ditunggu 1 keluarga jika tidak
                                                    kooperatif.</li>
                                                <li>Penunggu wajib menjaga kebersihan dan ketertiban ICU.</li>
                                            </ol>

                                            <p class="info-text">Keluarga telah membaca, memahami, dan menyetujui tata
                                                tertib ini.</p>
                                            <p class="info-text">Keluarga pasien atas nama
                                                <strong>{{ $row->nm_pasien }}</strong>.
                                            </p>

                                            {{-- Tanda Tangan --}}
                                            <div class="signature-section">

                                                {{-- TTD Keluarga --}}
                                                <div class="signature-box">
                                                    <h6>Yang Menyetujui (Keluarga)</h6>
                                                    <p style="margin-bottom: 15px;">
                                                        {{ $tatatertib->nama_keluarga1 ?: '____________________' }}</p>
                                                </div>

                                                {{-- TTD Perawat --}}
                                                <div class="signature-box">
                                                    <h6>Perawat ICU</h6>
                                                    <p style="margin-bottom: 15px;">
                                                        {{ $tatatertib->nm_perawat ?: '____________________' }}</p>
                                                </div>

                                            </div>


                                            {{-- TTD Kepala Ruangan ICU --}}
                                            <div class="signature-section" style="margin-top:20px;">
                                                <div class="signature-box">
                                                    <h6>Kepala Ruangan ICU</h6>

                                                    @if (!empty($tatatertib->ttd_kepala_ruangan))
                                                        @php
                                                            $imageUrl =
                                                                'http://192.168.100.31/webapps/tatatertibicu/' .
                                                                $tatatertib->ttd_kepala_ruangan;
                                                        @endphp

                                                        <img src="{{ $imageUrl }}" alt="TTD Kepala Ruangan ICU"
                                                            style="
                                                                width: 250px;
                                                                max-width: 100%;
                                                                background: #fff;
                                                                padding: 10px;
                                                                border: 1px solid #ccc;
                 "
                                                            onerror="this.style.display='none';">
                                                    @else
                                                        <div class="no-signature">Tanda tangan belum tersedia</div>
                                                    @endif
                                                </div>
                                            </div>


                                        </div>
                                    @else
                                        <em>Data Tata Tertib ICU tidak tersedia.</em>
                                    @endif
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="{{ asset('public/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('public/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
