{{-- Partial: Radiologi & Laboratorium --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('laporan_radlab') }}" method="POST">
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
            <i class="bx bx-home-circle me-2"></i>LAPORAN RADIOLOGI & LABORATORIUM
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-green" colspan="2">Pemeriksaan</th>
                        </tr>
                        <tr>
                            <th>Radiologi</th>
                            <th>Laboratorium</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-size:1.2rem;font-weight:600;">{{ $totalRadiologi }}</td>
                            <td style="font-size:1.2rem;font-weight:600;">{{ $totalLab }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>
