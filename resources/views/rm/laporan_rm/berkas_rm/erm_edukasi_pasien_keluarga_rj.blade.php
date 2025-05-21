<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>ERM - Ranap</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('img/favicon.png') }}" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('vendor/simple-datatables/style.css') }}" rel="stylesheet"> -->

    <!-- JQuery DataTable Css -->
    <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

</head>
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
<h5 style="color:BLUE;">ERM Ranap - Asesmen Kebutuhan Edukasi Dan Informasi</h5>
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
            <tr><td style="width: 20%;">No Rawat</td><td style="width: 1%;">:</td><td><?php echo $row->no_rawat; ?></td></tr>
            <tr><td>Tanggal Registrasi</td><td>:</td><td><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td></tr>
            <tr><td>Poliklinik</td><td>:</td><td>Ranap</td></tr>
            <tr>
              <td>Catatan Edukasi Pasien & Keluarga</td>
              <td>:</td>
              <td>
                <?php if (!$edukasi_pasien_keluarga_rj->isEmpty()) { ?>
                  <?php foreach ($edukasi_pasien_keluarga_rj as $e) { ?>
                <table class="table table-bordered sub-table" style="width:100%; font-size: 14px;">
                  <tr><th colspan="2" style="text-align: center;">A. PENGKAJIAN KEBUTUHAN EDUKASI</th></tr>
                  <tr><td colspan="2" style="text-align: center;">Tanggal Edukasi : <?php echo $e->tanggal; ?></td></tr>
                  <tr><th>1. Kesediaan Menerima Informasi</th><td>: <?php echo $e->kesediaan_menerima_informasi; ?></td></tr>
                  <tr><th>2. Bahasa Sehari-hari</th><td>: <?php echo $e->bahasa_sehari; ?></td></tr>
                  <tr><th>3. Perlu Penerjemah</th><td>: <?php echo $e->perlu_penerjemah; ?></td></tr>
                  <tr><th>4. Bahasa Isyarat</th><td>: <?php echo $e->bahasa_isyarat; ?></td></tr>
                  <tr><th>5. Cara Belajar Yang Disukai</th><td>: <?php echo $e->cara_belajar; ?></td></tr>
                  <tr><th>6. Tingkat Pendidikan</th><td>: <?php echo $e->pendidikan; ?></td></tr>
                  <tr><th>7. Hambatan Belajar</th><td>: <?php echo $e->hambatan_belajar; ?></td></tr>
                  <tr><th>8. Kemampuan Belajar</th><td>: <?php echo $e->kemampuan_belajar; ?></td></tr>
                  <tr>
                    <th>9. Nilai dan Keyakinan</th>
                    <td>
                      a. Penyakitnya merupakan: <?php echo $e->penyakitnya_merupakan; ?><br>
                      b. Keputusan memilih layanan kesehatan: <?php echo $e->keputusan_memilih_layanan; ?><br>
                      c. Keyakinan terhadap hasil terapi: <?php echo $e->keyakinan_terhadap_terapi; ?><br>
                      d. Aspek keyakinan selama masa perawatan: <?php echo $e->aspek_keyakinan_dipertimbangkan; ?>
                    </td>
                  </tr>
                  <tr><th>10. Kesediaan Menerima Informasi</th><td>: <?php echo $e->kesediaan_menerima_informasi; ?></td></tr>
                  <tr><th colspan="2" style="text-align: center;">B. PERENCANAAN KEBUTUHAN EDUKASI</th></tr>
                  <tr>
                    <th>Topik edukasi</th>
                    <td>
                      Penyakit yang diderita pasien: <?php echo $e->topik_edukasi_penyakit; ?><br>
                      Rencana tindakan / terapi: <?php echo $e->topik_edukasi_rencana_tindakan; ?><br>
                      Pengobatan dan prosedur yang diperlukan: <?php echo $e->topik_edukasi_pengobatan; ?><br>
                      Hasil pelayanan: <?php echo $e->topik_edukasi_hasil_layanan; ?>
                    </td>
                  </tr>
                  <tr><th>Petugas / Perawat</th><td>: <?php echo $e->nama_petugas; ?></td></tr>
                </table><br>
                <?php } ?>
                <?php } else { ?>
                    Tidak ada data Edukasi Pasien & Keluarga.
                <?php } ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</div>