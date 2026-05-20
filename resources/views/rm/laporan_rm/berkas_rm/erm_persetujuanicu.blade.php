<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERM - Persetujuan ICU/PICU</title>
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

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }

        .info-box table {
            width: 100%;
            margin-bottom: 0;
        }

        .info-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .info-box table td:first-child {
            font-weight: 600;
            width: 250px;
            color: #495057;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-persetujuan {
            background-color: #d4edda;
            color: #155724;
        }

        .status-penolakan {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <h5 style="color:BLUE;">ERM Ranap - Persetujuan ICU/PICU</h5>
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

                            {{-- Persetujuan ICU/PICU --}}
                            <tr>
                                <td colspan="3">
                                    @if ($persetujuanicu)
                                        <div class="content-section">
                                            <h6 style="text-align: center; margin-bottom: 20px;">
                                                PERNYATAAN PERSETUJUAN/PENOLAKAN ICU/PICU
                                            </h6>
                                            <p style="text-align: center; margin-bottom: 20px;">
                                                Tanggal: {{ date('d-m-Y', strtotime($persetujuanicu->tanggal)) }}
                                            </p>

                                            {{-- Data Penanggung Jawab --}}
                                            <div class="info-box">
                                                <h6 style="margin-bottom: 15px;">Data Penanggung Jawab</h6>
                                                <table>
                                                    <tr>
                                                        <td>Nama</td>
                                                        <td>: {{ $persetujuanicu->nama_penanggung_jawab ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Umur</td>
                                                        <td>: {{ $persetujuanicu->umur_penanggung_jawab ?? '-' }} Tahun</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Jenis Kelamin</td>
                                                        <td>: {{ $persetujuanicu->jenkel_penanggung_jawab ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Alamat</td>
                                                        <td>: {{ $persetujuanicu->alamat_penanggung_jawab ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>No. Telepon</td>
                                                        <td>: {{ $persetujuanicu->no_tlp_penanggung_jawab ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Hubungan dengan Pasien</td>
                                                        <td>: {{ $persetujuanicu->hubungan_penerima_informasi ?? '-' }}</td>
                                                    </tr>
                                                </table>
                                            </div>

                                            {{-- Informasi Tindakan ICU --}}
                                            <div class="info-box">
                                                <h6 style="margin-bottom: 15px;">Informasi Tindakan ICU/PICU</h6>
                                                <table>
                                                    <tr>
                                                        <td>Dokter yang Melaksanakan</td>
                                                        <td>: {{ $persetujuanicu->pemberi_informasi ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Kriteria Masuk ICU/PICU</td>
                                                        <td>: {{ $persetujuanicu->kriteria_masuk_icu ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tata Cara ICU/PICU</td>
                                                        <td>: {{ $persetujuanicu->informasi_tatacara_icu ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Resiko</td>
                                                        <td>: {{ $persetujuanicu->informasi_resiko ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Komplikasi</td>
                                                        <td>: {{ $persetujuanicu->informasi_komplikasi ?? '-' }}</td>
                                                    </tr>
                                                </table>
                                            </div>

                                            {{-- Status Persetujuan --}}
                                            <div style="margin: 20px 0; text-align: center;">
                                                <h6>Status Persetujuan</h6>
                                                @if($persetujuanicu->stts_persetujuan == 'Persetujuan')
                                                    <span class="status-badge status-persetujuan">
                                                        ✓ PERSETUJUAN
                                                    </span>
                                                @elseif($persetujuanicu->stts_persetujuan == 'Penolakan')
                                                    <span class="status-badge status-penolakan">
                                                        ✗ PENOLAKAN
                                                    </span>
                                                @else
                                                    <span class="status-badge" style="background: #e0e0e0; color: #666;">
                                                        - Belum Ditentukan -
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Data Pasien pada Form --}}
                                            <div class="info-box">
                                                <h6 style="margin-bottom: 15px;">Data Pasien</h6>
                                                <table>
                                                    <tr>
                                                        <td>Nama Pasien</td>
                                                        <td>: {{ $persetujuanicu->nama_pasien ?? $row->nm_pasien }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nomor Rekam Medis</td>
                                                        <td>: {{ $row->no_rkm_medis ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Jenis Kelamin</td>
                                                        <td>: {{ $persetujuanicu->jenkel_pasien ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Umur</td>
                                                        <td>: {{ $persetujuanicu->umur_pasien ?? '-' }} Tahun</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Alamat</td>
                                                        <td>: {{ $persetujuanicu->alamat_pasien ?? '-' }}</td>
                                                    </tr>
                                                </table>
                                            </div>

                                            {{-- Pernyataan --}}
                                            <div style="margin: 20px 0;">
                                                <h6>Pernyataan</h6>
                                                <ol style="text-align: justify; margin-right:50px;">
                                                    <li>
                                                        Bahwa berdasarkan pada penjelasan dokter di ICU/PICU, tindakan apapun yang dilakukan selalu mengandung beberapa konsekuensi dan risiko. Risiko potensial yang terjadi termasuk perubahan tekanan darah, reaksi obat (alergi), henti jantung, kerusakan otak, kelumpuhan, kerusakan saraf bahkan kematian. Saya menyadari hal ini dan risiko serta komplikasi lain yang mungkin terjadi.
                                                    </li>
                                                    <li>
                                                        Bahwa dalam praktek ilmu kedokteran, bukan merupakan ilmu pengetahuan yang pasti (exact science) dan saya menyadari tidak seorang pun dapat menjanjikan atau menjamin sesuatu yang berhubungan dengan tindakan medis di ICU/PICU.
                                                    </li>
                                                    <li>
                                                        Bahwa obat-obatan yang digunakan sebelum prosedur di ICU/PICU dapat saja menimbulkan komplikasi. Oleh karena itu, sudah menjadi kewajiban dan tanggung jawab saya untuk memberikan informasi kepada dokter semua obat-obatan yang saya sendiri/istri/suami/anak/ayah/ibu gunakan, termasuk aspirin, kontrasepsi obat-obatan flu, narkotik, marijuana, kokain dan lain-lain.
                                                    </li>
                                                    <li>
                                                        Bahwa selama pasien dirawat di ICU/PICU, dapat dilakukan tindakan medis sesuai kondisi pasien berdasarkan pertimbangan medis termasuk intubasi, pemakaian ventilator, kateter vena sentral serta transfusi darah dan/atau produk-produk darah.
                                                    </li>
                                                    <li>
                                                        Bahwa dokter ICU/PICU yang bertugas dapat melakukan konsultasi dengan dokter spesialis lainnya jika dirasakan perlu.
                                                    </li>
                                                    <li>
                                                        Bahwa apabila ada staff ICU/PICU yang bertugas di ICU/PICU mengalami luka tusuk atau terpapar cairan tubuh pasien maka pasien dan keluarganya setuju untuk diperiksa darahnya untuk screening penyakit infeksi khusus.
                                                    </li>
                                                </ol>
                                                <p style="margin-top: 15px; margin-right:50px;">
                                                    Saya menyadari dan mengerti sepenuhnya bahwa pada tindakan medis, berbagai risiko dan komplikasi yang didiskusikan sebelumnya dapat timbul. Saya juga menyadari bahwa selama berlangsungnya perawatan tersebut memerlukan tindakan-tindakan yang berhubungan dengan perawatan yang sedang dilakukan, untuk itu saya menyetujui dilakukannya tindakan tersebut apabila diperlukan.
                                                </p>
                                                <p>
                                                    Selanjutnya saya menyadari bahwa tidak ada jaminan atau janji-janji yang diberikan kepada saya sehubungan dengan hasil, segala tindakan dan atau perawatan yang akan dilakukan tim ICU/PICU selama perawatan.
                                                </p>
                                                <p>
                                                    Demikian pernyataan ini saya buat dengan penuh kesadaran dan tanpa paksaan.
                                                </p>
                                            </div>

                                            {{-- Tanda Tangan --}}
                                            <div class="signature-section">
                                                {{-- TTD Yang Membuat Pernyataan --}}
                                                <div class="signature-box">
                                                    <h6>Yang Membuat Pernyataan</h6>
                                                    <p style="margin-bottom: 10px; font-size: 14px;">
                                                        {{ $persetujuanicu->nama_penanggung_jawab ?? '-' }}
                                                    </p>
                                                    @if (!empty($persetujuanicu->ttd_keluarga))
                                                        @php
                                                            $filename = basename($persetujuanicu->ttd_keluarga);
                                                            $imageUrl = url('/ttdicu/' . $filename) . '?v=' . time();
                                                        @endphp
                                                        <img src="{{ $imageUrl }}" alt="TTD Yang Membuat Pernyataan"
                                                            style="width: 250px; max-width: 100%; background: #fff; padding: 10px; border: 1px solid #ccc;"
                                                            onerror="this.style.display='none';">
                                                    @else
                                                        <div class="no-signature">Tanda tangan belum tersedia</div>
                                                    @endif
                                                </div>

                                                {{-- TTD Saksi Keluarga --}}
                                                <div class="signature-box">
                                                    <h6>Saksi Keluarga</h6>
                                                    @if (!empty($persetujuanicu->ttd_saksi))
                                                        @php
                                                            $filename = basename($persetujuanicu->ttd_saksi);
                                                            $imageUrl = url('/ttdicu/' . $filename) . '?v=' . time();
                                                        @endphp
                                                        <img src="{{ $imageUrl }}" alt="TTD Saksi Keluarga"
                                                            style="width: 250px; max-width: 100%; background: #fff; padding: 10px; border: 1px solid #ccc;"
                                                            onerror="this.style.display='none';">
                                                    @else
                                                        <div class="no-signature">Tanda tangan belum tersedia</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <em>Data Persetujuan ICU/PICU tidak tersedia.</em>
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