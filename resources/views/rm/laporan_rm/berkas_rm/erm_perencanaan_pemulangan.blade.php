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
<h5 style="color:BLUE;">ERM Ranap - Discharge Planning</h5>
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
            <tr><td style="width: 20%; vertical-align: top;">No Rawat</td><td style="width: 1%; vertical-align: top;">:</td><td style="width: 79%; vertical-align: top;">{{ $row->no_rawat }}</td></tr>
            <tr><td>Tanggal Registrasi</td><td>:</td><td>{{ $row->tgl_registrasi }} | {{ $row->jam_reg }}</td></tr>
            <tr><td>Poliklinik</td><td>:</td><td>Ranap</td></tr>
            <tr><td>Perencanaan Pemulangan</td><td>:</td><td>
              @if (!$perencanaan_pemulangan->isEmpty())
              @foreach($perencanaan_pemulangan as $data)
                <table class="table table-bordered sub-table" style="width:100%; margin-bottom: 20px;">
                  <thead>
                    <tr>
                      <th>Rencana Pulang</th><th>Diagnosa Medis</th><th>Pengaruh RI Pasien & Keluarga</th>
                      <th>Keterangan Pengaruh RI Pasien & Keluarga</th><th>Pengaruh RI Pekerjaan / Sekolah</th>
                      <th>Keterangan Pengaruh RI Pekerjaan / Sekolah</th><th>Pengaruh RI Keuangan</th>
                      <th>Keterangan Pengaruh RI Keuangan</th><th>Antisipasi Masalah</th>
                      <th>Keterangan Antisipasi Masalah</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{ $data->rencana_pulang }}</td>
                      <td>{{ $data->diagnosa_medis }}</td>
                      <td>{{ $data->pengaruh_ri_pasien_dan_keluarga }}</td>
                      <td>{{ $data->keterangan_pengaruh_ri_pasien_dan_keluarga }}</td>
                      <td>{{ $data->pengaruh_ri_pekerjaan_sekolah }}</td>
                      <td>{{ $data->keterangan_pengaruh_ri_pekerjaan_sekolah }}</td>
                      <td>{{ $data->pengaruh_ri_keuangan }}</td>
                      <td>{{ $data->keterangan_pengaruh_ri_keuangan }}</td>
                      <td>{{ $data->antisipasi_masalah_saat_pulang }}</td>
                      <td>{{ $data->keterangan_antisipasi_masalah_saat_pulang }}</td>
                    </tr>
                  </tbody>
                </table>
                <table class="table table-bordered sub-table" style="width:100%; margin-bottom: 20px;">
                  <thead>
                    <tr>
                      <th>Bantuan Diperlukan</th><th>Keterangan Bantuan Diperlukan</th>
                      <th>Membantu Keperluan</th><th>Keterangan Yang Membantu Keperluan</th>
                      <th>Tinggal Sendiri</th><th>Keterangan Pasien Tinggal Sendiri</th>
                      <th>Peralatan Medis</th><th>Keterangan Peralatan Medis</th>
                      <th>Alat Bantu</th><th>Keterangan Memerlukan Alat Bantu</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{ $data->bantuan_diperlukan_dalam }}</td>
                      <td>{{ $data->keterangan_bantuan_diperlukan_dalam }}</td>
                      <td>{{ $data->adakah_yang_membantu_keperluan }}</td>
                      <td>{{ $data->keterangan_adakah_yang_membantu_keperluan }}</td>
                      <td>{{ $data->pasien_tinggal_sendiri }}</td>
                      <td>{{ $data->keterangan_pasien_tinggal_sendiri }}</td>
                      <td>{{ $data->pasien_menggunakan_peralatan_medis }}</td>
                      <td>{{ $data->keterangan_pasien_menggunakan_peralatan_medis }}</td>
                      <td>{{ $data->pasien_memerlukan_alat_bantu }}</td>
                      <td>{{ $data->keterangan_pasien_memerlukan_alat_bantu }}</td>
                    </tr>
                  </tbody>
                </table>
                <table class="table table-bordered sub-table" style="width:100%; margin-bottom: 20px;">
                  <thead>
                    <tr>
                      <th>Keperawatan Khusus</th><th>Keterangan Keperawatan Khusus</th>
                      <th>Memenuhi Kebutuhan</th><th>Keterangan Memenuhi Kebutuhan</th>
                      <th>Nyeri Kronis</th><th>Keterangan Nyeri Kronis</th>
                      <th>Edukasi Kesehatan</th><th>Keterangan Edukasi Kesehatan</th>
                      <th>Keterampilan Khusus</th><th>Keterangan Keterampilan Khusus</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{ $data->memerlukan_perawatan_khusus }}</td>
                      <td>{{ $data->keterangan_memerlukan_perawatan_khusus }}</td>
                      <td>{{ $data->bermasalah_memenuhi_kebutuhan }}</td>
                      <td>{{ $data->keterangan_bermasalah_memenuhi_kebutuhan }}</td>
                      <td>{{ $data->memiliki_nyeri_kronis }}</td>
                      <td>{{ $data->keterangan_memiliki_nyeri_kronis }}</td>
                      <td>{{ $data->memerlukan_edukasi_kesehatan }}</td>
                      <td>{{ $data->keterangan_memerlukan_edukasi_kesehatan }}</td>
                      <td>{{ $data->memerlukan_keterampilan_khusus }}</td>
                      <td>{{ $data->keterangan_memerlukan_keterampilan_khusus }}</td>
                    </tr>
                  </tbody>
                </table>
                <table class="table table-bordered sub-table" style="width:100%; margin-bottom: 20px;">
                  <thead>
                    <tr><th>Alasan Masuk</th><th>Pasien/Keluarga</th></tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{ $data->alasan_masuk }}</td>
                      <td>{{ $data->nama_pasien_keluarga }}</td>
                    </tr>
                  </tbody>
                </table>
              @endforeach
              @else
                Tidak ada data Perencanaan Pemulangan.
              @endif
            </td></tr>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</div>