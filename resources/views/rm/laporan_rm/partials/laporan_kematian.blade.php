{{-- Partial: Kematian --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('kematian') }}" method="POST">
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
    {{-- Rekapitulasi per Golongan --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-dizzy me-2"></i>REKAPITULASI KEMATIAN PER GOLONGAN
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Anggota</th><th>PNS</th><th>Keluarga</th><th>Siswa Dikbang</th>
                            <th>Siswa Diktuk</th><th>Mandiri</th><th>BPJS</th><th>Lainnya</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $anggota->total }}</td><td>{{ $pns->total }}</td><td>{{ $keluarga->total }}</td>
                            <td>{{ $dikbang->total }}</td><td>{{ $diktuk->total }}</td><td>{{ $umum->total }}</td>
                            <td>{{ $bpjs }}</td><td>{{ $lainnya->total }}</td>
                            <td class="td-red">{{ $total }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Per Unit --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#6f42c1;">
            <i class="bx bx-building me-2"></i>REKAPITULASI KEMATIAN PER UNIT
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Rawat Inap</th><th>IGD</th><th>Total</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>{{ $ranap->total2 }}</td><td>{{ $igd->total2 }}</td>
                            <td class="td-red">{{ $total2 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>
