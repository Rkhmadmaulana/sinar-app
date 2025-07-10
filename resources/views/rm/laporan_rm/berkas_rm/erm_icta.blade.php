<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Informed Consent</title>

  <!-- Favicons -->
  <link href="{{ asset('public/img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('public/vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/remixicon/remixicon.css') }}" rel="stylesheet">

  <!-- JQuery DataTable Css -->
  <link href="{{ asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ asset('public/css/style.css') }}" rel="stylesheet">

  <style>
    table td,
    table th {
      padding: 5px;
    }
    .sub-table th {
      background-color: #fffaf8;
      padding: 2px;
      width: 30%;
    }
    .sub-table td {
      padding: 2px;
    }
  </style>
</head>

<body>
  <h5 style="color: blue;">ERM - Informed Consent</h5>

  <div class="table-responsive">
    <table class="table table-bordered table-striped" style="width: 100%;">
      <thead>
        <tr>
          <th style="text-align: left;">Riwayat</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <table class="table table-bordered" style="width: 100%;">
              <tr>
                <td style="width: 20%;">No Rawat</td>
                <td style="width: 1%;">:</td>
                <td>{{ $row->no_rawat }}</td>
              </tr>
              <tr>
                <td style="width: 20%;">Nama Pasien</td>
                <td style="width: 1%;">:</td>
                <td>{{ $row->nm_pasien }}</td>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td>{{ $row->tgl_registrasi ?? '-' }} | {{ $row->jam_reg ?? '-' }}</td>
              </tr>
              <tr>
                <td>Poliklinik</td>
                <td>:</td>
                <td>Ranap</td>
              </tr>
              <tr>
                <td>Informed Consent</td>
                <td>:</td>
                <td>
                  <?php if (!$icta->isEmpty()) { ?>
                  <?php foreach ($icta as $i) { ?>
                  <table class="table table-bordered sub-table" style="width: 100%;">
                    <tr>
                      <th>Tanggal</th>
                      <td><?php echo $i->tanggal ?? '-'; ?></td>
                    </tr>
                    <br />
                    <tr>
                      <td colspan="2">A. JENIS INFORMASI</td>
                    </tr>
                    <tr>
                      <th>Diagnosa</th>
                      <td><?php echo $i->diagnosa ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Tindakan Kedokteran</th>
                      <td><?php echo $i->tindakan ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Indikasi Tindakan</th>
                      <td><?php echo $i->indikasi_tindakan ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Tata Cara</th>
                      <td><?php echo $i->tata_cara ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Tujuan</th>
                      <td><?php echo $i->tujuan ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Risiko</th>
                      <td><?php echo $i->risiko ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Komplikasi</th>
                      <td><?php echo $i->komplikasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Prognosis</th>
                      <td><?php echo $i->prognosis ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Alternatif dan Risikonya</th>
                      <td><?php echo $i->alternatif_dan_risikonya ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Lain-lain</th>
                      <td><?php echo $i->lain_lain ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Biaya</th>
                      <td><?php echo $i->biaya ?? '-'; ?></td>
                    </tr>
                  </table>




                  <table class="table table-bordered sub-table" style="width: 100%;">
                    <tr>
                      <td colspan="2">B. PENERIMA INFORMASI</td>
                    </tr>
                    <tr>
                      <th>Penerima Informasi</th>
                      <td><?php echo $i->penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Hubungan Penerima Informasi</th>
                      <td><?php echo $i->hubungan_penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Alasan Diwakilkan Penerima Informasi</th>
                      <td><?php echo $i->alasan_diwakilkan_penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Jenis Kelamin</th>
                      <td><?php echo $i->jk_penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>TGL Lahir</th>
                      <td><?php echo $i->tanggal_lahir_penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Umur</th>
                      <td><?php echo $i->umur_penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Alamat</th>
                      <td><?php echo $i->alamat_penerima_informasi ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>No. HP</th>
                      <td><?php echo $i->no_hp ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Respon Pernyataan</th>
                      <td><?php echo $i->pernyataan ?? '-'; ?></td>
                    </tr>
                  </table>


                  <table class="table table-bordered sub-table" style="width: 100%;">
                    <tr>
                      <td colspan="2">C. SAKSI-SAKSI</td>
                    </tr>
                    <tr>
                      <th>Saksi I : Keluarga</th>
                      <td><?php echo $i->saksi_keluarga ?? '-'; ?></td>
                    </tr>
                    <tr>
                      <th>Saksi II : Petugas/Perawat</th>
                      <td><?php echo $i->nama_petugas ?? '-'; ?></td>
                    </tr>
                  </table>
                  <br />
                  <?php } ?>
                  <?php } else { ?>
                  Tidak ada data consent tindakan anestesi.
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