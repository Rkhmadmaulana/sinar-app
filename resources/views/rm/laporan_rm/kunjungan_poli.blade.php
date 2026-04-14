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
                <a href="{{ route('kunjungan-poli.export-pdf') }}" target="_blank" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
                <a href="{{ route('kunjungan-poli.export-excel') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-1"></i> Download Excel
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalAddPenyakit">
                    + Tambah Penyakit
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalAddYear">
                    + Tambah Tahun
                </button>
                <form action="{{ route('kunjungan-poli.toggle-months') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $showMonths ? 'btn-info' : 'btn-outline-info' }}">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ $showMonths ? 'Bulan: Aktif' : '+ Filter Bulan' }}
                    </button>
                </form>
                <form action="{{ route('kunjungan-poli.toggle-poli') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:{{ $showPoli ? '#f59f00' : 'transparent' }}; color:{{ $showPoli ? '#fff' : '#f59f00' }}; border:1px solid #f59f00;">
                        <i class="fas fa-hospital me-1"></i>
                        {{ $showPoli ? 'Poli: Aktif' : '+ Filter Poli' }}
                    </button>
                </form>
                @if($showPoli)
                <button type="button" class="btn btn-sm" style="background:#198754; color:#fff; border:1px solid #198754;"
                        data-bs-toggle="modal" data-bs-target="#modalPilihPoli">
                    <i class="fas fa-check-square me-1"></i> Pilih Poli
                </button>
                @endif
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

                @if($showMonths)
                <div class="vr d-none d-md-block"></div>
                <div>
                    <div class="small text-muted mb-1 fw-semibold text-uppercase" style="font-size:11px;letter-spacing:.5px;">Detail</div>
                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill border border-info text-info"
                          style="font-size:12px; background:#e7f5ff;">
                        <i class="fas fa-check-circle" style="font-size:11px;"></i>
                        Filter Bulan Aktif
                    </span>
                </div>
                @endif

                @if($showPoli)
                <div class="vr d-none d-md-block"></div>
                <div>
                    <div class="small text-muted mb-1 fw-semibold text-uppercase" style="font-size:11px;letter-spacing:.5px;">Detail</div>
                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill border border-warning text-warning"
                          style="font-size:12px; background:#fff8e1;">
                        <i class="fas fa-hospital" style="font-size:11px;"></i>
                        Filter Poli Aktif
                    </span>
                </div>
                @endif
                @if($showPoli && count($selectedPolis) > 0)
                <div>
                    <div class="small text-muted mb-1 fw-semibold text-uppercase" style="font-size:11px;letter-spacing:.5px;">Poli Terpilih</div>
                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill border border-success text-success"
                          style="font-size:12px; background:#e8f5e9;">
                        {{ count($selectedPolis) }} poli dipilih
                    </span>
                </div>
                @endif
            </div>

            {{-- ════════════════════════════════════════════════════════
                 TABEL
            ═══════════════════════════════════════════════════════════ --}}
            @if($showPoli)
            {{-- ═══════════════════════════════════════════════════════
                 MODE: FILTER POLI AKTIF (Rawat Inap disembunyikan)
            ═══════════════════════════════════════════════════════════ --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tabelKunjungan"
                       style="font-size:13px; font-family:'Segoe UI',sans-serif; border-color:#dee2e6;">
                    <thead>
                        @if(!$showMonths)
                        {{-- ═══ POLI + TANPA BULAN (2-row header) ═══ --}}
                        <tr style="background:#343a40;color:#fff;">
                            <th rowspan="2" class="text-center align-middle border-end"
                                style="width:40px;">No</th>
                            <th rowspan="2" class="align-middle border-end"
                                style="min-width:220px;">
                                Kasus / Penyakit<br>
                                <small class="fw-normal opacity-75">(Kode ICD-10)</small>
                            </th>
                            <th colspan="{{ count($years) * 2 }}" class="text-center"
                                style="letter-spacing:.3px;">
                                Rawat Jalan per Poli
                            </th>
                        </tr>
                        <tr>
                            @foreach($years as $year)
                                <th class="text-center" style="min-width:75px; white-space:nowrap;">Pasien Baru</th>
                                <th class="text-center {{ !$loop->last ? 'border-end' : '' }}" style="min-width:75px; white-space:nowrap;">Kunjungan</th>
                            @endforeach
                        </tr>
                        @else
                        {{-- ═══ POLI + DENGAN BULAN (4-row header) ═══ --}}
                        @php $subColsPerYear = 26; @endphp
                        <tr style="background:#343a40;color:#fff;">
                            <th rowspan="4" class="text-center align-middle border-end"
                                style="width:40px;">No</th>
                            <th rowspan="4" class="align-middle border-end"
                                style="min-width:220px;">
                                Kasus / Penyakit<br>
                                <small class="fw-normal opacity-75">(Kode ICD-10)</small>
                            </th>
                            <th colspan="{{ count($years) * $subColsPerYear }}" class="text-center"
                                style="letter-spacing:.3px;">
                                Rawat Jalan per Poli
                            </th>
                        </tr>
                        <tr>
                            @foreach($years as $year)
                                <th colspan="{{ $subColsPerYear }}" class="text-center {{ !$loop->last ? 'border-end' : '' }}"
                                    style="font-weight:500;">
                                    {{ $year }}
                                </th>
                            @endforeach
                        </tr>
                        <tr style="background:#e9ecef; color:#343a40; font-weight:500;">
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    <th colspan="2" class="text-center {{ $m === 12 ? 'border-end' : '' }}"
                                        style="font-size:11px; padding:4px 6px;">
                                        {{ $monthLabels[$m] }}
                                    </th>
                                @endfor
                                <th colspan="2" class="text-center {{ !$loop->last ? 'border-end' : '' }}"
                                    style="font-size:11px; padding:4px 6px; background:#d0ebff; color:#0b5ed7;">
                                    Total
                                </th>
                            @endforeach
                        </tr>
                        <tr style="background:#f1f3f5; color:#495057; font-size:10px;">
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    <th class="text-center" style="min-width:40px; white-space:nowrap; padding:3px 4px;" title="Penyakit Baru">PB</th>
                                    <th class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="min-width:40px; white-space:nowrap; padding:3px 4px;" title="Kunjungan">K</th>
                                @endfor
                                <th class="text-center {{ !$loop->last ? 'border-end' : '' }}" style="min-width:40px; white-space:nowrap; padding:3px 4px; background:#d0ebff; color:#0b5ed7;" title="Penyakit Baru">PB</th>
                                <th class="text-center {{ !$loop->last ? 'border-end' : '' }}" style="min-width:40px; white-space:nowrap; padding:3px 4px; background:#d0ebff; color:#0b5ed7;" title="Kunjungan">K</th>
                            @endforeach
                        </tr>
                        @endif
                    </thead>

                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($rawatJalanData as $id => $rajal)
                            @php
                                $poliYearData = $rawatJalanPoliData[$id]['years'] ?? [];

                                // Filter polis by selectedPolis if any
                                $refPolis = [];
                                if (!$showMonths) {
                                    $allPolis = $poliYearData[$years[0]]['poli'] ?? [];
                                } else {
                                    // When months active, use yearly total's poli list
                                    $allPolis = $poliYearData[$years[0] ?? 0]['_total']['poli'] ?? [];
                                }
                                foreach ($allPolis as $kdP => $p) {
                                    if (count($selectedPolis) > 0 && !in_array($kdP, $selectedPolis)) {
                                        continue;
                                    }
                                    $refPolis[$kdP] = $p;
                                }

                                $hasPoli = count($refPolis) > 0;
                                $subRowCount = $hasPoli ? (1 + count($refPolis)) : 1;
                            @endphp

                            {{-- Main row: penyakit aggregate --}}
                            <tr class="{{ $no % 2 === 0 ? '' : 'table-light' }}"
                                style="border-color:#dee2e6;">
                                <td class="text-center text-muted" rowspan="{{ $subRowCount }}">
                                    {{ $no++ }}
                                </td>
                                <td>
                                    <span class="rounded-circle d-inline-block me-1"
                                          style="width:8px;height:8px;background:{{ $rajal['color'] }};vertical-align:middle;flex-shrink:0;"></span>
                                    <span class="fw-semibold">{{ $rajal['nama'] }}</span>
                                    <div class="text-muted" style="font-size:11px;">{{ $rajal['kode_icd'] }}</div>
                                </td>

                                @if(!$showMonths)
                                {{-- POLI + Tanpa Bulan --}}
                                @foreach($years as $year)
                                    @php
                                        $yd = $rajal['years'][$year] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0];
                                        $detailRajal = route('kunjungan-poli.detail')."?penyakit_id={$id}&year={$year}&type=rajal";
                                    @endphp
                                    <td class="text-center fw-semibold" style="background:#f0f7ff;">
                                        @if($yd['pasien_baru'] > 0)
                                            <a href="{{ $detailRajal }}&category=pasien_baru"
                                               target="_blank" class="text-decoration-none text-primary">
                                                {{ number_format($yd['pasien_baru'], 0, ',', '.') }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold {{ !$loop->last ? 'border-end' : '' }}" style="background:#f0f7ff;">
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
                                @else
                                {{-- POLI + Dengan Bulan --}}
                                @foreach($years as $year)
                                    @for($m = 1; $m <= 12; $m++)
                                        @php $yd = $rajal['years'][$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0]; @endphp
                                        <td class="text-center" style="font-size:11px; padding:3px 4px;">
                                            @if($yd['pasien_baru'] > 0)
                                                <span>{{ $yd['pasien_baru'] }}</span>
                                            @else
                                                <span class="text-muted" style="font-size:10px;">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">
                                            @if($yd['kunjungan'] > 0)
                                                <span>{{ $yd['kunjungan'] }}</span>
                                            @else
                                                <span class="text-muted" style="font-size:10px;">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                    @php $ydAll = $rajal['years'][$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0]; @endphp
                                    <td class="text-center fw-semibold" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                        @if($ydAll['pasien_baru'] > 0)
                                            {{ number_format($ydAll['pasien_baru'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold {{ !$loop->last ? 'border-end' : '' }}" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                        @if($ydAll['kunjungan'] > 0)
                                            {{ number_format($ydAll['kunjungan'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                @endif
                            </tr>

                            {{-- Sub-rows: per poliklinik --}}
                            @if($hasPoli)
                                @foreach($refPolis as $kdPoli => $poli)
                                    <tr style="background:#fffdf5; border-color:#dee2e6;">
                                        <td class="border-end" style="padding-left:24px; font-size:12px;">
                                            <i class="fas fa-chevron-right me-1" style="font-size:9px; color:#adb5bd;"></i>
                                            <span class="text-muted">{{ $poli['nm_poli'] }}</span>
                                        </td>

                                        @if(!$showMonths)
                                        {{-- Sub-row: Tanpa Bulan --}}
                                        @foreach($years as $year)
                                            @php
                                                $pYear = $poliYearData[$year]['poli'][$kdPoli] ?? null;
                                                $pb = $pYear['pasien_baru'] ?? 0;
                                                $kj = $pYear['kunjungan'] ?? 0;
                                            @endphp
                                            <td class="text-center" style="font-size:12px;">
                                                @if($pb > 0)
                                                    <span class="text-primary">{{ $pb }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center {{ !$loop->last ? 'border-end' : '' }}" style="font-size:12px;">
                                                @if($kj > 0)
                                                    <span class="text-primary">{{ $kj }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @else
                                        {{-- Sub-row: Dengan Bulan --}}
                                        @foreach($years as $year)
                                            @for($m = 1; $m <= 12; $m++)
                                                @php
                                                    $pMonth = $poliYearData[$year][$m]['poli'][$kdPoli] ?? null;
                                                    $pb = $pMonth['pasien_baru'] ?? 0;
                                                    $kj = $pMonth['kunjungan'] ?? 0;
                                                @endphp
                                                <td class="text-center" style="font-size:11px; padding:3px 4px;">
                                                    @if($pb > 0)
                                                        <span class="text-primary">{{ $pb }}</span>
                                                    @else
                                                        <span class="text-muted" style="font-size:10px;">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">
                                                    @if($kj > 0)
                                                        <span class="text-primary">{{ $kj }}</span>
                                                    @else
                                                        <span class="text-muted" style="font-size:10px;">-</span>
                                                    @endif
                                                </td>
                                            @endfor
                                            <td class="text-center fw-semibold" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                                @php
                                                    $pAll = $poliYearData[$year]['_total']['poli'][$kdPoli] ?? null;
                                                    $pb = $pAll['pasien_baru'] ?? 0;
                                                @endphp
                                                @if($pb > 0)
                                                    <span class="text-primary">{{ $pb }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-semibold {{ !$loop->last ? 'border-end' : '' }}" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                                @php
                                                    $pAll = $poliYearData[$year]['_total']['poli'][$kdPoli] ?? null;
                                                    $kj = $pAll['kunjungan'] ?? 0;
                                                @endphp
                                                @if($kj > 0)
                                                    <span class="text-primary">{{ $kj }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Baris JUMLAH --}}
                        <tr style="background:#f8f9fa; font-weight:600; border-top:2px solid #adb5bd;">
                            <td colspan="2" class="text-center text-uppercase"
                                style="letter-spacing:.5px; font-size:12px; color:#495057;">
                                Jumlah
                            </td>
                            @if(!$showMonths)
                            @foreach($years as $year)
                                @php $tj = $rawatJalanTotals[$year] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0]; @endphp
                                <td class="text-center">{{ number_format($tj['pasien_baru'], 0, ',', '.') }}</td>
                                <td class="text-center {{ !$loop->last ? 'border-end' : '' }}">{{ number_format($tj['kunjungan'], 0, ',', '.') }}</td>
                            @endforeach
                            @else
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    @php $tj = $rawatJalanTotals[$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0]; @endphp
                                    <td class="text-center" style="font-size:11px; padding:3px 4px;">{{ $tj['pasien_baru'] }}</td>
                                    <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">{{ $tj['kunjungan'] }}</td>
                                @endfor
                                @php $tjAll = $rawatJalanTotals[$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0]; @endphp
                                <td class="text-center fw-semibold" style="background:#e7f5ff; font-size:11px; padding:3px 4px;">{{ number_format($tjAll['pasien_baru'], 0, ',', '.') }}</td>
                                <td class="text-center fw-semibold {{ !$loop->last ? 'border-end' : '' }}" style="background:#e7f5ff; font-size:11px; padding:3px 4px;">{{ number_format($tjAll['kunjungan'], 0, ',', '.') }}</td>
                            @endforeach
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>{{-- /table-responsive --}}

            <div class="alert alert-warning alert-dismissible fade show py-2 mt-4" role="alert" style="font-size:12px;">
                <i class="fas fa-info-circle me-1"></i>
                Grafik tidak ditampilkan saat filter poli aktif. Rawat Inap disembunyikan. Nonaktifkan filter poli untuk melihat tampilan lengkap.
            </div>

            @else
            {{-- ═══════════════════════════════════════════════════════
                 MODE: NORMAL (original behavior, with or without months)
            ═══════════════════════════════════════════════════════════ --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tabelKunjungan"
                       style="font-size:13px; font-family:'Segoe UI',sans-serif; border-color:#dee2e6;">
                    <thead>
                        @if(!$showMonths)
                        {{-- ═══ TANPA BULAN (original 3-row header) ═══ --}}
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
                        @else
                        {{-- ═══ DENGAN BULAN (4-row header: Main → Year → Month → Sub) ═══ --}}
                        @php
                            $subColsPerYear = 26; // 12 bulan × 2 + 1 total × 2
                        @endphp
                        <tr style="background:#343a40;color:#fff;">
                            <th rowspan="4" class="text-center align-middle border-end"
                                style="width:40px; ">No</th>
                            <th rowspan="4" class="align-middle border-end"
                                style="min-width:180px;">
                                Kasus / Penyakit<br>
                                <small class="fw-normal opacity-75">(Kode ICD-10)</small>
                            </th>
                            <th colspan="{{ count($years) * $subColsPerYear }}" class="text-center border-end"
                                style="letter-spacing:.3px;">
                                Rawat Jalan
                            </th>
                            <th colspan="{{ count($years) * $subColsPerYear }}" class="text-center"
                                style="letter-spacing:.3px;">
                                Rawat Inap
                            </th>
                        </tr>

                        {{-- Row 2: Tahun --}}
                        <tr>
                            @foreach($years as $year)
                                <th colspan="{{ $subColsPerYear }}" class="text-center border-end"
                                    style="font-weight:500;">
                                    {{ $year }}
                                </th>
                            @endforeach
                            @foreach($years as $year)
                                <th colspan="{{ $subColsPerYear }}" class="text-center {{ !$loop->last ? 'border-end' : '' }}"
                                    style="font-weight:500;">
                                    {{ $year }}
                                </th>
                            @endforeach
                        </tr>

                        {{-- Row 3: Bulan + Total --}}
                        <tr style="background:#e9ecef; color:#343a40; font-weight:500;">
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    <th colspan="2" class="text-center {{ $m === 12 ? 'border-end' : '' }}"
                                        style="font-size:11px; padding:4px 6px;">
                                        {{ $monthLabels[$m] }}
                                    </th>
                                @endfor
                                <th colspan="2" class="text-center"
                                    style="font-size:11px; padding:4px 6px; background:#d0ebff; color:#0b5ed7;">
                                    Total
                                </th>
                            @endforeach
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    <th colspan="2" class="text-center {{ $m === 12 ? 'border-end' : '' }}"
                                        style="font-size:11px; padding:4px 6px;">
                                        {{ $monthLabels[$m] }}
                                    </th>
                                @endfor
                                <th colspan="2" class="text-center {{ !$loop->last ? 'border-end' : '' }}"
                                    style="font-size:11px; padding:4px 6px; background:#d0ebff; color:#0b5ed7;">
                                    Total
                                </th>
                            @endforeach
                        </tr>

                        {{-- Row 4: Sub-kolom --}}
                        <tr style="background:#f1f3f5; color:#495057; font-size:10px;">
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    <th class="text-center" style="min-width:40px; white-space:nowrap; padding:3px 4px;" title="Penyakit Baru">PB</th>
                                    <th class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="min-width:40px; white-space:nowrap; padding:3px 4px;" title="Kunjungan">K</th>
                                @endfor
                                <th class="text-center" style="min-width:40px; white-space:nowrap; padding:3px 4px; background:#d0ebff; color:#0b5ed7;" title="Penyakit Baru">PB</th>
                                <th class="text-center" style="min-width:40px; white-space:nowrap; padding:3px 4px; background:#d0ebff; color:#0b5ed7;" title="Kunjungan">K</th>
                            @endforeach
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    <th class="text-center" style="min-width:40px; white-space:nowrap; padding:3px 4px;" title="Jumlah Pasien">JP</th>
                                    <th class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="min-width:40px; white-space:nowrap; padding:3px 4px;" title="Keluar Meninggal">KM</th>
                                @endfor
                                <th class="text-center" style="min-width:40px; white-space:nowrap; padding:3px 4px; background:#d0ebff; color:#0b5ed7;" title="Jumlah Pasien">JP</th>
                                <th class="text-center {{ !$loop->last ? 'border-end' : '' }}" style="min-width:40px; white-space:nowrap; padding:3px 4px; background:#d0ebff; color:#0b5ed7;" title="Keluar Meninggal">KM</th>
                            @endforeach
                        </tr>
                        @endif
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

                                @if(!$showMonths)
                                {{-- Rawat Jalan (tanpa bulan) --}}
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

                                {{-- Rawat Inap (tanpa bulan) --}}
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
                                @else
                                {{-- Rawat Jalan (dengan bulan) --}}
                                @foreach($years as $year)
                                    @for($m = 1; $m <= 12; $m++)
                                        @php
                                            $yd = $rajal['years'][$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0];
                                            $detailRajal = route('kunjungan-poli.detail')."?penyakit_id={$id}&year={$year}&month={$m}&type=rajal";
                                        @endphp
                                        <td class="text-center" style="font-size:11px; padding:3px 4px;">
                                            @if($yd['pasien_baru'] > 0)
                                                <a href="{{ $detailRajal }}&category=pasien_baru"
                                                   target="_blank" class="text-decoration-none text-primary">
                                                    {{ $yd['pasien_baru'] }}
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size:10px;">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">
                                            @if($yd['kunjungan'] > 0)
                                                <a href="{{ $detailRajal }}&category=kunjungan"
                                                   target="_blank" class="text-decoration-none text-primary">
                                                    {{ $yd['kunjungan'] }}
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size:10px;">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                    @php $ydAll = $rajal['years'][$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0]; @endphp
                                    <td class="text-center fw-semibold" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                        @if($ydAll['pasien_baru'] > 0)
                                            {{ number_format($ydAll['pasien_baru'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                        @if($ydAll['kunjungan'] > 0)
                                            {{ number_format($ydAll['kunjungan'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Rawat Inap (dengan bulan) --}}
                                @foreach($years as $year)
                                    @for($m = 1; $m <= 12; $m++)
                                        @php $yi = $ranap['years'][$year][$m] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0,'total'=>0]; @endphp
                                        <td class="text-center" style="font-size:11px; padding:3px 4px;">
                                            @if($yi['jumlah_pasien'] > 0)
                                                <span>{{ $yi['jumlah_pasien'] }}</span>
                                            @else
                                                <span class="text-muted" style="font-size:10px;">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">
                                            @if($yi['keluar_meninggal'] > 0)
                                                <span class="text-danger">{{ $yi['keluar_meninggal'] }}</span>
                                            @else
                                                <span class="text-muted" style="font-size:10px;">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                    @php $yiAll = $ranap['years'][$year]['_total'] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0,'total'=>0]; @endphp
                                    <td class="text-center fw-semibold" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                        @if($yiAll['jumlah_pasien'] > 0)
                                            {{ number_format($yiAll['jumlah_pasien'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold {{ !$loop->last ? 'border-end' : '' }}" style="background:#f0f7ff; font-size:11px; padding:3px 4px;">
                                        @if($yiAll['keluar_meninggal'] > 0)
                                            {{ number_format($yiAll['keluar_meninggal'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                @endif
                            </tr>
                        @endforeach

                        {{-- Baris JUMLAH --}}
                        <tr style="background:#f8f9fa; font-weight:600; border-top:2px solid #adb5bd;">
                            <td colspan="2" class="text-center text-uppercase"
                                style="letter-spacing:.5px; font-size:12px; color:#495057;">
                                Jumlah
                            </td>
                            @if(!$showMonths)
                            @foreach($years as $year)
                                @php $tj = $rawatJalanTotals[$year] ?? ['pasien_baru'=>0,'kunjungan'=>0,'total'=>0]; @endphp
                                <td class="text-center">{{ number_format($tj['pasien_baru'], 0, ',', '.') }}</td>
                                <td class="text-center border-end">{{ number_format($tj['kunjungan'], 0, ',', '.') }}</td>
                            @endforeach
                            @foreach($years as $year)
                                @php $ti = $rawatInapTotals[$year] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0]; @endphp
                                <td class="text-center">{{ number_format($ti['jumlah_pasien'], 0, ',', '.') }}</td>
                                <td class="text-center {{ !$loop->last ? 'border-end' : '' }}">
                                    {{ number_format($ti['keluar_meninggal'], 0, ',', '.') }}
                                </td>
                            @endforeach
                            @else
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    @php $tj = $rawatJalanTotals[$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0]; @endphp
                                    <td class="text-center" style="font-size:11px; padding:3px 4px;">{{ $tj['pasien_baru'] }}</td>
                                    <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">{{ $tj['kunjungan'] }}</td>
                                @endfor
                                @php $tjAll = $rawatJalanTotals[$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0]; @endphp
                                <td class="text-center fw-semibold" style="background:#e7f5ff; font-size:11px; padding:3px 4px;">{{ number_format($tjAll['pasien_baru'], 0, ',', '.') }}</td>
                                <td class="text-center fw-semibold" style="background:#e7f5ff; font-size:11px; padding:3px 4px;">{{ number_format($tjAll['kunjungan'], 0, ',', '.') }}</td>
                            @endforeach
                            @foreach($years as $year)
                                @for($m = 1; $m <= 12; $m++)
                                    @php $ti = $rawatInapTotals[$year][$m] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0]; @endphp
                                    <td class="text-center" style="font-size:11px; padding:3px 4px;">{{ $ti['jumlah_pasien'] }}</td>
                                    <td class="text-center {{ $m === 12 ? 'border-end' : '' }}" style="font-size:11px; padding:3px 4px;">{{ $ti['keluar_meninggal'] }}</td>
                                @endfor
                                @php $tiAll = $rawatInapTotals[$year]['_total'] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0]; @endphp
                                <td class="text-center fw-semibold" style="background:#e7f5ff; font-size:11px; padding:3px 4px;">{{ number_format($tiAll['jumlah_pasien'], 0, ',', '.') }}</td>
                                <td class="text-center fw-semibold {{ !$loop->last ? 'border-end' : '' }}" style="background:#e7f5ff; font-size:11px; padding:3px 4px;">{{ number_format($tiAll['keluar_meninggal'], 0, ',', '.') }}</td>
                            @endforeach
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>{{-- /table-responsive --}}

            {{-- GRAFIK --}}
            @if(!$showMonths)
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
            @else
            <div class="alert alert-info alert-dismissible fade show py-2 mt-4" role="alert" style="font-size:12px;">
                <i class="fas fa-info-circle me-1"></i>
                Grafik tidak ditampilkan saat filter bulan aktif untuk menjaga keterbacaan data. Nonaktifkan filter bulan untuk melihat grafik.
            </div>
            @endif
            @endif {{-- end showPoli mode check --}}

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
                Semua penyakit, tahun, dan filter bulan akan dikembalikan ke nilai default. Lanjutkan?
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

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL: Pilih Poli
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalPilihPoli" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('kunjungan-poli.set-poli') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-semibold">
                        <i class="fas fa-check-square me-1 text-success"></i> Pilih Poliklinik
                    </h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted" style="font-size:11px;">Centang poli yang ingin ditampilkan. Biarkan kosong untuk menampilkan semua.</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:11px;"
                                onclick="toggleAllPolis(this)">
                            <span class="toggle-label">Centang Semua</span>
                        </button>
                    </div>
                    <hr class="my-1 mb-2">
                    <div class="row" style="max-height: 400px; overflow-y: auto;">
                        @foreach($allAvailablePolis as $kdPoli => $nmPoli)
                            <div class="col-md-6">
                                <div class="form-check py-1">
                                    <input class="form-check-input" type="checkbox" name="polis[]" value="{{ $kdPoli }}"
                                           id="poli_{{ $kdPoli }}"
                                           {{ in_array($kdPoli, $selectedPolis) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="poli_{{ $kdPoli }}" style="font-size:13px;">
                                        {{ $nmPoli }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm" style="background:#198754; color:#fff; border:1px solid #198754;">
                        Terapkan
                    </button>
                </div>
            </form>
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
    .modal-backdrop { z-index: 1040; width:100%; height:100%; }
    .modal { z-index: 1050; }

    /* Sticky scroll untuk tabel dengan bulan */
    @if($showMonths)
    .table-responsive { overflow-x: auto; }
    #tabelKunjungan { min-width: 2200px; }
    @endif
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const penyakitLabels = @json(array_column($penyakit, 'nama'));
    const years          = @json($years);
    const showMonths     = @json($showMonths);
    const rawatJalanData = @json($rawatJalanData);
    const rawatInapData  = @json($rawatInapData);

    @if(!$showMonths)
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
    @endif
});

    // Toggle All Polis
    function toggleAllPolis(btn) {
        const checks = document.querySelectorAll('#modalPilihPoli input[type="checkbox"][name="polis[]"]');
        const allChecked = Array.from(checks).every(c => c.checked);
        checks.forEach(c => c.checked = !allChecked);
        btn.querySelector('.toggle-label').textContent = allChecked ? 'Centang Semua' : 'Hapus Semua Centang';
    }
</script>
@endpush
@endsection
