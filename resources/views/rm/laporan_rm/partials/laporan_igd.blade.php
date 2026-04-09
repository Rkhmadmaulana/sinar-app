{{-- Partial: Laporan IGD --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('igd') }}" method="POST">
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
    {{-- Rekapitulasi Kasus IGD --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-bell-plus me-2"></i>REKAPITULASI PASIEN IGD
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Jenis Kasus</th><th>Anggota</th><th>PNS</th><th>Keluarga</th>
                            <th>Siswa Dikbang</th><th>Siswa Diktuk</th><th>Mandiri</th>
                            <th>BPJS</th><th>Lainnya</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Define helper function OUTSIDE the foreach to prevent "Cannot redeclare" error
                            if (!function_exists('qIgdHelper')) {
                                function qIgdHelper($gol, $kasus, $t1, $t2) {
                                    $r = DB::select("SELECT COUNT(*) as total FROM reg_periksa rp JOIN data_triase_igd dt ON dt.no_rawat=rp.no_rawat JOIN master_triase_macam_kasus mk ON mk.kode_kasus=dt.kode_kasus JOIN pasien_polri pp ON pp.no_rkm_medis=rp.no_rkm_medis WHERE mk.macam_kasus=? AND rp.tgl_registrasi BETWEEN ? AND ? AND $gol AND rp.kd_pj='BPJ'", [$kasus, $t1, $t2]);
                                    return $r[0]->total ?? 0;
                                }
                            }
                        @endphp
                        @foreach ($igd as $a)
                        <tr>
                            <td class="td-green fw-semibold">{{ $a->kasus }}</td>
                            @php
                                $anggota = qIgdHelper("pp.golongan_polri='1'", $a->kasus, $tgl1, $tgl2);
                                $pns = qIgdHelper("(pp.golongan_polri IN('2','7','8','10'))", $a->kasus, $tgl1, $tgl2);
                                $kel = qIgdHelper("(pp.golongan_polri IN('9','3'))", $a->kasus, $tgl1, $tgl2);
                                $dikbang = qIgdHelper("(pp.golongan_polri IN('4','6'))", $a->kasus, $tgl1, $tgl2);
                                $diktuk = qIgdHelper("pp.golongan_polri='5'", $a->kasus, $tgl1, $tgl2);
                                $umumR = DB::select("SELECT COUNT(*) as total FROM reg_periksa rp JOIN data_triase_igd dt ON dt.no_rawat=rp.no_rawat JOIN master_triase_macam_kasus mk ON mk.kode_kasus=dt.kode_kasus WHERE mk.macam_kasus=? AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj='UMU'", [$a->kasus, $tgl1, $tgl2]);
                                $umum = $umumR[0]->total ?? 0;
                                $bpjR = DB::select("SELECT COUNT(*) as total FROM reg_periksa rp JOIN data_triase_igd dt ON dt.no_rawat=rp.no_rawat JOIN master_triase_macam_kasus mk ON mk.kode_kasus=dt.kode_kasus WHERE mk.macam_kasus=? AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj='BPJ'", [$a->kasus, $tgl1, $tgl2]);
                                $bpjs = ($bpjR[0]->total ?? 0) - $anggota - $pns - $dikbang - $diktuk - $kel;
                                $othR = DB::select("SELECT COUNT(*) as total FROM reg_periksa rp JOIN data_triase_igd dt ON dt.no_rawat=rp.no_rawat JOIN master_triase_macam_kasus mk ON mk.kode_kasus=dt.kode_kasus WHERE mk.macam_kasus=? AND rp.tgl_registrasi BETWEEN ? AND ? AND rp.kd_pj NOT IN('UMU','BPJ')", [$a->kasus, $tgl1, $tgl2]);
                                $other = $othR[0]->total ?? 0;
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

    {{-- Layanan Lanjutan --}}
    <div class="alert alert-danger py-2 mb-3" style="font-size:12px;border-radius:8px;">
        <strong>* Perhatian:</strong> Filter pada tabel layanan lanjutan hanya mengikuti <b>TAHUN</b> dari tanggal awal.
    </div>

    <div class="subsection-card">
        <div class="card-head" style="background:#0d6efd;">
            <i class="bx bx-transfer me-2"></i>Layanan Lanjutan Pasien IGD &ndash; Tahun {{ $tahun }}
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Jenis Layanan</th><th>Jumlah Pasien</th><th>Persentase</th></tr></thead>
                    <tbody>
                        <tr><td>Pulang Sembuh</td><td>{{ $pulang ?? 0 }}</td><td>{{ $persenPulang ?? 0 }}%</td></tr>
                        <tr><td>Rujuk Rawat Inap (RRI)</td><td>{{ $rri ?? 0 }}</td><td>{{ $persenRri ?? 0 }}%</td></tr>
                        <tr><td>Rujuk FKTL Lain</td><td>{{ $rujukKeluar ?? 0 }}</td><td>{{ $persenRujuk ?? 0 }}%</td></tr>
                        <tr><td>Meninggal di IGD</td><td>{{ $meninggalIgd ?? 0 }}</td><td>{{ $persenMeninggalIgd ?? 0 }}%</td></tr>
                        <tr><td>Lainnya</td><td>{{ $lainnya ?? 0 }}</td><td>{{ $persenLainnya ?? 0 }}%</td></tr>
                    </tbody>
                    <tfoot><tr style="background:#f8f9fa;font-weight:600;"><td class="text-center">TOTAL</td><td class="td-red">{{ $total ?? 0 }}</td><td>100%</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Distribusi Bulanan --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#28a745;">
            <i class="bx bx-calendar me-2"></i>Distribusi Kunjungan Pasien IGD & PONEK &ndash; Tahun {{ $tahun }}
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Bulan</th><th>IGD</th><th>% IGD</th><th>PONEK</th><th>% PONEK</th></tr></thead>
                    <tbody>
                        @for($i=1;$i<=12;$i++)
                        @php
                            $igdVal=$data[$i]->igd??0; $ponekVal=$data[$i]->ponek??0;
                            $pIgd=$totalIgd>0?round(($igdVal/$totalIgd)*100,2):0;
                            $pPonek=$totalPonek>0?round(($ponekVal/$totalPonek)*100,2):0;
                        @endphp
                        <tr><td>{{ $bulan[$i] }}</td><td>{{ $igdVal }}</td><td>{{ $pIgd }}%</td><td>{{ $ponekVal }}</td><td>{{ $pPonek }}%</td></tr>
                        @endfor
                    </tbody>
                    <tfoot><tr style="background:#f8d7da;font-weight:600;"><td class="text-center">TOTAL</td><td class="td-red">{{ $totalIgd }}</td><td>100%</td><td class="td-red">{{ $totalPonek }}</td><td>100%</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Penyakit IGD --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#198754;">
            <i class="bx bx-bar-chart me-2"></i>10 Besar Penyakit IGD &ndash; Tahun {{ $tahun }}
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table" id="tabelTopPenyakit">
                    <thead><tr><th width="5%">No</th><th width="15%">Kode ICD</th><th>Nama Penyakit</th><th width="20%">Jumlah Kasus</th></tr></thead>
                    <tbody>
                        @forelse($topPenyakit as $i=>$row)
                        <tr><td class="text-center">{{ $i+1 }}</td><td class="text-center">{{ $row->kd_penyakit }}</td><td>{{ $row->nm_penyakit }}</td><td class="text-center">{{ $row->jumlah_kasus }}</td></tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Kematian IGD --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#6f42c1;">
            <i class="bx bx-dizzy me-2"></i>Data Kematian Pasien IGD & PONEK &ndash; Tahun {{ $tahun }}
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table" id="tabelKematian">
                    <thead><tr><th>No</th><th>No. RM</th><th>Nama Pasien</th><th>Alamat</th><th>Unit</th><th>ICD 1</th><th>ICD 2</th><th>ICD 3</th><th>ICD 4</th></tr></thead>
                    <tbody>
                        @forelse($dataKematian as $i=>$row)
                        <tr>
                            <td class="text-center">{{ $i+1 }}</td><td>{{ $row->no_rkm_medis }}</td><td>{{ $row->nm_pasien }}</td>
                            <td style="max-width:200px;white-space:normal;">{{ $row->alamat }}</td>
                            <td class="text-center">{{ $row->kd_poli=='PNK'?'PONEK':'IGD' }}</td>
                            <td>{{ $row->icd1??'TAD' }}</td><td>{{ $row->icd2??'TAD' }}</td><td>{{ $row->icd3??'TAD' }}</td><td>{{ $row->icd4??'TAD' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted">Tidak ada data kematian pada tahun {{ $tahun }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>

<script>
$(function(){
    if(typeof $.fn.DataTable!=='undefined'){
        $('#tabelTopPenyakit').DataTable({retrieve:true,pageLength:10,searching:false,info:false,paging:false});
        $('#tabelKematian').DataTable({retrieve:true,pageLength:10,language:{search:"Cari:",lengthMenu:"Tampilkan _MENU_",info:"_START_-_END_ dari _TOTAL_",paginate:{previous:"<<",next:">>"},zeroRecords:"Data tidak ditemukan"}});
    }
});
</script>
