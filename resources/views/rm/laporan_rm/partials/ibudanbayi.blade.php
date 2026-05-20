{{-- Partial: Ibu dan Bayi --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('ibudanbayi') }}" method="POST">
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
    {{-- Kelahiran --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-child me-2"></i>LAPORAN IBU DAN BAYI - KELAHIRAN
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Bayi Lahir Hidup</th><th>Bayi Lahir Mati</th><th>Bayi Ranap Mati</th><th>Total</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>{{ $bayilahir->total }}</td><td>{{ $bayimati->total }}</td>
                            <td>{{ $bayimatiranap->total }}</td><td class="td-red">{{ $total_lahirmati }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Berat Bayi --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#17a2b8;">
            <i class="bx bx-plus-medical me-2"></i>LAPORAN IBU DAN BAYI - BERAT LAHIR
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Berat Bayi >= 2500 Gr</th><th>Berat Bayi <= 2500 Gr</th><th>Total</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>{{ $bayi25->total }}</td><td>{{ $bayi24->total }}</td>
                            <td class="td-red">{{ $total_berat }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>
