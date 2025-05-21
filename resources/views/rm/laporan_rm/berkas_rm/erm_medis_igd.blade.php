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
    <h5  style="color:BLUE;">ERM Ranap - Awal Medis IGD</h5>
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

                          <!-- Awal Medis IGD -->
                            <?php 
                            if (!empty($awal_med_igd)) {?>
                              <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top;">Awal Medis IGD</td> 
                                <td style="width: 1%;  vertical-align: top;">:</td>  
                                <td style="width:79%;">
                                <?php 
                                foreach($awal_med_igd as $awal_medis_igd){ ?>
                                <form method="post">
                                    <div class="row clearfix">
                                      <div class="col-md-12">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Anamnesis</dt>
                                            <dd>
                                              <input type="text" class="form-control" name="hubungan" value="<?php echo $awal_medis_igd->anamnesis; ?>" disabled>
                                              <input type="text" class="form-control" name="hubungan" value="<?php echo $awal_medis_igd->hubungan; ?>" disabled>
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
                                            <dd><textarea rows="4" name="keluhan_utama" class="form-control" disabled><?php echo $awal_medis_igd->keluhan_utama; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Riwayat Penyakit Sekarang</dt>
                                            <dd><textarea rows="4" name="rps" class="form-control" disabled><?php echo $awal_medis_igd->rps; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row clearfix">
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Riwayat Penyakit Keluarga</dt>
                                            <dd><textarea rows="4" name="rpk" class="form-control" disabled><?php echo $awal_medis_igd->rpk; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Riwayat Penyakit Dahulu</dt>
                                            <dd><textarea rows="4" name="rpd" class="form-control" disabled><?php echo $awal_medis_igd->rpd; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row clearfix">
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Riwayat Pengunaan Obat</dt>
                                            <dd><textarea rows="4" name="rpo" class="form-control" disabled><?php echo $awal_medis_igd->rpo; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Riwayat Alergi</dt>
                                            <dd><input type="text" class="form-control" name="alergi" disabled><?php echo $awal_medis_igd->alergi; ?></textarea></dd>
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
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->keadaan; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-4">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Kesadaran</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->kesadaran; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>GCS(E,V,M)</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->gcs; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>TB</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->tb; ?>">
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
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->bb; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>TD</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->td; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Nadi</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->nadi; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>RR</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->rr; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Suhu</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->suhu; ?>">
                                            </dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-2">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>SpO2</dt>
                                            <dd>
                                              <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->spo; ?>">
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
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->kepala; ?>">
                                                </dd>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <div class="form-line">
                                                <dt>Abdomen</dt>
                                                <dd>
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->abdomen; ?>">
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
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->gigi; ?>">
                                                </dd>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <div class="form-line">
                                                <dt>Genital & Anus</dt>
                                                <dd>
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->genital; ?>">
                                                </dd>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="row clearfix">
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <div class="form-line">
                                                <dt>Leher</dt>
                                                <dd>
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->leher; ?>">
                                                </dd>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <div class="form-line">
                                                <dt>Ekstremitas</dt>
                                                <dd>
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->ekstremitas; ?>">
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
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->thoraks; ?>">
                                                </dd>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <div class="form-line">
                                                <dt>Mata</dt>
                                                <dd>
                                                  <input type="text" class="form-control" disabled value="<?php echo $awal_medis_igd->mata; ?>">
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
                                            <dd><textarea rows="16" name="ket_fisik" class="form-control" disabled><?php echo $awal_medis_igd->ket_fisik; ?></textarea></dd>
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
                                            <dt>Keterangan</dt>
                                            <dd><textarea rows="4" name="ket_lokalis" class="form-control" disabled><?php echo $awal_medis_igd->ket_lokalis; ?></textarea></dd>
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
                                            <dt>EKG</dt>
                                            <dd><textarea rows="4" name="penunjang" class="form-control" disabled><?php echo $awal_medis_igd->ekg; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-4">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Radiologi</dt>
                                            <dd><textarea rows="4" name="penunjang" class="form-control" disabled><?php echo $awal_medis_igd->rad; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-4">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Laborat</dt>
                                            <dd><textarea rows="4" name="penunjang" class="form-control" disabled><?php echo $awal_medis_igd->lab; ?></textarea></dd>
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
                                            <dd><textarea rows="4" name="diagnosis" class="form-control" disabled><?php echo $awal_medis_igd->diagnosis; ?></textarea></dd>
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
                                            <dd><textarea rows="4" name="tata" class="form-control" disabled><?php echo $awal_medis_igd->tata; ?></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </form>
                                  <?php } ?>
                                </td>
                              </tr>
                            <?php } ?>
                          <!-- End Awal Medis IGD -->

       
                          
                      </table>
                  </td>
                </tr>
              <!-- Akhir Terbackup no_rawat -->
              <?php  ?>
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