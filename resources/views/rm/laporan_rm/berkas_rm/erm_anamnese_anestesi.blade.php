<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ERM - Ranap</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{ asset('public/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('public/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('public/vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{asset('public/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <!-- <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet"> -->

  <!-- JQuery DataTable Css -->
  <link href="{{asset('public/vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('public/vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css')}}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{asset('public/css/style.css')}}" rel="stylesheet">

  <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

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
  <h5 style="color:BLUE;">ERM Ranap - Anamnese Anestesi</h5>
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
                <td style="width: 20%; text-align: left; vertical-align: top;">Pemeriksaan</td>  
                  <td style="width: 1%;  vertical-align: top;">:</td>  
                  <td style="width:79%;">
                  <?php 
                  foreach($anamnese_anestesi as $anamnese_an){ ?>
                  <table border="1px"  style="width:100%;">
                      <tr>
                        <th bgcolor='#FFFAF8'>Tanggal</th>
                        <th bgcolor='#FFFAF8'>Jam</th>
                        <th bgcolor='#FFFAF8'>Suhu (C)</th>
                        <th bgcolor='#FFFAF8'>Berat (Kg)</th>
                        <th bgcolor='#FFFAF8'>Tinggi (Cm)</th>
                        <th bgcolor='#FFFAF8'>Tensi (mmHg)</th>
                        <th bgcolor='#FFFAF8'>Nadi (/ Menit)</th>
                        <th bgcolor='#FFFAF8'>RR (/ Menit)</th>
                        <th bgcolor='#FFFAF8'>SpO2%</th>
                        <th bgcolor='#FFFAF8'>Alergi</th>
                        <th bgcolor='#FFFAF8'>Kesadaran</th>
                        <th bgcolor='#FFFAF8'>GCS (E,V,M)</th>
                      </tr>
                      <tr>
                        <td><?php echo $anamnese_an->tgl_perawatan; ?></td>
                        <td><?php echo $anamnese_an->jam_rawat; ?></td>
                        <td><?php echo $anamnese_an->suhu_tubuh; ?></td>
                        <td><?php echo $anamnese_an->berat; ?></td>
                        <td><?php echo $anamnese_an->tinggi; ?></td>
                        <td><?php echo $anamnese_an->tensi; ?></td>
                        <td><?php echo $anamnese_an->nadi; ?></td>
                        <td><?php echo $anamnese_an->respirasi; ?></td>
                        <td><?php echo $anamnese_an->spo2; ?></td>
                        <td><?php echo $anamnese_an->alergi; ?></td>
                        <td><?php echo $anamnese_an->kesadaran; ?></td>
                        <td><?php echo $anamnese_an->gcs; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Keluhan</th>
                        <td colspan="9"><?php echo $anamnese_an->keluhan; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Pemeriksaan</th>
                        <td colspan="9"><?php echo $anamnese_an->pemeriksaan; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Tindak Lanjut(PLAN)</th>
                        <td colspan="9"><?php echo $anamnese_an->rtl; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">EKG</th>
                        <td colspan="9"><?php echo $anamnese_an->ekg; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Rontgent Thoraks/CT/MRI</th>
                        <td colspan="9"><?php echo $anamnese_an->radiologi; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Hb/Ht/Wbc/Plt</th>
                        <td colspan="9"><?php echo $anamnese_an->lab; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Elektrolit</th>
                        <td colspan="9"><?php echo $anamnese_an->elektrolit; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Permasalahan/Diagnosis</th>
                        <td colspan="9"><?php echo $anamnese_an->diagnosis; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Urinalisis</th>
                        <td colspan="9"><?php echo $anamnese_an->urinalisis; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Status Fisik ASA</th>
                        <td colspan="9"><?php echo $anamnese_an->status_fisik; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Lain-lain</th>
                        <td colspan="9"><?php echo $anamnese_an->lain_lain; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Teknik anestesi yang dipakai</th>
                        <td colspan="9"><?php echo $anamnese_an->teknik; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Penatalaksanaan nyeri</th>
                        <td colspan="9"><?php echo $anamnese_an->nyeri; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Rencana perawatan post anestesi</th>
                        <td colspan="9"><?php echo $anamnese_an->rencana_perawatan; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Instruksi pra anestesi</th>
                        <td colspan="9"><?php echo $anamnese_an->instruksi; ?></td>
                      </tr>
                      <tr>
                        <th bgcolor='#FFFAF8' colspan="2">Dokter Anestesi</th>
                        <td colspan="9"><?php echo $anamnese_an->nm_dokter; ?></td>
                      </tr>
                  </table><br>
                    <?php } ?>
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
