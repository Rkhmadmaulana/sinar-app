@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Detail {{ $kategoriNama }}</h4>
            <p class="mb-0">
                <strong>Kategori:</strong> {{ $kategoriNo }} | 
                <strong>Periode:</strong> {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
            </p>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Total Data:</strong> {{ count($data) }}</p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>No Rawat</th>
                            <th>No RM</th>
                            <th>Nama Pasien</th>
                            <th class="text-center">JK</th>
                            <th>Tgl Registrasi</th>
                            <th>Nama Perawatan</th>
                            <th>Poliklinik</th>
                            <th class="text-center">Jenis Pelayanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $item->no_rawat }}</td>
                                <td>{{ $item->no_rkm_medis }}</td>
                                <td>{{ $item->nm_pasien }}</td>
                                <td class="text-center">{{ $item->jk }}</td>
                                <td>{{ date('d-m-Y', strtotime($item->tgl_registrasi)) }}</td>
                                <td>{{ $item->nm_perawatan }}</td>
                                <td>{{ $item->nm_poli }}</td>
                                <td class="text-center" >
                                    @if($item->jenis_pelayanan == 'Dokter')
                                        <span class="badge badge-primary" style="color:grey;">{{ $item->jenis_pelayanan }}</span>
                                    @elseif($item->jenis_pelayanan == 'Perawat')
                                        <span class="badge badge-success" style="color:grey;">{{ $item->jenis_pelayanan }}</span>
                                    @else
                                        <span class="badge badge-info" style="color:grey;">{{ $item->jenis_pelayanan }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .card-header, nav, .sidebar, .main-header, .main-footer {
            display: none !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        .table {
            font-size: 10px;
        }
    }
</style>
@endsection