<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Penilaian Pre Anestesi / Sedasi</title>

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
  <h5 style="color:BLUE;">ERM - Penilaian Pre Anestesi / Sedasi</h5>

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
                <td>Penilaian Pre Anestesi / Sedasi</td>
              </tr>
              <tr>  
                <td></td>
                <td></td>
                <td>
                  <?php if (!$penilaianprean->isEmpty()) { ?>
                    <?php foreach($penilaianprean as $i) { ?>
                      <table border="1px" style="width:100%;">
                          <tr><th>Tanggal</th><td><?php echo $i->tanggal ?? '-'; ?></td></tr>
                          <tr><th>Nama Dokter</th><td><?php echo $i->dokterprean ?? '-'; ?></td></tr>
                          <tr><th>Diagnosa</th><td><?php echo $i->diagnosa ?? '-'; ?></td></tr>
                          <tr><th>Rencana Tindakan</th><td><?php echo $i->rencana_tindakan ?? '-'; ?></td></tr>
                          <tr><th>Tb</th><td><?php echo $i->tb ?? '-'; ?></td></tr>
                          <tr><th>Bb</th><td><?php echo $i->bb ?? '-'; ?></td></tr>
                          <tr><th>Td</th><td><?php echo $i->td ?? '-'; ?></td></tr>
                          <tr><th>Io2</th><td><?php echo $i->io2 ?? '-'; ?></td></tr>
                          <tr><th>Nadi</th><td><?php echo $i->nadi ?? '-'; ?></td></tr>
                          <tr><th>Pernapasan</th><td><?php echo $i->pernapasan ?? '-'; ?></td></tr>
                          <tr><th>Suhu</th><td><?php echo $i->suhu ?? '-'; ?></td></tr>
                          <tr><th>Fisik Cardiovasculer</th><td><?php echo $i->fisik_cardiovasculer ?? '-'; ?></td></tr>
                          <tr><th>Fisik Paru</th><td><?php echo $i->fisik_paru ?? '-'; ?></td></tr>
                          <tr><th>Fisik Abdomen</th><td><?php echo $i->fisik_abdomen ?? '-'; ?></td></tr>
                          <tr><th>Fisik Extrimitas</th><td><?php echo $i->fisik_extrimitas ?? '-'; ?></td></tr>
                          <tr><th>Fisik Endokrin</th><td><?php echo $i->fisik_endokrin ?? '-'; ?></td></tr>
                          <tr><th>Fisik Ginjal</th><td><?php echo $i->fisik_ginjal ?? '-'; ?></td></tr>
                          <tr><th>Fisik Obatobatan</th><td><?php echo $i->fisik_obatobatan ?? '-'; ?></td></tr>
                          <tr><th>Fisik Laborat</th><td><?php echo $i->fisik_laborat ?? '-'; ?></td></tr>
                          <tr><th>Fisik Penunjang</th><td><?php echo $i->fisik_penunjang ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Penyakit Alergiobat</th><td><?php echo $i->riwayat_penyakit_alergiobat ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Penyakit Alergilainnya</th><td><?php echo $i->riwayat_penyakit_alergilainnya ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Penyakit Terapi</th><td><?php echo $i->riwayat_penyakit_terapi ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Kebiasaan Merokok</th><td><?php echo $i->riwayat_kebiasaan_merokok ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Kebiasaan Ket Merokok</th><td><?php echo $i->riwayat_kebiasaan_ket_merokok ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Kebiasaan Alkohol</th><td><?php echo $i->riwayat_kebiasaan_alkohol ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Kebiasaan Ket Alkohol</th><td><?php echo $i->riwayat_kebiasaan_ket_alkohol ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Kebiasaan Obat</th><td><?php echo $i->riwayat_kebiasaan_obat ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Kebiasaan Ket Obat</th><td><?php echo $i->riwayat_kebiasaan_ket_obat ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Medis Cardiovasculer</th><td><?php echo $i->riwayat_medis_cardiovasculer ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Medis Respiratory</th><td><?php echo $i->riwayat_medis_respiratory ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Medis Endocrine</th><td><?php echo $i->riwayat_medis_endocrine ?? '-'; ?></td></tr>
                          <tr><th>Riwayat Medis Lainnya</th><td><?php echo $i->riwayat_medis_lainnya ?? '-'; ?></td></tr>
                          <tr><th>Asa</th><td><?php echo $i->asa ?? '-'; ?></td></tr>
                          <tr><th>Puasa</th><td><?php echo $i->puasa ?? '-'; ?></td></tr>
                          <tr><th>Rencana Anestesi</th><td><?php echo $i->rencana_anestesi ?? '-'; ?></td></tr>
                          <tr><th>Rencana Perawatan</th><td><?php echo $i->rencana_perawatan ?? '-'; ?></td></tr>
                          <tr><th>Catatan Khusus</th><td><?php echo $i->catatan_khusus ?? '-'; ?></td></tr>
                      </table>
                      <br>
                    <?php } ?>
                  <?php } else { ?>
                    Tidak ada data Penilaian Pre Anestesi.
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