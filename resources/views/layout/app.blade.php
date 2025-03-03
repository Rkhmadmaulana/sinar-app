<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - Sinar</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset ('vendor/bootstrap/css/bootstrap11.css') }}" rel="stylesheet">
  <link href="{{asset ('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <!-- <link href="{{asset('vendor/simple-datatables/style.css')}}" rel="stylesheet"> -->

  <!-- JQuery DataTable Css -->
  <link href="{{asset('vendor/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('vendor/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css')}}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{asset('css/style.css')}}" rel="stylesheet">

  <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

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



  <!-- Vendor JS Files -->
  <script src="{{asset('vendor/apexcharts/apexcharts.min.js')}}"></script>
  <!-- Jquery Core Js -->
  <script src="{{asset('vendor/jquery/jquery.min.js')}}"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> jika js apex tidak berfungsi -->
  <script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('vendor/chart.js/chart.umd.js')}}"></script>
  <script src="{{asset('vendor/echarts/echarts.min.js')}}"></script>
  <script src="{{asset('vendor/quill/quill.js')}}"></script>
  <!-- <script src="{{asset('vendor/simple-datatables/simple-datatables.js')}}"></script> -->
  <script src="{{asset('vendor/tinymce/tinymce.min.js')}}"></script>
  <script src="{{asset('vendor/php-email-form/validate.js')}}"></script>
  <!-- Slimscroll Plugin Js -->
  <script src="{{asset('vendor/jquery-slimscroll/jquery.slimscroll.js')}}"></script>
  <!-- Jquery Validation Plugin Css -->
  <script src="{{asset('vendor/jquery-validation/jquery.validate.js')}}"></script>
  <!-- JQuery Steps Plugin Js -->
  <script src="{{asset('vendor/jquery-steps/jquery.steps.js')}}"></script>
  <!-- datatable -->
  <script src="{{asset('vendor/jquery-datatable/jquery.dataTables.js')}}"></script>
  <script src="{{asset('vendor/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js')}}"></script>
  <script src="{{asset('vendor/jquery-datatable/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
  <!-- Jquery CountTo Plugin Js -->
  <script src="{{asset('vendor/jquery-countto/jquery.countTo.js')}}"></script>


  <!-- Template Main JS File -->
  <script src="{{asset('js/main.js')}}"></script>

<script>
  $(document).ready(function() {

    $('#kelengkapan').dataTable( {
       responsive: true,
       order: [[ 0, 'desc' ]]
                        
      } );

      $('#kelengkapan2').dataTable( {
       responsive: true,
       order: [[ 0, 'desc' ]]
                        
      } );
    
      // Event delegation agar tetap aktif setelah pagination
  $(document).on('click', 'a[data-toggle="modal"]', function(e) {
        e.preventDefault(); // Mencegah navigasi langsung

        var target_modal = $(this).data('target'); // Ambil ID modal
        var remote_content = $(this).attr('href'); // URL dari href

        if (remote_content.indexOf('#') === 0) return; // Jika href mengarah ke #

        var modal = $(target_modal);
        var modalBodyContent = modal.find('#modal-body-content');

        modalBodyContent.html("Loading..."); // Set loading sebelum request

        // Load konten modal SEBELUM modal ditampilkan
        modalBodyContent.load(remote_content, function(response, status, xhr) {
            if (status === "error") {
                modalBodyContent.html("<p style='color: red;'>Gagal mengambil data.</p>");
            }

            // Setelah data selesai dimuat, baru buka modal
            modal.modal('show');
        });
    });
    // Reset modal setelah ditutup dan pastikan scroll normal kembali
    $('#ermModal').on('hidden.bs.modal', function() {
        $(this).find('#modal-body-content').html(''); // Kosongkan isi modal
        $(this).removeData('bs.modal'); // Hapus cache modal agar fresh saat dibuka lagi
        $('body').removeClass('modal-open'); // Hapus efek modal open di body
        $('.modal-backdrop').remove(); // Hapus overlay modal

        // **Fix agar scroll tidak hilang setelah modal ditutup**
        setTimeout(function() {
            if (!$('.modal.show').length) { // Jika tidak ada modal yang terbuka
                $('body').css({ 'overflow': 'auto', 'padding-right': '0' });
            }
        }, 300);
    });

  });
  

</script>
</body>

</html>