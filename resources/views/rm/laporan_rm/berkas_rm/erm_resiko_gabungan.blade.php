<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - RESIKO JATUH LANSIA/ANAK/DEWASA</title>

  <link rel="icon" href="{{ asset('public/img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('public/img/apple-touch-icon.png') }}">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">
  <link href="{{ asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('public/vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">
  <link href="{{ asset('public/css/style.css') }}" rel="stylesheet">

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
  <h5 style="color:BLUE;">ERM Ranap - Asesmen Resiko Jatuh</h5>
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
                <td><?= $row->no_rawat; ?></td>
              </tr>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
              </tr>
              <tr>
                <td>Nama Pasien</td>
                <td>:</td>
                <td><?= $row->nm_pasien; ?></td>
              </tr>
              <tr>
                <td>Data Resiko Jatuh Pasien Lansia/Anak/Dewasa</td>
                <td>:</td>
                <td>
                <?php if (!empty($resiko)) { ?>
                  <?php foreach ($resiko as $e) { ?>
                    <table class="table table-bordered sub-table" style="width:100%; border-collapse: collapse;">
                      <tr>
                        <th>Tanggal</th>
                        <td><?= $e->tanggal ?? '-'; ?></td>
                        <th>Skor</th>
                      </tr>

                      <?php if ($table === 'penilaian_lanjutan_resiko_jatuh_dewasa') { ?>
                        <tr>
                            <th>Riwayat Jatuh (1 Tahun Terakhir)</th>
                            <td><?= $e->penilaian_jatuhmorse_skala1; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai1; ?></td>
                        </tr>
                        <tr>
                            <th>Diagnosis Sekunder (>= 2 Diagnosis Medis)</th>
                            <td><?= $e->penilaian_jatuhmorse_skala2; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai2; ?></td>
                        </tr>
                        <tr>
                            <th>Alat Bantu</th>
                            <td><?= $e->penilaian_jatuhmorse_skala3; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai3; ?></td>
                        </tr>
                        <tr>
                            <th>Terpasang Infuse</th>
                            <td><?= $e->penilaian_jatuhmorse_skala4; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai4; ?></td>
                        </tr>
                        <tr>
                            <th>Gaya Berjalan</th>
                            <td><?= $e->penilaian_jatuhmorse_skala5; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai5; ?></td>
                        </tr>
                        <tr>
                            <th>Status Mental</th>
                            <td><?= $e->penilaian_jatuhmorse_skala6; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai6; ?></td>
                        </tr>
                        <tr>
                            <th colspan="2"  style="text-align: center;">TOTAL SKOR</th>
                            <td><?= $e->penilaian_jatuhmorse_totalnilai; ?></td>
                        </tr>
                        <tr>
                            <th>Hasil Skrining</th>
                            <td colspan="2"><?= $e->hasil_skrining; ?></td>
                        </tr>
                        <tr>
                            <th>Saran</th>
                            <td colspan="2"><?= $e->saran; ?></td>
                        </tr>

                      <?php } elseif ($table === 'penilaian_lanjutan_resiko_jatuh_lansia') { ?>
                        <tr>
                            <th>Riwayat Jatuh</th>
                            <td><?= $e->penilaian_jatuhmorse_skala1; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai1; ?></td>
                        </tr>
                        <tr>
                            <th>Status Mental</th>
                            <td><?= $e->penilaian_jatuhmorse_skala2; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai2; ?></td>
                        </tr>
                        <tr>
                            <th>Penglihatan</th>
                            <td><?= $e->penilaian_jatuhmorse_skala3; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai3; ?></td>
                        </tr>
                        <tr>
                            <th>Kebiasaan Berkemih</th>
                            <td><?= $e->penilaian_jatuhmorse_skala4; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai4; ?></td>
                        </tr>
                        <tr>
                            <th style="text-align: center;">Nilai Transfer & Mobilitas</th>
                            <th colspan="2" ></th>
                        </tr>
                        <tr>
                            <th>Nilai Transfer</th>
                            <td><?= $e->penilaian_jatuhmorse_skala5; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai5; ?></td>
                        </tr>
                        <tr>
                            <th>Nilai Mobilitas</th>
                            <td><?= $e->penilaian_jatuhmorse_skala6; ?></td>
                            <td><?= $e->penilaian_jatuhmorse_nilai6; ?></td>
                        </tr>
                        <tr>
                            <th colspan="2"  style="text-align: center;">TOTAL SKOR</th>
                            <td><?= $e->penilaian_jatuhmorse_totalnilai; ?></td>
                        </tr>
                        <tr>
                            <th>Hasil Skrining</th>
                            <td colspan="2"><?= $e->hasil_skrining; ?></td>
                        </tr>
                        <tr>
                            <th>Saran</th>
                            <td colspan="2"><?= $e->saran; ?></td>
                        </tr>

                        <?php } elseif ($table === 'penilaian_lanjutan_resiko_jatuh_anak') { ?>
                        <tr>
                            <th>Umur</th>
                            <td><?= $e->penilaian_humptydumpty_skala1; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai1; ?></td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td><?= $e->penilaian_humptydumpty_skala2; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai2; ?></td>
                        </tr>
                        <tr>
                            <th>Diagnosa</th>
                            <td><?= $e->penilaian_humptydumpty_skala3; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai3; ?></td>
                        </tr>
                        <tr>
                            <th>Gangguan Kognitif</th>
                            <td><?= $e->penilaian_humptydumpty_skala4; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai4; ?></td>
                        </tr>
                        <tr>
                            <th>Faktor Lingkungan</th>
                            <td><?= $e->penilaian_humptydumpty_skala5; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai5; ?></td>
                        </tr>
                        <tr>
                            <th>Efek Obat/Penenang/Anestesi</th>
                            <td><?= $e->penilaian_humptydumpty_skala6; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai6; ?></td>
                        </tr>
                        <tr>
                            <th>Penggunaan Obat</th>
                            <td><?= $e->penilaian_humptydumpty_skala7; ?></td>
                            <td><?= $e->penilaian_humptydumpty_nilai7; ?></td>
                        </tr>
                        <tr>
                            <th colspan="2"  style="text-align: center;">TOTAL SKOR</th>
                            <td><?= $e->penilaian_humptydumpty_totalnilai; ?></td>
                        </tr>
                        <tr>
                            <th>Hasil Skrining</th>
                            <td colspan="2"><?= $e->hasil_skrining; ?></td>
                        </tr>
                        <tr>
                            <th>Saran</th>
                            <td colspan="2"><?= $e->saran; ?></td>
                        </tr>
                      <?php } ?>
                      </table>
                      <br>
                    <?php } // end foreach ?>
                  <?php } else { ?>
                    Tidak ada Asesmen
                  <?php } // end if ?>
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