<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Persetujuan / Penolakan Tindakan Dokter</title>

  <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">

  <style>
    table td, table th {
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
  <h5 style="color:BLUE;">ERM - Persetujuan / Penolakan Tindakan Dokter</h5>

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
                <td>Persetujuan / Penolakan Tindakan Dokter</td>
                <td>:</td>
                <td>
                  <?php if (!$persetujuanpenolakan->isEmpty()) { ?>
                    <?php foreach($persetujuanpenolakan as $i) { ?>
                      <table class="table table-bordered sub-table" style="width:100%;">
                        <tr><th>No Pernyataan</th><td><?php echo $i->no_pernyataan ?? '-'; ?></td></tr>
                        <tr><th>Tanggal</th><td><?php echo $i->tanggal ?? '-'; ?></td></tr>
                        <tr><th>Diagnosa</th><td><?php echo $i->diagnosa ?? '-'; ?></td></tr>
                        <tr><th>Diagnosa Konfirmasi</th><td><?php echo $i->diagnosa_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Tindakan</th><td><?php echo $i->tindakan ?? '-'; ?></td></tr>
                        <tr><th>Tindakan Konfirmasi</th><td><?php echo $i->tindakan_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Indikasi Tindakan</th><td><?php echo $i->indikasi_tindakan ?? '-'; ?></td></tr>
                        <tr><th>Indikasi Tindakan Konfirmasi</th><td><?php echo $i->indikasi_tindakan_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Tata Cara</th><td><?php echo $i->tata_cara ?? '-'; ?></td></tr>
                        <tr><th>Tata Cara Konfirmasi</th><td><?php echo $i->tata_cara_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Tujuan</th><td><?php echo $i->tujuan ?? '-'; ?></td></tr>
                        <tr><th>Tujuan Konfirmasi</th><td><?php echo $i->tujuan_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Risiko</th><td><?php echo $i->risiko ?? '-'; ?></td></tr>
                        <tr><th>Risiko Konfirmasi</th><td><?php echo $i->risiko_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Komplikasi</th><td><?php echo $i->komplikasi ?? '-'; ?></td></tr>
                        <tr><th>Komplikasi Konfirmasi</th><td><?php echo $i->komplikasi_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Prognosis</th><td><?php echo $i->prognosis ?? '-'; ?></td></tr>
                        <tr><th>Prognosis Konfirmasi</th><td><?php echo $i->prognosis_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Alternatif Dan Risikonya</th><td><?php echo $i->alternatif_dan_risikonya ?? '-'; ?></td></tr>
                        <tr><th>Alternatif Konfirmasi</th><td><?php echo $i->alternatif_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Biaya</th><td><?php echo $i->biaya ?? '-'; ?></td></tr>
                        <tr><th>Biaya Konfirmasi</th><td><?php echo $i->biaya_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Lain Lain</th><td><?php echo $i->lain_lain ?? '-'; ?></td></tr>
                        <tr><th>Lain Lain Konfirmasi</th><td><?php echo $i->lain_lain_konfirmasi ?? '-'; ?></td></tr>
                        <tr><th>Nama Dokter Bedah</th><td><?php echo $i->nama_dokter_bedah ?? '-'; ?></td></tr>
                        <tr><th>Nama Perawat</th><td><?php echo $i->nama_perawat ?? '-'; ?></td></tr>
                        <tr><th>Penerima Informasi</th><td><?php echo $i->penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>Alasan Diwakilkan</th><td><?php echo $i->alasan_diwakilkan_penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>Jenis Kelamin Penerima</th><td><?php echo $i->jk_penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>Tanggal Lahir Penerima</th><td><?php echo $i->tanggal_lahir_penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>Umur Penerima</th><td><?php echo $i->umur_penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>Alamat Penerima</th><td><?php echo $i->alamat_penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>No HP</th><td><?php echo $i->no_hp ?? '-'; ?></td></tr>
                        <tr><th>Hubungan</th><td><?php echo $i->hubungan_penerima_informasi ?? '-'; ?></td></tr>
                        <tr><th>Pernyataan</th><td><?php echo $i->pernyataan ?? '-'; ?></td></tr>
                        <tr><th>Saksi Keluarga</th><td><?php echo $i->saksi_keluarga ?? '-'; ?></td></tr>
                      </table><br>
                    <?php } ?>
                  <?php } else { ?>
                    Tidak ada data Persetujuan / Penolakan Tindakan Dokter.
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
