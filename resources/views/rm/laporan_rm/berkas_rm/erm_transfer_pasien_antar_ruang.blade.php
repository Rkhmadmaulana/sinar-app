<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />

  <title>ERM - Ranap</title>
  <link href="{{ asset('img/favicon.png') }}" rel="icon" />
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700"
    rel="stylesheet" />

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
    rel="stylesheet" />

  <!-- Template Main CSS File -->
  <link href="{{ asset('css/style.css') }}" rel="stylesheet" />

  <style>
    table td,
    table th {
      padding: 5px;
    }

    .sub-table th {
      background-color: #fffaf8;
      padding: 2px;
      width: 30%;
      border: 1px solid #ccc;
      text-align: left;
    }

    .sub-table td {
      padding: 2px 8px;
      border: 1px solid #ccc;
      vertical-align: top;
    }
  </style>
</head>

<body>
  <h5 style="color: blue;">ERM Ranap - Transfer Pasien Antar Ruang</h5>
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
            <table class="table table-bordered sub-table" style="width:100%; border-collapse: collapse;">
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
                <td style="vertical-align: top;">TRANSFER PASIEN ANTAR RUANG</td>
                <td style="vertical-align: top;">:</td>
                <td style="padding: 0;">
                  <?php if (!$transfer_pasien_antar_ruang->isEmpty()) { ?>
                  <?php foreach ($transfer_pasien_antar_ruang as $transfer_pasien_antar_ruang) { ?>
                  <table class="table sub-table" style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <thead>
                      <tr>
                        <th>Nama Pasien</th>
                        <th>Tanggal Lahir</th>
                        <th>Tanggal Masuk</th>
                        <th>Tanggal Pindah</th>
                        <th>Indikasi Pindah</th>
                        <th>Keterangan Indikasi Pindah</th>
                        <th>Asal Ruang Rawat / Poliklinik</th>
                        <th>Ruang Rawat Selanjutnya</th>
                        <th>Metode Pemindahan</th>
                        <th>Diagnosa Utama</th>
                        <th>Diagnosa Sekunder</th>
                        <th>Prosedur Yang Sudah Dilakukan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><?php echo $transfer_pasien_antar_ruang->nm_pasien; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->tgl_lahir; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->tanggal_masuk; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->tanggal_pindah; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->indikasi_pindah_ruang; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->keterangan_indikasi_pindah_ruang; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->asal_ruang; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->ruang_selanjutnya; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->metode_pemindahan_pasien; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->diagnosa_utama; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->diagnosa_sekunder; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->prosedur_yang_sudah_dilakukan; ?></td>
                      </tr>
                    </tbody>
                    <thead>
                      <tr>
                        <th>Obat Yang Telah Diberikan</th>
                        <th>Pemeriksaan Penunjang Yang Sudah Dilakukan</th>
                        <th>Peralatan Yang Menyertai</th>
                        <th>Keterangan Peralatan Menyertai</th>
                        <th>Menyetujui Pemindahan</th>
                        <th>Nama Keluarga/Penanggung Jawab</th>
                        <th>Hubungan</th>
                        <th>Keadaan Umum SbT</th>
                        <th>TD SbT</th>
                        <th>Nadi SbT</th>
                        <th>RR SbT</th>
                        <th>Suhu SbT</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><?php echo $transfer_pasien_antar_ruang->obat_yang_telah_diberikan; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->pemeriksaan_penunjang_yang_dilakukan; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->peralatan_yang_menyertai; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->keterangan_peralatan_yang_menyertai; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->pasien_keluarga_menyetujui; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->nama_menyetujui; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->hubungan_menyetujui; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->keadaan_umum_sebelum_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->td_sebelum_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->nadi_sebelum_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->rr_sebelum_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->suhu_sebelum_transfer; ?></td>
                      </tr>
                    </tbody>
                    <thead>
                      <tr>
                        <th>Keluhan Utama Sebelum Transfer</th>
                        <th>Keadaan Umum StT</th>
                        <th>TD StT</th>
                        <th>Nadi StT</th>
                        <th>RR StT</th>
                        <th>Suhu StT</th>
                        <th>Keluhan Utama Setelah Transfer</th>
                        <th>Petugas Yang Menyerahkan</th>
                        <th>Petugas Yang Menerima</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><?php echo $transfer_pasien_antar_ruang->keluhan_utama_sebelum_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->keadaan_umum_sesudah_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->td_sesudah_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->nadi_sesudah_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->rr_sesudah_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->suhu_sesudah_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->keluhan_utama_sesudah_transfer; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->nama_petugas_menyerahkan; ?></td>
                        <td><?php echo $transfer_pasien_antar_ruang->nama_petugas_menerima; ?></td>
                      </tr>
                    </tbody>
                  </table>
                  <?php } ?>
                  <?php } else { ?>
                    Tidak ada data Transfer Pasien Antar Ruang.
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
