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
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    table td, table th {
        border: 1px solid #ccc;
        padding: 6px;
        vertical-align: top;
        text-align: left;
    }

    table th {
        background-color: #f9f9f9;
        font-weight: bold;
    }

    .inner-table th, .inner-table td {
        border: 1px solid #ddd;
        padding: 4px;
    }

    .inner-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }
</style>

    <h5  style="color:BLUE;">ERM Ranap</h5>
      <div class="table-responsive">
      <table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Riwayat</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <table>
          <tr>
            <td>No Rawat</td>
            <td>:</td>
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
            <td>General Consent</td>
            <td>:</td>
            <td>
            <?php foreach($persetujuan_umum as $persetujuan_umum){ ?>
                <table class="inner-table">
                  <tr>
                    <th>No. Persetujuan</th>
                    <td><?php echo $persetujuan_umum->no_surat; ?></td>
                  </tr>
                  <tr>
                    <th>Tanggal</th>
                    <td><?php echo $persetujuan_umum->tanggal; ?></td>
                  </tr>
                  <tr>
                    <th>Petugas</th>
                    <td><?php echo $persetujuan_umum->nama; ?></td>
                  </tr>
                  <tr>
                    <th colspan="5">Penanggung Jawab Pasien:</th>
                  </tr>
                  <tr>
                    <th>Nama</th>
                    <th>Nomor KTP</th>
                    <th>Jenis Kelamin</th>
                    <th>Nomor Telepon/HP</th>
                    <th>Bertindak untuk/Atas Nama</th>
                  </tr>
                  <tr>
                  <td><?php echo $persetujuan_umum->nama_pj; ?></td>
                  <td><?php echo $persetujuan_umum->no_ktppj; ?></td>
                  <td><?php echo $persetujuan_umum->jkpj; ?></td>
                  <td><?php echo $persetujuan_umum->no_telp; ?></td>
                  <td><?php echo $persetujuan_umum->pengobatan_kepada; ?></td>
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
