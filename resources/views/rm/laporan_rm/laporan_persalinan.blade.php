@extends('layout.app')

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Laporan Persalinan</h3>
    </div>
    <div class="card-body">
      {{-- Filter Tanggal --}}
      <form action="{{ route('laporan.laporan_persalinan') }}" method="GET" class="mb-4">
        <div class="row align-items-center mb-3">
          <div class="col-auto">
            <label for="tanggal_awal">Dari</label>
            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
          </div>
          <div class="col-auto">
            <label for="tanggal_akhir">Sampai</label>
            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
          </div>
          <div class="col-auto mt-4">
            <button type="submit" class="btn btn-primary">Cari</button>
          </div>
        </div>
      </form>

      <div class="row mb-3">
        <div class="col-md-6">
          <h5>Data Persalinan Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
        </div>
        <div class="col-md-6">
          <form action="{{ route('laporan.laporan_persalinan') }}" method="GET" id="searchForm">
            <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
            <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            <div class="input-group">
              <input type="text" class="form-control" placeholder="Cari Pasien..." name="keyword" value="{{ $keyword }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      {{-- Tabel Utama --}}
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No.</th>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>No. Rawat</th>
              <th>No. Rekam Medis</th>
              <th>Nama Pasien</th>
              <th>Dokter DPJP</th>
            </tr>
          </thead>
          <tbody>
          @foreach($data as $key => $item)
            <tr>
                <td>{{ ($data->currentPage() - 1) * $data->perPage() + $key + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('H:i') }}</td>
                <td>
                    <a href="javascript:void(0)" class="btn-detail"
                        data-no_rawat="{{ $item->no_rawat }}"
                        data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                        data-jam="{{ \Carbon\Carbon::parse($item->tanggal)->format('H:i:s') }}">
                        {{ $item->no_rawat }}
                    </a>
                </td>
                <td>{{ $item->no_rkm_medis }}</td>
                <td>{{ $item->nm_pasien }}</td>
                <td>{{ $item->nm_dokter ?? '-' }}</td>
            </tr>
            <tr class="detail-row" id="detail-{{ $item->no_rawat }}" style="display: none;">
                <td colspan="7"><div class="detail-container"></div></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3 d-flex justify-content-center">
        {{ $data->appends(['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'keyword' => $keyword])->links() }}
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-detail').forEach(btn => {
    btn.addEventListener('click', () => {
      const noRawat = btn.dataset.no_rawat;
      const tanggal = btn.dataset.tanggal;
      const jam     = btn.dataset.jam;
      const detailRow = document.getElementById(`detail-${noRawat}`);
      const container = detailRow.querySelector('.detail-container');

      // hide all
      document.querySelectorAll('.detail-row').forEach(r => {
        r.style.display = 'none';
        r.querySelector('.detail-container').innerHTML = '';
      });
      container.innerHTML = '<p>Loading…</p>';
      detailRow.style.display = '';

      fetch(`{{ url('/laporan/persalinan/detail') }}/${btoa(noRawat)}?tanggal=${tanggal}&jam=${jam}`)
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(data => {
          const p = data.persalinan;
          const k = data.kebidanan;
          let html = '';

          if (!p && (!Array.isArray(k) || k.length === 0)) {
            container.innerHTML = `<div class="alert alert-warning">
              ❌ Tidak ada data persalinan atau observasi kebidanan.</div>`;
            detailRow.scrollIntoView({ behavior: 'smooth' });
            return;
          }

          // ==== Detail Persalinan ====
          if (p) {
            html += `
              <h5 class="text-primary mb-3">📝 Detail Catatan Persalinan</h5>
              <div class="accordion mb-4" id="accPersalinan">

                <!-- Waktu Persalinan -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hWaktu">
                    <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#cWaktu">
                      ⏱ Waktu Persalinan
                    </button>
                  </h2>
                  <div id="cWaktu" class="accordion-collapse collapse show" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>No. Rawat</th><td>${p.no_rawat}</td></tr>
                        <tr><th>Mulai</th><td>${p.mulai}</td></tr>
                        <tr><th>Selesai</th><td>${p.selesai}</td></tr>
                        <tr><th>Kala 1/2/3/Jml</th>
                          <td>${p.waktu_persalinan_kala1||'-'} /
                              ${p.waktu_persalinan_kala2||'-'} /
                              ${p.waktu_persalinan_kala3||'-'} /
                              ${p.waktu_persalinan_jumlah||'-'}</td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Jahitan & Perineum -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hJahitan">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#cJahitan">
                      ✂️ Jahitan & Perineum
                    </button>
                  </h2>
                  <div id="cJahitan" class="accordion-collapse collapse" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>Perineum</th><td>${p.perineum||'-'}</td></tr>
                        <tr><th>Jahitan Luar</th>
                          <td>${p.jahitan_luar_1||'-'} / ${p.jahitan_luar_2||'-'}</td>
                        </tr>
                        <tr><th>Jahitan Dalam</th>
                          <td>${p.jahitan_dalam_1||'-'} / ${p.jahitan_dalam_2||'-'}</td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Bayi -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hBayi">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#cBayi">
                      👶 Kondisi Bayi
                    </button>
                  </h2>
                  <div id="cBayi" class="accordion-collapse collapse" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>Anak</th><td>${p.anak||'-'}</td></tr>
                        <tr><th>Status Lahir</th><td>${p.status_lahir||'-'}</td></tr>
                        <tr><th>APGAR</th><td>${p.apgar_score||'-'}</td></tr>
                        <tr><th>BB / PB</th><td>${p.bb||'-'} / ${p.pb||'-'}</td></tr>
                        <tr><th>Kelainan</th><td>${p.kelainan||'-'}</td></tr>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Plasenta, Ketuban, Tali Pusat -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hPlasenta">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#cPlasenta">
                      🌡️ Plasenta & Tali Pusat
                    </button>
                  </h2>
                  <div id="cPlasenta" class="accordion-collapse collapse" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>Ketuban</th><td>${p.ketuban||'-'}</td></tr>
                        <tr><th>Placenta</th><td>${p.placenta||'-'}</td></tr>
                        <tr><th>Ukuran</th><td>${p.ukuran||'-'}</td></tr>
                        <tr><th>Tali Pusat</th><td>${p.tali_pusat||'-'}</td></tr>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Darah & Kontraksi -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hDarah">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#cDarah">
                      🩸 Darah & Kontraksi
                    </button>
                  </h2>
                  <div id="cDarah" class="accordion-collapse collapse" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>Darah 1-4</th>
                          <td>${p.darah_keluar_kala_1||'-'},${p.darah_keluar_kala_2||'-'},${p.darah_keluar_kala_3||'-'},${p.darah_keluar_kala_4||'-'}</td>
                        </tr>
                        <tr><th>Total Darah</th><td>${p.darah_keluar_jumlah||'-'}</td></tr>
                        <tr><th>Kontraksi</th><td>${p.kontraksi_uterus||'-'}</td></tr>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Vital & Umum Ibu -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hVital">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#cVital">
                      ❤️ Vital & Kondisi Umum
                    </button>
                  </h2>
                  <div id="cVital" class="accordion-collapse collapse" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>TD</th><td>${p.td||'-'}</td></tr>
                        <tr><th>RR</th><td>${p.rr||'-'}</td></tr>
                        <tr><th>SPO₂</th><td>${p.spo2||'-'}</td></tr>
                        <tr><th>Nadi</th><td>${p.nadi||'-'}</td></tr>
                        <tr><th>Suhu</th><td>${p.suhu||'-'}</td></tr>
                        <tr><th>PPV</th><td>${p.ppv||'-'}</td></tr>
                        <tr><th>Kondisi Umum</th><td>${p.kondisi_umum||'-'}</td></tr>
                        <tr><th>Petugas</th><td>${p.nama_petugas||'-'}</td></tr>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Pengobatan & Catatan -->
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hPengobatan">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#cPengobatan">
                      💊 Pengobatan & Catatan
                    </button>
                  </h2>
                  <div id="cPengobatan" class="accordion-collapse collapse" data-bs-parent="#accPersalinan">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>Pengobatan</th><td>${p.pengobatan||'-'}</td></tr>
                      </table>
                      <div class="mt-2">${p.catatan||'<em>- Tidak ada catatan.</em>'}</div>
                    </div>
                  </div>
                </div>

              </div>
            `;
          }

          // ==== Observasi Kebidanan ====
          if (Array.isArray(k) && k.length) {
            html += `<h5 class="text-success mb-3">👩‍⚕️ Observasi Kebidanan</h5>
                     <div class="accordion" id="accKbd">`;
            k.forEach((o,i) => {
              html += `
                <div class="accordion-item">
                  <h2 class="accordion-header" id="hKbd${i}">
                    <button class="accordion-button ${i? 'collapsed':''}"
                            data-bs-toggle="collapse"
                            data-bs-target="#cKbd${i}">
                      ⏱ ${o.tgl_perawatan} ${o.jam_rawat}
                    </button>
                  </h2>
                  <div id="cKbd${i}" class="accordion-collapse collapse ${!i?'show':''}" data-bs-parent="#accKbd">
                    <div class="accordion-body p-0">
                      <table class="table table-sm mb-0">
                        <tr><th>GCS</th><td>${o.gcs||'-'}</td></tr>
                        <tr><th>TD</th><td>${o.td||'-'}</td></tr>
                        <tr><th>HR</th><td>${o.hr||'-'}</td></tr>
                        <tr><th>RR</th><td>${o.rr||'-'}</td></tr>
                        <tr><th>Suhu</th><td>${o.suhu||'-'}</td></tr>
                        <tr><th>SpO₂</th><td>${o.spo2||'-'}</td></tr>
                        <tr><th>Kontradiksi</th><td>${o.kontradiksi||'-'}</td></tr>
                        <tr><th>BJJ</th><td>${o.bjj||'-'}</td></tr>
                        <tr><th>PPV</th><td>${o.ppv||'-'}</td></tr>
                        <tr><th>VT</th><td>${o.vt||'-'}</td></tr>
                        <tr><th>Petugas</th><td>${o.nama_petugas||'-'}</td></tr>
                        <tr><th>Dokter</th><td>${o.nm_dokter||'-'}</td></tr>
                      </table>
                    </div>
                  </div>
                </div>
              `;
            });
            html += `</div>`;
          } else if (!p) {
            html += `<p class="text-warning">⚠️ Belum ada observasi kebidanan.</p>`;
          }

          container.innerHTML = html;
          detailRow.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(err => {
          container.innerHTML = `<div class="alert alert-danger">
            ⚠️ Gagal memuat: ${err.message}
          </div>`;
        });
    });
  });
});
</script>
@endsection

