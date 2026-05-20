@extends($layout ?? 'layout.app')

@section('title', 'Dashboard Rawat Inap')

@section('content')
@if(!($isAjax ?? false))
    @include('rm.ranap.layout.menu_ranap')
@endif
<div id="ranap-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm" style="border-radius:12px;">

            {{-- ── Header ──────────────────────────────────────────────── --}}
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-radius:12px 12px 0 0;border-bottom:2px solid #e9ecef;">
                <h5 class="mb-0 fw-semibold text-dark">
                    <i class="fas fa-bed me-2"></i> Dashboard Rawat Inap
                </h5>
            </div>

            <div class="card-body px-4 py-3">

                {{-- ── Filter Form ─────────────────────────────────────── --}}
                <form id="filterForm" action="{{ route('ranap') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Dari Tanggal</label>
                            @if (isset($tgl1))
                                <input type="date" value="{{ $tgl1 }}" class="form-control form-control-sm" name="tgl1">
                            @else
                                <input type="date" class="form-control form-control-sm" name="tgl1">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Sampai Tanggal</label>
                            @if (isset($tgl2))
                                <input type="date" value="{{ $tgl2 }}" class="form-control form-control-sm" name="tgl2">
                            @else
                                <input type="date" class="form-control form-control-sm" name="tgl2">
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">Kamar</label>
                            <select name="kode_kamar" class="form-select form-select-sm">
                                <option value="" selected>Semua Kamar</option>
                                @foreach ($pilihan_kamar as $item)
                                    <option value="{{ $item->kd_bangsal }}"
                                        @if (isset($kodekamar) && $kodekamar == $item->kd_bangsal) selected @endif>
                                        {{ $item->nm_bangsal }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">Cara Bayar</label>
                            <select name="kodepj" class="form-select form-select-sm">
                                <option value=""
                                    @if (isset($kodepj) && $kodepj == '') selected @endif>Semua
                                </option>
                                @foreach ($pilihan_cara_bayar as $pj)
                                    <option value="{{ $pj->kd_pj }}"
                                        @if (isset($kodepj) && $kodepj == $pj->kd_pj) selected @endif>
                                        {{ $pj->png_jawab }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="tombol" value="filter" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Summary Stats --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:12px;padding:20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:2rem;font-weight:700;line-height:1;">{{ array_sum($datacara_bayar ?? []) }}</div>
                                    <div style="font-size:1rem;font-weight:600;opacity:.9;">Total Kunjungan</div>
                                    <small style="opacity:.7;">Rawat Inap</small>
                                </div>
                                <i class="fas fa-bed" style="font-size:2.5rem;opacity:.25;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#11998e,#38ef7d);color:#fff;border-radius:12px;padding:20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:2rem;font-weight:700;line-height:1;">{{ count($labelkelas ?? []) }}</div>
                                    <div style="font-size:1rem;font-weight:600;opacity:.9;">Kelas Kamar</div>
                                    <small style="opacity:.7;">{{ $subjudul_line ?? '' }}</small>
                                </div>
                                <i class="fas fa-door-open" style="font-size:2.5rem;opacity:.25;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#ff6b6b,#ee5a24);color:#fff;border-radius:12px;padding:20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:2rem;font-weight:700;line-height:1;">{{ count($labelspeldokter ?? []) }}</div>
                                    <div style="font-size:1rem;font-weight:600;opacity:.9;">Dokter</div>
                                    <small style="opacity:.7;">Yang Menangani</small>
                                </div>
                                <i class="fas fa-user-md" style="font-size:2.5rem;opacity:.25;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#4facfe,#00f2fe);color:#fff;border-radius:12px;padding:20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:2rem;font-weight:700;line-height:1;">{{ count($labelcara_bayar ?? []) }}</div>
                                    <div style="font-size:1rem;font-weight:600;opacity:.9;">Cara Bayar</div>
                                    <small style="opacity:.7;">Jenis Pembayaran</small>
                                </div>
                                <i class="fas fa-credit-card" style="font-size:2.5rem;opacity:.25;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════════════════
                     CHARTS GRID
                ═══════════════════════════════════════════════════════════ --}}
                <div class="row g-3">

                    {{-- Row 1: Line Chart Cara Bayar | Line Chart Kelas --}}
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Grafik Cara Bayar</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_line"></div>
                                <div id="chart_cara_bayar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Grafik Kelas</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_linekelas"></div>
                                <div id="chart_pie_kelas"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Kabupaten | Kecamatan | Kelurahan --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Kabupaten</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="kabupaten"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Kecamatan</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="kecamatan"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Kelurahan</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="kelurahan"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Pelayanan Dokter | Pelayanan Perawat --}}
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Pelayanan Dokter</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_pelayanan_dokter"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Pelayanan Perawat</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_pelayanan_prw"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 4: Status Daftar | Pelayanan --}}
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Status Daftar</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_status_daftar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Pelayanan</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_pel"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 5: Prosedur | Diagnosa --}}
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Prosedur</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_prosedur"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Diagnosa</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_diagnosa"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 6: Gizi (Adime) --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                            <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                                <span class="fw-semibold" style="font-size:13px;">Gizi (Adime)</span>
                            </div>
                            <div class="card-body p-3">
                                <div id="chart_bar_gizi"></div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /row g-3 --}}

            </div>{{-- /card-body --}}
        </div>{{-- /card --}}
    </div>{{-- /container-fluid --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const warnapoli = @json($warnabayar);
            const warnakelas = @json($warnakelas);
            const warnaKab = @json($warna_sql_Kabupaten);
            const warnaKec = @json($warnakec);
            const warnaKel = @json($warna_sql_kelurahan);
            const warnaProsedur = @json($warna_sqlprosedur);
            const warnaDiagnosa = @json($warna_sqldiagnosa);
            const warnaPelDokter = @json($warnapeldokter);
            const warnaPelPrw = @json($warnapelprw);
            const warnaSttsDaftar = @json($warnastts_daftar);
            const warnaPel = @json($warnapel);
            const warnaAdime = @json($warnastts_adime);
            const lineColors6 = ['#008FFB', '#FF4560', '#00E396', '#feb019', '#ff455f', '#775dd0'];

            createLineChart("#chart_line", @json($labelstat),
                [{ name: 'INHEALTH', data: @json($inhealth) },
                 { name: 'BKK', data: @json($bkk) },
                 { name: 'JAMKESDA', data: @json($jamkesda) },
                 { name: 'Pendamping JKN', data: @json($pjkn) },
                 { name: 'BPJS', data: @json($bpjs) },
                 { name: 'Umum', data: @json($umum) }], lineColors6);

            createPieChart("#chart_cara_bayar", @json($labelcara_bayar), @json($datacara_bayar), warnapoli);
            createPieChart("#chart_pie_kelas", @json($labelkelas), @json($datakelas), warnakelas);

            createLineChart("#chart_linekelas", @json($labelstatkelas),
                [{ name: 'VVIP', data: @json($vvip) },
                 { name: 'VIP', data: @json($vip) },
                 { name: 'Utama', data: @json($utama) },
                 { name: 'Kelas 1', data: @json($kelas1) },
                 { name: 'Kelas 2', data: @json($kelas2) },
                 { name: 'Kelas 3', data: @json($kelas3) }], lineColors6);

            createBarChart("#chart_bar_prosedur", @json($labelsprosedur), @json($data_sqlprosedur), warnaProsedur);
            createBarChart("#chart_bar_diagnosa", @json($labelsdiagnosa), @json($data_sqldiagnosa), warnaDiagnosa);
            createBarChart("#chart_bar_pelayanan_dokter", @json($labelspeldokter), @json($datapeldokter), warnaPelDokter);
            createBarChart("#chart_bar_pelayanan_prw", @json($labelspelprw), @json($datapelprw), warnaPelPrw);
            createBarChart("#chart_bar_status_daftar", @json($labels_stts_daftar), @json($data_stts_daftar), warnaSttsDaftar);
            createBarChart("#chart_bar_pel", @json($labelspel), @json($datapel), warnaPel);
            createBarChart("#chart_bar_gizi", @json($labels_adime), @json(array_values($data_adime)), warnaAdime);
            createBarChart("#kabupaten", @json($labels_kab), @json($data_sql_kab), warnaKab);
            createBarChart("#kecamatan", @json($labels_kecamatan), @json($data_kecamatan), warnaKec);
            createBarChart("#kelurahan", @json($labels_kel), @json($data_sql_kel), warnaKel);

            function createLineChart(selector, categories, series, colors) {
                new ApexCharts(document.querySelector(selector), {
                    series: series,
                    chart: { type: 'line', height: 300, fontFamily: "'Segoe UI', sans-serif", toolbar: { show: false }, background: 'transparent' },
                    colors: colors,
                    stroke: { width: 2, curve: 'smooth' },
                    dataLabels: { enabled: false },
                    xaxis: { categories: categories, labels: { style: { fontSize: '11px', colors: '#6c757d' } }, axisBorder: { color: '#dee2e6' }, axisTicks: { color: '#dee2e6' } },
                    yaxis: { labels: { formatter: function(v) { return Math.round(v).toLocaleString('id'); }, style: { colors: '#6c757d', fontSize: '11px' } } },
                    grid: { borderColor: '#e9ecef', strokeDashArray: 4 },
                    legend: { position: 'top', fontSize: '12px', markers: { radius: 2 } },
                    tooltip: { shared: true, intersect: false, y: { formatter: function(v) { return v.toLocaleString('id') + ' pasien'; } } }
                }).render();
            }

            function createPieChart(selector, labels, series, colors) {
                new ApexCharts(document.querySelector(selector), {
                    series: series,
                    chart: { type: 'pie', height: 300, fontFamily: "'Segoe UI', sans-serif", toolbar: { show: false }, background: 'transparent' },
                    labels: labels,
                    colors: colors || warnapoli,
                    legend: { position: 'bottom', fontSize: '12px' },
                    dataLabels: { style: { fontSize: '11px' }, dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.4 } },
                    stroke: { width: 2, colors: ['#fff'] },
                    tooltip: { y: { formatter: function(v) { return v.toLocaleString('id') + ' pasien'; } } },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 300 }, legend: { position: 'bottom', fontSize: '10px' } } }]
                }).render();
            }

            function createBarChart(selector, categories, seriesData, colors) {
                new ApexCharts(document.querySelector(selector), {
                    series: [{ name: 'Jumlah', data: seriesData }],
                    chart: { type: 'bar', height: 300, fontFamily: "'Segoe UI', sans-serif", toolbar: { show: false }, background: 'transparent' },
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
                    dataLabels: { enabled: false },
                    colors: colors || warnaKab,
                    xaxis: { categories: categories, labels: { style: { fontSize: '11px', colors: '#6c757d' } }, axisBorder: { color: '#dee2e6' }, axisTicks: { color: '#dee2e6' } },
                    yaxis: { labels: { formatter: function(v) { return Math.round(v).toLocaleString('id'); }, style: { colors: '#6c757d', fontSize: '11px' } } },
                    grid: { borderColor: '#e9ecef', strokeDashArray: 4 },
                    legend: { position: 'top', fontSize: '12px', markers: { radius: 2 } },
                    tooltip: { y: { formatter: function(v) { return v.toLocaleString('id') + ' pasien'; } } }
                }).render();
            }
        });
    </script>
</div>
@endsection
