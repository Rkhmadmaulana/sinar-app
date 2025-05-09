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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">

  <!-- DataTables CSS -->
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">

  <style>
    table td, table th {
      padding: 5px;
    }
  </style>
</head>

<body>
  <!-- <div class="container mt-4"> -->
    <h5 class="text-primary">ERM Ranap</h5>

    <div class="table-responsive">
      <table id="erm" class="table table-bordered table-striped w-100">
        <thead>
          <tr>
            <th>Riwayat</th>
          </tr>
        </thead>
      </table>

      <table class="table table-bordered mt-3">
        <tr>
          <td class="w-25">No Rawat</td>
          <td class="w-5">:</td>
          <td><?php echo $row->no_rawat; ?></td>
        </tr>
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
  <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">
    Pemeriksaan Radiologi
  </td>
  <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>
  <td style="width: 79%; padding: 2px;">

    <!-- Radiologi -->
    <?php $uniqueRadiologi = $radiologi->unique(function ($item) {
      return $item->tgl_periksa . '|' . $item->jam . '|' . $item->hasil;
    }); ?>

    <?php foreach($uniqueRadiologi as $rad): ?>
      <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0; margin-bottom: 10px;">
        <tr>
          <th style="width: 30%; background-color: #FFFAF8; padding: 4px;">Tanggal Periksa</th>
          <td style="padding: 4px;"><?php echo $rad->tgl_periksa; ?></td>
        </tr>
        <tr>
          <th style="width: 30%; background-color: #FFFAF8; padding: 4px;">Jam Periksa</th>
          <td style="padding: 4px;"><?php echo $rad->jam; ?></td>
        </tr>
        <tr>
          <th style="background-color: #FFFAF8; padding: 4px;">Dokter Perujuk</th>
          <td style="padding: 4px;"><?php echo $rad->nm_dokter; ?></td>
        </tr>
        <tr>
          <th style="width: 30%; background-color: #FFFAF8; padding: 4px;">Hasil Bacaan Radiologi</th>
          <td style="padding: 4px;"><?php echo $rad->hasil; ?></td>
        </tr>
      </table>
    <?php endforeach; ?>
  </td>
</tr>
<tr>
  <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">
    Pemeriksaan Laboratorium
  </td>
  <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>
  <td style="width: 79%; padding: 2px;">
    <?php foreach($lab as $group): ?>
      <table border="1" style="width:100%; margin-bottom: 20px; border-collapse: collapse;">
        <thead style="background-color: #f2f2f2;">
          <tr>
            <th colspan="5" style="padding: 6px; text-align: left;">
              <strong>No Order:</strong> <?php echo $group->noorder; ?> |
              <strong>Tanggal Permintaan:</strong> <?php echo $group->tgl_permintaan . ' ' . $group->jam_permintaan; ?> |
              <strong>Dokter Perujuk:</strong> <?php echo $group->nm_dokter; ?>
            </th>
          </tr>
          <tr>
            <th style="padding: 6px;">Pemeriksaan</th>
            <th style="padding: 6px;">Hasil</th>
            <th style="padding: 6px;">Satuan</th>
            <th style="padding: 6px;">Nilai Rujukan</th>
            <th style="padding: 6px;">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $pemeriksaan = explode('|', $group->daftar_pemeriksaan);
            $nilai = explode('|', $group->daftar_nilai);
            $satuan = explode('|', $group->daftar_satuan);
            $rujukan = explode('|', $group->daftar_rujukan);
            $keterangan = explode('|', $group->daftar_keterangan);
            for ($i = 0; $i < count($pemeriksaan); $i++):
          ?>
            <tr>
              <td style="padding: 4px;"><?php echo $pemeriksaan[$i]; ?></td>
              <td style="padding: 4px;"><?php echo $nilai[$i]; ?></td>
              <td style="padding: 4px;"><?php echo $satuan[$i]; ?></td>
              <td style="padding: 4px;"><?php echo $rujukan[$i]; ?></td>
              <td style="padding: 4px;"><?php echo $keterangan[$i]; ?></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  </td>
</tr>
  <!-- </div> -->
</body>

</html>
