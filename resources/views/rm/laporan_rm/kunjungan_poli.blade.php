@extends('layout.app')

@section('title', 'Kunjungan Poli - Data Penyakit')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">

        {{-- ── Header ──────────────────────────────────────────────── --}}
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                Data Kunjungan Poli Berdasarkan Kasus / Penyakit
            </h5>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalAddPenyakit">
                    + Tambah Penyakit
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalAddYear">
                    + Tambah Tahun
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalReset">
                    ↺ Reset Default
                </button>
            </div>
        </div>

        <div class="card-body px-4 py-3">

            {{-- ── Flash messages ───────────────────────────────────── --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ── Filter chips ─────────────────────────────────────── --}}
            <div class="d-flex flex-wrap align-items-start gap-3 mb-4 pb-3 border-bottom">

                {{-- Penyakit --}}
                <div>
                    <div class="small text-muted mb-1 fw-semibold text-uppercase" style="font-size:11px;letter-spacing:.5px;">Penyakit / Kasus</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($penyakit as $p)
                            <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill border"
                                  style="font-size:12px; background:#f8f9fa;">
                                <span class="rounded-circle d-inline-block"
                                      style="width:8px;height:8px;background:{{ $p['color'] }};flex-shrink:0;"></span>
                                {{ $p['nama'] }}
                                <span class="text-muted">({{ $p['kode_icd'] }})</span>
                                <form action="{{ route('kunjungan-poli.hapus-penyakit') }}" method="POST"
                                      class="d-inline m-0 p-0"
                                      onsubmit="return confirm('Hapus penyakit \'{{ $p['nama'] }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="penyakit_id" value="{{ $p['id'] }}">
                                    <button type="submit"
                                            class="btn p-0 border-0 lh-1 text-muted"
                                            style="font-size:13px;line-height:1;background:none;"
                                            title="Hapus">×</button>
                                </form>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="vr d-none d-md-block"></div>

                {{-- Tahun --}}
                <div>
                    <div class="small text-muted mb-1 fw-semibold text-uppercase" style="font-size:11px;letter-spacing:.5px;">Tahun</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($years as $year)
                            <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill border"
                                  style="font-size:12px; background:#f8f9fa;">
                                {{ $year }}
                                @if(count($years) > 1)
                                    <form action="{{ route('kunjungan-poli.hapus-tahun') }}" method="POST"
                                          class="d-inline m-0 p-0"
                                          onsubmit="return confirm('Hapus tahun {{ $year }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="year" value="{{ $year }}">
                                        <button type="submit"
                                                class="btn p-0 border-0 lh-1 text-muted"
                                                style="font-size:13px;background:none;"
                                                title="Hapus">×</button>
                                    </form>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════
                 TABEL GABUNGAN
            ═══════════════════════════════════════════════════════════ --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tabelKunjungan"
                       style="font-size:13px; font-family:'Segoe UI',sans-serif; border-color:#dee2e6;">
                    <thead>
                        {{-- Row 1: No | Kasus/Penyakit | Rawat Jalan (span tahun×2) | Rawat Inap (span tahun×2) --}}
                        <tr style="background:#343a40;color:#fff;">
                            <th rowspan="3" class="text-center align-middle border-end"
                                style="width:40px; ">No</th>
                            <th rowspan="3" class="align-middle border-end"
                                style="min-width:180px;">
                                Kasus / Penyakit<br>
                                <small class="fw-normal opacity-75">(Kode ICD-10)</small>
                            </th>
                            <th colspan="{{ count($years) * 2 }}" class="text-center border-end"
                                style="letter-spacing:.3px;">
                                Rawat Jalan
                            </th>
                            <th colspan="{{ count($years) * 2 }}" class="text-center"
                                style="letter-spacing:.3px;">
                                Rawat Inap
                            </th>
                        </tr>

                        {{-- Row 2: Tahun (masing-masing span 2 kolom, di bawah Rawat Jalan dan Rawat Inap) --}}
                        <tr>
                            @foreach($years as $year)
                                <th colspan="2" class="text-center border-end"
                                    style="font-weight:500;">
                                    {{ $year }}
                                </th>
                            @endforeach
                            @foreach($years as $year)
                                <th colspan="2" class="text-center {{ !$loop->last ? 'border-end' : '' }}"
                                    style="font-weight:500;">
                                    {{ $year }}
                                </th>
                            @endforeach
                        </tr>

                        {{-- Row 3: Sub-kolom --}}
                        <tr style="background:#f1f3f5; color:#495057;">
                            @foreach($years as $year)
                                <th class="text-center" style="min-width:75px; white-space:nowrap;">Pasien Baru</th>
                                <th class="text-center border-end" style="min-width:75px; white-space:nowrap;">Kunjungan</th>
                            @endforeach
                            @foreach($years as $year)
                                <th class="text-center" style="min-width:75px; white-space:nowrap;">Jml Pasien</th>
                                <th class="text-center {{ !$loop->last ? 'border-end' : '' }}"
                                    style="min-width:75px; white-space:nowrap;">Keluar Meninggal</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($rawatJalanData as $id => $rajal)
                            @php $ranap = $rawatInapData[$id] ?? null; @endphp
                            <tr class="{{ $no % 2 === 0 ? '' : 'table-light' }}"
                                style="border-color:#dee2e6;">
                                <td class="text-center text-muted">{{ $no++ }}</td>
                                <td>
                                    <span class="rounded-circle d-inline-block me-1"
                                          style="width:8px;height:8px;background:{{ $rajal['color'] }};vertical-align:middle;flex-shrink:0;"></span>
                                    <span class="fw-semibold">{{ $rajal['nama'] }}</span>
                                    <div class="text-muted" style="font-size:11px;">{{ $rajal['kode_icd'] }}</div>
                                </td>

                                {{-- Rawat Jalan --}}
                                @foreach($years as $year)
                                    @php
                                        $yd = $rajal['years'][$year] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0];
                                        $detailRajal = route('kunjungan-poli.detail')."?penyakit_id={$id}&year={$year}&type=rajal";
                                    @endphp
                                    <td class="text-center">
                                        @if($yd['pasien_baru'] > 0)
                                            <a href="{{ $detailRajal }}&category=pasien_baru"
                                               target="_blank" class="text-decoration-none text-primary">
                                                {{ number_format($yd['pasien_baru'], 0, ',', '.') }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center border-end">
                                        @if($yd['kunjungan'] > 0)
                                            <a href="{{ $detailRajal }}&category=kunjungan"
                                               target="_blank" class="text-decoration-none text-primary">
                                                {{ number_format($yd['kunjungan'], 0, ',', '.') }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Rawat Inap --}}
                                @foreach($years as $year)
                                    @php
                                        $yi = $ranap['years'][$year] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0];
                                        $detailRanap = route('kunjungan-poli.detail')."?penyakit_id={$id}&year={$year}&type=ranap";
                                    @endphp
                                    <td class="text-center">
                                        @if($yi['jumlah_pasien'] > 0)
                                            <a href="{{ $detailRanap }}&category=jumlah_pasien"
                                               target="_blank" class="text-decoration-none text-primary">
                                                {{ number_format($yi['jumlah_pasien'], 0, ',', '.') }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center {{ !$loop->last ? 'border-end' : '' }}">
                                        @if($yi['keluar_meninggal'] > 0)
                                            <a href="{{ $detailRanap }}&category=keluar_meninggal"
                                               target="_blank" class="text-decoration-none text-danger">
                                                {{ number_format($yi['keluar_meninggal'], 0, ',', '.') }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- Baris JUMLAH --}}
                        <tr style="background:#f8f9fa; font-weight:600; border-top:2px solid #adb5bd;">
                            <td colspan="2" class="text-center text-uppercase"
                                style="letter-spacing:.5px; font-size:12px; color:#495057;">
                                Jumlah
                            </td>
                            @foreach($years as $year)
                                @php $tj = $rawatJalanTotals[$year] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0]; @endphp
                                <td class="text-center">{{ number_format($tj['pasien_baru'], 0, ',', '.') }}</td>
                                <td class="text-center border-end">{{ number_format($tj['kunjungan'],   0, ',', '.') }}</td>
                            @endforeach
                            @foreach($years as $year)
                                @php $ti = $rawatInapTotals[$year] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0]; @endphp
                                <td class="text-center">{{ number_format($ti['jumlah_pasien'],    0, ',', '.') }}</td>
                                <td class="text-center {{ !$loop->last ? 'border-end' : '' }}">
                                    {{ number_format($ti['keluar_meninggal'], 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>{{-- /table-responsive --}}

            {{-- ════════════════════════════════════════════════════════
                 GRAFIK
            ═══════════════════════════════════════════════════════════ --}}
            <div class="row mt-4">
                <div class="col-lg-6 mb-4">
                    <div class="card border shadow-none">
                        <div class="card-header bg-white border-bottom py-2">
                            <span class="fw-semibold" style="font-size:13px;">Grafik Rawat Jalan</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chartRawatJalan"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card border shadow-none">
                        <div class="card-header bg-white border-bottom py-2">
                            <span class="fw-semibold" style="font-size:13px;">Grafik Rawat Inap</span>
                        </div>
                        <div class="card-body p-3">
                            <div id="chartRawatInap"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL: Tambah Penyakit
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAddPenyakit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('kunjungan-poli.tambah-penyakit') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-semibold">+ Tambah Penyakit / Kasus</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Nama Penyakit / Kasus <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_penyakit" class="form-control form-control-sm" required
                               placeholder="Contoh: Diabetes Mellitus">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Kode ICD-10 <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="kode_icd" class="form-control form-control-sm" required
                               placeholder="Contoh: E10-E14 atau I20, I50">
                        <div class="form-text" style="font-size:11px;">
                            Format range: <code>E10-E14</code> &nbsp;|&nbsp; Beberapa kode: <code>I20, I50</code>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Deskripsi <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="deskripsi" class="form-control form-control-sm" rows="2"
                                  placeholder="Deskripsi singkat"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL: Tambah Tahun
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAddYear" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('kunjungan-poli.tambah-tahun') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-semibold">+ Tambah Tahun</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <label class="form-label fw-semibold" style="font-size:13px;">Tahun</label>
                    <input type="number" name="new_year" class="form-control form-control-sm" required
                           min="2000" max="2100" value="{{ date('Y') }}">
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL: Konfirmasi Reset
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalReset" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-semibold">Reset ke Default</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3" style="font-size:13px;">
                Semua penyakit dan tahun yang ditambahkan akan dikembalikan ke nilai default. Lanjutkan?
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('kunjungan-poli.reset') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark">Ya, Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Tabel: border lebih soft */
    #tabelKunjungan { border-collapse: collapse; }
    #tabelKunjungan th, #tabelKunjungan td {
        vertical-align: middle;
        border-color: #dee2e6 !important;
        padding: 7px 10px;
    }
    #tabelKunjungan tbody tr:hover { background: #f0f4f8 !important; }

    /* Grafik card */
    .card.border.shadow-none { border-color: #e9ecef !important; }

    /* Chip/badge filter */
    .rounded-pill.border { border-color: #dee2e6 !important; }

    /* Pastikan modal backdrop penuh */
    .modal-backdrop { z-index: 1040; }
    .modal { z-index: 1050; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const penyakitLabels = @json(array_column($penyakit, 'nama'));
    const years          = @json($years);
    const rawatJalanData = @json($rawatJalanData);
    const rawatInapData  = @json($rawatInapData);

    // Warna netral/monokromatik untuk chart
    const palette = ['#4a6fa5','#6b8cba','#8dadd0','#b0cce5','#d3e8f0',
                     '#5a7a6a','#7a9a8a','#9ab9aa','#bcd8ca','#ddf0e0'];

    function buildChart(elId, dataObj, title) {
        const series = years.map(year => ({
            name: String(year),
            data: Object.values(dataObj).map(item => item.years?.[year]?.total ?? 0),
        }));

        new ApexCharts(document.getElementById(elId), {
            series,
            chart: {
                type: 'bar',
                height: 300,
                fontFamily: "'Segoe UI', sans-serif",
                toolbar: { show: false },
                background: 'transparent',
            },
            plotOptions: {
                bar: { columnWidth: '55%', borderRadius: 2 },
            },
            dataLabels: {
                enabled: true,
                formatter: v => v > 0 ? v.toLocaleString('id') : '',
                style: { fontSize: '10px', colors: ['#333'] },
                offsetY: -4,
            },
            colors: palette,
            xaxis: {
                categories: penyakitLabels,
                labels: {
                    rotate: -30,
                    style: { fontSize: '11px', colors: '#6c757d' },
                },
                axisBorder: { color: '#dee2e6' },
                axisTicks: { color: '#dee2e6' },
            },
            yaxis: {
                labels: {
                    formatter: v => Math.round(v).toLocaleString('id'),
                    style: { colors: '#6c757d', fontSize: '11px' },
                },
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 4,
            },
            legend: {
                position: 'top',
                fontSize: '12px',
                markers: { radius: 2 },
            },
            title: {
                text: title,
                align: 'left',
                style: { fontSize: '12px', fontWeight: '600', color: '#343a40' },
            },
            tooltip: {
                y: { formatter: v => v.toLocaleString('id') + ' pasien' },
            },
        }).render();
    }

    buildChart('chartRawatJalan', rawatJalanData, 'Rawat Jalan');
    buildChart('chartRawatInap',  rawatInapData,  'Rawat Inap');
});
</script>
@endpush
@endsection
