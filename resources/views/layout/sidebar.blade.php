<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="{{ route('dashboard') }}">
          <i class="bx bx-grid-alt"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-heading">ADMIN</li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#settings-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-gear-fill"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="settings-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="settings-account.html">
              <i class="ri-account-box-fill" style="font-size: 17px;"></i><span>Account</span>
            </a>
          </li>
        </ul>
      </li><!-- End Settings Nav -->

      <li class="nav-heading">KINERJA LAYANAN</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="#">
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

      <li class="nav-item">
        <a class="nav-link collapsed" href="#">
          <i class="ri-bar-chart-grouped-fill"></i>
          <span>Laporan Rekam Medis</span>
        </a>
      </li><!-- End Laporan Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->
