<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Dashboard - Sinar</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{asset('public/img/favicon.png') }}" rel="icon">
  <link href="{{asset('public/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Bootstrap CSS - Pilih salah satu -->
  <link href="{{asset('public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('public/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('public/vendor/remixicon/remixicon.css')}}" rel="stylesheet">

  <!-- DataTables CSS - Gunakan CDN untuk compatibility -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Template Main CSS File -->
  <link href="{{asset('public/css/style.css')}}" rel="stylesheet">

  @stack('styles')
</head>

<body>

  <!-- ======= Header ======= -->
  @include('layout.header')
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  @include('layout.sidebar')
  <!-- End Sidebar-->

  <!-- Start #main -->
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>@yield('title')</h1>
      <nav>
        <ol class="breadcrumb">
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      @yield('content')
    </section>

  </main>
  <!-- End #main -->

  <!-- ======= Footer ======= -->
  @include('layout.footer')
  <!-- End Footer -->

  <!-- jQuery - Pilih salah satu -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <!-- Bootstrap JS - Konsisten dengan CSS -->
  <script src="{{asset('public/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

  <!-- Vendor JS Files -->
  <script src="{{asset('public/vendor/apexcharts/apexcharts.min.js')}}"></script>
  <script src="{{asset('public/vendor/chart.js/chart.umd.js')}}"></script>
  <script src="{{asset('public/vendor/echarts/echarts.min.js')}}"></script>
  <script src="{{asset('public/vendor/quill/quill.js')}}"></script>
  <script src="{{asset('public/vendor/tinymce/tinymce.min.js')}}"></script>
  <script src="{{asset('public/vendor/php-email-form/validate.js')}}"></script>

  <!-- Plugin JS Files -->
  <script src="{{asset('public/vendor/jquery-slimscroll/jquery.slimscroll.js')}}"></script>
  <script src="{{asset('public/vendor/jquery-validation/jquery.validate.js')}}"></script>
  <script src="{{asset('public/vendor/jquery-steps/jquery.steps.js')}}"></script>
  <script src="{{asset('public/vendor/jquery-countto/jquery.countTo.js')}}"></script>

  <!-- DataTables JS - Gunakan CDN untuk compatibility -->
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


  <!-- Template Main JS File -->
  <script src="{{asset('public/js/main.js')}}"></script>

<script>
  $(document).ready(function() {

    //$('#kelengkapan').dataTable( {
    //   responsive: true,
    //   order: [[ 0, 'desc' ]]
    //});

    $('#kelengkapan2').dataTable( {
       responsive: true,
       order: [[ 0, 'desc' ]]
    });
    
    // Event delegation agar tetap aktif setelah pagination
    $(document).on('click', 'a[data-toggle="modal"]', function(e) {
        e.preventDefault();

        var target_modal = $(this).data('target');
        var remote_content = $(this).attr('href');

        if (remote_content.indexOf('#') === 0) return;

        var modal = $(target_modal);
        var modalBodyContent = modal.find('#modal-body-content');

        modalBodyContent.html("Loading...");

        modalBodyContent.load(remote_content, function(response, status, xhr) {
            if (status === "error") {
                modalBodyContent.html("<p style='color: red;'>Gagal mengambil data.</p>");
            }
            modal.modal('show');
        });
    });

    // Reset modal setelah ditutup
    $('#ermModal').on('hidden.bs.modal', function() {
        $(this).find('#modal-body-content').html('');
        $(this).removeData('bs.modal');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();

        setTimeout(function() {
            if (!$('.modal.show').length) {
                $('body').css({ 'overflow': 'auto', 'padding-right': '0' });
            }
        }, 300);
    });

  });
</script>

@stack('scripts')
</body>

</html>