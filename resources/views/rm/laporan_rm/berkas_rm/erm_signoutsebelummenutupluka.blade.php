<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Sign Out Sebelum Menutup Luka</title>

  <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">

  <!-- JQuery DataTable Css -->
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">

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
  <h5 style="color:BLUE;">ERM - Sign Out Sebelum Menutup Luka</h5>

  <div class="table-responsive">
    <table class="table table-bordered table-striped" style="width:100%;">
      <thead>
        <tr>
          <th style="text-align: left;">Riwayat</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <table class="table table-bordered" style="width:100%;">
              <tr>
                <td style="width: 20%;">No Rawat</td>
                <td style="width: 1%;">:</td>
                <td>{{ $data->no_rawat }}</td>
              </tr>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td>{{ $data->tgl_registrasi ?? '-' }} | {{ $data->jam_reg ?? '-' }}</td>
              </tr>
              <tr>
                <td>Poliklinik</td>
                <td>:</td>
                <td>Ranap</td>
              </tr>
              <tr>
                <td>Sign Out Sebelum Menutup Luka</td>
                <td>:</td>
                <td>
                  <?php if (!$signoutsebelummenutupluka->isEmpty()) { ?>
                    <?php foreach($signoutsebelummenutupluka as $i) { ?>
                      <table class="table table-bordered sub-table" style="width:100%;">
                        <tr><th>Tanggal dan Jam</th><td><?php echo $i->tanggal ?? '-'; ?></td></tr>
                        <tr><th>SNCN</th><td><?php echo $i->sncn ?? '-'; ?></td></tr>
                        <tr><th>Tindakan</th><td><?php echo $i->tindakan ?? '-'; ?></td></tr>
                        <tr><th>Dokter Bedah</th><td><?php echo $i->nama_dokter_bedah ?? '-'; ?></td></tr>
                        <tr><th>Dokter Anestesi</th><td><?php echo $i->nama_dokter_anestesi ?? '-'; ?></td></tr>
                        <tr><th>Verbal Tindakan</th><td><?php echo $i->verbal_tindakan ?? '-'; ?></td></tr>
                        <tr><th>Verbal Kelengkapan Kasa</th><td><?php echo $i->verbal_kelengkapan_kasa ?? '-'; ?></td></tr>
                        <tr><th>Verbal Instrumen</th><td><?php echo $i->verbal_instrumen ?? '-'; ?></td></tr>
                        <tr><th>Verbal Alat Tajam</th><td><?php echo $i->verbal_alat_tajam ?? '-'; ?></td></tr>
                        <tr><th>Kelengkapan Spesimen Label</th><td><?php echo $i->kelengkapan_specimen_label ?? '-'; ?></td></tr>
                        <tr><th>Kelengkapan Spesimen Formulir</th><td><?php echo $i->kelengkapan_specimen_formulir ?? '-'; ?></td></tr>
                        <tr><th>Peninjauan Kegiatan Dokter Bedah</th><td><?php echo $i->peninjauan_kegiatan_dokter_bedah ?? '-'; ?></td></tr>
                        <tr><th>Peninjauan Kegiatan Dokter Anestesi</th><td><?php echo $i->peninjauan_kegiatan_dokter_anestesi ?? '-'; ?></td></tr>
                        <tr><th>Peninjauan</th><td><?php echo $i->peninjauan_kegiatan_perawat_kamar_ok ?? '-'; ?></td></tr>
                        <tr><th>Perhatian Utama Fase Pemulihan</th><td><?php echo $i->perhatian_utama_fase_pemulihan ?? '-'; ?></td></tr>
                        <tr><th>Nama Perawat</th><td><?php echo $i->nama_perawat_ok ?? '-'; ?></td></tr>
                      </table>
                      <br>
                    <?php } ?>
                  <?php } else { ?>
                    Tidak ada data Sign Out Sebelum Menutup Luka.
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
