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
  <h5 style="color:BLUE;">ERM Ranap - SEP BPJS</h5>
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
                <td>SEP BPJS</td>
                <td>:</td>
                <td>
                    @if ($sep->isNotEmpty())
                      @foreach ($sep as $s)
                        <table class="table table-bordered sub-table" style="width:100%; margin-bottom: 10px;">
                            <tr><th>Nomor SEP</th><td>{{ $s->no_sep }}</td></tr>
                            <tr><th>Tanggal SEP</th><td>{{ $s->tglsep }}</td></tr>
                            <tr><th>Nomor Kartu</th><td>{{ $s->no_kartu }}</td></tr>
                            <tr><th>Tanggal Lahir</th><td>{{ $s->tanggal_lahir }}</td></tr>
                            <tr><th>Nomor Telepon</th><td>{{ $s->notelep }}</td></tr>
                            <tr>
                              <th>Jenis Pelayanan</th>
                              <td>
                                @if ($s->jnspelayanan == 1)
                                  Rawat Inap
                                @elseif ($s->jnspelayanan == 2)
                                  Rawat Jalan
                                @else
                                  Tidak Dikenal
                                @endif
                              </td>
                            </tr>
                            <tr><th>Tanggal Pulang</th><td>{{ $s->tglpulang }}</td></tr>
                            <tr><th>Sub/Spesialis</th><td>{{ $s->nmpolitujuan }}</td></tr>
                            <tr><th>Dokter</th><td>{{ $s->nmdpdjp }}</td></tr>
                            <tr><th>Diagnosa Awal</th><td>{{ $s->nmdiagnosaawal }}</td></tr>
                            <tr><th>Peserta</th><td>{{ $s->peserta }}</td></tr>
                            <tr>
                              <th>Jenis Kunjungan</th>
                              <td>
                                @if ($s->tujuankunjungan == 0)
                                  Normal
                                @elseif ($s->tujuankunjungan == 1)
                                  Prosedur
                                @elseif ($s->tujuankunjungan == 2)
                                  Konsul Dokter
                                @else
                                  Tidak Dikenal
                                @endif
                              </td>
                            </tr>
                            <tr><th>Kelas Hak</th><td>Kelas {{ $s->klsrawat }}</td></tr>
                            <tr><th>Kelas Rawat</th><td>{{ $s->klsnaik }}</td></tr>
                            <tr><th>Catatan</th><td>{{ $s->catatan }}</td></tr>
                        </table>
                      @endforeach
                    @else
                      <em>Data SEP tidak tersedia.</em>
                    @endif
                </td>
               </tr><!--<p>CATATAN : BERKAS SEP INI BELUM BERFUNGSI SEPENUHNYA. FITUR CHECKLIST BELUM AKTIF.</p> -->
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</body>
</html>
