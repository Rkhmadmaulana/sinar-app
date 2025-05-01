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
                                    <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                        <?php foreach ($lab as $jenis => $items): ?>
                                            <?php
                                                $first = $items->first();
                                                $uniquePemeriksaan = $items->unique(function ($item) {
                                                    return $item->pemeriksaan . '|' . $item->nilai . '|' . $item->nilai_rujukan;
                                                });
                                            ?>

                                            <!-- Dokter dan Jenis Perawatan -->
                                            <tr>
                                                <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Dokter Perujuk</th>
                                                <td colspan="2" style="padding: 2px;"><?php echo $first->nm_dokter; ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #FFFAF8; padding: 2px;">Jenis Perawatan</th>
                                                <td colspan="2" style="padding: 2px;"><?php echo $jenis; ?></td>
                                            </tr>

                                            <!-- Header Pemeriksaan -->
                                            <tr style="background-color: #FFFAF8;">
                                                <th style="padding: 2px;">Pemeriksaan</th>
                                                <th style="padding: 2px;">Nilai</th>
                                                <th style="padding: 2px;">Nilai Rujukan</th>
                                            </tr>

                                            <!-- Data Pemeriksaan -->
                                            <?php foreach ($uniquePemeriksaan as $item): ?>
                                            <tr>
                                                <td style="padding: 2px;"><?php echo $item->pemeriksaan; ?></td>
                                                <td style="padding: 2px;"><?php echo $item->nilai; ?></td>
                                                <td style="padding: 2px;"><?php echo $item->nilai_rujukan; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>


                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">Pemeriksaan Radiologi</td>  
                                <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>  
                                <td style="width: 79%; padding: 2px;">
                                    <?php foreach($radiologi as $radiologi){ ?>
                                    <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Tanggal Periksa</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->tgl_periksa; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Dokter Perujuk</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->nm_dokter; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Hasil Bacaan Radiologi</th>
                                            <td style="padding: 2px;"><?php echo $radiologi->hasil; ?></td>
                                        </tr>
                                    </table>
                                    <br>
                                    <?php } ?>
                                </td>
                            </tr>

                            