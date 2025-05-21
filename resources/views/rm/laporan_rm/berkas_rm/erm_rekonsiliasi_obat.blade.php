<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Ranap</title>
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="{{asset ('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{asset ('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css')}}" rel="stylesheet">
  <link href="{{asset('css/style.css')}}" rel="stylesheet">

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
  <h5 style="color:BLUE;">ERM Ranap - Rekonsiliasi Obat</h5>
  <div class="table-responsive">
    <table id="erm" class="table table-bordered table-striped" style="width:100%;">
      <thead>
        <tr>
          <th style="width: 100%; text-align: left; vertical-align: top;">Riwayat</th>
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
                <td>Rekonsiliasi Obat</td>
                <td>:</td>
                <td>
                  <?php if (!$rekonsiliasi_obat->isEmpty()) { ?>
                  <?php foreach($rekonsiliasi_obat as $r) { ?>
                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>No. Rekonsiliasi</th><td><?php echo $r->no_rekonsiliasi; ?></td></tr>
                      <tr><th>Tanggal Wawancara</th><td><?php echo $r->tanggal_wawancara; ?></td></tr>
                      <tr><th>Alergi Obat</th><td><?php echo $r->alergi_obat; ?></td></tr>
                      <tr><th>Manifestasi Alergi</th><td><?php echo $r->manifestasi_alergi; ?></td></tr>
                      <tr><th>Dampak Alergi</th><td><?php echo $r->dampak_alergi; ?></td></tr>
                      <tr><th>Rekonsiliasi Saat</th><td><?php echo $r->rekonsiliasi_obat_saat; ?></td></tr>
                    </table><br>
                  <?php } ?>
                  <?php } else { ?>
                      Tidak ada data Rekonsiliasi Obat.
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td>Detail Rekonsiliasi Obat</td>
                <td>:</td>
                <td>
                  <?php if (!$detail_rekonsiliasi_obat->isEmpty()) { ?>
                  <?php foreach($detail_rekonsiliasi_obat as $d) { ?>
                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>Nama Obat</th><td><?php echo $d->nama_obat; ?></td></tr>
                      <tr><th>Dosis Obat</th><td><?php echo $d->dosis_obat; ?></td></tr>
                      <tr><th>Frekuensi</th><td><?php echo $d->frekuensi; ?></td></tr>
                      <tr><th>Cara Pemberian</th><td><?php echo $d->cara_pemberian; ?></td></tr>
                      <tr><th>Waktu Pemberian Terakhir</th><td><?php echo $d->waktu_pemberian_terakhir; ?></td></tr>
                      <tr><th>Tindak Lanjut</th><td><?php echo $d->tindak_lanjut; ?></td></tr>
                      <tr><th>Perubahan Aturan Pakai</th><td><?php echo $d->perubahan_aturan_pakai; ?></td></tr>
                    </table><br>
                  <?php } ?>
                  <?php } else { ?>
                      Tidak ada data Detail Rekonsiliasi Obat.
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
