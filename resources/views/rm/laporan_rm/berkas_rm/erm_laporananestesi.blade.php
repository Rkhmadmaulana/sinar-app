<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ERM - Laporan Anestesi</title>

  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

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
  <h5 style="color:BLUE;">ERM - Laporan Anestesi</h5>

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
                <td>Laporan Anastesi</td>
                <td>:</td>
                <td>
                  <?php if (!$laporananestesi->isEmpty()) { ?>
                    <?php foreach($laporananestesi as $i) { ?>
                      <table class="table table-bordered sub-table" style="width:100%;">
                        <tr><th>Tanggal dan Jam</th><td><?php echo $i->tanggal ?? '-'; ?></td></tr>
                        <tr><th>Jam Masuk</th><td><?php echo $i->masuk ?? '-'; ?></td></tr>
                        <tr><th>Kesadaran</th><td><?php echo $i->kesadaran ?? '-'; ?></td></tr>
                        <tr><th>Support 1</th><td><?php echo $i->support_1 ?? '-'; ?></td></tr>
                        <tr><th>Support 2</th><td><?php echo $i->support_2 ?? '-'; ?></td></tr>
                        <tr><th>Skor Nyeri</th><td><?php echo $i->skor_nyeri ?? '-'; ?></td></tr>
                        <tr><th>Respirasi</th><td><?php echo $i->respirasi ?? '-'; ?></td></tr>
                        <tr><th>Alat Bantu</th><td><?php echo $i->alat_bantu ?? '-'; ?></td></tr>
                        <tr><th>O2</th><td><?php echo $i->o2 ?? '-'; ?></td></tr>
                        <tr><th>SPO2</th><td><?php echo $i->spo2 ?? '-'; ?></td></tr>
                      </table><br>
                    <?php } ?>
                  <?php } else { ?>
                    Tidak ada data Laporan Anastesi.
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
