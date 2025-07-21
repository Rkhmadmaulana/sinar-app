<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - Ranap</title>
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
  <h5 style="color:BLUE;">ERM Ranap - Laporan Operasi</h5>
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
                <td>Laporan Operasi</td>
                <td>:</td>
                <td>
                  @if ($laporanop)
                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>Nama Dokter</th><td>{{ $laporanop->nm_dokter }}</td></tr>
                      <tr><th>Waktu Mulai Operasi</th><td>{{ $laporanop->tanggal }}</td></tr>
                      <tr><th>Waktu Selesai Operasi</th><td>{{ $laporanop->selesaioperasi }}</td></tr>
                      <tr><th>Diagnosa Preop</th><td>{{ $laporanop->diagnosa_preop }}</td></tr>
                      <tr><th>Diagnosa Postop</th><td>{{ $laporanop->diagnosa_postop }}</td></tr>
                      <tr><th>Jaringan Dieksisi</th><td>{{ $laporanop->jaringan_dieksekusi }}</td></tr>
                      <tr><th>Permintaan PA</th><td>{{ $laporanop->permintaan_pa }}</td></tr>
                      <tr><th>Nama / Macam Operasi</th><td>{{ $laporanop->jenis_operasi }}</td></tr>
                      <tr>
                        <th>Laporan</th>
                        <td>
                            <ul style="padding-left: 16px;">
                            @foreach(explode('-', $laporanop->laporan_operasi) as $item)
                                @if(trim($item) != '')
                                <li>{{ trim($item) }}</li>
                                @endif
                            @endforeach
                            </ul>
                        </td>
                      </tr>
                      <tr><th>Instruksi Post Operasi</th><td>{{ $laporanop->instruksi }}</td></tr>
                    </table>
                  @else
                    <em>Data Laporan Operasi tidak tersedia.</em>
                  @endif
                </td>
               </tr><!--<p>CATATAN : BERKAS LAPORAN OPERASI INI BELUM BERFUNGSI SEPENUHNYA. FITUR CHECKLIST BELUM AKTIF.</p> -->
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    
  </div>
</body>
</html>
