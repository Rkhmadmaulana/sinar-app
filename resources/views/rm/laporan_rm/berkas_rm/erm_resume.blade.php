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

  <h5>ERM Ranap</h5>

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
                <td>Resume Medis</td>
                <td>:</td>
                <td>
                  <?php foreach ($resume as $rm) { ?>
                    <table class="table table-bordered sub-table" style="width:100%; border-collapse: collapse;">
                    <?php if (!empty($dpjp_dokter)) { ?>
                      <tr>
                        <th>DPJP</th>
                        <td>
                          <ul style="margin:0; padding-left: 16px;">
                            <?php foreach ($dpjp_dokter as $dokter) { ?>
                              <li><?= $dokter->nm_dokter; ?></li>
                            <?php } ?>
                          </ul>
                        </td>
                      </tr>
                    <?php } ?>
                      <tr>
                        <th>Diagnosa Awal</th>
                        <td><?= $rm->diagnosa_awal; ?></td>
                      </tr>
                      <tr>
                        <th>Alasan</th>
                        <td><?= $rm->alasan; ?></td>
                      </tr>
                      <tr>
                        <th>Keluhan Utama</th>
                        <td><?= $rm->keluhan_utama; ?></td>
                      </tr>
                      <tr>
                        <th>Pemeriksaan Fisik</th>
                        <td>
                          <ul style="margin:0; padding-left: 16px;">
                            <?php foreach (explode("\n", $rm->pemeriksaan_fisik) as $item): ?>
                              <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars($item) ?></li>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </ul>
                        </td>
                      </tr>
                      <tr>
                        <th>Jalannya Penyakit</th>
                        <td><?= $rm->jalannya_penyakit; ?></td>
                      </tr>
                      <tr>
                        <th>Pemeriksaan Penunjang</th>
                        <td>
                          <ul style="margin:0; padding-left: 16px;">
                            <?php foreach (explode("\n", $rm->pemeriksaan_penunjang) as $item): ?>
                              <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars($item) ?></li>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </ul>
                        </td>
                      </tr>
                      <tr>
                        <th>Hasil Laborat</th>
                        <td>
                          <ul style="margin:0; padding-left: 16px;">
                            <?php foreach (explode("\n", $rm->hasil_laborat) as $item): ?>
                              <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars($item) ?></li>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </ul>
                        </td>
                      </tr>
                      <tr>
                        <th>Tindakan dan Operasi</th>
                        <td><?= $rm->tindakan_dan_operasi; ?></td>
                      </tr>
                      <tr>
                        <th>Obat di RS</th>
                        <td>
                          <ul style="margin:0; padding-left: 16px;">
                            <?php foreach (explode("\n", $rm->obat_di_rs) as $item): ?>
                              <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars($item) ?></li>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </ul>
                        </td>
                      </tr>
                      <tr>
                        <th>Kode Diagnosa Utama</th>
                        <td><?= $rm->kd_diagnosa_utama; ?></td>
                      </tr>
                      <tr>
                        <th>Diagnosa Utama</th>
                        <td><?= $rm->diagnosa_utama; ?></td>
                      </tr>
                      <tr>
                        <th>Kode Diagnosa Sekunder</th>
                        <td><?= $rm->kd_diagnosa_sekunder; ?></td>
                      </tr>
                      <tr>
                        <th>Diagnosa Sekunder</th>
                        <td><?= $rm->diagnosa_sekunder; ?></td>
                      </tr>
                      <tr>
                        <th>Kode Prosedur Utama</th>
                        <td><?= $rm->kd_prosedur_utama; ?></td>
                      </tr>
                      <tr>
                        <th>Prosedur Utama</th>
                        <td><?= $rm->prosedur_utama; ?></td>
                      </tr>
                      <tr>
                        <th>Kode Prosedur Sekunder</th>
                        <td><?= $rm->kd_prosedur_sekunder; ?></td>
                      </tr>
                      <tr>
                        <th>Prosedur Sekunder</th>
                        <td><?= $rm->prosedur_sekunder; ?></td>
                      </tr>
                      <tr>
                        <th>Alergi</th>
                        <td><?= $rm->alergi; ?></td>
                      </tr>
                      <tr>
                        <th>Diet</th>
                        <td><?= $rm->diet; ?></td>
                      </tr>
                      <tr>
                        <th>Lab Belum</th>
                        <td><?= $rm->lab_belum; ?></td>
                      </tr>
                      <tr>
                        <th>Edukasi</th>
                        <td><?= $rm->edukasi; ?></td>
                      </tr>
                      <tr>
                        <th>Keterangan Keluar</th>
                        <td><?= $rm->ket_keluar; ?></td>
                      </tr>
                      <tr>
                        <th>Keadaan</th>
                        <td><?= $rm->keadaan; ?></td>
                      </tr>
                      <tr>
                        <th>Dilanjutkan</th>
                        <td><?= $rm->dilanjutkan; ?></td>
                      </tr>
                      <tr>
                        <th>Keterangan Dilanjutkan</th>
                        <td><?= $rm->ket_dilanjutkan; ?></td>
                      </tr>
                      <tr>
                        <th>Kontrol</th>
                        <td><?= $rm->kontrol; ?></td>
                      </tr>
                      <tr>
                        <th>Obat Pulang</th>
                        <td>
                          <ul style="margin:0; padding-left: 16px;">
                            <?php foreach (explode("\n", $rm->obat_pulang) as $item): ?>
                              <?php if (trim($item) !== ''): ?>
                                <li><?= htmlspecialchars($item) ?></li>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </ul>
                        </td>
                      </tr>
                    </table>
                    <br>
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
