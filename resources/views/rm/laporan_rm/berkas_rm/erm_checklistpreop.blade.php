<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Check List Pre Operasi</title>

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

</head>

<style>
  table td, table th {
    padding: 5px;
  }
  
</style>

<body>
  <h5 style="color:BLUE;">ERM - Check List Pre Operasi</h5>

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
            <table border="1px" style="width:100%;">
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
                <td>Pemeriksaan</td>
                <td>:</td>
                <td>Check List Pre Operasi</td>
              </tr>
              <tr>  
                <td></td>
                <td></td>
                <td>
                  <?php if (!$checklistpreop->isEmpty()) { ?>
                    <?php foreach($checklistpreop as $i) { ?>
                      <table border="1px" style="width:100%;">
                        <tr><th>Tanggal</th><td>>></td><td><?php echo $i->tanggal ?? '-'; ?></td></tr>
                        <tr><th>SCNC</th><td>>></td><td><?php echo $i->sncn ?? '-'; ?></td></tr>
                        <tr><th>tindakan</th><td>>></td><td><?php echo $i->tindakan ?? '-'; ?></td></tr>
                        <tr><th>Dokter Bedah</th><td>>></td><td><?php echo $i->nama_dokter_bedah ?? '-'; ?></td></tr>
                        <tr><th>Dokter Anestesi</th><td>>></td><td><?php echo $i->nama_dokter_anestesi ?? '-'; ?></td></tr>
                        <tr><th>identitas</th><td>>></td><td><?php echo $i->identitas ?? '-'; ?></td></tr>
                        <tr><th>surat_izin_bedah</th><td>>></td><td><?php echo $i->surat_ijin_bedah ?? '-'; ?></td></tr>
                        <tr><th>surat_izin_anestesi</th><td>>></td><td><?php echo $i->surat_ijin_anestesi ?? '-'; ?></td></tr>
                        <tr><th>surat_izin_transfusi</th><td>>></td><td><?php echo $i->surat_ijin_transfusi ?? '-'; ?></td></tr>
                        <tr><th>penandaan_area_operasi</th><td>>></td><td><?php echo $i->penandaan_area_operasi ?? '-'; ?></td></tr>
                        <tr><th>keadaan_umum</th><td>>></td><td><?php echo $i->keadaan_umum ?? '-'; ?></td></tr>
                        <tr><th>Pemeriksaan Penunjang Rontgen</th><td>>></td><td><?php echo $i->pemeriksaan_penunjang_rontgen ?? '-'; ?></td></tr>
                        <tr><th>Keterangan Pemeriksaan Penunjang Rontgen</th><td>>></td><td><?php echo $i->keterangan_pemeriksaan_penunjang_rontgen ?? '-'; ?></td></tr>
                        <tr><th>Pemeriksaan Penunjang EKG</th><td>>></td><td><?php echo $i->pemeriksaan_penunjang_ekg ?? '-'; ?></td></tr>
                        <tr><th>Keterangan Pemeriksaan Penunjang EKG</th><td>>></td><td><?php echo $i->keterangan_pemeriksaan_penunjang_ekg ?? '-'; ?></td></tr>
                        <tr><th>Pemerisaan Penunjang USG</th><td>>></td><td><?php echo $i->pemeriksaan_penunjang_usg ?? '-'; ?></td></tr>
                        <tr><th>Keterangan Pemerisaan Penunjang USG </th><td>>></td><td><?php echo $i->keterangan_pemeriksaan_penunjang_usg ?? '-'; ?></td></tr>
                        <tr><th>Pemeriksaan Penunjang CTSCAN</th><td>>></td><td><?php echo $i->pemeriksaan_penunjang_ctscan ?? '-'; ?></td></tr>
                        <tr><th>Keterangan Pemeriksaan Penunjang CTSCAN</th><td>>></td><td><?php echo $i->keterangan_pemeriksaan_penunjang_ctscan ?? '-'; ?></td></tr>
                        <tr><th>Pemeriksaan Penunjang MRI</th><td>>></td><td><?php echo $i->pemeriksaan_penunjang_mri ?? '-'; ?></td></tr>
                        <tr><th>Keterangan Pemeriksaan Penunjang MRI</th><td>>></td><td><?php echo $i->keterangan_pemeriksaan_penunjang_mri ?? '-'; ?></td></tr>
                        <tr><th>Persiapan Darah</th><td>>></td><td><?php echo $i->persiapan_darah ?? '-'; ?></td></tr>
                        <tr><th>Keterangan Persiapan Darah</th><td>>></td><td><?php echo $i->keterangan_persiapan_darah ?? '-'; ?></td></tr>
                        <tr><th>Perlengkapan Khusus</th><td>>></td><td><?php echo $i->perlengkapan_khusus ?? '-'; ?></td></tr>
                        <tr><th>Petugas Ruangan</th><td>>></td><td><?php echo $i->nama_petugas_ruangan ?? '-'; ?></td></tr>
                        <tr><th>Perawat OK</th><td>>></td><td><?php echo $i->nama_perawat_ok ?? '-'; ?></td></tr>
                      </table>
                      <br>
                    <?php } ?>
                  <?php } else { ?>
                    Tidak ada data Penilaian Pre Operasi.
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