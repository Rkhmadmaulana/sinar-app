@extends('layout.app')

@section('title', 'Dashboard')
@section('content')

<section class="section dashboard">

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                <img src="{{asset('img/logo.png')}}" alt="Profile" class="rounded-circle" style="width: 200px; height: auto;">
                <h2 class="card-title
                ">SINAR</h2>
                <h3>Selamat Datang</h3>

                <h5 class="card-title">Motto</h5>
                <p class="small fst-italic"> Senyum - Sapa - Santun</p>
                
                <h5 class="card-title">Visi</h5>
                <p class="small fst-italic">Menjadikan RSUD Pangeran Jaya Sumitra Kotabaru sebagai Institusi Pelayanan Kesehatan Terdepan dengan Pelayanan Berkualitas dan Terstandar.</p>

                <h5 class="card-title">Misi</h5>
                <p class="small fst-italic">Menjalankan tata kelola rumah sakit yang terakreditasi dengan dukungan sumber daya manusia yang profesional.</p>
                <p class="small fst-italic">Mengembangkan RSUD Pangeran Jaya Sumitra Kotabaru menjadi rumah sakit rujukan regional dan rumah sakit pendidikan.</p>
                <p class="small fst-italic">Memenuhi kebutuhan pelanggan dengan menyediakan layanan keperawatan yang berkualitas.</p> 
                <p class="small fst-italic">Meningkatkan sarana dan prasarana penunjang pelayanan.</p>    
            </div>
        </div>
    </div>
</div>
@endsection