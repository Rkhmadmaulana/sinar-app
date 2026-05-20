@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
<section class="section dashboard">

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div style="overflow: hidden; white-space: nowrap;">
                    <h2 class="marquee-text">
                        <a style="color: inherit; text-decoration: none;">
                            Jika Anda mengalami kendala, mohon segera menghubungi Tim IT untuk pengecekan dan perbaikan lebih lanjut.
                        </a>
                    </h2>
                </div>
            </div>
            <div class="card">
                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                    <img src="{{ asset('public/img/logo.png') }}" alt="Profile" class="rounded-circle" style="width: 200px; height: auto;">
                    <h3>SELAMAT DATANG</h3>

                    <h5 class="card-title">Motto</h5>
                    <p class="small fst-italic">Senyum - Sapa - Santun</p>

                    <h5 class="card-title">Visi</h5>
                    <p class="small fst-italic">Menjadikan RSUD Pangeran Jaya Sumitra Kotabaru sebagai Institusi Pelayanan Kesehatan Terdepan dengan Pelayanan Berkualitas dan Terstandar.</p>

                    <h5 class="card-title">Misi</h5>
                    <p class="small fst-italic">Menjalankan tata kelola rumah sakit yang terakreditasi dengan dukungan sumber daya manusia yang profesional.</p>
                    <p class="small fst-italic">Mengembangkan RSUD Pangeran Jaya Sumitra Kotabaru menjadi rumah sakit rujukan regional dan rumah sakit pendidikan.</p>
                    <p class="small fst-italic">Memenuhi kebutuhan pelanggan dengan menyediakan layanan keperawatan yang berkualitas.</p>
                    <p class="small fst-italic">Meningkatkan sarana dan prasarana penunjang pelayanan.</p>

                    <br><br>
                    <p>Silakan cek kembali nanti untuk melihat pembaruan terbaru.</p>
                        <p><a href="{{ route('profil') }}" target="_blank" >Salam Hormat Tim IT ❤️</a></p>
            </div>
        </div>
    </div>

</section>


<style>


.marquee-text {
    font-size: 1rem;
    font-weight: bold; 
    color: #333; 
    text-align: center;
    padding: 10px;
    white-space: nowrap;
    overflow: hidden;
    display: inline-block;
    animation: marquee 25s linear infinite; 
    width: 100%; 
}


@keyframes marquee {
    0% {
        transform: translateX(100%); 
    }
    100% {
        transform: translateX(-100%); 
    }
}


.marquee-text a {
    color: inherit;
    text-decoration: none;
}

.marquee-text a:hover {
    text-decoration: underline; 
}

.card {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 192, 203, 0.3); /* Subtle pink border */
    box-shadow: 0 2px 4px rgba(255, 192, 203, 0.1); /* Subtle pink shadow */
}

</style>

@endsection
