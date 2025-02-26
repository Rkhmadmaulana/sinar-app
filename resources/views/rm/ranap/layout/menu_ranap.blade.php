<div class="container-xxl  container-p-y">
    <!-- Menu -->

    <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
        <li class="nav-item">
            <button class="nav-link" onclick="window.location.href='{{ route('ranap') }}'" data-url="{{ route('ranap') }}">
                <i class="tf-icons bx bx-home-circle"></i> Perawatan
            </button>
        </li>
        {{-- <li class="nav-item">
                  <button class="nav-link" onclick="window.location.href=''"
                      >
                      <i class="tf-icons bx bx-home-circle"></i> Instalasi Bedah Sentral
                  </button>
              </li> --}}

    </ul>
    <!-- Menu -->
</div>

<script>
    // Mendapatkan URL saat ini
    var currentUrl = window.location.href;

    // Memeriksa setiap tombol di dalam daftar dan menetapkan kelas active jika sesuai dengan URL
    document.querySelectorAll('.nav-link').forEach(function(button) {
        // Mendapatkan URL yang terkait dengan tombol
        var buttonUrl = button.getAttribute('data-url');

        // Memeriksa apakah URL tombol sama dengan URL saat ini
        if (buttonUrl && currentUrl.includes(buttonUrl)) {
            button.classList.add('active');
        }
    });
</script>
