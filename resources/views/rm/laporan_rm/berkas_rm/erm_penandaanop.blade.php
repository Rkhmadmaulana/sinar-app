<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>ERM - PENANDAAN OPERASI PRIA / WANITA</title>

    <!-- Favicons -->
    <link href="{{ asset('public/img/favicon.png') }}" rel="icon">
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

    <!-- JQuery DataTable Css -->
    <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}"
        rel="stylesheet">
    <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

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
    <h5 style="color:BLUE;">ERM - Penandaan Operasi Pria / Perempuan</h5>
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
                                <td>{{ $data->no_rawat }}</td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">Nama Pasien</td>
                                <td style="width: 1%;">:</td>
                                <td>{{ $data->nm_pasien }}</td>
                            </tr>
                            <td>Tanggal Registrasi</td>
                            <td>:</td>
                            <td>{{ $data->tgl_registrasi ?? '-' }} | {{ $data->jam_reg ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Poliklinik</td>
                    <td>:</td>
                    <td>Ranap</td>
                </tr>

                <tr>
                    <td>Penandaan Operasi Pria / Wanita</td>
                    <td>:</td>
                    <td>
                        <?php if (!$penandaan->isEmpty()) { ?>
                        <?php foreach ($penandaan as $item){ ?>
                        <div class="col-md-4 mb-4">
                            <img src="<?php echo url('/berkas-image/' . basename($item->lokasi_file)); ?>" alt="Gambar Berkas Digital" class="card-img-top">
                        </div>
                        <?php } ?>
                        <?php } else { ?>
                        Tidak ada gambar coretan.
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
