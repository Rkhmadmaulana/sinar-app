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
  <h5 style="color:BLUE;">ERM Ranap - Triase</h5>
  <div class="table-responsive">
    <table id="erm" class="table table-bordered table-striped" style="width:100%;">
      <thead>
        <tr><th>Riwayat</th></tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <table class="table table-bordered" style="width:100%;">
              <tr><td style="width: 20%;">No Rawat</td><td style="width: 1%;">:</td><td>{{ $row->no_rawat }}</td></tr>
              <tr><td>Tanggal Registrasi</td><td>:</td><td>{{ $row->tgl_registrasi }} | {{ $row->jam_reg }}</td></tr>
              <tr><td>Poliklinik</td><td>:</td><td>Ranap</td></tr>
              <tr>
                <td>Catatan TRIASE IGD</td>
                <td>:</td>
                <td>
                  @foreach ($data_triase_igd as $data_triase_igd)
                    <table class="table table-bordered sub-table" style="width:100%;">
                      <tr><th>Tanggal Kunjungan</th><td>{{ $data_triase_igd->tgl_kunjungan }}</td></tr>
                      <tr><th>Cara Datang</th><td>{{ $data_triase_igd->cara_masuk }}</td></tr>
                      <tr><th>Transportasi</th><td>{{ $data_triase_igd->alat_transportasi }}</td></tr>
                      <tr><th>Alasan Kedatangan</th><td>{{ $data_triase_igd->alasan_kedatangan }}</td></tr>
                      <tr><th>Keterangan Kedatangan</th><td>{{ $data_triase_igd->keterangan_kedatangan }}</td></tr>
                      <tr><th>Macam Kasus</th><td>{{ $data_triase_igd->nama_kasus }}</td></tr>
                      @if (!empty($data_triase_igd->anamnesa_singkat))
                        <tr><th>Anamnesa Singkat</th><td>{{ $data_triase_igd->anamnesa_singkat }}</td></tr>
                      @endif
                      @if (!empty($data_triase_igd->keluhan_utama) || !empty($data_triase_igd->kebutuhan_khusus))
                        <tr><th>Keluhan Utama</th><td>{{ $data_triase_igd->keluhan_utama }}</td></tr>
                        <tr><th>Kebutuhan Khusus</th><td>{{ $data_triase_igd->kebutuhan_khusus }}</td></tr>
                      @endif
                      <tr>
                        <th>Tanda Vital</th>
                        <td>
                          Suhu (C): {{ $data_triase_igd->suhu }}, Tensi: {{ $data_triase_igd->tekanan_darah }},
                          Nadi: {{ $data_triase_igd->nadi }}, Respirasi: {{ $data_triase_igd->pernapasan }},
                          Saturasi O²: {{ $data_triase_igd->saturasi_o2 }}, Nyeri: {{ $data_triase_igd->nyeri }}
                        </td>
                      </tr>
                      <tr><th style="background-color: #f4f4d6;">PEMERIKSAAN</th><td style="background-color: #f4f4d6;"><strong>{{ $data_triase_igd->skala_triase }}</strong></td></tr>
                      <tr>
                        <th>Daftar Pemeriksaan</th>
                        <td>
                          @php $pemeriksaan = $data_triase_igd->pemeriksaan ?? []; @endphp
                          @if (count($pemeriksaan) > 0)
                            <ul style="margin: 0; padding-left: 18px;">
                              @foreach ($pemeriksaan as $item)
                                <li><strong>{{ $item['nama'] ?? '-' }}</strong><br><small>{{ $item['pengkajian'] ?? '-' }}</small></li>
                              @endforeach
                            </ul>
                          @else
                            Tidak ada pemeriksaan tercatat.
                          @endif
                        </td>
                      </tr>
                      @if (!empty($data_triase_igd->plan_primer) || !empty($data_triase_igd->plan_sekunder))
                        <tr><th>Plan</th><td>{{ $data_triase_igd->plan_primer ?? $data_triase_igd->plan_sekunder }}</td></tr>
                      @endif
                      <tr><th>Tanggal & Jam</th><td>{{ $data_triase_igd->tanggaltriase_primer ?? ($data_triase_igd->tanggaltriase_sekunder ?? '-') }}</td></tr>
                      <tr><th>Catatan</th><td>{{ $data_triase_igd->catatan_primer ?? ($data_triase_igd->catatan_sekunder ?? '-') }}</td></tr>
                      <tr><th>Dokter/Petugas Jaga IGD</th><td>{{ $data_triase_igd->nama_primer ?? ($data_triase_igd->nama_sekunder ?? '-') }}</td></tr>
                    </table><br>
                  @endforeach
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
