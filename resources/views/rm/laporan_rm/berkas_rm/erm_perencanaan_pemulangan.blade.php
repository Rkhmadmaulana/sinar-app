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
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 20%; vertical-align: top;"><strong>No Rawat</strong></td>
                            <td style="width: 1%; vertical-align: top;">:</td>
                            <td style="width: 79%;"><?php echo $row->no_rawat; ?></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;"><strong>Tanggal Registrasi</strong></td>
                            <td style="vertical-align: top;">:</td>
                            <td><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;"><strong>Poliklinik</strong></td>
                            <td style="vertical-align: top;">:</td>
                            <td>Ranap</td>
                        </tr>
                        <br>
                        <tr>
                            <td style="vertical-align: top;"><strong>Perencanaan Pemulangan</strong></td>
                            <td style="vertical-align: top;">:</td>

                        </tr>
                    </table>


                    <?php foreach($perencanaan_pemulangan as $perencanaan_pemulangan): ?>
                    <table style="width: 100%; border: 1px solid #ccc; border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr style="background-color: #f9f9f9;">
                                <th style="border: 1px solid #ccc; padding: 5px;">Rencana Pulang</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Diagnosa Medis</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Pengaruh RI Pasien & Keluarga</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Pengaruh RI Pasien &
                                    Keluarga</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Pengaruh RI Pekerjaan / Sekolah</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Pengaruh RI Pekerjaan /
                                    Sekolah</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Pengaruh RI Keuangan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Pengaruh RI Keuangan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Antisipasi Masalah</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Antisipasi Masalah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->rencana_pulang; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->diagnosa_medis; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->pengaruh_ri_pasien_dan_keluarga; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_pengaruh_ri_pasien_dan_keluarga; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->pengaruh_ri_pekerjaan_sekolah; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_pengaruh_ri_pekerjaan_sekolah; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->pengaruh_ri_keuangan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_pengaruh_ri_keuangan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->antisipasi_masalah_saat_pulang; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_antisipasi_masalah_saat_pulang; ?></td>
                            </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ccc; padding: 5px;">Bantuan Diperlukan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Bantuan Diperlukan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Membantu Keperluan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Yang Membantu Keperluan
                                </th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Tinggal Sendiri</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Pasien Tinggal Sendiri</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Peralatan Medis</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Peralatan Medis</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Alat Bantu</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Memerlukan Alat Bantu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->bantuan_diperlukan_dalam; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_bantuan_diperlukan_dalam; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->adakah_yang_membantu_keperluan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_adakah_yang_membantu_keperluan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->pasien_tinggal_sendiri; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_pasien_tinggal_sendiri; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->pasien_menggunakan_peralatan_medis; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_pasien_menggunakan_peralatan_medis; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->pasien_memerlukan_alat_bantu; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_pasien_memerlukan_alat_bantu; ?></td>
                            </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keperawatan Khusus</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Keperawatan Khusus</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Memenuhi Kebutuhan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Memenuhi Kebutuhan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Nyeri Kronis</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Nyeri Kronis</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Edukasi Kesehatan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Edukasi Kesehatan</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterampilan Khusus</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Keterangan Keterampilan Khusus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->memerlukan_perawatan_khusus; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_memerlukan_perawatan_khusus; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->bermasalah_memenuhi_kebutuhan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_bermasalah_memenuhi_kebutuhan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->memiliki_nyeri_kronis; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_memiliki_nyeri_kronis; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->memerlukan_edukasi_kesehatan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_memerlukan_edukasi_kesehatan; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->memerlukan_keterampilkan_khusus; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->keterangan_memerlukan_keterampilkan_khusus; ?></td>
                            </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ccc; padding: 5px;">Alasan Masuk</th>
                                <th style="border: 1px solid #ccc; padding: 5px;">Pasien/Keluarga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->alasan_masuk; ?></td>
                                <td style="border: 1px solid #ccc; padding: 5px;"><?php echo $perencanaan_pemulangan->nama_pasien_keluarga; ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endforeach; ?>
                </td>
            </tr>
            <!-- End Pemeriksaan  -->
