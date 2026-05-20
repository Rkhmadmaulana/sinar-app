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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('public/vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{asset('public/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <!-- <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet"> -->

  <!-- JQuery DataTable Css -->
  <link href="{{asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('public/vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css')}}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{asset('public/css/style.css')}}" rel="stylesheet">

  <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
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
  <h5 style="color:BLUE;">ERM Ranap - Laporan Sedasi</h5>
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
                <td style="width: 20%; text-align: left; vertical-align: top;">Laporan Sedasi</td>  
                  <td style="width: 1%;  vertical-align: top;">:</td>  
                  <td style="width:79%;">
                  <?php 
                  foreach($laporan_sedasi as $sedasi){ ?>
                  <table border="1px"  style="width:100%;">
                      <tr>
                        <th bgcolor='#FFFAF8'>Tanggal</th>
                        <th bgcolor='#FFFAF8'>Jam Mulai</th>
                        <th bgcolor='#FFFAF8'>Jam Selesai</th>
                      </tr>
                      <tr>
                        <td><?php echo $sedasi->tanggal_tindakan; ?></td>
                        <td><?php echo $sedasi->jam_mulai; ?></td>
                        <td><?php echo $sedasi->jam_selesai; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Diagnosis</th>
                        <td colspan="9"><?php echo $sedasi->diagnosis; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Tindakan</th>
                        <td colspan="9"><?php echo $sedasi->tindakan; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Level sedasi</th>
                        <td colspan="9"><?php echo $sedasi->level_sedasi; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Obat sedasi yang diberikan</th>
                        <td colspan="9"><?php echo $sedasi->obat_sedasi; ?></td>
                      </tr>
                      <?php
                        // mapping deskripsi score
                        $mapping = [
                        'tanda_vital' => [
                            2 => '< 20% dari tanda vital sebelum tindakan',
                            1 => '20-40% dari tanda vital sebelum tindakan',
                            0 => '> 20% dari tanda vital sebelum tindakan',
                        ],
                        'aktivitas' => [
                            2 => 'Sadar penuh dan dapat bergerak sendiri',
                            1 => 'Sadar, penuh membutuhkan bantuan',
                            0 => 'Tidak sadar',
                        ],
                        'mual' => [
                            2 => 'Minimal, membutuhkan terapi oral',
                            1 => 'Moderate, membutuhkan terapi parental',
                            0 => 'Berat, membutuhkan terapi lanjut',
                        ],
                        'nyeri' => [
                            2 => 'Nyeri, dapat dikontrol dengan terapi oral',
                            1 => 'Nyeri, tidak dapat dikontrol dengan terapi oral',
                        ],
                        'perdarahan' => [
                            2 => 'Minimal, tidak perlu ganti balutan',
                            1 => 'Moderate, perlu 2 kali ganti balutan',
                            0 => 'Banyak, perlu ≥ 3 kali ganti balutan',
                        ],
                        ];
                        ?>
                        <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Tanda Vital (TD dan Nadi)</th>
                        <td colspan="9">
                            <?php $s = $sedasi->tanda_vital_score; ?>
                            <?= $s . ' - ' . ($mapping['tanda_vital'][$s] ?? ''); ?>
                        </td>
                        </tr>
                        <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Aktivitas</th>
                        <td colspan="9">
                            <?php $s = $sedasi->aktivitas_score; ?>
                            <?= $s . ' - ' . ($mapping['aktivitas'][$s] ?? ''); ?>
                        </td>
                        </tr>
                        <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Mual/Muntah</th>
                        <td colspan="9">
                            <?php $s = $sedasi->mual_score; ?>
                            <?= $s . ' - ' . ($mapping['mual'][$s] ?? ''); ?>
                        </td>
                        </tr>
                        <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Nyeri</th>
                        <td colspan="9">
                            <?php $s = $sedasi->nyeri_score; ?>
                            <?= $s . ' - ' . ($mapping['nyeri'][$s] ?? ''); ?>
                        </td>
                        </tr>
                        <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Perdarahan dari luka operasi</th>
                        <td colspan="9">
                            <?php $s = $sedasi->perdarahan_score; ?>
                            <?= $s . ' - ' . ($mapping['perdarahan'][$s] ?? ''); ?>
                        </td>
                        </tr>

                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Total Skor</th>
                        <td colspan="9"><?php echo $sedasi->total_score; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Dokter Anestesi</th>
                        <td colspan="9"><?php echo $sedasi->nm_dokter; ?></td>
                      </tr>
                  </table><br>
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
