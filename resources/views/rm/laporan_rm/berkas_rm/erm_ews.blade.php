<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - EWS</title>

  <!-- Favicons -->
  <link href="<?= asset('img/favicon.png'); ?>" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="<?= asset('vendor/bootstrap/css/bootstrap11.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/boxicons/css/boxicons.min.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/quill/quill.snow.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/quill/quill.bubble.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/remixicon/remixicon.css'); ?>" rel="stylesheet">
  
  <!-- JQuery DataTable CSS -->
  <link href="<?= asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css'); ?>" rel="stylesheet">
  <link href="<?= asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css'); ?>" rel="stylesheet">

  <!-- Template Main CSS -->
  <link href="<?= asset('css/style.css'); ?>" rel="stylesheet">

  <style>
    table td,
    table th {
      padding: 5px;
    }

    th {
      background-color: #FFFAF8;
    }

    h5 {
      color: blue;
    }

    .sub-table th {
      width: 30%;
    }

    .sub-table td, .sub-table th {
      padding: 2px;
      vertical-align: top;
    }

    .main-table td {
      vertical-align: top;
    }
  </style>
</head>

<body>

  <h5>ERM - Ranap</h5>

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
            <table class="table table-bordered main-table" style="width:100%;">
              <tr>
                <td style="width: 20%;">No Rawat</td>
                <td style="width: 1%;">:</td>
                <td><?= $row->no_rawat; ?></td>
              </tr>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
              </tr>
              <tr>
                <td>Nama Pasien</td>
                <td>:</td>
                <td><?= $row->nm_pasien; ?></td>
              </tr>
              <tr>
                <td>Data EWS Neonatus/PEWS Anak/PEWS Dewasa/MEOWS Obstetri</td>
                <td>:</td>
                <td>
                  <?php foreach ($ews as $e) { ?>
                    <table class="table table-bordered sub-table" style="width:100%; border-collapse: collapse;">
                      <tr>
                        <th>Tanggal</th>
                        <td><?= $e->tanggal ?? '-'; ?></td>
                      </tr>

                      <?php if ($table === 'pemantauan_pews_anak') { ?>
                        <tr>
                            <th>Parameter Perilaku</th>
                            <td><?= $e->parameter_perilaku; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Perilaku</th>
                            <td><?= $e->skor_perilaku; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Crt atau Warna Kulit</th>
                            <td><?= $e->parameter_crt_atau_warna_kulit; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Crt atau Warna Kulit</th>
                            <td><?= $e->skor_crt_atau_warna_kulit; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Perespirasi</th>
                            <td><?= $e->parameter_perespirasi; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Perespirasi</th>
                            <td><?= $e->skor_perespirasi; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Total</th>
                            <td><?= $e->skor_total; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Total</th>
                            <td><?= $e->parameter_total; ?></td>
                        </tr>
                        <tr>
                            <th>Petugas</th>
                            <td><?= $e->nama; ?></td>
                        </tr>

                      <?php } elseif ($table === 'pemantauan_pews_dewasa') { ?>
                        <tr>
                            <th>Parameter Laju Respirasi</th>
                            <td><?= $e->parameter_laju_respirasi; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Laju Respirasi</th>
                            <td><?= $e->skor_laju_respirasi; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Saturasi Oksigen</th>
                            <td><?= $e->parameter_saturasi_oksigen; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Saturasi Oksigen</th>
                            <td><?= $e->skor_saturasi_oksigen; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Suplemen Oksigen</th>
                            <td><?= $e->parameter_suplemen_oksigen; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Suplemen Oksigen</th>
                            <td><?= $e->skor_suplemen_oksigen; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Tekanan Darah Sistolik</th>
                            <td><?= $e->parameter_tekanan_darah_sistolik; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Tekanan Darah Sistolik</th>
                            <td><?= $e->skor_tekanan_darah_sistolik; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Laju Jantung</th>
                            <td><?= $e->parameter_laju_jantung; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Kesadaran</th>
                            <td><?= $e->parameter_kesadaran; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Kesadaran</th>
                            <td><?= $e->skor_kesadaran; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Temparatur</th>
                            <td><?= $e->parameter_temperatur; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Temperatur</th>
                            <td><?= $e->skor_temperatur; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Total</th>
                            <td><?= $e->skor_total; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Total</th>
                            <td><?= $e->parameter_total; ?></td>
                        </tr>
                        <tr>
                            <th>Petugas</th>
                            <td><?= $e->nama; ?></td>
                        </tr>

                      <?php } elseif ($table === 'pemantauan_ews_neonatus') { ?>
                        <tr>
                            <th>Parameter 1</th>
                            <td><?= $e->parameter1; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 1</th>
                            <td><?= $e->skor1; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter 2</th>
                            <td><?= $e->parameter2; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 2</th>
                            <td><?= $e->skor2; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter 3</th>
                            <td><?= $e->parameter3; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 3</th>
                            <td><?= $e->skor3; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter 4</th>
                            <td><?= $e->parameter4; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 4</th>
                            <td><?= $e->skor4; ?></td>
                        </tr><tr>
                            <th>Parameter 5</th>
                            <td><?= $e->parameter5; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 5</th>
                            <td><?= $e->skor5; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter 6</th>
                            <td><?= $e->parameter6; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 6</th>
                            <td><?= $e->skor6; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter 7</th>
                            <td><?= $e->parameter7; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 7</th>
                            <td><?= $e->skor7; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter 8</th>
                            <td><?= $e->parameter8; ?></td>
                        </tr>
                        <tr>
                            <th>Skor 8</th>
                            <td><?= $e->skor8; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Total</th>
                            <td><?= $e->skor_total; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Total</th>
                            <td><?= $e->parameter_total; ?></td>
                        </tr>
                        <tr>
                            <th>Code Blue</th>
                            <td><?= $e->code_blue; ?></td>
                        </tr>
                        <tr>
                            <th>Petugas</th>
                            <td><?= $e->nama; ?></td>
                        </tr>

                      <?php } elseif ($table === 'pemantauan_meows_obstetri') { ?>
                        <tr>
                            <th>Parameter Pernapasan</th>
                            <td><?= $e->parameter_pernapasan; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Pernapasan</th>
                            <td><?= $e->skor_pernapasan; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Saturasi</th>
                            <td><?= $e->parameter_saturasi; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Saturasi</th>
                            <td><?= $e->skor_saturasi; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Temperatur</th>
                            <td><?= $e->parameter_temperatur; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Temperatur</th>
                            <td><?= $e->skor_temperatur; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Tekanan Darah Sistole</th>
                            <td><?= $e->parameter_tekanan_darah_sistole; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Tekanan Darah Sistolik</th>
                            <td><?= $e->skor_tekanan_darah_sistole; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Tekanan Darah Diastole</th>
                            <td><?= $e->parameter_tekanan_darah_diastole; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Tekanan Darah Diastole</th>
                            <td><?= $e->skor_tekanan_darah_diastole; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Denyut Jantung</th>
                            <td><?= $e->parameter_denyut_jantung; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Kesadaran</th>
                            <td><?= $e->parameter_kesadaran; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Kesadaran</th>
                            <td><?= $e->skor_kesadaran; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Ketuban</th>
                            <td><?= $e->parameter_ketuban; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Ketuban</th>
                            <td><?= $e->skor_ketuban; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Discharge</th>
                            <td><?= $e->parameter_discharge; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Proteinuria</th>
                            <td><?= $e->parameter_proteinuria; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Proteinuria</th>
                            <td><?= $e->skor_proteinuria; ?></td>
                        </tr>
                        <tr>
                            <th>Skor Total</th>
                            <td><?= $e->skor_total; ?></td>
                        </tr>
                        <tr>
                            <th>Parameter Total</th>
                            <td><?= $e->parameter_total; ?></td>
                        </tr>
                        <tr>
                            <th>Code Blue</th>
                            <td><?= $e->code_blue; ?></td>
                        </tr>
                        <tr>
                            <th>Petugas</th>
                            <td><?= $e->nama; ?></td>
                        </tr>
                      <?php } ?>
                    </table>
                    <br>
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
