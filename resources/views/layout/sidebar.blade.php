@php
$level = session()->get('level');

$user = DB::table('user')
    ->select('*') // Gunakan select untuk memilih kolom
    ->where('id_user', session()->get('id_user')) // Gunakan where untuk parameter
    ->first();
$peg = DB::table('pegawai')
    ->select('nama', 'photo') // Pilih kolom yang diinginkan
    ->where('nik', session()->get('nik')) // Gunakan where untuk parameter
    ->first();

// Tentukan URL foto pegawai


@endphp

<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="{{ route('dashboard') }}">
          <i class="bx bx-grid-alt"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      {{-- Modul Admin --}}
          @if($level=="admin")
          <li class="nav-heading">ADMIN</li>
          <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#settings-nav" data-bs-toggle="collapse" href="#">
              <i class="bi bi-gear-fill"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="settings-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
              <li>
                <a href="{{ route('account') }}">
                  <i class="ri-account-box-fill" style="font-size: 17px;"></i><span>Account</span>
                </a>
              </li>
            </ul>
          </li>
          @endif
        {{-- Modul Admin --}}
        




        {{-- Modul RM --}}
        @if($level=="admin" || $level== "pegawai")  
      <li class="nav-heading">KINERJA LAYANAN</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('kinerja') }}">
          <i class="bx bxs-spreadsheet"></i>
          <span>Kinerja Layanan</span>
        </a>
      </li><!-- End Kinerja Layanan Page Nav -->

      <li class="nav-heading">TREND</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('poliklinik') }}">
          <i class="ri-wheelchair-fill"></i>
          <span>Rawat Jalan</span>
        </a>
      </li><!-- End Ralan Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('ranap') }}">
          <i class="bx bxs-bed"></i>
          <span>Rawat Inap</span>
        </a>
      </li><!-- End Ranap Page Nav -->

      <li class="nav-heading">LAPORAN</li>

      <!-- Laporan Rekam Medis -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('kelengkapan') }}">
          <i class="ri-bar-chart-grouped-fill"></i>
          <span>Laporan Rekam Medis</span>
        </a>
      </li>

      <!-- Laporan Pasien -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#laporan-pasien-nav" data-bs-toggle="collapse" href="#">
          <i class="ri-user-heart-fill"></i><span>Laporan Pasien</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="laporan-pasien-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('laporan.pasien-meninggal') }}">
              <i class="bi bi-circle"></i><span>Data Pasien Meninggal</span>
            </a>
          </li>
          <li>
            <a href="{{ route('laporan.laporan_persalinan') }}">
              <i class="bi bi-circle"></i><span>Laporan Persalinan</span>
            </a>
          </li>
        </ul>
      </li>

      <!-- Laporan Rujukan -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#laporan-rujukan-nav" data-bs-toggle="collapse" href="#">
          <i class="ri-hospital-fill"></i><span>Laporan Rujukan</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="laporan-rujukan-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('laporan.rujukan-keluar') }}">
              <i class="bi bi-circle"></i><span>Rujukan Keluar</span>
            </a>
          </li>
          <li>
            <a href="{{ route('laporan.rujukan-masuk') }}">
              <i class="bi bi-circle"></i><span>Rujukan Masuk</span>
            </a>
          </li>
        </ul>
      </li>

      <!-- Laporan RL (SIRS) -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#laporan-rl-nav" data-bs-toggle="collapse" href="#">
          <i class="ri-file-list-3-fill"></i><span>Rekapitulasi Laporan</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="laporan-rl-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('laporan.rl-3-4-pengunjung') }}">
              <i class="bi bi-circle"></i><span>RL 3.4 Rekapitulasi Pengunjung</span>
            </a>
          </li>
          <li>
            <a href="{{ route('laporan.rujukan-rekap') }}">
              <i class="bi bi-circle"></i><span>RL 3.10 Rekapitulasi Rujukan</span>
            </a>
          </li>
          <li>
            <a href="{{ route('morbiditas-rawat-inap') }}">
              <i class="bi bi-circle"></i><span>RL 4.1 Morbiditas Rawat Inap</span>
            </a>
          </li>
          <li>
            <a href="{{ route('morbiditas-rawat-jalan') }}">
              <i class="bi bi-circle"></i><span>RL 5.1 Morbiditas Rawat Jalan</span>
            </a>
          </li>
        </ul>
      </li>

      <!-- Laporan Farmasi -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('totalresep') }}">
          <i class="bx bxs-capsule"></i>
          <span>Laporan Farmasi</span>
        </a>
      </li>
      @endif
      {{-- Modul RM --}}

      <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('logout') }}">
          <i class="bi bi-box-arrow-right"></i>
          <span>Sign Out</span>
        </a>
      </li>

    </ul>

  </aside><!-- End Sidebar-->