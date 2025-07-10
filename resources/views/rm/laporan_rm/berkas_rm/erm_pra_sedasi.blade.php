<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - Ranap</title>
  <link rel="icon" href="{{ asset('public/img/favicon.png') }}">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
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
  <h5 style="color:BLUE;">ERM Ranap - Penilaian Pra Sedasi</h5>
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
                <td>{{ $row->no_rawat }}</td>
              </tr>
              <tr>
                <td style="width: 20%;">Nama Pasien</td>
                <td style="width: 1%;">:</td>
                <td>{{ $row->nm_pasien }}</td>
              </tr>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td>{{ $row->tgl_registrasi }} | {{ $row->jam_reg }}</td>
              </tr>
              <tr>
                <td>Poliklinik</td>
                <td>:</td>
                <td>{{ $row->status_lanjut }}</td>
              </tr>
              <tr>
                <td>Penilaian Pra Sedasi</td>
                <td>:</td>
                <td>
                  @if ($sedasi)
                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>Nama Dokter</th><td>{{ $sedasi->nm_dokter }}</td></tr>
                      <tr><th>Riwayat Penyakit Dahulu</th><td>{{ $sedasi->riwayat_penyakit_dahulu }}</td></tr>
                      <tr><th>Obat</th><td>{{ $sedasi->obat_yang_dikonsumsi }}</td></tr>
                      <tr><th>Riwayat Alergi Obat-obatan</th><td>{{ $sedasi->riwayat_alergi_obat }}</td></tr>
                      <tr><th>TD</th><td>{{ $sedasi->td }}</td></tr>
                      <tr><th>Respirasi</th><td>{{ $sedasi->respirasi }}</td></tr>
                      <tr><th>HR</th><td>{{ $sedasi->hr }}</td></tr>
                      <tr><th>Temperatur</th><td>{{ $sedasi->temperatur }}</td></tr>
                      <tr><th>Jalan Napas</th><td>{{ $sedasi->jalan_napas }}</td></tr>
                      <tr><th>Resume / Diagnosa Pra Anastesi</th><td>{{ $sedasi->resume }}</td></tr>
                      <tr><th>Umur</th><td>{{ $sedasi->umur }}</td></tr>
                      <tr><th>BB</th><td>{{ $sedasi->bb }}</td></tr>
                      <tr><th>TB</th><td>{{ $sedasi->tb }}</td></tr>
                      <tr><th>Rencana Tindakan Operasi</th><td>{{ $sedasi->rencana_tindakan_op }}</td></tr>
                      <tr><th>Rencana Tindakan Anastesi</th><td>{{ $sedasi->rencana_tindakan_an }}</td></tr>
                      <tr><th>Instruksi Pra Anastesi</th><td>{{ $sedasi->instruksi_pra_anestesi }}</td></tr>
                    </table>
                  @else
                    <em>Data Pra Sedasi tidak tersedia.</em>
                  @endif
                </td>
               </tr><!--<p>CATATAN : BERKAS PRA SEDASI INI BELUM BERFUNGSI SEPENUHNYA. FITUR CHECKLIST BELUM AKTIF.</p> -->
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    
  </div>
</body>
</html>
