@extends('layout.app')

@section('title', 'Detail Kunjungan Poli')

@section('content')
<div class="container-fluid">
    <div class="card">

        {{-- ── Header ───────────────────────────────────────────────── --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="ri-file-list-3-line me-1"></i>Detail Data Kunjungan
            </h5>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                    <i class="ri-printer-line me-1"></i>Cetak
                </button>
                <button onclick="window.close()" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-close-line me-1"></i>Tutup
                </button>
            </div>
        </div>

        <div class="card-body">

            {{-- ── Info filter yang sedang ditampilkan ──────────────── --}}
            <div class="alert alert-info py-2 mb-3">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <strong>Penyakit:</strong>
                        {{ $selectedPenyakit['nama'] }}
                        <span class="text-muted">({{ $selectedPenyakit['kode_icd'] }})</span>
                    </div>
                    <div class="col-sm-3">
                        <strong>Tahun:</strong> {{ $year }}
                    </div>
                    <div class="col-sm-3">
                        <strong>Jenis:</strong>
                        {{ $type === 'rajal' ? 'Rawat Jalan' : 'Rawat Inap' }}
                    </div>
                    <div class="col-sm-12">
                        <strong>Kategori:</strong>
                        @php
                            $kategoriLabel = [
                                'pasien_baru'      => 'Pasien Baru',
                                'kunjungan'        => 'Kunjungan (Pasien Lama)',
                                'jumlah_pasien'    => 'Jumlah Pasien',
                                'keluar_meninggal' => 'Keluar Meninggal',
                            ];
                        @endphp
                        {{ $kategoriLabel[$category] ?? $category }}
                        &mdash; <strong>{{ $data->count() }}</strong> data ditemukan
                    </div>
                </div>
            </div>

            {{-- ── Tabel ────────────────────────────────────────────── --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="detailTable" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>No. Rawat</th>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th class="text-center" style="width:40px;">L/P</th>
                            <th class="text-center">Umur</th>
                            @if($type === 'rajal')
                                <th>Tgl. Registrasi</th>
                                <th>Poliklinik</th>
                                <th class="text-center">Status Daftar</th>
                            @else
                                <th>Tgl. Masuk</th>
                                <th>Tgl. Keluar</th>
                                <th>Bangsal</th>
                                <th class="text-center">Status Pulang</th>
                            @endif
                            <th>Kode ICD</th>
                            <th>Diagnosa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $item)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $item->no_rawat }}</td>
                                <td>{{ $item->no_rkm_medis }}</td>
                                <td>{{ $item->nm_pasien }}</td>
                                <td class="text-center">{{ $item->jk }}</td>
                                <td class="text-center">{{ $item->umur }}</td>
                                @if($type === 'rajal')
                                    <td>{{ $item->tgl_registrasi }}</td>
                                    <td>{{ $item->nm_poli }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $item->stts_daftar === 'Baru' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $item->stts_daftar }}
                                        </span>
                                    </td>
                                @else
                                    <td>{{ $item->tgl_masuk }}</td>
                                    <td>{{ $item->tgl_keluar ?? '-' }}</td>
                                    <td>{{ $item->nm_bangsal }}</td>
                                    <td class="text-center">
                                        @if($item->stts_pulang)
                                            <span class="badge {{ $item->stts_pulang === 'Meninggal' ? 'bg-danger' : 'bg-success' }}">
                                                {{ $item->stts_pulang }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $item->kd_penyakit }}</td>
                                <td>{{ $item->nm_penyakit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $type === 'rajal' ? 11 : 12 }}" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line fs-4 d-block mb-1"></i>
                                    Tidak ada data ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header .d-flex { display: none !important; }
        .card { border: none !important; }
        .alert { border: 1px solid #ccc !important; background: #f8f9fa !important; }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    $('#detailTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        }
    });
});
</script>
@endpush
