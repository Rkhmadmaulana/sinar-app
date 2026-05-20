{{-- Partial: Pertumbuhan --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('pertumbuhan') }}" method="POST">
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
    <div class="alert alert-info py-2 mb-3" style="font-size:12px;border-radius:8px;">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Perbandingan:</strong> Pasien Bulan Lalu = {{ $dari ?? '-' }} S/D {{ $sampai ?? '-' }} | Bulan Ini = {{ $tgllap }}<br>
        <strong>Rumus:</strong> ((Bulan Ini - Bulan Lalu) / Bulan Lalu) x 100%
    </div>

    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-trending-up me-2"></i>PERTUMBUHAN PRODUKTIFITAS
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="2" class="th-green">Kunjungan Rawat Jalan</th>
                            <th colspan="2" class="th-green">Kunjungan IGD</th>
                            <th colspan="2" class="th-green">Pasien Rawat Inap</th>
                            <th colspan="2" class="th-green">Radiologi</th>
                            <th colspan="2" class="th-green">Laboratorium</th>
                        </tr>
                        <tr>
                            <th>Jumlah</th><th>Pertumbuhan</th>
                            <th>Jumlah</th><th>Pertumbuhan</th>
                            <th>Jumlah</th><th>Pertumbuhan</th>
                            <th>Jumlah</th><th>Pertumbuhan</th>
                            <th>Jumlah</th><th>Pertumbuhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $sqlrajal->total }}</td>
                            <td>
                                @if($pertumbuhan_ralan >= 0)<span style="color:#28a745;"><i class="bx bx-trending-up"></i> {{ $pertumbuhan_ralan }}%</span>@endif
                                @if($pertumbuhan_ralan < 0)<span style="color:#dc3545;"><i class="bx bx-trending-down"></i> {{ $pertumbuhan_ralan }}%</span>@endif
                            </td>
                            <td>{{ $sqligd->total }}</td>
                            <td>
                                @if($pertumbuhan_igd >= 0)<span style="color:#28a745;"><i class="bx bx-trending-up"></i> {{ $pertumbuhan_igd }}%</span>@endif
                                @if($pertumbuhan_igd < 0)<span style="color:#dc3545;"><i class="bx bx-trending-down"></i> {{ $pertumbuhan_igd }}%</span>@endif
                            </td>
                            <td>{{ $sqlranap->total }}</td>
                            <td>
                                @if($pertumbuhan_ranap >= 0)<span style="color:#28a745;"><i class="bx bx-trending-up"></i> {{ $pertumbuhan_ranap }}%</span>@endif
                                @if($pertumbuhan_ranap < 0)<span style="color:#dc3545;"><i class="bx bx-trending-down"></i> {{ $pertumbuhan_ranap }}%</span>@endif
                            </td>
                            <td>{{ $sqlrad->total }}</td>
                            <td>
                                @if($pertumbuhan_rad >= 0)<span style="color:#28a745;"><i class="bx bx-trending-up"></i> {{ $pertumbuhan_rad }}%</span>@endif
                                @if($pertumbuhan_rad < 0)<span style="color:#dc3545;"><i class="bx bx-trending-down"></i> {{ $pertumbuhan_rad }}%</span>@endif
                            </td>
                            <td>{{ $sqllab->total }}</td>
                            <td>
                                @if($pertumbuhan_lab >= 0)<span style="color:#28a745;"><i class="bx bx-trending-up"></i> {{ $pertumbuhan_lab }}%</span>@endif
                                @if($pertumbuhan_lab < 0)<span style="color:#dc3545;"><i class="bx bx-trending-down"></i> {{ $pertumbuhan_lab }}%</span>@endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi (Rajal, Ranap) dan Tanggal Periksa (Radiologi, Laboratorium)</p>
</div>
