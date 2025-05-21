<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERM - Ranap</title>
  <link rel="icon" href="{{ asset('img/favicon.png') }}">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">
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
                <td><?php echo $row->no_rawat; ?></td>
              </tr>
              <tr>
                <td style="width: 20%;">Nama Pasien</td>
                <td style="width: 1%;">:</td>
                <td><?php echo $row->nm_pasien; ?></td>
              </tr>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td><?php echo $row->tgl_registrasi; ?> | <?php echo $row->jam_reg; ?></td>
              </tr>
              <tr>
                <td>Poliklinik</td>
                <td>:</td>
                <td><?php echo $row->status_lanjut; ?></td>
              </tr>
              <tr>
                <td>SEP BPJS</td>
                <td>:</td>
                <td>
                    @if ($sep)
                        <table class="table table-bordered sub-table" style="width:100%;">
                            <tr><th>Nomor SEP</th><td>{{ $sep->no_sep }}</td></tr>
                            <tr><th>Tanggal SEP</th><td>{{ $sep->tglsep }}</td></tr>
                            <tr><th>Nomor Kartu</th><td>{{ $sep->no_kartu }}</td></tr>
                            <tr><th>Tanggal Lahir</th><td>{{ $sep->tanggal_lahir }}</td></tr>
                            <tr><th>Nomor Telepon</th><td>{{ $sep->notelep }}</td></tr>
                            <tr>
                              <th>Jenis Pelayanan</th>
                              <td>
                                  @if (!$sep || is_null($sep->jnspelayanan))
                                      -
                                  @elseif ($sep->jnspelayanan == 1)
                                      Rawat Inap
                                  @elseif ($sep->jnspelayanan == 2)
                                      Rawat Jalan
                                  @else
                                      Tidak Dikenal
                                  @endif
                              </td>
                            </tr>
                            <tr><th>Tanggal Pulang</th><td>{{ $sep->tglpulang }}</td></tr>
                            <tr><th>Sub/Spesialis</th><td>{{ $sep->nmpolitujuan }}</td></tr>
                            <tr><th>Dokter</th><td>{{ $sep->nmdpdjp }}</td></tr>
                            <tr><th>Diagnosa Awal</th><td>{{ $sep->nmdiagnosaawal }}</td></tr>
                            <tr><th>Peserta</th><td>{{ $sep->peserta }}</td></tr>
                            <tr>
                              <th>Jenis Kunjungan</th>
                              <td>
                                @if (!$sep || is_null($sep->tujuankunjungan))
                                  -
                              @elseif ($sep->tujuankunjungan == 0)
                                  Normal
                              @elseif ($sep->tujuankunjungan == 1)
                                  Prosedur
                              @elseif ($sep->tujuankunjungan == 2)  
                                  Konsul Dokter 
                              @else
                                  Tidak Dikenal
                              @endif
                              </td>
                            </tr>
                            <tr><th>Kelas Hak</th><td>Kelas {{ $sep->klsrawat }}</td></tr>
                            <tr><th>Kelas Rawat</th><td>{{ $sep->klsnaik }}</td></tr>
                            <tr><th>Catatan</th></th><td>{{ $sep->catatan }}</td></tr>
                        </table>
                    @else
                        <em>Data SEP tidak tersedia.</em>
                    @endif
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
