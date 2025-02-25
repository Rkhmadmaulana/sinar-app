<div class="col-md-6">
    <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <ul class="nav nav-pills mb-3 nav-fill" role="tablist">

                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('kunjunganrajal') }}'"
                                data-url="{{ route('kunjunganrajal') }}">
                                <i class="tf-icons bx bx-handicap"></i> Pasien Rawat Jalan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('kunjunganranap') }}'"
                                data-url="{{ route('kunjunganranap') }}">
                                <i class="tf-icons bx bx-hotel"></i> Pasien Rawat Ranap
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('penyakitterbanyak') }}'"
                                data-url="{{ route('penyakitterbanyak') }}">
                                <i class="tf-icons bx bx-line-chart"></i> Penyakit Terbanyak
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('penyakitmenular') }}'"
                                data-url="{{ route('penyakitmenular') }}">
                                <i class="tf-icons bx bx-plus-medical"></i> Penyakit Menular
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('igd') }}'"
                                data-url="{{ route('igd') }}">
                                <i class="tf-icons bx bx-bell-plus"></i> Laporan IGD
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('kematian') }}'"
                                data-url="{{ route('kematian') }}">
                                <i class="tf-icons bx bx-dizzy"></i> Kematian
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('pertumbuhan') }}'"
                                data-url="{{ route('pertumbuhan') }}">
                                <i class="tf-icons bx bx-trending-up"></i> Pertumbuhan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('laporan_radlab') }}'"
                                data-url="{{ route('laporan_radlab') }}">
                                <i class="tf-icons bx bx-bell-plus"></i> Radiologi & Laboratorium
                            </button>
                        </li>
                </div>
            </div>
        </div>
    </div>
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
