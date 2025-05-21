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
    <h5  style="color:BLUE;">ERM Ranap - Asesmen Awal Medis</h5>
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

                          <!-- Awal Medis Umum  SUDAH-->
                            <?php if (!empty($awal_medis_umum)) {?> 
                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top;">Awal Medis Umum</td>  
                                <td style="width: 1%;  vertical-align: top;">:</td>  
                                <td style="width:79%;">
                                    <?php foreach ($awal_medis_umum as $awal_medis_umum_ranap) { ?>
                                      <form method="post">
                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Anamnesis</dt>
                                                          <dd>
                                                              <select disabled name="anamnesis" class="metode_racik" id="anamnesis" style="width:100%">
                                                                  <option value="Autoanamnesis" <?php if ($awal_medis_umum_ranap->anamnesis == 'Autoanamnesis') echo 'selected'; ?>>Autoanamnesis</option>
                                                                  <option value="Alloanamnesis" <?php if ($awal_medis_umum_ranap->anamnesis == 'Alloanamnesis') echo 'selected'; ?>>Alloanamnesis</option>
                                                              </select>
                                                              <input disabled type="text" name="hubungan" value="<?php echo $awal_medis_umum_ranap->hubungan ?? ''; ?>">
                                                              <input disabled type="hidden" name="no_rawat" value="<?php echo $awal_medis_umum_ranap->no_rawat ?? ''; ?>">
                                                              <input disabled type="hidden" name="tanggal" value="<?php echo $awal_medis_umum_ranap->tanggal ?? ''; ?>">
                                                            </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>I. RIWAYAT KESEHATAN</dt>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Keluhan Utama</dt>
                                                          <dd><textarea disabled rows="4" name="keluhan_utama" class="form-control"><?php echo $awal_medis_umum_ranap->keluhan_utama; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Riwayat Penyakit Sekarang</dt>
                                                          <dd><textarea disabled rows="4" name="rps" class="form-control"><?php echo $awal_medis_umum_ranap->rps; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Riwayat Penyakit Keluarga</dt>
                                                          <dd><textarea disabled rows="4" name="rpk" class="form-control"><?php echo $awal_medis_umum_ranap->rpk; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Riwayat Penyakit Dahulu</dt>
                                                          <dd><textarea disabled rows="4" name="rpd" class="form-control"><?php echo $awal_medis_umum_ranap->rpd; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Riwayat Pengunaan Obat</dt>
                                                          <dd><textarea disabled rows="4" name="rpo" class="form-control"><?php echo $awal_medis_umum_ranap->rpo; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Riwayat Alergi</dt>
                                                          <dd><input disabled type="text" class="form-control" name="alergi"><?php echo $awal_medis_umum_ranap->alergi; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>II. PEMERIKSAAN FISIK</dt>
                                              </div>
                                              <div class="col-md-4">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Keadaan Umum</dt>
                                                          <dd>
                                                              <select disabled name="keadaan" class="metode_racik" id="keadaan" style="width:100%">
                                                                  <option value="Sehat" <?php if ($awal_medis_umum_ranap->keadaan == 'Sehat') echo 'selected'; ?>>Sehat</option>
                                                                  <option value="Sakit Ringan" <?php if ($awal_medis_umum_ranap->keadaan == 'Sakit Ringan') echo 'selected'; ?>>Sakit Ringan</option>
                                                                  <option value="Sakit Sedang" <?php if ($awal_medis_umum_ranap->keadaan == 'Sakit Sedang') echo 'selected'; ?>>Sakit Sedang</option>
                                                                  <option value="Sakit Berat" <?php if ($awal_medis_umum_ranap->keadaan == 'Sakit Berat') echo 'selected'; ?>>Sakit Berat</option>
                                                              </select>
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-4">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Kesadaran</dt>
                                                          <dd>
                                                              <select disabled name="kesadaran" class="metode_racik" id="kesadaran" style="width:100%">
                                                                  <option value="Compos Mentis" <?php if ($awal_medis_umum_ranap->kesadaran == 'Compos Mentis') echo 'selected'; ?>>Compos Mentis</option>
                                                                  <option value="Apatis" <?php if ($awal_medis_umum_ranap->kesadaran == 'Apatis') echo 'selected'; ?>>Apatis</option>
                                                                  <option value="Somnolen" <?php if ($awal_medis_umum_ranap->kesadaran == 'Somnolen') echo 'selected'; ?>>Somnolen</option>
                                                                  <option value="sopor" <?php if ($awal_medis_umum_ranap->kesadaran == 'sopor') echo 'selected'; ?>>sopor</option>
                                                                  <option value="Koma" <?php if ($awal_medis_umum_ranap->kesadaran == 'Koma') echo 'selected'; ?>>Koma</option>
                                                              </select>
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>GCS(E,V,M)</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="gcs" value="<?php echo $awal_medis_umum_ranap->gcs; ?>">
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>TB</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="tb" value="<?php echo $awal_medis_umum_ranap->tb; ?>">Cm
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>BB</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="bb" value="<?php echo $awal_medis_umum_ranap->bb; ?>">Kg
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>TD</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="td" value="<?php echo $awal_medis_umum_ranap->td; ?>">mmHg
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Nadi</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="nadi" value="<?php echo $awal_medis_umum_ranap->nadi; ?>">x/menit
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>RR</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="rr" value="<?php echo $awal_medis_umum_ranap->rr; ?>">x/menit
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Suhu</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="suhu" value="<?php echo $awal_medis_umum_ranap->suhu; ?>">'C
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-2">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>SpO2</dt>
                                                          <dd>
                                                              <input disabled type="text" class="form-control" name="spo" value="<?php echo $awal_medis_umum_ranap->spo; ?>">%
                                                          </dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row clearfix">
                                              <div class="col-md-6">
                                                  <div class="row clearfix">
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Kepala</dt>
                                                                  <dd>
                                                                      <select disabled name="kepala" class="metode_racik" id="kepala" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->kepala == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->kepala == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->kepala == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Abdomen</dt>
                                                                  <dd>
                                                                      <select disabled name="abdomen" class="metode_racik" id="abdomen" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->abdomen == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->abdomen == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->abdomen == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Mata</dt>
                                                                  <dd>
                                                                      <select disabled name="mata" class="metode_racik" id="mata" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->mata == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->mata == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->mata == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Jantung</dt>
                                                                  <dd>
                                                                      <select disabled name="jantung" class="metode_racik" id="jantung" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->jantung == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->jantung == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->jantung == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Gigi & Mulut</dt>
                                                                  <dd>
                                                                      <select disabled name="gigi" class="metode_racik" id="gigi" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->gigi == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->gigi == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->gigi == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Genital & Anus</dt>
                                                                  <dd>
                                                                      <select disabled name="genital" class="metode_racik" id="genital" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->genital == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->genital == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->genital == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>THT</dt>
                                                                  <dd>
                                                                      <select disabled name="tht" class="metode_racik" id="tht" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->tht == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->tht == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->tht == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Ekstremitas</dt>
                                                                  <dd>
                                                                      <select disabled name="ekstremitas" class="metode_racik" id="ekstremitas" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->ekstremitas == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->ekstremitas == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->ekstremitas == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Thoraks</dt>
                                                                  <dd>
                                                                      <select disabled name="thoraks" class="metode_racik" id="thoraks" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->thoraks == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->thoraks == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->thoraks == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Kulit</dt>
                                                                  <dd>
                                                                      <select disabled name="kulit" class="metode_racik" id="kulit" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->kulit == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->kulit == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->kulit == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                      <div class="col-md-6">
                                                          <div class="form-group">
                                                              <div class="form-line">
                                                                  <dt>Paru</dt>
                                                                  <dd>
                                                                      <select disabled name="paru" class="metode_racik" id="paru" style="width:100%">
                                                                          <option value="Normal" <?php if ($awal_medis_umum_ranap->paru == 'Normal') echo 'selected'; ?>>Normal</option>
                                                                          <option value="Abnormal" <?php if ($awal_medis_umum_ranap->paru == 'Abnormal') echo 'selected'; ?>>Abnormal</option>
                                                                          <option value="Tidak Diperiksa" <?php if ($awal_medis_umum_ranap->paru == 'Tidak Diperiksa') echo 'selected'; ?>>Tidak Diperiksa</option>
                                                                      </select>
                                                                  </dd>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Keterangan Fisik</dt>
                                                          <dd><textarea disabled rows="16" name="ket_fisik" class="form-control"><?php echo $awal_medis_umum_ranap->ket_fisik; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>III. STATUS LOKALIS</dt>
                                              </div>
                                              <div class="col-md-12">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <?php
                                                          echo '<img src="/edokter/module/ralan/images/penilaian_awal.png" style="width:100%" />';
                                                          ?>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-12">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Keterangan</dt>
                                                          <dd><textarea disabled rows="4" name="ket_lokalis" class="form-control"><?php echo $awal_medis_umum_ranap->ket_lokalis; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>IV. PEMERIKSAAN PENUNJANG</dt>
                                              </div>
                                              <div class="col-md-4">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Laboratorium</dt>
                                                          <dd><textarea disabled rows="4" name="lab" class="form-control"><?php echo $awal_medis_umum_ranap->lab; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-4">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Radiologi</dt>
                                                          <dd><textarea disabled rows="4" name="rad" class="form-control"><?php echo $awal_medis_umum_ranap->rad; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="col-md-4">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Penunjang Lainnya</dt>
                                                          <dd><textarea disabled rows="4" name="penunjang" class="form-control"><?php echo $awal_medis_umum_ranap->penunjang; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>

                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>V. DIAGNOSIS/ASESMEN</dt>
                                              </div>
                                              <div class="col-md-12">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Diagnosis / Asesmen</dt>
                                                          <dd><textarea disabled rows="4" name="diagnosis" class="form-control"><?php echo $awal_medis_umum_ranap->diagnosis; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>VI. TATALAKSANA</dt>
                                              </div>
                                              <div class="col-md-12">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Tatalaksana</dt>
                                                          <dd><textarea disabled rows="4" name="tata" class="form-control"><?php echo $awal_medis_umum_ranap->tata; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row clearfix">
                                              <div class="col-md-12">
                                                  <dt>VII. EDUKASI</dt>
                                              </div>
                                              <div class="col-md-12">
                                                  <div class="form-group">
                                                      <div class="form-line">
                                                          <dt>Edukasi</dt>
                                                          <dd><textarea disabled rows="4" name="edukasi" class="form-control"><?php echo $awal_medis_umum_ranap->edukasi; ?></textarea></dd>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </form>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                          <!-- End Awal Medis Umum  -->
                      </table>
                  </td>
                </tr>
              <!-- Akhir Terbackup no_rawat -->
              </tbody>
            </table>
      </div>
    <script>
        $('#erm').dataTable( {
        responsive: true,
        order: [[ 0, 'desc' ]],
        "lengthMenu": [[1, 5, 10, -1], [1, 5, 10, "All"]],
        "dom": '<l><p><f>rt<ip><"clear">',
        "pagingType": "full_numbers"
        } );
    </script>
</html>