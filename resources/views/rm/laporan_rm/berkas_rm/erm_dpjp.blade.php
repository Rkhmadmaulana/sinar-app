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

                        <!-- DPJP -->
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">DPJP</td>
                            <td style="width: 1%; vertical-align: top;">:</td>
                            <td style="width:79%; padding: 0;">
                                <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc;">
                                    <thead>
                                        <tr style="background-color: #fffaf8;">
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Kode
                                                Dokter</th>
                                            <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Nama
                                                Dokter</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dpjp_ranap as $dpjp) { ?>
                                        <tr>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $dpjp->kd_dokter; ?></td>
                                            <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $dpjp->nm_dokter; ?></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
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
