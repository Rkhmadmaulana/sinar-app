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
                                    if ($soap_igd) {
                                    ?>
                                  PEMERIKSAAN RALAN
                                  <?php 
                                  foreach($soap_igd as $soapigd){ ?>
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
                                        <td><?php echo $soapigd->tgl_perawatan; ?></td>
                                        <td><?php echo $soapigd->jam_rawat; ?></td>
                                        <td><?php echo $soapigd->suhu_tubuh; ?></td>
                                        <td><?php echo $soapigd->tensi; ?></td>
                                        <td><?php echo $soapigd->nadi; ?></td>
                                        <td><?php echo $soapigd->respirasi; ?></td>
                                        <td><?php echo $soapigd->tinggi; ?></td>
                                        <td><?php echo $soapigd->berat; ?></td>
                                        <td><?php echo $soapigd->gcs; ?></td>
                                        <td><?php echo $soapigd->alergi; ?></td>
                                        <td><?php echo $soapigd->kesadaran; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Subjek</th>
                                        <td colspan="9"><?php echo $soapigd->keluhan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Objek</th>
                                        <td colspan="9"><?php echo $soapigd->pemeriksaan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Asesment</th>
                                        <td colspan="9"><?php echo $soapigd->penilaian; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Plan</th>
                                        <td colspan="9"><?php echo $soapigd->rtl; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Petugas</th>
                                        <td colspan="9"><?php echo $soapigd->nama; ?></td>
                                      </tr>
                                  </table>
                                  <br>
                                  <?php } ?>
                                    <!-- End pemerisaan Ralan -->
                                    <?php } ?>
                                    <?php 
                                    if ($soap_ranap) {
                                    ?>
                                  PEMERIKSAAN RANAP
                                  <?php 
                                  foreach($soap_ranap as $soap){ ?>
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
                                        <td><?php echo $soap->tgl_perawatan; ?></td>
                                        <td><?php echo $soap->jam_rawat; ?></td>
                                        <td><?php echo $soap->suhu_tubuh; ?></td>
                                        <td><?php echo $soap->tensi; ?></td>
                                        <td><?php echo $soap->nadi; ?></td>
                                        <td><?php echo $soap->respirasi; ?></td>
                                        <td><?php echo $soap->tinggi; ?></td>
                                        <td><?php echo $soap->berat; ?></td>
                                        <td><?php echo $soap->gcs; ?></td>
                                        <td><?php echo $soap->alergi; ?></td>
                                        <td><?php echo $soap->kesadaran; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Subjek</th>
                                        <td colspan="9"><?php echo $soap->keluhan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Objek</th>
                                        <td colspan="9"><?php echo $soap->pemeriksaan; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Asesment</th>
                                        <td colspan="9"><?php echo $soap->penilaian; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Plan</th>
                                        <td colspan="9"><?php echo $soap->rtl; ?></td>
                                      </tr>
                                      <tr>
                                        <th bgcolor='#FFFAF8' colspan="2">Petugas</th>
                                        <td colspan="9"><?php echo $soap->nama; ?></td>
                                      </tr>
                                  </table>
                                  <br>
                                    <?php } ?>
                                    <?php } ?>
                                </td>  
                            </tr>
                          <!-- End Pemeriksaan  -->

                          <!-- Jika Tindakan Kosong Maka Tidak Muncul -->

                          
                          <!-- Awal Keperawatan Umum SUDAH-->
                                    <?php $awal_keperawatan_r = $awal_keperawatan_ranap->toArray();
                                            foreach($awal_keperawatan_r as $awal_keperawatan){  ?>
                                <tr>
                                  <td style="width: 20%; text-align: left; vertical-align: top;">Awal Keperawatan Umum</td>
                                  <td style="width: 1%;  vertical-align: top;">:</td>
                                  <td style="width:79%;">
                                            <?php
                                            $pengkaji1=fetch_assoc(query("SELECT nama FROM petugas WHERE nip = '{$awal_keperawatan->nip1}'"));
                                            $pengkaji2=fetch_assoc(query("SELECT nama FROM petugas WHERE nip = '{$awal_keperawatan->nip2}}'"));
                                            $dpjp=fetch_assoc(query("SELECT nama FROM petugas WHERE nip = '{$awal_keperawatan->kd_dokter}'"));
                                            ?>
                                          <table width="100%" border="1" align="center" cellpadding="3px" cellspacing="0" class="tbl_form">
                                              <tr align='center'>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Pengkaji 1</td>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Pengkaji 2</td>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>DPJP</td>
                                              </tr>
                                              <tr>
                                                <td valign='top'  colspan="2" width='33%'><?php echo $pengkaji1->nama; ?></td>
                                                <td valign='top'  colspan="2" width='33%'><?php echo $pengkaji2->nama; ?></td>
                                                <td valign='top'  colspan="2" width='33%'><?php echo $dpjp->nama; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Tanggal</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Macam Kasus</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Tiba Di Ruang Rawat</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Cara Masuk</td>
                                                <td valign='top'   colspan="2" width='33%' bgcolor='#f8fdf3'>Anamnesis</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  ><?php echo $awal_keperawatan->tanggal; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->kasus_trauma; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->tiba_diruang_rawat; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->cara_masuk; ?></td> 
                                                <td valign='top'   colspan="2" width='33%'><?php echo $awal_keperawatan->informasi; ?>,<?php echo $awal_keperawatan->ket_informasi; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="6" >I. RIWAYAT KESEHATAN</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="3" width='50%' bgcolor='#f8fdf3'>Riwayat Penyakit Saat Ini</td>
                                                <td valign='top'  colspan="3" width='50%' bgcolor='#f8fdf3'>Riwayat Sakit Dahulu</td>
                                              </tr>
                                              <tr>
                                                <td valign='top'  colspan="3" width='50%'><?php echo $awal_keperawatan->rps; ?></td>
                                                <td valign='top'  colspan="3" width='50%'><?php echo $awal_keperawatan->rpd; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Riwayat Penyakit Keluarga</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Riwayat Penggunaan Obat</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Riwayat Pembedahan</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Riwayat Dirawat Di RS</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Alat Bantu Yang Dipakai</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Apakah Dalam Keadaan Hamil/ Sedang Menyusui</td>
                                              </tr>
                                              <tr>
                                                <td valign='top'  ><?php echo $awal_keperawatan->rpk; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->rpo; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_pembedahan; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_dirawat_dirs; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->alat_bantu_dipakai; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_kehamilan; ?>, <?php echo $awal_keperawatan->riwayat_kehamilan_perkiraan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Riwayat Tranfusi Darah</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Riwayat Alergi</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Merokok</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Alkohol</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Obat Tidur</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Olahraga</td>
                                              </tr>
                                              <tr>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_tranfusi; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_alergi; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_merokok; ?>, <?php echo $awal_keperawatan->riwayat_merokok_jumlah; ?> Batang/Hari</td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_alkohol; ?>, <?php echo $awal_keperawatan->riwayat_alkohol_jumlah; ?> Gelas/Hari</td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_narkoba; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->riwayat_olahraga; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="6" >II. PEMERIKSAAN FISIK</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Kesadaran Mental</td>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Keadaan Umum</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>GCS(E,V,M)</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>TD</td>
                                              </tr>
                                              <tr>
                                                <td valign='top'  colspan="2" width='33%'><?php echo $awal_keperawatan->pemeriksaan_mental; ?></td>
                                                <td valign='top'  colspan="2" width='33%'><?php echo $awal_keperawatan->pemeriksaan_keadaan_umum; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->pemeriksaan_gcs; ?></td>
                                                <td valign='top'  ><?php echo $awal_keperawatan->pemeriksaan_td; ?> mmHg</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Nadi</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>RR</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Suhu</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>SpO2</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>BB</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>TB</td>
                                              </tr>
                                              <tr>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_nadi; ?> x/Menit</td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_rr; ?> x/Menit</td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_suhu; ?> °C</td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_spo2; ?> %</td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_bb; ?> Kg</td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_tb; ?> cm</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kepala</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Wajah</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Leher</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kejang</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Sensorik</td>
                                              </tr>
                                              <tr>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_susunan_kepala; ?>, <?php echo $awal_keperawatan->pemeriksaan_susunan_kepala_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_susunan_wajah; ?>, <?php echo $awal_keperawatan->pemeriksaan_susunan_wajah_keterangan; ?> </td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_susunan_leher; ?> </td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_susunan_kejang; ?>, <?php echo $awal_keperawatan->pemeriksaan_susunan_kejang_keterangan; ?> </td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_susunan_sensorik; ?> </td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Pulsasi</td>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Sirkulas</td>
                                                <td valign='top'  colspan="2" width='33%' bgcolor='#f8fdf3'>Denyut Nadi</td>
                                              </tr>
                                              <tr>
                                                <td valign='top' colspan="2" width='33%'><?php echo $awal_keperawatan->pemeriksaan_kardiovaskuler_pulsasi; ?> </td>
                                                <td valign='top' colspan="2" width='33%'><?php echo $awal_keperawatan->pemeriksaan_kardiovaskuler_sirkulasi; ?>, <?php echo $awal_keperawatan->pemeriksaan_kardiovaskuler_sirkulasi_keterangan; ?> </td>
                                                <td valign='top' colspan="2" width='33%'><?php echo $awal_keperawatan->pemeriksaan_kardiovaskuler_denyut_nadi; ?> </td>
                                              </tr>
                                              <tr >
                                                <td  align='center' bgcolor='#f8fdf3'>Respirasi</td>
                                                <td  colspan="5">
                                                  Retraksi : <?php echo $awal_keperawatan->pemeriksaan_respirasi_retraksi; ?> | Pola Nafas : <?php echo $awal_keperawatan->pemeriksaan_respirasi_pola_nafas; ?><br>
                                                  Suara Nafas : <?php echo $awal_keperawatan->pemeriksaan_respirasi_suara_nafas; ?> | Batuk & Sekresi : <?php echo $awal_keperawatan->pemeriksaan_respirasi_batuk; ?><br>
                                                  Volume : <?php echo $awal_keperawatan->pemeriksaan_respirasi_volume_pernafasan; ?> | Jenis Pernafasaan : <?php echo $awal_keperawatan->pemeriksaan_respirasi_jenis_pernafasan; ?>, <?php echo $awal_keperawatan->pemeriksaan_respirasi_jenis_pernafasan_keterangan; ?><br>
                                                  Irama : <?php echo $awal_keperawatan->pemeriksaan_respirasi_irama_nafas; ?> 
                                                </td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Mulut</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Tenggorokan</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Lidah</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Abdomen</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Gigi</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Peistatik Usus  |  Anus</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_mulut; ?>, <?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_mulut_keterangan; ?> </td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_tenggorokan; ?>, <?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_tenggorokan_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_lidah; ?>, <?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_lidah_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_abdomen; ?>, <?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_abdomen_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_gigi; ?>, <?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_gigi_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_peistatik_usus; ?>  |  <?php echo $awal_keperawatan->pemeriksaan_gastrointestinal_anus; ?></td>
                                              </tr>
                                              <tr >
                                                <td  align='center' bgcolor='#f8fdf3'>Neurologi</td>
                                                <td  colspan="5">
                                                  Sensorik : <?php echo $awal_keperawatan->pemeriksaan_neurologi_sensorik; ?> | Penglihatan : <?php echo $awal_keperawatan->pemeriksaan_neurologi_pengelihatan; ?>,  <?php echo $awal_keperawatan->pemeriksaan_neurologi_pengelihatan_keterangan; ?><br>
                                                  Alat Bantu Penglihatan : <?php echo $awal_keperawatan->pemeriksaan_neurologi_alat_bantu_penglihatan; ?> | Motorik : <?php echo $awal_keperawatan->pemeriksaan_neurologi_motorik; ?><br>
                                                  Pendengaran : <?php echo $awal_keperawatan->pemeriksaan_neurologi_pendengaran; ?> | Bicara : <?php echo $awal_keperawatan->pemeriksaan_neurologi_bicara; ?>, <?php echo $awal_keperawatan->pemeriksaan_neurologi_bicara_keterangan; ?><br>
                                                  Otot : <?php echo $awal_keperawatan->pemeriksaan_neurologi_kekuatan_otot; ?>
                                                </td>
                                              </tr>
                                              <tr align='center'>
                                                <td rowspan="2" colspan="2" bgcolor='#f8fdf3'>Integument</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kulit</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Warna Kulit</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Turgor</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Resiko Decubi</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  ><?php echo $awal_keperawatan->pemeriksaan_integument_kulit; ?></td>
                                                <td valign='top'><?php echo $awal_keperawatan->pemeriksaan_integument_warnakulit; ?></td>
                                                <td valign='top'><?php echo $awal_keperawatan->pemeriksaan_integument_turgor; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_integument_dekubitas; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td rowspan="2"    bgcolor='#f8fdf3'>Muskuloskletal</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Oedema</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Pergerakan Sendi</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kekuatan Otot</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Fraktur</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Nyeri Sendi</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_oedema; ?>, <?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_oedema_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_pergerakan_sendi; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_kekauatan_otot; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_fraktur; ?>, <?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_fraktur_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_nyeri_sendi; ?>, <?php echo $awal_keperawatan->pemeriksaan_muskuloskletal_nyeri_sendi_keterangan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td rowspan="2"   colspan="2" bgcolor='#f8fdf3'>Eliminasi</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>BAB </td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>BAK</td>
                                              </tr>
                                              <tr align='center'>
                                                <td colspan="2" valign='top'>Frekuensi :<?php echo $awal_keperawatan->pemeriksaan_eliminasi_bab_frekuensi_jumlah; ?> x/<?php echo $awal_keperawatan->pemeriksaan_eliminasi_bab_frekuensi_durasi; ?> | Konsistensi : <?php echo $awal_keperawatan->pemeriksaan_eliminasi_bab_konsistensi; ?> | Warna : <?php echo $awal_keperawatan->pemeriksaan_eliminasi_bab_warna; ?></td>
                                                <td colspan="2" valign='top'>Frekuensi :<?php echo $awal_keperawatan->pemeriksaan_eliminasi_bak_frekuensi_jumlah; ?> x/<?php echo $awal_keperawatan->pemeriksaan_eliminasi_bak_frekuensi_durasi; ?> | Warna : <?php echo $awal_keperawatan->pemeriksaan_eliminasi_bak_warna; ?> | Lain-lain : <?php echo $awal_keperawatan->pemeriksaan_eliminasi_bak_lainlain; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="6" >III. POLA KEHIDUPAN SEHARI-HARI</td>
                                              </tr>
                                              <tr align='center'>
                                                <td rowspan="2"    bgcolor='#f8fdf3'>Pola Aktifitas</td>
                                                <td valign='top'  bgcolor='#f8fdf3'>Mandi</td>
                                                <td valign='top'  bgcolor='#f8fdf3'>Makan/Minum</td>
                                                <td valign='top'  bgcolor='#f8fdf3'>Berpakaian</td>
                                                <td valign='top'  bgcolor='#f8fdf3'>Eliminasi</td>
                                                <td valign='top'  bgcolor='#f8fdf3'>Berpindah</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' ><?php echo $awal_keperawatan->pola_aktifitas_mandi; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pola_aktifitas_makanminum; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pola_aktifitas_berpakaian; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pola_aktifitas_eliminasi; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->pola_aktifitas_berpindah; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td rowspan="2"   bgcolor='#f8fdf3'>Pola Nutrisi</td>
                                                <td valign='top'  bgcolor='#f8fdf3'>Porsi Makan</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Frekuensi Makan</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Jenis Makanan</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' ><?php echo $awal_keperawatan->pola_nutrisi_porsi_makan; ?> Porsi</td>
                                                <td valign='top' colspan="2"><?php echo $awal_keperawatan->pola_nutrisi_frekuesi_makan; ?> x/hari</td>
                                                <td valign='top' colspan="2"><?php echo $awal_keperawatan->pola_nutrisi_jenis_makanan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td rowspan="2"   bgcolor='#f8fdf3'>Pola Tidur</td>
                                                <td valign='top'  colspan="5"bgcolor='#f8fdf3'>Lama Tidur</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="5"><?php echo $awal_keperawatan->pola_tidur_lama_tidur; ?> jam/hari, <?php echo $awal_keperawatan->pola_tidur_gangguan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="6" >IV. PENGKAJIAN FUNGSI</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Kemampuan Aktifitas Sehari-hari</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Berjalan</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Aktifitas</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_kemampuan_sehari; ?></td>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_berjalan; ?>, <?php echo $awal_keperawatan->pengkajian_fungsi_berjalan_keterangan; ?></td>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_aktifitas; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Alat Ambilasi</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Ekstremitas Atas</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Ekstremitas Bawah</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_ambulasi; ?></td>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_ekstrimitas_atas; ?>, <?php echo $awal_keperawatan->pengkajian_fungsi_ekstrimitas_atas_keterangan; ?></td>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_ekstrimitas_bawah; ?>, <?php echo $awal_keperawatan->pengkajian_fungsi_ekstrimitas_bawah_keterangan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Kemampuan Menggenggam</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Kemampuan Koordinasi</td>
                                                <td valign='top'  colspan="2" bgcolor='#f8fdf3'>Kesimpulan Gangguan Fungsi</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_menggenggam; ?>, <?php echo $awal_keperawatan->pengkajian_fungsi_menggenggam_keterangan; ?></td>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_koordinasi; ?>, <?php echo $awal_keperawatan->pengkajian_fungsi_koordinasi_keterangan; ?></td>
                                                <td valign='top'   colspan="2"><?php echo $awal_keperawatan->pengkajian_fungsi_kesimpulan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="6" >V. RIWAYAT PSIKOLOGIS - SOSIAL - EKONOMI - BUDAYA - SPIRITUAL</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kondisi Psikologis</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Gangguan Jiwa</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Perilaku</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Hubungan Keluarga</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Tinggal</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kepercayaan</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' ><?php echo $awal_keperawatan->riwayat_psiko_kondisi_psiko; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->riwayat_psiko_gangguan_jiwa; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->riwayat_psiko_perilaku; ?>, <?php echo $awal_keperawatan->riwayat_psiko_perilaku_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->riwayat_psiko_hubungan_keluarga; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->riwayat_psiko_tinggal; ?>, <?php echo $awal_keperawatan->riwayat_psiko_tinggal_keterangan; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->riwayat_psiko_nilai_kepercayaan; ?>, <?php echo $awal_keperawatan->riwayat_psiko_nilai_kepercayaan_keterangan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="3"  bgcolor='#f8fdf3'>Pendidikan PJ</td>
                                                <td valign='top' colspan="3"  bgcolor='#f8fdf3'>Edukasi Diberikan</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'  colspan="3"><?php echo $awal_keperawatan->riwayat_psiko_pendidikan_pj; ?></td>
                                                <td valign='top'  colspan="3"><?php echo $awal_keperawatan->riwayat_psiko_edukasi_diberikan; ?>, <?php echo $awal_keperawatan->riwayat_psiko_edukasi_diberikan_keterangan; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="6" >VI. PENILAIAN TINGKAT NYERI</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top'   bgcolor='#f8fdf3'>Nyeri</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Penyebab</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Kualitas</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Lokasi</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Menyebar</td>
                                                <td valign='top'   bgcolor='#f8fdf3'>Skala | Durasi(menit)</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' ><?php echo $awal_keperawatan->penilaian_nyeri; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->penilaian_nyeri_penyebab; ?>, <?php echo $awal_keperawatan->penilaian_nyeri_ket_penyebab; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->penilaian_nyeri_kualitas; ?>, <?php echo $awal_keperawatan->penilaian_nyeri_ket_kualitas; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->penilaian_nyeri_lokasi; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->penilaian_nyeri_menyebar; ?></td>
                                                <td valign='top' ><?php echo $awal_keperawatan->penilaian_nyeri_skala; ?> | <?php echo $awal_keperawatan->penilaian_nyeri_waktu; ?></td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="2"  bgcolor='#f8fdf3'>Nyeri Hilang Bila</td>
                                                <td valign='top' colspan="2" bgcolor='#f8fdf3'>Diberitahukan Kepada Dokter</td>
                                                <td valign='top' colspan="2" bgcolor='#f8fdf3'>Jam</td>
                                              </tr>
                                              <tr align='center'>
                                                <td valign='top' colspan="2"><?php echo $awal_keperawatan->penilaian_nyeri_hilang; ?><?php echo $awal_keperawatan->penilaian_nyeri_ket_hilang; ?></td>
                                                <td valign='top' colspan="2"><?php echo $awal_keperawatan->penilaian_nyeri_diberitahukan_dokter; ?></td>
                                                <td valign='top' colspan="2"><?php echo $awal_keperawatan->penilaian_nyeri_jam_diberitahukan_dokter; ?></td>
                                              </tr>
                                          </table>
                                         
                                  </td>
                                </tr>
                                 <?php } ?>
                          <!-- End Awal Keperawatan Umum -->

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
                                      </form>2
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                          <!-- End Awal Medis Umum  -->

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