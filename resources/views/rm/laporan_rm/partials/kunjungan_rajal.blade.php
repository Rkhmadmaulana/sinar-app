{{-- Partial: Pasien Rawat Jalan --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('kunjunganrajal') }}" method="POST">
        @csrf
        <div class="filter-row">
            <div class="filter-group">
                <label>Dari Tanggal</label>
                <input type="date" name="tgl1" value="{{ $tgl1 ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="tgl2" value="{{ $tgl2 ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="filter-group btn-group">
                <button type="submit" name="tombol" value="filter" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <button type="submit" name="download_pdf" value="1" class="btn btn-danger"><i class="bx bx-download me-1"></i>PDF</button>
            </div>
        </div>
    </form>
</div>

<div class="partial-body">
    {{-- Pengunjung & Kunjungan Table --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-bar-chart-alt-2 me-2"></i>REKAPITULASI PENGUNJUNG DAN KUNJUNGAN RAWAT JALAN
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-red" colspan="9">Pengunjung</th>
                            <th class="th-green" colspan="9">Kunjungan</th>
                        </tr>
                        <tr>
                            <th class="th-red">Anggota</th><th class="th-red">PNS</th><th class="th-red">Keluarga</th>
                            <th class="th-red">Siswa Dikbang</th><th class="th-red">Siswa Diktuk</th><th class="th-red">Mandiri</th>
                            <th class="th-red">BPJS</th><th class="th-red">Lainnya</th><th class="th-red">Total</th>
                            <th class="th-green">Anggota</th><th class="th-green">PNS</th><th class="th-green">Keluarga</th>
                            <th class="th-green">Siswa Dikbang</th><th class="th-green">Siswa Diktuk</th><th class="th-green">Mandiri</th>
                            <th class="th-green">BPJS</th><th class="th-green">Lainnya</th><th class="th-green">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $anggotapolri->anggota_polri }}</td>
                            <td>{{ $anggotapns->anggota_pns }}</td>
                            <td>{{ $anggotakelpolri->anggota_kel_polri }}</td>
                            <td>{{ $dikbang->siswa_dikbang }}</td>
                            <td>{{ $diktuk->siswa_diktuk }}</td>
                            <td>{{ $pasien_umum->pasienumum }}</td>
                            <td>{{ $total_pengunjung_bpjs }}</td>
                            <td>{{ $pasien_other->pasienother }}</td>
                            <td class="td-red">{{ $total_pengunjung }}</td>
                            <td>{{ $anggotapolri->kunjungan_anggota_polri }}</td>
                            <td>{{ $anggotapns->kunjungan_anggota_pns }}</td>
                            <td>{{ $anggotakelpolri->kunjungan_kel_polri }}</td>
                            <td>{{ $dikbang->kunjungan_siswa_dikbang }}</td>
                            <td>{{ $diktuk->kunjungan_siswa_diktuk }}</td>
                            <td>{{ $pasien_umum->kunjungan_pasienumum }}</td>
                            <td>{{ $total_kunjungan_bpjs }}</td>
                            <td>{{ $pasien_other->kunjungan_pasienother }}</td>
                            <td class="td-green">{{ $total_kunjungan }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Hasil Rawat Jalan --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#17a2b8;">
            <i class="bx bx-check-shield me-2"></i>Laporan Hasil Rawat Jalan Tahun {{ $tahun }}
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Hasil Pelayanan</th><th>Jumlah Pasien</th><th>Persentase</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Sembuh</td><td>{{ $sembuhRalan ?? 0 }}</td><td>{{ $persenSembuhRalan ?? 0 }}%</td></tr>
                        <tr><td>Rujuk ke Rawat Inap</td><td>{{ $ranapRalan ?? 0 }}</td><td>{{ $persenRanapRalan ?? 0 }}%</td></tr>
                        <tr><td>Lainnya</td><td>{{ $lainnyaRalan ?? 0 }}</td><td>{{ $persenLainnyaRalan ?? 0 }}%</td></tr>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8f9fa;font-weight:600;"><td class="text-center">TOTAL</td><td>{{ $totalRalan ?? 0 }}</td><td>100%</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>
