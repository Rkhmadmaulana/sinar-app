<div class="container-xxl  container-p-y">
        
    <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
        <li class="nav-item">
            <button class="nav-link" onclick="window.location.href='{{ route('poliklinik') }}'" data-url="{{ route('poliklinik') }}">
                <i class="tf-icons bx bx-home-circle"></i> Poliklinik
            </button>
        </li>
        <!-- <li class="nav-item">
            <button class="nav-link" onclick="window.location.href='{{ route('allpoliklinikkhusus',['kd_poli' => 'IRM']) }}'"
                data-url="{{ route('allpoliklinikkhusus',['kd_poli' => 'IRM']) }}">
                <i class="tf-icons bx bx-home-circle"></i> Rehab Medik
            </button>
        </li> -->
        <li class="nav-item">
        <button class="nav-link" onclick="window.location.href='{{ route('hemodialisa') }}'" data-url="{{ route('hemodialisa') }}">
            <i class="tf-icons bx bx-home-circle"></i> Unit Hemodialisa
        </button>
        </li>
        <li class="nav-item">
        <button class="nav-link" onclick="window.location.href='{{ route('igdk') }}'" data-url="{{ route('igdk') }}">
            <i class="tf-icons bx bx-home-circle"></i> Instalasi Gawat Darurat
        </button>
        </li>
        <li class="nav-item">
        <button class="nav-link" onclick="window.location.href='{{ route('allpoliklinikkhusus',['kd_poli' => 'MCU']) }}'"
            data-url="{{ route('allpoliklinikkhusus',['kd_poli' => 'MCU']) }}">
            <i class="tf-icons bx bx-home-circle"></i> Medical Chek Up
        </button>
        </li>
        <!-- <li class="nav-item">
        <button class="nav-link" onclick="window.location.href='{{ route('lab') }}'" data-url="{{ route('lab') }}">
            <i class="tf-icons bx bx-home-circle"></i> Instalasi Laboratorium
        </button>
        </li>
        <li class="nav-item">
        <button class="nav-link" onclick="window.location.href='{{ route('radiologi') }}'" data-url="{{ route('radiologi') }}">
            <i class="tf-icons bx bx-home-circle"></i> Instalasi Radiologi
        </button>
        </li> -->
    </ul>

</div>
<script>
  // Mendapatkan URL saat ini
  var currentUrl = window.location.href;

  // Memeriksa setiap tombol di dalam daftar dan menetapkan kelas active jika sesuai dengan URL
  document.querySelectorAll('.nav-link').forEach(function (button) {
      // Mendapatkan URL yang terkait dengan tombol
      var buttonUrl = button.getAttribute('data-url');

      // Memeriksa apakah URL tombol sama dengan URL saat ini
      if (buttonUrl && currentUrl.includes(buttonUrl)) {
          button.classList.add('active');
      }
  });
</script>