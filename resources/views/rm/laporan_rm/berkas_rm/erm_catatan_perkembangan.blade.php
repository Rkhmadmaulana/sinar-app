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