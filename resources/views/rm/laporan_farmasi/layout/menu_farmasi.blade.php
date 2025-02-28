<div class="col-md-6">
    <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link" onclick="window.location.href='{{ route('totalresep') }}'"
                                data-url="{{ route('totalresep') }}">
                                <i class="tf-icons bx bxs-capsule"></i> Total Resep
                            </button>
                        </li>
                    </ul>
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
