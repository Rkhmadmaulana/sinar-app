{{-- Partial: Pasien Rawat Ranap --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('kunjunganranap') }}" method="POST">
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
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-bed me-2"></i>REKAPITULASI KUNJUNGAN RAWAT INAP
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Anggota</th><th>PNS</th><th>Keluarga</th><th>Siswa Dikbang</th>
                            <th>Siswa Diktuk</th><th>Mandiri</th><th>BPJS</th><th>Lainnya</th><th>Total</th>
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
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Keluar | Periode: {{ $tgllap }}</p>
</div>
