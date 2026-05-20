{{-- Partial: Kegiatan Operasi --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('operasi') }}" method="POST">
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
            <i class="bx bx-shield-plus me-2"></i>JUMLAH PASIEN OPERASI
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Ruangan</th><th>BPJS</th><th>Umum</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($op as $a)
                        <tr>
                            <td class="td-green fw-semibold">{{ $a->jenis_op }}</td>
                            @php
                                $qBpj = DB::select("SELECT COUNT(*) as total FROM reg_periksa a INNER JOIN kamar_inap b ON b.no_rawat=a.no_rawat INNER JOIN booking_operasi c ON c.no_rawat=b.no_rawat WHERE a.tgl_registrasi BETWEEN ? AND ? AND c.tanggal BETWEEN ? AND ? AND a.kd_pj='BPJ' AND c.status IN('Proses Operasi','Selesai')",[$tgl1,$tgl2,$tgl1,$tgl2]);
                                $qUm = DB::select("SELECT COUNT(*) as total FROM reg_periksa a INNER JOIN kamar_inap b ON b.no_rawat=a.no_rawat INNER JOIN booking_operasi c ON c.no_rawat=b.no_rawat WHERE a.tgl_registrasi BETWEEN ? AND ? AND c.tanggal BETWEEN ? AND ? AND a.kd_pj='PJ2' AND c.status IN('Proses Operasi','Selesai')",[$tgl1,$tgl2,$tgl1,$tgl2]);
                            @endphp
                            <td>{{ $qBpj[0]->total ?? 0 }}</td>
                            <td>{{ $qUm[0]->total ?? 0 }}</td>
                            <td class="td-red">{{ $a->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>
