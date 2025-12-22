@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RL 3.4 - Rekapitulasi Pengunjung</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rl-3-4-pengunjung') }}" method="GET" class="mb-4">
                <div class="row align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_awal" class="mb-0 mr-2">Dari&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" 
                               value="{{ $tanggalAwal ?? now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_akhir" class="mb-0 mr-2">Sampai&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                               value="{{ $tanggalAkhir ?? now()->endOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ isset($tanggalAwal) ? date('d-m-Y', strtotime($tanggalAwal)) : now()->startOfMonth()->format('d-m-Y') }} 
                        s/d {{ isset($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : now()->endOfMonth()->format('d-m-Y') }}</h5>
                </div>
            </div>

            <div class="mb-3">
                <a href="{{ route('laporan.rl-3-4-pengunjung') }}?download_pdf=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="text-center bg-light">
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th style="width: 50%;">Jenis Pengunjung</th>
                            <th style="width: 40%;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>Pengunjung Baru</td>
                            <td class="text-center font-weight-bold">{{ number_format($pengunjungBaru, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td>Pengunjung Lama</td>
                            <td class="text-center font-weight-bold">{{ number_format($pengunjungLama, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="2" class="text-center">TOTAL</td>
                            <td class="text-center">{{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection