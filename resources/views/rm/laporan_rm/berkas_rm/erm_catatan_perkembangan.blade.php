<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - Ranap</title>

  <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">

  <!-- JQuery DataTable CSS -->
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">

  <!-- Template Main CSS -->
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">

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
  <h5>ERM Ranap - Catatan Perkembangan</h5>
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
                <td>Catatan Keperawatan Rawat Inap</td>
                <td>:</td>
                <td>
                  <?php if (!$ctt_kep->isEmpty()) { ?>
                  <?php foreach ($ctt_kep as $cp) { ?>
                    <table class="table table-bordered sub-table" style="width:100%; border-collapse: collapse;">
                      <tr><th>Tanggal</th><td><?= $cp->tanggal; ?></td></tr>
                      <tr><th>Jam</th><td><?= $cp->jam; ?></td></tr>
                      <tr><th>Uraian</th><td><?= $cp->uraian; ?></td></tr>
                      <tr><th>Petugas</th><td><?= $cp->nama; ?></td></tr>
                    </table>
                    <br>
                  <?php } ?>
                  <?php } else { ?>
                      Tidak ada data Catatan Perkembangan.
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
