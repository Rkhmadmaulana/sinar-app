@extends($layout ?? 'layout.app')
@section('title', 'Dashboard Rawat Jalan - Hemodialisa')
@section('content')
@if(!($isAjax ?? false))
@include('rm.rajal.layout.menu_rajal')
@endif
<div id="rajal-content">
<div class="container-fluid">
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-radius:12px 12px 0 0;border-bottom:2px solid #e9ecef;">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-hospital me-2"></i> Dashboard Rawat Jalan - Unit Hemodialisa
            </h5>
        </div>
        <div class="card-body px-4 py-3">

            {{-- Filter Form --}}
            <form action="{{ route('hemodialisa') }}" method="GET">
                <div class="row g-2 align-items-end mb-4">
                    <div class="col-lg-2 col-md-4 col-6">
                        <label class="form-label fw-semibold small text-muted">Dari Tanggal</label>
                        <input type="date" value="{{ $tgl1 ?? now()->startOfMonth()->format('Y-m-d') }}" class="form-control form-control-sm" name="tgl1">
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <label class="form-label fw-semibold small text-muted">Sampai Tanggal</label>
                        <input type="date" value="{{ $tgl2 ?? now()->format('Y-m-d') }}" class="form-control form-control-sm" name="tgl2">
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <label class="form-label fw-semibold small text-muted">Dokter</label>
                        <select name="dokter" class="form-control form-control-sm">
                            <option value="" selected>Semua Dokter</option>
                            @foreach ($pilihan_dokter as $item)
                                <option value="{{ $item->kd_dokter }}" @if(isset($kddokter) && $kddokter == $item->kd_dokter) selected @endif> {{ $item->nm_dokter }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <label class="form-label fw-semibold small text-muted">Cara Bayar</label>
                        <select name="cara_bayar" class="form-control form-control-sm">
                            <option value="" @if(isset($kd_pj) && $kd_pj == "") selected @endif>Semua</option>
                            @foreach ($pilihan_cara_bayar as $pj)
                                <option value="{{ $pj->kd_pj }}" @if(isset($kd_pj) && $kd_pj == $pj->kd_pj) selected @endif> {{ $pj->png_jawab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <label class="form-label fw-semibold small text-muted">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="" @if(isset($status) && $status == "") selected @endif>Semua</option>
                            <option value="Sudah" @if(isset($status) && $status == "Sudah") selected @endif>Sudah</option>
                            <option value="Batal" @if(isset($status) && $status == "Batal") selected @endif>Batal</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
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
                                <div style="font-size:2rem;font-weight:700;line-height:1;">{{ array_sum($data ?? []) }}</div>
                                <div style="font-size:1rem;font-weight:600;opacity:.9;">Total Kunjungan</div>
                                <small style="opacity:.7;">Hemodialisa</small>
                            </div>
                            <i class="fas fa-tint" style="font-size:2.5rem;opacity:.25;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#11998e,#38ef7d);color:#fff;border-radius:12px;padding:20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:2rem;font-weight:700;line-height:1;">{{ count($labels ?? []) }}</div>
                                <div style="font-size:1rem;font-weight:600;opacity:.9;">Poliklinik Aktif</div>
                                <small style="opacity:.7;">{{ $subjudul_line ?? '' }}</small>
                            </div>
                            <i class="fas fa-hospital" style="font-size:2.5rem;opacity:.25;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#ff6b6b,#ee5a24);color:#fff;border-radius:12px;padding:20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:2rem;font-weight:700;line-height:1;">{{ count($labeldokter ?? []) }}</div>
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

            {{-- Chart 1: Trend Kunjungan (line) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Trend Kunjungan</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_line"></div>
                </div>
            </div>

            {{-- Chart 2: Poliklinik (bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Poliklinik</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_poli"></div>
                </div>
            </div>

            {{-- Chart 3: Rujuk Masuk (bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Rujuk Masuk</span>
                </div>
                <div class="card-body p-3">
                    <div id="rujuk_masuk"></div>
                </div>
            </div>

            {{-- Charts 4: Kabupaten | Kecamatan | Kelurahan (3-col) --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kabupaten</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kabupaten"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kecamatan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kecamatan"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kelurahan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kelurahan"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 5: Dokter (bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Dokter</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_dokter"></div>
                </div>
            </div>

            {{-- Charts 6: Cara Bayar (pie) | Status (pie) --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Cara Bayar</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_cara_bayar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Status</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_stts"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts 7: Status Daftar (pie) | Jenis Kelamin (pie) --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Status Daftar</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="status_daftar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Jenis Kelamin</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="jenis_kelamin"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const warnapoli = @json($warnapoli);
    const warnadokter = @json($warnadokter);
    const warnabayar = @json($warnabayar);
    const warnastts = @json($warnastts);
    const warnasttsDaftar = @json($warnastts_daftar);
    const warnajk = @json($warnajk);
    const warnaKab = @json($warna_sql_Kabupaten);
    const warnaKec = @json($warnakec);
    const warnaKel = @json($warna_sql_kelurahan);
    const warnaRujuk = @json($warnaperujuk);
    const warnaPel = @json($warnapel);

    const baseChart = {
        fontFamily: "'Segoe UI', sans-serif",
        toolbar: { show: false },
        background: 'transparent'
    };

    const baseGrid = {
        borderColor: '#e9ecef',
        strokeDashArray: 4
    };

    const baseXaxis = {
        labels: { style: { fontSize: '11px', colors: '#6c757d' } },
        axisBorder: { color: '#dee2e6' },
        axisTicks: { color: '#dee2e6' }
    };

    const baseYaxis = {
        labels: {
            formatter: function(v) { return Math.round(v).toLocaleString('id'); },
            style: { colors: '#6c757d', fontSize: '11px' }
        }
    };

    const baseLegend = {
        position: 'top',
        fontSize: '12px',
        markers: { radius: 2 }
    };

    // Gender-aware tooltip builder for bar charts
    function genderTooltip(genderData, w, total) {
        var gender = genderData || {L: 0, P: 0};
        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[w.globals.tooltip.hoveredDataPointIndex !== undefined ? w.globals.tooltip.hoveredDataPointIndex : 0] + '</div>' +
               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
               '<span class="apexcharts-tooltip-marker" style="background-color: #4a6fa5;"></span>' +
               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + '</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
               '</div></div>';
    }

    // Bar chart base options
    function barOpts(genderData) {
        return {
            chart: Object.assign({ type: 'bar', height: 300 }, baseChart),
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
            dataLabels: { enabled: false },
            colors: warnapoli,
            grid: baseGrid,
            xaxis: Object.assign({}, baseXaxis),
            yaxis: Object.assign({}, baseYaxis),
            legend: Object.assign({}, baseLegend),
            tooltip: genderData ? {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var total = series[seriesIndex][dataPointIndex];
                    return genderTooltip(genderData[dataPointIndex], w, total);
                }
            } : {}
        };
    }

    // ==========================================
    // Chart 1: Trend Kunjungan (line)
    // ==========================================
    new ApexCharts(document.querySelector("#chart_line"), {
        series: [
            { name: 'BPJS', data: @json($bpjs) },
            { name: 'Umum', data: @json($umum) }
        ],
        chart: Object.assign({ type: 'line', height: 300, zoom: { enabled: false } }, baseChart),
        colors: ['#008FFB', '#FF4560'],
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        xaxis: Object.assign({ categories: @json($labelstat) }, baseXaxis),
        yaxis: Object.assign({}, baseYaxis),
        grid: baseGrid,
        legend: Object.assign({}, baseLegend),
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: function(v) { return v.toLocaleString('id') + ' pasien'; } }
        }
    }).render();

    // ==========================================
    // Chart 2: Poliklinik (bar)
    // ==========================================
    (function() {
        var genderData = @json($tooltip_gender ?? []);
        var opts = barOpts(genderData);
        opts.series = [{ name: 'Jumlah', data: @json($data) }];
        opts.xaxis = Object.assign({ categories: @json($labels) }, baseXaxis);
        new ApexCharts(document.querySelector("#chart_poli"), opts).render();
    })();

    // ==========================================
    // Chart 3: Rujuk Masuk (bar)
    // ==========================================
    (function() {
        var genderData = @json($tooltip_gender_rujuk ?? []);
        var opts = barOpts(genderData);
        opts.series = [{ name: 'Jumlah', data: @json($data_sql_rujuk_masuk) }];
        opts.xaxis = Object.assign({ categories: @json($labels_rujuk_masuk) }, baseXaxis);
        new ApexCharts(document.querySelector("#rujuk_masuk"), opts).render();
    })();

    // ==========================================
    // Chart 4a: Kabupaten (bar)
    // ==========================================
    (function() {
        var genderData = @json($tooltip_gender_kab ?? []);
        var opts = barOpts(genderData);
        opts.chart = Object.assign({ type: 'bar', height: 280 }, baseChart);
        opts.series = [{ name: 'Jumlah', data: @json($data_sql_kab) }];
        opts.xaxis = Object.assign({ categories: @json($labels_kab), labels: { style: { fontSize: '10px', colors: '#6c757d' }, rotate: -45, rotateAlways: false, hideOverlappingLabels: true }, axisBorder: { color: '#dee2e6' }, axisTicks: { color: '#dee2e6' } });
        new ApexCharts(document.querySelector("#kabupaten"), opts).render();
    })();

    // ==========================================
    // Chart 4b: Kecamatan (bar)
    // ==========================================
    (function() {
        var genderData = @json($tooltip_gender_kecamatan ?? []);
        var opts = barOpts(genderData);
        opts.chart = Object.assign({ type: 'bar', height: 280 }, baseChart);
        opts.series = [{ name: 'Jumlah', data: @json($data_kecamatan) }];
        opts.xaxis = Object.assign({ categories: @json($labels_kecamatan), labels: { style: { fontSize: '10px', colors: '#6c757d' }, rotate: -45, rotateAlways: false, hideOverlappingLabels: true }, axisBorder: { color: '#dee2e6' }, axisTicks: { color: '#dee2e6' } });
        new ApexCharts(document.querySelector("#kecamatan"), opts).render();
    })();

    // ==========================================
    // Chart 4c: Kelurahan (bar)
    // ==========================================
    (function() {
        var genderData = @json($tooltip_gender_kel ?? []);
        var opts = barOpts(genderData);
        opts.chart = Object.assign({ type: 'bar', height: 280 }, baseChart);
        opts.series = [{ name: 'Jumlah', data: @json($data_sql_kel) }];
        opts.xaxis = Object.assign({ categories: @json($labels_kel), labels: { style: { fontSize: '10px', colors: '#6c757d' }, rotate: -45, rotateAlways: false, hideOverlappingLabels: true }, axisBorder: { color: '#dee2e6' }, axisTicks: { color: '#dee2e6' } });
        new ApexCharts(document.querySelector("#kelurahan"), opts).render();
    })();

    // ==========================================
    // Chart 5: Dokter (bar)
    // ==========================================
    (function() {
        var genderData = @json($tooltip_gender_dokter ?? []);
        var opts = barOpts(genderData);
        opts.series = [{ name: 'Jumlah Pasien', data: @json($datadokter) }];
        opts.xaxis = Object.assign({ categories: @json($labeldokter) }, baseXaxis);
        new ApexCharts(document.querySelector("#chart_dokter"), opts).render();
    })();

    // ==========================================
    // Chart 6a: Cara Bayar (pie)
    // ==========================================
    (function() {
        var genderDataBayar = @json($tooltip_gender_cara_bayar ?? []);
        new ApexCharts(document.querySelector("#chart_cara_bayar"), {
            series: @json($datacara_bayar),
            chart: Object.assign({ type: 'pie', height: 300 }, baseChart),
            labels: @json($labelcara_bayar),
            colors: ['#008FFB', '#FF4560'],
            legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } },
            tooltip: {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var total = series[seriesIndex];
                    var gender = genderDataBayar[seriesIndex] || {L: 0, P: 0};
                    return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[seriesIndex] + '</div>' +
                           '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                           '<span class="apexcharts-tooltip-marker" style="background-color: ' + w.config.colors[seriesIndex] + ';"></span>' +
                           '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                           '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + '</span></div>' +
                           '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                           '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                           '</div></div>';
                }
            }
        }).render();
    })();

    // ==========================================
    // Chart 6b: Status (pie)
    // ==========================================
    new ApexCharts(document.querySelector("#chart_stts"), {
        series: @json($datastts),
        chart: Object.assign({ type: 'pie', height: 300 }, baseChart),
        labels: @json($labelsstts),
        colors: warnaRujuk,
        legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } }
    }).render();

    // ==========================================
    // Chart 7a: Status Daftar (pie)
    // ==========================================
    (function() {
        var genderDataSttsDaftar = @json($tooltip_gender_stts_daftar ?? []);
        new ApexCharts(document.querySelector("#status_daftar"), {
            series: @json($data_stts_daftar),
            chart: Object.assign({ type: 'pie', height: 300 }, baseChart),
            labels: @json($labels_stts_daftar),
            colors: warnaKab,
            legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } },
            tooltip: {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var total = series[seriesIndex];
                    var gender = genderDataSttsDaftar[seriesIndex] || {L: 0, P: 0};
                    return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[seriesIndex] + '</div>' +
                           '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                           '<span class="apexcharts-tooltip-marker" style="background-color: ' + w.config.colors[seriesIndex] + ';"></span>' +
                           '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                           '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + '</span></div>' +
                           '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                           '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                           '</div></div>';
                }
            }
        }).render();
    })();

    // ==========================================
    // Chart 7b: Jenis Kelamin (pie)
    // ==========================================
    new ApexCharts(document.querySelector("#jenis_kelamin"), {
        series: @json($data_jk),
        chart: Object.assign({ type: 'pie', height: 300 }, baseChart),
        labels: @json($labels_jk),
        colors: warnaKec,
        legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } }
    }).render();

});
</script>
@endsection
