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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset ('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{asset ('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <!-- <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet"> -->

  <!-- JQuery DataTable Css -->
  <link href="{{asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css')}}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{asset('css/style.css')}}" rel="stylesheet">

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
    <h5  style="color:BLUE;">ERM Ranap</h5>
      <div class="table-responsive">
            <table id="erm"  class="table table-bordered table-striped" style="width:100%;">
              <thead>
                <tr>
                  <th style="width: 100%; text-align: left; vertical-align: top;">Riwayat</th>
                </tr>
              </thead>
              <tbody>
              <!-- Awal Terbackup no_rawat -->
                <tr>
                  <td >
                      <table border="1px"  style="width:100%;">
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
                                <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">Pemeriksaan Laboratorium</td>  
                                <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>  
                                <td style="width: 79%; padding: 2px;">
                                    <?php foreach($lab as $lab){ ?>
                                    <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">No. Permintaan Lab</th>
                                            <td style="padding: 2px;"><?php echo $lab->noorder; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Tanggal Permintaan</th>
                                            <td style="padding: 2px;"><?php echo $lab->tgl_permintaan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Jam Permintaan</th>
                                            <td style="padding: 2px;"><?php echo $lab->jam_permintaan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Tanggal Sampel</th>
                                            <td style="padding: 2px;"><?php echo $lab->tgl_sampel; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jam Sampel</th>
                                            <td style="padding: 2px;"><?php echo $lab->jam_sampel; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Tanggal Hasil</th>
                                            <td style="padding: 2px;"><?php echo $lab->tgl_hasil; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jam Hasil</th>
                                            <td style="padding: 2px;"><?php echo $lab->jam_hasil; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Dokter Perujuk</th>
                                            <td style="padding: 2px;"><?php echo $lab->nm_dokter; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Informasi Tambahan</th>
                                            <td style="padding: 2px;"><?php echo $lab->informasi_tambahan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Diagnosa Klinis</th>
                                            <td style="padding: 2px;"><?php echo $lab->diagnosa_klinis; ?></td>
                                        </tr>
                                    </table>
                                    <br>
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">Pemeriksaan Radiologi</td>  
                                <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>  
                                <td style="width: 79%; padding: 2px;">
                                    <?php foreach($radiologi as $radiologi){ ?>
                                    <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">No. Permintaan Radiologi</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->noorder; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Tanggal Permintaan</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->tgl_permintaan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Jam Permintaan</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->jam_permintaan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Tanggal Hasil</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->tgl_hasil; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jam Hasil</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->jam_hasil; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Dokter Perujuk</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->nm_dokter; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Informasi Tambahan</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->informasi_tambahan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Diagnosa Klinis</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->diagnosa_klinis; ?></td>
                                        </tr>
                                    </table>
                                    <br>
                                    <?php } ?>
                                </td>
                            </tr>

                            