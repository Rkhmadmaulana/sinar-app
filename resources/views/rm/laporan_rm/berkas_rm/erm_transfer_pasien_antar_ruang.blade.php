<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>ERM - Ranap</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('img/favicon.png') }}" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('vendor/simple-datatables/style.css') }}" rel="stylesheet"> -->

    <!-- JQuery DataTable Css -->
    <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

</head>
<!-- Style -->
<style media="screen">
    table td,
    table th {
        padding: 5px;
    }
</style>
<!-- End Style -->
<h5 style="color:BLUE;">ERM Ranap</h5>
<div class="table-responsive">
    <table id="erm" class="table table-bordered table-striped" style="width:100%;">
        <thead>
            <tr>
                <th style="width: 100%; text-align: left; vertical-align: top;">Riwayat</th>
            </tr>
        </thead>
        <tbody>
            <!-- Awal Terbackup no_rawat -->
            <tr>
                <td>
                    <table border="1px" style="width:100%;">
                        <!-- No Rawat -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">No Rawat</td>
                            <td style="width: 1%; vertical-align: top;">:</td>
                            <td style="width:79%;"><?php echo $row->no_rawat; ?></td>
                        </tr>
                        <!-- End No Rawat -->

                        <!-- Tanggal Registrasi -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">Tanggal Registrasi</td>
                            <td style="width: 1%; vertical-align: top;">:</td>
                            <td style="width:79%;"><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
                        </tr>
                        <!-- End Tanggal Registrasi -->

                        <!-- Poliklinik -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">Poliklinik</td>
                            <td style="width: 1%; vertical-align: top;">:</td>
                            <td style="width:79%;">Ranap</td>
                        </tr>
                        <!-- End Poliklinik -->

                        <!-- TRANSFER PASIEN ANTAR RUANG -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">TRANSFER PASIEN ANTAR RUANG
                            </td>
                            <td style="width: 1%; vertical-align: top;">:</td>
                            <td style="width:79%; padding: 0;">
                                <?php foreach ($transfer_pasien_antar_ruang as $transfer_pasien_antar_ruang) { ?>
                                <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc;">
                                    <thead>
                                        <tr style="background-color: #fffaf8;">
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Nama Pasien</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Tanggal Lahir</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Tanggal Masuk</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Tanggal Pindah</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Indikasi Pindah</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Keterangan Indikasi Pindah</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Asal Ruang Rawat / Poliklinik</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Ruang Rawat Selanjutnya</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Metode Pemindahan</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Diagnosa Utama</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Diagnosa Sekunder</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Prosedur Yang Sudah Dilakukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->nm_pasien; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->tgl_lahir; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->tanggal_masuk; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->tanggal_pindah; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->indikasi_pindah_ruang; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->keterangan_indikasi_pindah_ruang; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->asal_ruang; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->ruang_selanjutnya; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->metode_pemindahan_pasien; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->diagnosa_utama; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->diagnosa_sekunder; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->prosedur_yang_sudah_dilakukan; ?></td>
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr style="background-color: #fffaf8;">
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Obat Yang Telah Diberikan</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Pemeriksaan Penunjang Yang Sudah Dilakukan</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Peralatan Yang Menyertai</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Keterangan Peralatan Menyertai</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Menyetujui Pemindahan</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Nama Keluarga/Penanggung Jawab</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Hubungan</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Keadaan Umum SbT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                TD SbT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Nadi SbT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                RR SbT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Suhu SbT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->obat_yang_telah_diberikan; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->pemeriksaan_penunjang_yang_dilakukan; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->peralatan_yang_menyertai; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->keterangan_peralatan_yang_menyertai; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->pasien_keluarga_menyetujui; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->nama_menyetujui; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->hubungan_menyetujui; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->keadaan_umum_sebelum_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->td_sebelum_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->nadi_sebelum_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->rr_sebelum_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->suhu_sebelum_transfer; ?></td>
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr style="background-color: #fffaf8;">
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Keluhan Utama Sebelum Transfer</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Keadaan Umum StT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                TD StT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Nadi StT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                RR StT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Suhu StT</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Keluhan Utama Setelah Transfer</th>

                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Petugas Yang Menyerahkan</th>

                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">
                                                Petugas Yang Menerima</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->keluhan_utama_sebelum_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->keadaan_umum_sesudah_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->td_sesudah_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->nadi_sesudah_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->rr_sesudah_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->suhu_sesudah_transfer; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->keluhan_utama_sesudah_transfer; ?></td>

                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->nama_petugas_menerima; ?></td>

                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $transfer_pasien_antar_ruang->nama_petugas_menyerahkan; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php } ?>
                            </td>
                        </tr>
                        <!-- End DPJP -->
                    </table>
                </td>
            </tr>
            <!-- Akhir Terbackup no_rawat -->
        </tbody>
    </table>
</div>
