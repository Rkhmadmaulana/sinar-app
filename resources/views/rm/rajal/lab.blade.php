@extends($layout ?? 'layout.app')
@section('title', 'Dashboard Rawat Jalan - Laboratorium')
@section('content')
@if(!($isAjax ?? false))
@include('rm.rajal.layout.menu_rajal')
@endif
<div id="rajal-content">
<div class="container-fluid">
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-radius:12px 12px 0 0;border-bottom:2px solid #e9ecef;">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-hospital me-2"></i> Dashboard Rawat Jalan - Laboratorium
            </h5>
        </div>
        <div class="card-body px-4 py-3">

            {{-- Filter Form --}}
            <form id="filterForm" action="{{ route('lab') }}" method="POST">
                @csrf
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Dari Tanggal</label>
                        @if(isset($tgl1))
                        <input type="date" value="{{ $tgl1 }}" class="form-control form-control-sm" name="tgl1">
                        @else
                        <input type="date" class="form-control form-control-sm" name="tgl1">
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Sampai Tanggal</label>
                        @if(isset($tgl2))
                        <input type="date" value="{{ $tgl2 }}" class="form-control form-control-sm" name="tgl2">
                        @else
                        <input type="date" class="form-control form-control-sm" name="tgl2">
                        @endif
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Dokter</label>
                        <select name="dokter" class="form-control form-control-sm">
                            <option value="" selected>Semua Dokter</option>
                            @foreach ($pilihan_dokter as $item)
                            <option value="{{ $item->kd_dokter }}" @if(isset($kddokter) && $kddokter == $item->kd_dokter) selected @endif>{{ $item->nm_dokter }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Cara Bayar</label>
                        <select name="cara_bayar" class="form-control form-control-sm">
                            <option value="" @if(isset($kd_pj) && $kd_pj == "") selected @endif>Semua</option>
                            @foreach ($pilihan_cara_bayar as $pj)
                            <option value="{{ $pj->kd_pj }}" @if(isset($kd_pj) && $kd_pj == $pj->kd_pj) selected @endif>{{ $pj->png_jawab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="" @if(isset($status) && $status == "") selected @endif>Semua</option>
                            <option value="Sudah" @if(isset($status) && $status == "Sudah") selected @endif>Sudah</option>
                            <option value="Belum" @if(isset($status) && $status == "Belum") selected @endif>Belum</option>
                            <option value="Batal" @if(isset($status) && $status == "Batal") selected @endif>Batal</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <button type="submit" name="tombol" value="filter" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            {-- Summary Stats --}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:12px;padding:20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:2rem;font-weight:700;line-height:1;">{{ array_sum($data ?? []) }}</div>
                                <div style="font-size:1rem;font-weight:600;opacity:.9;">Total Kunjungan</div>
                                <small style="opacity:.7;">Laboratorium</small>
                            </div>
                            <i class="fas fa-chart-line" style="font-size:2.5rem;opacity:.25;"></i>
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


            {{-- Row 1: Trend Kunjungan (Line) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Trend Kunjungan</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_line"></div>
                </div>
            </div>

            {{-- Row 2: Poliklinik (Bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Poliklinik</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_poli"></div>
                </div>
            </div>

            {{-- Row 3: Rujuk Masuk (Bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Rujuk Masuk</span>
                </div>
                <div class="card-body p-3">
                    <div id="rujuk_masuk"></div>
                </div>
            </div>

            {{-- Row 4: Kabupaten | Kecamatan | Kelurahan --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kabupaten</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kabupaten"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kecamatan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kecamatan"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kelurahan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kelurahan"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 5: Dokter (Bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Dokter</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_dokter"></div>
                </div>
            </div>

            {{-- Row 6: Cara Bayar (Pie) | Status (Pie) --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Cara Bayar</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_cara_bayar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Status</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_stts"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 7: Pelayanan (Bar) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Pelayanan</span>
                </div>
                <div class="card-body p-3">
                    <div id="chart_pelayanan"></div>
                </div>
            </div>

            {{-- Row 8: Status Daftar (Pie) | Jenis Kelamin (Pie) --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Status Daftar</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="status_daftar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-0" style="border-radius:10px;">
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

    const baseLegend = {
        position: 'top',
        fontSize: '12px',
        markers: { radius: 2 }
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

    // Chart Line - Trend Kunjungan
    new ApexCharts(document.querySelector("#chart_line"), Object.assign({}, baseChart, {
        series: [{
            name: 'BPJS',
            data: @json($bpjs)
        }, {
            name: 'Umum',
            data: @json($umum)
        }],
        chart: Object.assign({ type: 'line', height: 320 }, baseChart.chart),
        colors: ['#008FFB', '#FF4560'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labelstat) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: { shared: true, intersect: false }
    })).render();

    // Chart Bar - Poliklinik
    new ApexCharts(document.querySelector("#chart_poli"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah',
            data: @json($data)
        }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnapoli,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Bar - Rujuk Masuk
    new ApexCharts(document.querySelector("#rujuk_masuk"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah',
            data: @json($data_sql_rujuk_masuk)
        }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnaRujuk,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_rujuk_masuk) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Bar - Kabupaten
    new ApexCharts(document.querySelector("#kabupaten"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah',
            data: @json($data_sql_kab)
        }],
        chart: Object.assign({ type: 'bar', height: 300 }, baseChart.chart),
        colors: warnaKab,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_kab) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Bar - Kecamatan
    new ApexCharts(document.querySelector("#kecamatan"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah',
            data: @json($data_kecamatan)
        }],
        chart: Object.assign({ type: 'bar', height: 300 }, baseChart.chart),
        colors: warnaKec,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_kecamatan) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Bar - Kelurahan
    new ApexCharts(document.querySelector("#kelurahan"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah',
            data: @json($data_sql_kel)
        }],
        chart: Object.assign({ type: 'bar', height: 300 }, baseChart.chart),
        colors: warnaKel,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_kel) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Bar - Dokter
    new ApexCharts(document.querySelector("#chart_dokter"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah Pasien',
            data: @json($datadokter)
        }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnadokter,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labeldokter) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Pie - Cara Bayar
    new ApexCharts(document.querySelector("#chart_cara_bayar"), Object.assign({}, baseChart, {
        series: @json($datacara_bayar),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labelcara_bayar),
        colors: warnabayar,
        legend: Object.assign({ position: 'bottom', fontSize: '12px', markers: { radius: 2 } })
    })).render();

    // Chart Pie - Status
    new ApexCharts(document.querySelector("#chart_stts"), Object.assign({}, baseChart, {
        series: @json($datastts),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labelsstts),
        colors: warnastts,
        legend: Object.assign({ position: 'bottom', fontSize: '12px', markers: { radius: 2 } })
    })).render();

    // Chart Bar - Pelayanan
    new ApexCharts(document.querySelector("#chart_pelayanan"), Object.assign({}, baseChart, {
        series: [{
            name: 'Jumlah',
            data: @json($datapel)
        }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnaPel,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labelspel) }, baseXaxis),
        yaxis: baseYaxis
    })).render();

    // Chart Pie - Status Daftar
    new ApexCharts(document.querySelector("#status_daftar"), Object.assign({}, baseChart, {
        series: @json($data_stts_daftar),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labels_stts_daftar),
        colors: warnasttsDaftar,
        legend: Object.assign({ position: 'bottom', fontSize: '12px', markers: { radius: 2 } })
    })).render();

    // Chart Pie - Jenis Kelamin
    new ApexCharts(document.querySelector("#jenis_kelamin"), Object.assign({}, baseChart, {
        series: @json($data_jk),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labels_jk),
        colors: warnajk,
        legend: Object.assign({ position: 'bottom', fontSize: '12px', markers: { radius: 2 } })
    })).render();
});
</script>
@endsection
