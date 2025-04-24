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

                          <!-- Pemeriksaan  -->
                          <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top;">Pemeriksaan</td>  
                                  <td style="width: 1%;  vertical-align: top;">:</td>  
                                  <td style="width:79%;">
                                    <?php
                                    if ($cppt_igd) {
                                    ?>
                                  PEMERIKSAAN RALAN
                                  <?php 
                                  foreach($cppt_igd as $cpptigd){ ?>
                                  <table border="1px"  style="width:100%;">
                                      <tr>
                                        <th bgcolor='#FFFAF8'>Tanggal</th>
                                        <th bgcolor='#FFFAF8'>Jam</th>
                                        <th bgcolor='#FFFAF8'>Suhu (C)</th>
                                        <th bgcolor='#FFFAF8'>Tensi (mmHg)</th>
                                        <th bgcolor='#FFFAF8'>Nadi (/ Menit)</th>
                                        <th bgcolor='#FFFAF8'>RR (/ Menit)</th>
                                        <th bgcolor='#FFFAF8'>Tinggi (Cm)</th>
                                        <th bgcolor='#FFFAF8'>Berat (Kg)</th>
                                        <th bgcolor='#FFFAF8'>GCS (E,V,M)</th>
                                        <th bgcolor='#FFFAF8'>Alergi</th>
                                        <th bgcolor='#FFFAF8'>Kesadaran</th>
                                      </tr>
                                      <tr>
                                        <td><?php echo $cpptigd->tgl_perawatan; ?></td>
                                        <td><?php echo $cpptigd->jam_rawat; ?></td>
                                        <td><?php echo $cpptigd->suhu_tubuh; ?></td>
                                        <td><?php echo $cpptigd->tensi; ?></td>
                                        <td><?php echo $cpptigd->nadi; ?></td>
                                        <td><?php echo $cpptigd->respirasi; ?></td>
                                        <td><?php echo $cpptigd->tinggi; ?></td>
                                        <td><?php echo $cpptigd->berat; ?></td>
                                        <td><?php echo $cpptigd->gcs; ?></td>
                                        <td><?php echo $cpptigd->alergi; ?></td>
                                        <td><?php echo $cpptigd->kesadaran; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Subjek</th>
                                        <td colspan="9"><?php echo $cpptigd->keluhan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Objek</th>
                                        <td colspan="9"><?php echo $cpptigd->pemeriksaan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Asesment</th>
                                        <td colspan="9"><?php echo $cpptigd->penilaian; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Plan</th>
                                        <td colspan="9"><?php echo $cpptigd->rtl; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Petugas</th>
                                        <td colspan="9"><?php echo $cpptigd->nama; ?></td>
                                      </tr>
                                  </table>
                                  <br>
                                  <?php } ?>
                                    <!-- End pemerisaan Ralan -->
                                    <?php } ?>
                                    <?php 
                                    if ($cppt_ranap) {
                                    ?>
                                  PEMERIKSAAN RANAP
                                  <?php 
                                  foreach($cppt_ranap as $cppt){ ?>
                                  <table border="1px"  style="width:100%;">
                                      <tr>
                                        <th bgcolor='#FFFAF8'>Tanggal</th>
                                        <th bgcolor='#FFFAF8'>Jam</th>
                                        <th bgcolor='#FFFAF8'>Suhu (C)</th>
                                        <th bgcolor='#FFFAF8'>Tensi (mmHg)</th>
                                        <th bgcolor='#FFFAF8'>Nadi (/ Menit)</th>
                                        <th bgcolor='#FFFAF8'>RR (/ Menit)</th>
                                        <th bgcolor='#FFFAF8'>Tinggi (Cm)</th>
                                        <th bgcolor='#FFFAF8'>Berat (Kg)</th>
                                        <th bgcolor='#FFFAF8'>GCS (E,V,M)</th>
                                        <th bgcolor='#FFFAF8'>Alergi</th>
                                        <th bgcolor='#FFFAF8'>Kesadaran</th>
                                      </tr>
                                      <tr>
                                        <td><?php echo $cppt->tgl_perawatan; ?></td>
                                        <td><?php echo $cppt->jam_rawat; ?></td>
                                        <td><?php echo $cppt->suhu_tubuh; ?></td>
                                        <td><?php echo $cppt->tensi; ?></td>
                                        <td><?php echo $cppt->nadi; ?></td>
                                        <td><?php echo $cppt->respirasi; ?></td>
                                        <td><?php echo $cppt->tinggi; ?></td>
                                        <td><?php echo $cppt->berat; ?></td>
                                        <td><?php echo $cppt->gcs; ?></td>
                                        <td><?php echo $cppt->alergi; ?></td>
                                        <td><?php echo $cppt->kesadaran; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Subjek</th>
                                        <td colspan="9"><?php echo $cppt->keluhan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Objek</th>
                                        <td colspan="9"><?php echo $cppt->pemeriksaan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Asesment</th>
                                        <td colspan="9"><?php echo $cppt->penilaian; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Plan</th>
                                        <td colspan="9"><?php echo $cppt->rtl; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Petugas</th>
                                        <td colspan="9"><?php echo $cppt->nama; ?></td>
                                      </tr>
                                  </table>
                                  <br>
                                    <?php } ?>
                                    <?php } ?>
                                </td>  
                            </tr>
                          <!-- End Pemeriksaan  -->