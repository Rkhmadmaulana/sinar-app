<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>ERM - Ranap</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('public/img/favicon.png') }}" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('public/vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('public/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('public/vendor/simple-datatables/style.css') }}" rel="stylesheet"> -->

    <!-- JQuery DataTable Css -->
    <link href="{{ asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}"
        rel="stylesheet">
    <link href="{{ asset('public/vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('public/css/style.css') }}" rel="stylesheet">

    <!-- <link rel="stylesheet" href="{{ asset('public/css/style.css') }}"> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

    <style>
        table td,
        table th {
            padding: 5px;
        }

        .sub-table th {
            background-color: #fffaf8;
            padding: 2px;
            width: 30%;
            border: 1px solid #ccc;
            text-align: left;
        }

        .sub-table td {
            padding: 2px 8px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <h5 style="color: blue;">ERM Ranap - Transfer Pasien Antar Ruang</h5>
    <div class="table-responsive">
        <table id="erm" class="table table-bordered table-striped" style="width:100%;">
            <thead>
                <tr>
                    <th style="width: 100%; text-align: left; vertical-align: top;">Riwayat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table table-bordered sub-table" style="width:100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 20%;">No Rawat</td>
                                <td style="width: 1%;">:</td>
                                <td><?php echo $row->no_rawat; ?></td>
                            </tr>
                            <tr>
                                <td>Nama Pasien</td>
                                <td>:</td>
                                <td><?= $row->nm_pasien; ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal Registrasi</td>
                                <td>:</td>
                                <td><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
                            </tr>
                            <tr>
                                <td>Poliklinik</td>
                                <td>:</td>
                                <td>Ranap</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">Transfer Pasien Antar Ruang</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="padding: 0;">
                                    <?php foreach ($transfer_pasien_antar_ruang as $transfer_pasien_antar_ruang) { ?>
                                    <table class="table sub-table"
                                        style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Nama Pasien</th>
                                                <th style="text-align: center;">Tanggal Lahir</th>
                                                <th style="text-align: center;">Tanggal Masuk</th>
                                                <th style="text-align: center;">Tanggal Pindah</th>
                                                <th style="text-align: center;">Indikasi Pindah</th>
                                                <th style="text-align: center;">Keterangan Indikasi Pindah</th>
                                                <th style="text-align: center;">Asal Ruang Rawat / Poliklinik</th>
                                                <th style="text-align: center;">Ruang Rawat Selanjutnya</th>
                                                <th style="text-align: center;">Metode Pemindahan</th>
                                                <th style="text-align: center;">Diagnosa Utama</th>
                                                <th style="text-align: center;">Diagnosa Sekunder</th>
                                                <th style="text-align: center;">Prosedur Yang Sudah Dilakukan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $transfer_pasien_antar_ruang->nm_pasien; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->tgl_lahir; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->tanggal_masuk; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->tanggal_pindah; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->indikasi_pindah_ruang; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->keterangan_indikasi_pindah_ruang; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->asal_ruang; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->ruang_selanjutnya; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->metode_pemindahan_pasien; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->diagnosa_utama; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->diagnosa_sekunder; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->prosedur_yang_sudah_dilakukan; ?></td>
                                            </tr>
                                        </tbody>
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Obat Yang Telah Diberikan</th>
                                                <th style="text-align: center;">Pemeriksaan Penunjang Yang Sudah
                                                    Dilakukan</th>
                                                <th style="text-align: center;">Peralatan Yang Menyertai</th>
                                                <th style="text-align: center;">Keterangan Peralatan Menyertai</th>
                                                <th style="text-align: center;">Menyetujui Pemindahan</th>
                                                <th style="text-align: center;">Nama Keluarga/Penanggung Jawab</th>
                                                <th style="text-align: center;">Hubungan</th>
                                                <th style="text-align: center;">Keadaan Umum SbT</th>
                                                <th style="text-align: center;">TD SbT</th>
                                                <th style="text-align: center;">Nadi SbT</th>
                                                <th style="text-align: center;">RR SbT</th>
                                                <th style="text-align: center;">Suhu SbT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $transfer_pasien_antar_ruang->obat_yang_telah_diberikan; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->pemeriksaan_penunjang_yang_dilakukan; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->peralatan_yang_menyertai; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->keterangan_peralatan_yang_menyertai; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->pasien_keluarga_menyetujui; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->nama_menyetujui; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->hubungan_menyetujui; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->keadaan_umum_sebelum_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->td_sebelum_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->nadi_sebelum_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->rr_sebelum_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->suhu_sebelum_transfer; ?></td>
                                            </tr>
                                        </tbody>
                                        <thead>
                                            <tr>
                                                <th colspan="2" style="text-align: center;">Keluhan Utama Sebelum
                                                    Transfer</th>
                                                <th colspan="2" style="text-align: center;">Keadaan Umum StT</th>
                                                <th style="text-align: center;">TD StT</th>
                                                <th style="text-align: center;">Nadi StT</th>
                                                <th style="text-align: center;">RR StT</th>
                                                <th style="text-align: center;">Suhu StT</th>
                                                <th style="text-align: center;">Keluhan Utama Setelah Transfer</th>
                                                <th style="text-align: center;">Petugas Yang Menyerahkan</th>
                                                <th colspan="2" style="text-align: center;">Petugas Yang Menerima
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="2"><?php echo $transfer_pasien_antar_ruang->keluhan_utama_sebelum_transfer; ?></td>
                                                <td colspan="2"><?php echo $transfer_pasien_antar_ruang->keadaan_umum_sesudah_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->td_sesudah_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->nadi_sesudah_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->rr_sesudah_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->suhu_sesudah_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->keluhan_utama_sesudah_transfer; ?></td>
                                                <td><?php echo $transfer_pasien_antar_ruang->nama_petugas_menyerahkan; ?></td>
                                                <td colspan="2"><?php echo $transfer_pasien_antar_ruang->nama_petugas_menerima; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php } ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
