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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset ('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{asset ('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <!-- <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet"> -->

  <!-- JQuery DataTable Css -->
  <link href="{{asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css')}}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{asset('css/style.css')}}" rel="stylesheet">

  <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

</head>
<!-- Style -->
<style media="screen">
    table td,
    table th {
      padding: 5px;
    }
</style>
<!-- End Style -->
    <h5  style="color:BLUE;">ERM Ranap</h5>
      <div class="table-responsive">
            <table id="erm"  class="table table-bordered table-striped" style="width:100%;">
              <thead>
                <tr>
                  <th style="width: 100%; text-align: left; vertical-align: top;">Riwayat</th>
                </tr>
              </thead>
              <tbody>
              <!-- Awal Terbackup no_rawat -->
                <tr>
                  <td >
                      <table border="1px"  style="width:100%;">
                          <!-- No Rawat  -->  
                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top;">No Rawat</td>
                                <td style="width: 1%;  vertical-align: top;">:</td>
                                <td style="width:79%;"><?php echo $row->no_rawat; ?></td>  
                            </tr>
                          <!-- End No Rawat  -->
                          
                          <!-- Tanggal Regist  -->
                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top;">Tanggal Registrasi</td>  
                                <td style="width: 1%;  vertical-align: top;">:</td>  
                                <td style="width:79%;"><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>  
                            </tr>
                          <!-- End Tanggal Regist  -->

                          <!-- Poliklinik  -->
                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top;">Poliklinik</td>  
                                <td style="width: 1%;  vertical-align: top;">:</td>  
                                <td style="width:79%;">
                                Ranap
                                </td>
                            </tr>
                                <!-- End Poliklinik  -->
                            <tr>
                                <td style="width: 20%; text-align: left; vertical-align: top; padding: 2px;">Catatan Pemberian Obat</td>  
                                <td style="width: 1%; vertical-align: top; padding: 2px;">:</td>  
                                <td style="width: 79%; padding: 2px;">
                                    <?php foreach($cpo as $cpo){ ?>
                                    <table border="1" style="width:100%; border-collapse: collapse; border-spacing: 0;">
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Nama Obat</th>
                                            <td style="padding: 2px;"><?php echo $cpo->nama_obat; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Dosis</th>
                                            <td style="padding: 2px;"><?php echo $cpo->dosis; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cara Pemberian Obat</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cara_pemberian; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Jumlah Sisa Obat</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jlh_sisa_obat; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Waktu Simpan</th>
                                            <td style="padding: 2px;"><?php echo $cpo->waktu_simpan; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Tanggal Pemberian</th>
                                            <td style="padding: 2px;"><?php echo $cpo->tgl_pemberian; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Jumlah Obat</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jlh_obat; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jenis Obat</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jenis_obat; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 1</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam1; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 2</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam2; ?></td>
                                        </tr><tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 3</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam3; ?></td>
                                        </tr><tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 4</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam4; ?></td>
                                        </tr><tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 5</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam5; ?></td>
                                        </tr><tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 6</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam6; ?></td>
                                        </tr><tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 7</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam7; ?></td>
                                        </tr><tr>
                                            <th style="width: 30%; background-color: #FFFAF8; padding: 2px;">Cek Jam 8</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_jam8; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 2</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian2; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 3</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian3; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 4</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian4; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 5</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian5; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 6</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian6; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 7</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian7; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Jadwal Pemberian 8</th>
                                            <td style="padding: 2px;"><?php echo $cpo->jadwal_pemberian8; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Keterangan 1</th>
                                            <td style="padding: 2px;"><?php echo $cpo->ket1; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Keterangan 2</th>
                                            <td style="padding: 2px;"><?php echo $cpo->ket2; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Keterangan 3</th>
                                            <td style="padding: 2px;"><?php echo $cpo->ket3; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Cek Double</th>
                                            <td style="padding: 2px;"><?php echo $cpo->cek_dobel; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Petugas 1</th>
                                            <td style="padding: 2px;"><?php echo $cpo->nama_petugas1; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Petugas 2</th>
                                            <td style="padding: 2px;"><?php echo $cpo->nama_petugas2; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #FFFAF8; padding: 2px;">Sift</th>
                                            <td style="padding: 2px;"><?php echo $cpo->sift; ?></td>
                                        </tr>
                                    </table>
                                    <br>
                                    <?php } ?>
                                </td>
                            </tr>