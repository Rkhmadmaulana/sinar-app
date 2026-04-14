@extends($layout ?? 'layout.app')
@section('title', 'Dashboard Rawat Jalan')
@section('content')
@if(!($isAjax ?? false))
@include('rm.rajal.layout.menu_rajal')
@endif
<div id="rajal-content">

<div class="container-fluid">
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-radius:12px 12px 0 0;border-bottom:2px solid #e9ecef;">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-hospital me-2"></i> Dashboard Rawat Jalan - Poliklinik
            </h5>
        </div>
        <div class="card-body px-4 py-3">

            {{-- Filter Form --}}
            <form id="filterForm" action="{{ route('poliklinik') }}" method="POST">
                @csrf
                <div class="row g-2 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Dari Tanggal</label>
                        <input type="date"
                            value="{{ $tgl1 ?? now()->startOfMonth()->format('Y-m-d') }}"
                            class="form-control form-control-sm"
                            name="tgl1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Sampai Tanggal</label>
                        <input type="date"
                            value="{{ $tgl2 ?? now()->format('Y-m-d') }}"
                            class="form-control form-control-sm"
                            name="tgl2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Poliklinik</label>
                        <select name="poli" class="form-control form-control-sm" id="filterDropdown" style="width:100%">
                            <option value="" selected>Semua Poli</option>
                            @foreach ($pilihan_poli as $item)
                                <option value="{{ $item->kd_poli }}" @if(isset($kdpoli) && $kdpoli == $item->kd_poli) selected @endif>
                                    {{ $item->nm_poli }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Dokter</label>
                        <select name="dokter" class="form-control form-control-sm" style="width:100%">
                            <option value="" selected>Semua Dokter</option>
                            @foreach ($pilihan_dokter as $item)
                                <option value="{{ $item->kd_dokter }}" @if(isset($kddokter) && $kddokter == $item->kd_dokter) selected @endif> {{ $item->nm_dokter }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Cara Bayar</label>
                        <select name="cara_bayar" class="form-control form-control-sm" style="width:100%">
                            <option value="" @if(isset($kd_pj) && $kd_pj == "") selected @endif>Semua</option>
                            @foreach ($pilihan_cara_bayar as $pj)
                                <option value="{{ $pj->kd_pj }}" @if(isset($kd_pj) && $kd_pj == $pj->kd_pj) selected @endif> {{ $pj->png_jawab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Status</label>
                        <select name="status" class="form-control form-control-sm" style="width:100%">
                            <option value="" @if(isset($status) && $status == "") selected @endif>Semua</option>
                            <option value="Sudah" @if(isset($status) && $status == "Sudah") selected @endif>Sudah</option>
                            <option value="Batal"@if(isset($status) && $status == "Batal") selected @endif>Batal</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
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
                                <small style="opacity:.7;">Rawat Jalan</small>
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

            {{-- Row 1: Trend Chart (full width) --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Trend Kunjungan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_line"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Poliklinik Chart (full width) --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Poliklinik</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_poli"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 3: Rujuk Masuk (full width) --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Rujuk Masuk</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="rujuk_masuk"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 4: Kabupaten | Kecamatan | Kelurahan --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kabupaten</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kabupaten"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kecamatan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kecamatan"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Kelurahan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="kelurahan"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 5: Dokter (full width) --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Dokter</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_dokter"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 6: Cara Bayar | Status --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Cara Bayar</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_cara_bayar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Status</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_stts"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 7: Prosedur | Diagnosa (with Excel button) --}}
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="downloadDiagnosaExcel()">
                        <i class="fas fa-file-excel me-1"></i> Download Excel Diagnosa
                    </button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Prosedur</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_prosedur"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Diagnosa</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_diagnosa"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 8: Pelayanan (full width) --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Pelayanan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chart_pelayanan"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 9: Status Daftar | Jenis Kelamin --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
                        <div class="card-header bg-white py-2" style="border-radius:10px 10px 0 0;border-bottom:2px solid #e9ecef;">
                            <span class="fw-semibold" style="font-size:13px;">Status Daftar</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="status_daftar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
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
function downloadDiagnosaExcel() {
    const form = document.getElementById('filterForm');
    if (!form) {
        alert('Form filter tidak ditemukan');
        return;
    }
    const formData = new FormData(form);
    const downloadForm = document.createElement('form');
    downloadForm.method = 'POST';
    downloadForm.action = '{{ route("poliklinik.download.excel") }}';
    downloadForm.style.display = 'none';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    downloadForm.appendChild(csrfInput);
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        downloadForm.appendChild(input);
    }
    document.body.appendChild(downloadForm);
    downloadForm.submit();
    setTimeout(() => {
        document.body.removeChild(downloadForm);
    }, 1000);
}

document.addEventListener("DOMContentLoaded", function() {
    // Vibrant chart colors from controller
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
    const warnaProsedur = @json($warna_sqlprosedur);
    const warnaDiagnosa = @json($warna_sqldiagnosa);
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

    // Gender-aware tooltip helper for bar charts
    function genderBarTooltip({series, seriesIndex, dataPointIndex, w}, genderData, percData) {
        var total = series[seriesIndex][dataPointIndex];
        var gender = genderData[dataPointIndex] || {L: 0, P: 0};
        var perc = percData[dataPointIndex] || 0;
        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
               '<span class="apexcharts-tooltip-marker" style="background-color: #4a6fa5;"></span>' +
               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
               '</div></div>';
    }

    // Gender-aware tooltip helper for pie charts
    function genderPieTooltip({series, seriesIndex, dataPointIndex, w}, genderData, percData) {
        var total = series[seriesIndex];
        var gender = genderData[seriesIndex] || {L: 0, P: 0};
        var perc = percData[seriesIndex] || 0;
        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[seriesIndex] + '</div>' +
               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
               '<span class="apexcharts-tooltip-marker" style="background-color: ' + w.config.colors[seriesIndex] + ';"></span>' +
               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
               '</div></div>';
    }

    // Kode-aware tooltip helper for bar charts (diagnosa/prosedur)
    function kodeBarTooltip({series, seriesIndex, dataPointIndex, w}, genderData, percData, fullNames, kodeList) {
        var total = series[seriesIndex][dataPointIndex];
        var gender = genderData[dataPointIndex] || {L: 0, P: 0};
        var perc = percData[dataPointIndex] || 0;
        var namaLengkap = fullNames[dataPointIndex] || 'Unknown';
        var kode = kodeList[dataPointIndex] || '-';
        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + kode + ' - ' + namaLengkap + '</div>' +
               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
               '<span class="apexcharts-tooltip-marker" style="background-color: #4a6fa5;"></span>' +
               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
               '</div></div>';
    }

    // Tooltip data variables
    var genderDataPoli = @json($tooltip_gender ?? []);
    var percentagesPoli = @json($percentages_poli ?? []);
    var genderDataRujuk = @json($tooltip_gender_rujuk ?? []);
    var percentagesRujuk = @json($percentages_rujuk_masuk ?? []);
    var genderDataKab = @json($tooltip_gender_kab ?? []);
    var percentagesKab = @json($percentages_kab ?? []);
    var genderDataKec = @json($tooltip_gender_kecamatan ?? []);
    var percentagesKec = @json($percentages_kecamatan ?? []);
    var genderDataKel = @json($tooltip_gender_kel ?? []);
    var percentagesKel = @json($percentages_kel ?? []);
    var genderDataDokter = @json($tooltip_gender_dokter ?? []);
    var percentagesDokter = @json($percentages_dokter ?? []);
    var genderDataBayar = @json($tooltip_gender_cara_bayar ?? []);
    var percentagesBayar = @json($percentages_cara_bayar ?? []);
    var genderDataDiagnosa = @json($tooltip_gender_diagnosa ?? []);
    var percentagesDiagnosa = @json($percentages_diagnosa ?? []);
    var fullNamesDiagnosa = @json($fullnames_diagnosa ?? []);
    var kodeDiagnosa = @json($kode_diagnosa ?? []);
    var genderDataProsedur = @json($tooltip_gender_prosedur ?? []);
    var percentagesProsedur = @json($percentages_prosedur ?? []);
    var fullNamesProsedur = @json($fullnames_prosedur ?? []);
    var kodeProsedur = @json($kode_prosedur ?? []);
    var percentagesPel = @json($percentages_pelayanan ?? []);
    var fullNamesPel = @json($fullnames_pelayanan ?? []);
    var genderDataSttsDaftar = @json($tooltip_gender_stts_daftar ?? []);
    var percentagesSttsDaftar = @json($percentages_stts_daftar ?? []);

    // 1. Chart Line - Trend Kunjungan
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

    // 2. Chart Bar - Poliklinik
    new ApexCharts(document.querySelector("#chart_poli"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data) }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnapoli,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return genderBarTooltip(ctx, genderDataPoli, percentagesPoli); }
        }
    })).render();

    // 3. Chart Bar - Rujuk Masuk
    new ApexCharts(document.querySelector("#rujuk_masuk"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data_sql_rujuk_masuk) }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnaRujuk,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_rujuk_masuk) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return genderBarTooltip(ctx, genderDataRujuk, percentagesRujuk); }
        }
    })).render();

    // 4. Chart Bar - Kabupaten
    new ApexCharts(document.querySelector("#kabupaten"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data_sql_kab) }],
        chart: Object.assign({ type: 'bar', height: 300 }, baseChart.chart),
        colors: warnaKab,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_kab) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return genderBarTooltip(ctx, genderDataKab, percentagesKab); }
        }
    })).render();

    // 5. Chart Bar - Kecamatan
    new ApexCharts(document.querySelector("#kecamatan"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data_kecamatan) }],
        chart: Object.assign({ type: 'bar', height: 300 }, baseChart.chart),
        colors: warnaKec,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_kecamatan) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return genderBarTooltip(ctx, genderDataKec, percentagesKec); }
        }
    })).render();

    // 6. Chart Bar - Kelurahan
    new ApexCharts(document.querySelector("#kelurahan"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data_sql_kel) }],
        chart: Object.assign({ type: 'bar', height: 300 }, baseChart.chart),
        colors: warnaKel,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labels_kel) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return genderBarTooltip(ctx, genderDataKel, percentagesKel); }
        }
    })).render();

    // 7. Chart Bar - Dokter
    new ApexCharts(document.querySelector("#chart_dokter"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah Pasien', data: @json($datadokter) }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnadokter,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labeldokter) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return genderBarTooltip(ctx, genderDataDokter, percentagesDokter); }
        }
    })).render();

    // 8. Chart Pie - Cara Bayar
    new ApexCharts(document.querySelector("#chart_cara_bayar"), Object.assign({}, baseChart, {
        series: @json($datacara_bayar),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labelcara_bayar),
        colors: warnabayar,
        legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } },
        dataLabels: { style: { fontSize: '11px' }, dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.4 } },
        stroke: { width: 2, colors: ['#fff'] },
        tooltip: {
            custom: function(ctx) { return genderPieTooltip(ctx, genderDataBayar, percentagesBayar); }
        }
    })).render();

    // 9. Chart Pie - Status
    new ApexCharts(document.querySelector("#chart_stts"), Object.assign({}, baseChart, {
        series: @json($datastts),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labelsstts),
        colors: warnastts,
        legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } },
        dataLabels: { style: { fontSize: '11px' }, dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.4 } },
        stroke: { width: 2, colors: ['#fff'] }
    })).render();

    // 10. Chart Bar - Prosedur
    new ApexCharts(document.querySelector("#chart_prosedur"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data_sqlprosedur) }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnaProsedur,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labelsprosedur) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return kodeBarTooltip(ctx, genderDataProsedur, percentagesProsedur, fullNamesProsedur, kodeProsedur); }
        }
    })).render();

    // 11. Chart Bar - Diagnosa
    new ApexCharts(document.querySelector("#chart_diagnosa"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($data_sqldiagnosa) }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnaDiagnosa,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labelsdiagnosa) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function(ctx) { return kodeBarTooltip(ctx, genderDataDiagnosa, percentagesDiagnosa, fullNamesDiagnosa, kodeDiagnosa); }
        }
    })).render();

    // 12. Chart Bar - Pelayanan
    new ApexCharts(document.querySelector("#chart_pelayanan"), Object.assign({}, baseChart, {
        series: [{ name: 'Jumlah', data: @json($datapel) }],
        chart: Object.assign({ type: 'bar', height: 320 }, baseChart.chart),
        colors: warnaPel,
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
        dataLabels: { enabled: false },
        grid: baseGrid,
        legend: baseLegend,
        xaxis: Object.assign({ categories: @json($labelspel) }, baseXaxis),
        yaxis: baseYaxis,
        tooltip: {
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                var total = series[seriesIndex][dataPointIndex];
                var perc = percentagesPel[dataPointIndex] || 0;
                var namaLengkap = fullNamesPel[dataPointIndex] || 'Unknown';
                return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + namaLengkap + '</div>' +
                       '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                       '<span class="apexcharts-tooltip-marker" style="background-color: #4a6fa5;"></span>' +
                       '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                       '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                       '</div></div>';
            }
        }
    })).render();

    // 13. Chart Pie - Status Daftar
    new ApexCharts(document.querySelector("#status_daftar"), Object.assign({}, baseChart, {
        series: @json($data_stts_daftar),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labels_stts_daftar),
        colors: warnasttsDaftar,
        legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } },
        dataLabels: { style: { fontSize: '11px' }, dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.4 } },
        stroke: { width: 2, colors: ['#fff'] },
        tooltip: {
            custom: function(ctx) { return genderPieTooltip(ctx, genderDataSttsDaftar, percentagesSttsDaftar); }
        }
    })).render();

    // 14. Chart Pie - Jenis Kelamin
    new ApexCharts(document.querySelector("#jenis_kelamin"), Object.assign({}, baseChart, {
        series: @json($data_jk),
        chart: Object.assign({ type: 'pie', height: 320 }, baseChart.chart),
        labels: @json($labels_jk),
        colors: warnajk,
        legend: { position: 'bottom', fontSize: '12px', markers: { radius: 2 } },
        dataLabels: { style: { fontSize: '11px' }, dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.4 } },
        stroke: { width: 2, colors: ['#fff'] }
    })).render();
});
</script>
@endsection
