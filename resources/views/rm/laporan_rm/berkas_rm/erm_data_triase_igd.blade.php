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
                                TRIASE IGD </td>
                            <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>
                            <td style="width: 79%; padding: 2px;">
                                <?php foreach($data_triase_igd as $data_triase_igd){ ?>
                                <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                    <tr>
                                        <th style="width: 20%; background-color: #FFFAF8; padding: 2px;">Tanggal
                                            Kunjungan
                                        </th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->tgl_kunjungan; ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Cara Datang</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->cara_masuk; ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Macam Kasus</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr style="background-color: #f4f4d6;">
                                        <th colspan="2" style="padding: 6px;">KETERANGAN</th>
                                    </tr>

                                    <?php if ($table === 'data_triase_primer') { ?>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Keluhan Utama</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->keluhan_utama; ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Tanda Vital</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Kebutuhan Khusus</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->kebutuhan_khusus; ?></td>
                                    </tr>

                                    <?php } elseif ($table === 'data_triase_sekunder') { ?>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Anamnesa Singkat</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->anamnesa_singkat; ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Tanda Vital :</th>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Suhu (C)</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->suhu; ?></td>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Nyeri</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->nyeri; ?></td>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Tensi</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->tekanan_darah; ?></td>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Nadi (/menit)</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->nadi; ?></td>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Saturasi O2 (%)</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->saturasi_o2; ?></td>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Respirasi</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->pernapasan; ?></td>
                                    </tr>
                                    <tr style="background-color: #f4f4d6;">
                                        <th colspan="2" style="padding: 6px;">PEMERIKSAAN</th>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%; background-color: #FFFAF8; padding: 2px;">Perlu
                                            PEMERIKSAAN</th>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%; background-color: #FFFAF8; padding: 2px;">Perlu
                                            Dekomentaminasi?</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Kategori Usia</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Penempatan Pasien</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Sumber Daya</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Nyeri Hebat</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Plan</th>
                                        <td style="padding: 2px;"><?php echo $data_triase_igd->plan; ?></td>
                                    </tr>
                                </table>
                                <table border="1"
                                    style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                    <tr>
                                        <th style="width: 20%; background-color: #FFFAF8; padding: 2px;">Tanggal & Jam
                                        </th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Catatan</th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #FFFAF8; padding: 2px;">Dokter/Petugas Jaga IGD
                                        </th>
                                        <td style="padding: 2px;"></td>
                                    </tr>
                                </table>
                                <br>
                                <?php } ?>
                            </td>
                        </tr>
                        <!-- End Pemeriksaan  -->
