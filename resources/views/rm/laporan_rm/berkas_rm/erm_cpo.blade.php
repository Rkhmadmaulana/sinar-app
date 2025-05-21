<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - Ranap</title>
  <link rel="icon" href="{{ asset('img/favicon.png') }}">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">
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
  <h5 style="color:BLUE;">ERM Ranap - CPO</h5>
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
                <td>Catatan Pemberian Obat</td>
                <td>:</td>
                <td>
                  <?php if (!$cpo->isEmpty()) { ?>
                  <?php foreach($cpo as $cpo) { ?>
                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>Nama Obat</th><td><?php echo $cpo->nama_obat; ?></td></tr>
                      <tr><th>Dosis</th><td><?php echo $cpo->dosis; ?></td></tr>
                      <tr><th>Cara Pemberian Obat</th><td><?php echo $cpo->cara_pemberian; ?></td></tr>
                      <tr><th>Jadwal Pemberian</th><td><?php echo $cpo->jadwal_pemberian; ?></td></tr>
                      <tr><th>Jumlah Sisa Obat</th><td><?php echo $cpo->jlh_sisa_obat; ?></td></tr>
                      <tr><th>Waktu Simpan</th><td><?php echo $cpo->waktu_simpan; ?></td></tr>
                      <tr><th>Tanggal Pemberian</th><td><?php echo $cpo->tgl_pemberian; ?></td></tr>
                      <tr><th>Jumlah Obat</th><td><?php echo $cpo->jlh_obat; ?></td></tr>
                      <tr><th>Jenis Obat</th><td><?php echo $cpo->jenis_obat; ?></td></tr>
                      <?php for($i=1; $i<=8; $i++) { ?>
                        <tr><th>Cek Jam <?php echo $i; ?></th><td><?php echo $cpo->{'cek_jam'.$i}; ?></td></tr>
                      <?php } ?>
                      <?php for($i=2; $i<=8; $i++) { ?>
                        <tr><th>Jadwal Pemberian <?php echo $i; ?></th><td><?php echo $cpo->{'jadwal_pemberian'.$i}; ?></td></tr>
                      <?php } ?>
                      <?php for($i=1; $i<=3; $i++) { ?>
                        <tr><th>Keterangan <?php echo $i; ?></th><td><?php echo $cpo->{'ket'.$i}; ?></td></tr>
                      <?php } ?>
                    </table><br>
                  <?php } ?>
                  <?php } else { ?>
                      Tidak ada data CPO.
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
