{{-- Partial: Penyakit Terbanyak --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('penyakitterbanyak') }}" method="POST">
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
    {{-- Rawat Inap --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-line-chart me-2"></i>10 PENYAKIT TERBANYAK RAWAT INAP
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Penyakit</th><th>Anggota</th><th>PNS</th><th>Keluarga</th>
                            <th>Siswa Dikbang</th><th>Siswa Diktuk</th><th>Mandiri</th>
                            <th>BPJS</th><th>Lainnya</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diagnosa as $a)
                        <tr>
                            <td class="td-green text-start">{{ $a->nama }} ({{ $a->kode }})</td>
                            @php
                                $q = function($gol, $st, $kd, $t1, $t2) {
                                    $r = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien dp JOIN reg_periksa rp ON dp.no_rawat=rp.no_rawat JOIN pasien_polri pp ON pp.no_rkm_medis=rp.no_rkm_medis WHERE dp.kd_penyakit=? AND rp.status_lanjut=? AND rp.tgl_registrasi BETWEEN ? AND ? AND $gol AND rp.kd_pj='BPJ'", [$kd,$st,$t1,$t2]);
                                    return $r[0]->total ?? 0;
                                };
                                $qUmu = function($st,$kd,$t1,$t2) {
                                    $r = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien dp JOIN reg_periksa rp ON dp.no_rawat=rp.no_rawat WHERE dp.kd_penyakit=? AND rp.status_lanjut=? AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj='UMU'", [$kd,$st,$t1,$t2]);
                                    return $r[0]->total ?? 0;
                                };
                                $qOther = function($st,$kd,$t1,$t2) {
                                    $r = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien dp JOIN reg_periksa rp ON dp.no_rawat=rp.no_rawat WHERE dp.kd_penyakit=? AND rp.status_lanjut=? AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj NOT IN('UMU','BPJ')", [$kd,$st,$t1,$t2]);
                                    return $r[0]->total ?? 0;
                                };
                                $anggota = $q("pp.golongan_polri='1'", "Ranap", $a->kode, $tgl1, $tgl2);
                                $pns = $q("(pp.golongan_polri IN('2','7','8','10'))", "Ranap", $a->kode, $tgl1, $tgl2);
                                $kel = $q("(pp.golongan_polri IN('9','3'))", "Ranap", $a->kode, $tgl1, $tgl2);
                                $dikbang = $q("(pp.golongan_polri IN('4','6'))", "Ranap", $a->kode, $tgl1, $tgl2);
                                $diktuk = $q("pp.golongan_polri='5'", "Ranap", $a->kode, $tgl1, $tgl2);
                                $umum = $qUmu("Ranap", $a->kode, $tgl1, $tgl2);
                                $bpjRaw = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien dp JOIN reg_periksa rp ON dp.no_rawat=rp.no_rawat WHERE dp.kd_penyakit=? AND rp.status_lanjut='Ranap' AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj='BPJ'", [$a->kode,$tgl1,$tgl2]);
                                $bpjs = ($bpjRaw[0]->total ?? 0) - $anggota - $pns - $dikbang - $diktuk - $kel;
                                $other = $qOther("Ranap", $a->kode, $tgl1, $tgl2);
                            @endphp
                            <td>{{ $anggota }}</td><td>{{ $pns }}</td><td>{{ $kel }}</td>
                            <td>{{ $dikbang }}</td><td>{{ $diktuk }}</td><td>{{ $umum }}</td>
                            <td>{{ $bpjs }}</td><td>{{ $other }}</td>
                            <td class="td-red">{{ $a->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Rawat Jalan --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#17a2b8;">
            <i class="bx bx-line-chart me-2"></i>10 PENYAKIT TERBANYAK RAWAT JALAN
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Penyakit</th><th>Anggota</th><th>PNS</th><th>Keluarga</th>
                            <th>Siswa Dikbang</th><th>Siswa Diktuk</th><th>Mandiri</th>
                            <th>BPJS</th><th>Lainnya</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diagnosa_ralan as $a)
                        <tr>
                            <td class="td-green text-start">{{ $a->nama }} ({{ $a->kode }})</td>
                            @php
                                $anggota = $q("pp.golongan_polri='1'", "Ralan", $a->kode, $tgl1, $tgl2);
                                $pns = $q("(pp.golongan_polri IN('2','7','8','10'))", "Ralan", $a->kode, $tgl1, $tgl2);
                                $kel = $q("(pp.golongan_polri IN('9','3'))", "Ralan", $a->kode, $tgl1, $tgl2);
                                $dikbang = $q("(pp.golongan_polri IN('4','6'))", "Ralan", $a->kode, $tgl1, $tgl2);
                                $diktuk = $q("pp.golongan_polri='5'", "Ralan", $a->kode, $tgl1, $tgl2);
                                $umum = $qUmu("Ralan", $a->kode, $tgl1, $tgl2);
                                $bpjRaw = DB::select("SELECT COUNT(*) as total FROM diagnosa_pasien dp JOIN reg_periksa rp ON dp.no_rawat=rp.no_rawat WHERE dp.kd_penyakit=? AND rp.status_lanjut='Ralan' AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj='BPJ'", [$a->kode,$tgl1,$tgl2]);
                                $bpjs = ($bpjRaw[0]->total ?? 0) - $anggota - $pns - $dikbang - $diktuk - $kel;
                                $other = $qOther("Ralan", $a->kode, $tgl1, $tgl2);
                            @endphp
                            <td>{{ $anggota }}</td><td>{{ $pns }}</td><td>{{ $kel }}</td>
                            <td>{{ $dikbang }}</td><td>{{ $diktuk }}</td><td>{{ $umum }}</td>
                            <td>{{ $bpjs }}</td><td>{{ $other }}</td>
                            <td class="td-red">{{ $a->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pasien Baru --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#6c757d;">
            <i class="bx bx-user-plus me-2"></i>PASIEN BARU RSUD PANGERAN JAYA SUMITRA
        </div>
        <div class="card-body-content text-center py-3">
            <span style="font-size:2rem;font-weight:700;color:#343a40;">{{ $pasien_baru->pasienbaru ?? 0 }}</span>
            <div class="text-muted" style="font-size:12px;">Jumlah Pasien Baru</div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>
