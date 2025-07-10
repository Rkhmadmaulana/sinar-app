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
  <h5 style="color:BLUE;">ERM Ranap - Penilaian Pra Operasi</h5>
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
                <td>Penilaian Pra Operasi</td>
                <td>:</td>
                <td>
                  @if ($ppo)
                    @php
                      $persiapan = $ppo->hal_hal_yang_perludi_persiapkan ?? '';
                      $analgenetik = '-';
                      $transfusi = '-';
                      $icu = '-';
                      if (Str::contains($persiapan, 'Rencana Pemberian Analgenetik Pasca Operasi:')) {
                          preg_match('/Rencana Pemberian Analgenetik Pasca Operasi:\s*(.*?)\s*Rencana Transfusi Darah:/', $persiapan, $match1);
                          $analgenetik = $match1[1] ?? '-';
                      }
                      if (Str::contains($persiapan, 'Rencana Transfusi Darah:')) {
                          preg_match('/Rencana Transfusi Darah:\s*(.*?)\s*Rencana Perawatan Icu Pasca Operasi:/', $persiapan, $match2);
                          $transfusi = $match2[1] ?? '-';
                      }
                      if (Str::contains($persiapan, 'Rencana Perawatan Icu Pasca Operasi:')) {
                          preg_match('/Rencana Perawatan Icu Pasca Operasi:\s*(.*)$/', $persiapan, $match3);
                          $icu = $match3[1] ?? '-';
                      }
                    @endphp

                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>Nama Dokter</th><td>{{ $ppo->nm_dokter }}</td></tr>
                      <tr><th>Anamnesa</th><td>{{ $ppo->ringkasan_klinik }}</td></tr>
                      <tr><th>Pemeriksaan Fisik</th><td>{{ $ppo->pemeriksaan_fisik }}</td></tr>
                      <tr><th>Pemeriksaan Penunjang / Diagnostik</th><td>{{ $ppo->pemeriksaan_diagnostik }}</td></tr>
                      <tr><th>Diagnosa</th><td>{{ $ppo->diagnosa_pre_operasi }}</td></tr>
                      <tr><th>Terapi Pra Operasi</th><td>{{ $ppo->terapi_pre_operasi }}</td></tr>
                      <tr><th>Rencana Tindakan Operasi</th><td>{{ $ppo->rencana_tindakan_bedah }}</td></tr>
                      <tr><th>Rencana Pemberian Analgenetik Pasca Operasi</th><td>{{ $analgenetik }}</td></tr>
                      <tr><th>Rencana Transfusi Darah</th><td>{{ $transfusi }}</td></tr>
                      <tr><th>Rencana Perawatan Icu Pasca Operasi</th><td>{{ $icu }}</td></tr>
                    </table>
                  @else
                    <em>Data Pra Operasi tidak tersedia.</em>
                  @endif
                </td>
               </tr><!--<p>CATATAN : BERKAS PRA OPERASI INI BELUM BERFUNGSI SEPENUHNYA. FITUR CHECKLIST BELUM AKTIF.</p> -->
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    
  </div>
</body>
</html>
