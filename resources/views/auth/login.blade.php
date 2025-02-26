<!DOCTYPE html>
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>SIMBHA</title>
    <meta name="description" content="" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo-rs.PNG')}}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('vendor/css/pages/page-auth.css') }}" />
    <!-- Helpers -->
    <script src="{{ asset('vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('js/config.js') }}"></script>
  </head>
  <style>
    body {
      background-image: url('{{asset('img/bg-sinar.jpg')}}'); /* Ganti dengan path gambar Anda */
      background-size: cover; /* Untuk menyesuaikan ukuran gambar agar menutupi seluruh body */
      background-position: center; /* Untuk menengahkan gambar */
    }
    .card {
        /* Transparan */
        background-color: rgba(128, 244, 244, 0.3); /* Nilai alpha (0.7) menentukan tingkat transparansi */
        /* Atur atribut lain sesuai kebutuhan Anda */
      }
  </style>
  <body>
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="card-header text-center">
                <div class="align-center p-b-15"><a href="" target="_blank"><img src="{{ asset('img/logo-rs.PNG')}}" height="100" width="100"></a></div>
                
              </div>
              <!-- /Logo -->
              <form action="{{ route('login-proses') }}" method="POST">
                @csrf
                <div class="mb-3">
                  @error('username')
                  <small style="color: red;">{{ $message }}</small><br>
                  @enderror
                  <label for="email" class="form-label">Username</label>
                  <input
                    type="text"
                    class="form-control"
                    id="email"
                    name="username" 
                    placeholder="Enter your username"
                    autofocus required
                  />
                </div>
                <div class="mb-3 form-password-toggle">
                  {{-- <div class="d-flex justify-content-between"> --}}
                    <!-- <a href="auth-forgot-password-basic.html">
                      <small>Forgot Password?</small>
                    </a> -->
                  {{-- </div> --}}
                  @error('password')
                    <small style="color: red;">{{ $message }}</small><br>
                    @enderror
                    <label class="form-label" for="password">Password</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" required
                    />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                </div>
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                </div>
              </form>
            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Untuk Alert-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Tidak bisa klik kanan dan screenshot saat prod --}}
      <script>
        // Cek apakah APP_DEBUG = false
        var isDebugMode = @json(env('APP_DEBUG', false));

        // Jika debug mode false, disable right click
        if (!isDebugMode) {
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });

            document.addEventListener('visibilitychange', function() {
              if (document.visibilityState === 'hidden') {
                  document.body.classList.add('blurred');
              } else {
                  document.body.classList.remove('blurred');
              }
            });

          window.addEventListener('keydown', function(e) {
              // Prevent screenshot using PrintScreen key
              if (e.key === 'PrintScreen' || (e.ctrlKey && e.key === 'PrintScreen')) {
                  e.preventDefault();
              }
              // Jika ingin menangani lebih banyak kombinasi tombol untuk pencegahan
              if (e.ctrlKey && e.key === 's') {
                  e.preventDefault();
              }
              if (e.ctrlKey && e.key === 'p') {
                  e.preventDefault();
              }
              // Cek apakah Ctrl + Shift + I ditekan
              if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                  e.preventDefault();
              }

              // Cek apakah Ctrl + Shift + J ditekan
              if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                  e.preventDefault();
              }

              // Cek apakah F12 ditekan
              if (e.key === 'F12' || e.keyCode === 123) {
                  e.preventDefault();
              }

              // Cek apakah Ctrl + U ditekan (untuk memblokir View Source)
              if (e.ctrlKey && e.key === 'U') {
                  e.preventDefault();
              }
          });

          // Mencegah penggunaan DevTools
          window.oncontextmenu = function() { return false; }

          if (typeof console !== "undefined") {
              console.log = function() {};
              console.warn = function() {};
              console.error = function() {};
          }

        }
      </script>
    {{-- End Tidak bisa klik kanan dan screenshot saat prod --}}

    @if($message = Session::get('failed'))
    <script>
      Swal.fire({
      icon: "error",
      title: "{!! addslashes($message) !!}",
      showConfirmButton: false,
      timer: 1500
    });
    </script>
    @endif
    @if($message = Session::get('success'))
    <script>
      Swal.fire({
      icon: "success",
      title: "{!! addslashes($message) !!}",
      showConfirmButton: false,
      timer: 1500
    });
    </script>
@endif
  </body>
</html>
