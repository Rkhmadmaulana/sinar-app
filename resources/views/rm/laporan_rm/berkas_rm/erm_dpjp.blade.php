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
    <link href="{{ asset('public/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

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
    <link href="{{ asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
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
            background-color: #FFFAF8;
            padding: 2px;
            width: 30%;
        }
        .sub-table td {
            padding: 2px;
        }
    </style>
</head>

<body>
<h5 style="color:BLUE;">ERM Ranap - DPJP</h5>
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
                    <tr>
                        <td style="width: 20%;">No Rawat</td>
                        <td style="width: 1%;">:</td>
                        <td><?php echo $row->no_rawat; ?></td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Nama Pasien</td>
                        <td style="width: 1%;">:</td>
                        <td><?php echo $row->nm_pasien; ?></td>
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
                        <td>DPJP</td>
                        <td>:</td>
                        <td>
                            <table class="table table-bordered sub-table" style="width:100%;">
                                <thead>
                                <tr>
                                    <th>Kode Dokter</th>
                                    <th>Nama Dokter</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!$dpjp_ranap->isEmpty()) { ?>
                                <?php foreach ($dpjp_ranap as $dpjp) { ?>
                                    <tr>
                                        <td><?php echo $dpjp->kd_dokter; ?></td>
                                        <td><?php echo $dpjp->nm_dokter; ?></td>
                                    </tr>
                                <?php } ?>
                                <?php } else { ?>
                                    Tidak ada data DPJP.
                                <?php } ?>
                                </tbody>
                            </table>
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
