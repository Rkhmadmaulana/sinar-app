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
                        <!-- No Rawat  -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">No Rawat</td>
                            <td style="width: 1%;  vertical-align: top;">:</td>
                            <td style="width:79%;"><?php echo $row->no_rawat; ?></td>
                        </tr>
                        <!-- End No Rawat  -->

                        <!-- Tanggal Regist  -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">Tanggal Registrasi</td>
                            <td style="width: 1%;  vertical-align: top;">:</td>
                            <td style="width:79%;"><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
                        </tr>
                        <!-- End Tanggal Regist  -->

                        <!-- Poliklinik  -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">Poliklinik</td>
                            <td style="width: 1%;  vertical-align: top;">:</td>
                            <td style="width:79%;">
                                Ranap
                            </td>
                        </tr>
                        <!-- End Poliklinik  -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">Catatan
                                EDUKASI PASIEN & KELUARGA </td>
                            <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>
                            <td style="width: 79%; padding: 2px;">
                                <?php foreach($edukasi_pasien_keluarga_rj as $edukasi_pasien_keluarga_rj){ ?>
                                <table border="1"
                                    style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">
                                    <tr>
                                        <th colspan="2"
                                            style="background-color: #fffaf8; text-align: center; padding: 8px; font-weight: bold;">
                                            A. PENGKAJIAN KEBUTUHAN EDUKASI</th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 6px;">
                                            Tanggal Edukasi : <?php echo $edukasi_pasien_keluarga_rj->tanggal; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; padding: 6px;">1. Kesediaan Menerima Informasi</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->kesediaan_menerima_informasi; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">2. Bahasa Sehari-hari</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->bahasa_sehari; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">3. Perlu Penerjemah</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->perlu_penerjemah; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">4. Bahasa Isyarat</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->bahasa_isyarat; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">5. Cara Belajar Yang Disukai</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->cara_belajar; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">6. Tingkat Pendidikan</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->pendidikan; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">7. Hambatan Belajar</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->hambatan_belajar; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">8. Kemampuan Belajar</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->kemampuan_belajar; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px; vertical-align: top;">9. Nilai dan Keyakinan</td>
                                        <td style="padding: 6px;">
                                            a. Penyakitnya merupakan : <?php echo $edukasi_pasien_keluarga_rj->penyakitnya_merupakan; ?><br>
                                            b. Keputusan memilih layanan kesehatan : <?php echo $edukasi_pasien_keluarga_rj->keputusan_memilih_layanan; ?><br>
                                            c. Keyakinan terhadap hasil terapi : <?php echo $edukasi_pasien_keluarga_rj->keyakinan_terhadap_terapi; ?><br>
                                            d. Aspek keyakinan selama masa perawatan : <?php echo $edukasi_pasien_keluarga_rj->aspek_keyakinan_dipertimbangkan; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px;">10. Kesediaan Menerima Informasi</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->kesediaan_menerima_informasi; ?></td>
                                    </tr>

                                    <tr>
                                        <th colspan="2"
                                            style="background-color: #fffaf8; text-align: center; padding: 8px; font-weight: bold;">
                                            B. PERENCANAAN KEBUTUHAN EDUKASI</th>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px; vertical-align: top;">Topik edukasi yang harus
                                            diberikan kepada pasien dan keluarga antara lain :</td>
                                        <td style="padding: 6px;">
                                            Penyakit yang diderita pasien : <?php echo $edukasi_pasien_keluarga_rj->topik_edukasi_penyakit; ?><br>
                                            Rencana tindakan /terapi : <?php echo $edukasi_pasien_keluarga_rj->topik_edukasi_rencana_tindakan; ?><br>
                                            Pengobatan dan prosedur yang diberikan atau diperlukan :
                                            <?php echo $edukasi_pasien_keluarga_rj->topik_edukasi_pengobatan; ?><br>
                                            Hasil pelayanan, termasuk terjadinya kejadian yang diharapkan dan tidak
                                            diharapkan : <?php echo $edukasi_pasien_keluarga_rj->topik_edukasi_hasil_layanan; ?>
                                        </td>
                                    </tr>
                                    <br>
                                    <tr>
                                        <td style="padding: 6px;">Petugas / Perawat</td>
                                        <td style="padding: 6px;">: <?php echo $edukasi_pasien_keluarga_rj->nama_petugas; ?></td>
                                    </tr>
                                </table>
                                <br>
                                <?php } ?>
                            </td>
                        </tr>
                        <!-- End Pemeriksaan  -->
