<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>ERM - Ranap</title>
  <meta content="" name="description" />
  <meta content="" name="keywords" />
  <link href="{{ asset('img/favicon.png') }}" rel="icon" />
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet" />
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/quill/quill.snow.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/quill/quill.bubble.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/remixicon/remixicon.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css') }}" rel="stylesheet" />
  <link href="{{ asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
  <style>
    table td,
    table th {
      padding: 5px;
    }
    /* styling sub table sama seperti code 2 */
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
  <h5 style="color:BLUE;">ERM Ranap - Asesmen Resiko Jatuh</h5>
  <div class="table-responsive">
    <table class="table table-bordered table-striped" style="width:100%;">
      <thead>
        <tr>
          <th style="text-align:left;">Riwayat</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <table class="table table-bordered" style="width:100%;">
              <tr>
                <td style="width:20%;">No Rawat</td>
                <td style="width:1%;">:</td>
                <td>{{ $data->no_rawat }}</td>
              </tr>
              <tr>
                <td>Tanggal Registrasi</td>
                <td>:</td>
                <td>{{ $data->tgl_registrasi }} | {{ $data->jam_reg }}</td>
              </tr>
              <tr>
                <td>Poliklinik</td>
                <td>:</td>
                <td>Ranap</td>
              </tr>
              <tr>
                <td>Asesmen Resiko Jatuh Anak / Dewasa / Lansia</td>
                <td>:</td>
                <td>
                  @if($has_anak)
                    <a href="{{ route('erm_ranap_resikoanak', ['id' => $data->no_rawat]) }}" target="_blank">Asesmen Resiko Jatuh Anak</a><br />
                  @endif
                  @if($has_lansia)
                    <a href="{{ route('erm_ranap_resikolansia', ['id' => $data->no_rawat]) }}" target="_blank">Asesmen Resiko Jatuh Lansia</a><br />
                  @endif
                  @unless($has_anak || $has_lansia)
                    Tidak ada data asesmen ditemukan.
                  @endunless
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
