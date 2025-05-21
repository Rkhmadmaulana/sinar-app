<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>ERM - Resiko Jatuh Anak</title>

  <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon" />
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet"
  />

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet" />

  <!-- JQuery DataTable Css -->
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet" />
  <link
    href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
    rel="stylesheet"
  />

  <!-- Template Main CSS File -->
  <link href="{{ asset('css/style.css') }}" rel="stylesheet" />

  <style>
    table td,
    table th {
      padding: 5px;
    }

    /* sub-table styling seperti di code 2 */
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
  <h5 style="color:BLUE;">ERM - Resiko Jatuh Anak</h5>

  <div class="table-responsive">
    <table class="table table-bordered table-striped" style="width: 100%">
      <thead>
        <tr>
          <th style="text-align: left">Riwayat</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <table class="table table-bordered" style="width: 100%">
              <tr>
                <td style="width: 20%">No Rawat</td>
                <td style="width: 1%">:</td>
                <td>{{ $row->no_rawat }}</td>
              </tr>
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
                <td>Pemeriksaan</td>
                <td>:</td>
                <td>RESIKO JATUH ANAK</td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td>
                  <?php if (!$resiko_anak->isEmpty()) { ?>
                  <?php foreach ($resiko_anak as $item) { ?>
                  <table class="table table-bordered sub-table" style="width: 100%">
                    <tr>
                      <th>Tanggal</th>
                      <td><?php echo $item->tanggal; ?></td>
                    </tr>
                    <tr>
                      <th>Umur</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala1; ?></td>
                    </tr>
                    <tr>
                      <th>Jenis Kelamin</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala2; ?></td>
                    </tr>
                    <tr>
                      <th>Diagnosa</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala3; ?></td>
                    </tr>
                    <tr>
                      <th>Gangguan Kognitif</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala4; ?></td>
                    </tr>
                    <tr>
                      <th>Faktor Lingkungan</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala5; ?></td>
                    </tr>
                    <tr>
                      <th>Waktu Respon</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala6; ?></td>
                    </tr>
                    <tr>
                      <th>Riwayat Jatuh</th>
                      <td><?php echo $item->penilaian_humptydumpty_skala7; ?></td>
                    </tr>
                    <tr>
                      <th><b>Total skala</b></th>
                      <td><b><?php echo $item->penilaian_humptydumpty_totalnilai; ?></b></td>
                    </tr>
                    <tr>
                      <th>Hasil Skrining</th>
                      <td><?php echo $item->hasil_skrining; ?></td>
                    </tr>
                    <tr>
                      <th>Petugas</th>
                      <td><?php echo $item->nama_petugas ?? '-'; ?></td>
                    </tr>
                  </table>
                  <br />
                  <?php } ?>
                  <?php } else { ?>
                  Tidak ada data asesmen resiko anak.
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
