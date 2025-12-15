@extends('layout.app')

@section('content')
<style>
    .detail-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .detail-table th, 
    .detail-table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
    }
    
    .detail-table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        text-align: center;
        font-weight: 600;
    }
    
    .detail-table tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .detail-table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Detail {{ $kategoriInfo['nama'] ?? 'Kategori' }}</h4>
            <p class="mb-0">
                <strong>Kode:</strong> {{ $kategoriKode }} | 
                <strong>Tipe:</strong> {{ $rujukanType === 'rujukan' ? 'Rujukan' : 'Non Rujukan' }}
                @if($sumberRujukan)
                    | <strong>Sumber:</strong> {{ ucwords(str_replace('_', ' ', $sumberRujukan)) }}
                @endif
                @if($statusType)
                    | <strong>Status:</strong> {{ ucwords($statusType) }}
                @endif
            </p>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Periode:</strong> {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</p>
                <p><strong>Total Data:</strong> {{ count($data) }}</p>
            </div>
            
            <div class="table-responsive">
                <table class="detail-table table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Rawat</th>
                            <th>No RM</th>
                            <th>Nama Pasien</th>
                            <th>JK</th>
                            <th>Tgl Registrasi</th>
                            @if($isNeonatal)
                                <th>Tgl Lahir</th>
                                <th>BB (gram)</th>
                                <th>UK (minggu)</th>
                                <th>Kondisi Lahir</th>
                            @else
                                <th>Tgl Lahir</th>
                                <th>Umur (bulan)</th>
                            @endif
                            @if($rujukanType === 'rujukan')
                                <th>Perujuk</th>
                                <th>Alamat Perujuk</th>
                            @else
                                <th>Asal Pasien</th>
                            @endif
                            <th>Keluhan</th>
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
                                @if($isNeonatal)
                                    <td>{{ $item->tgl_lahir ? date('d-m-Y', strtotime($item->tgl_lahir)) : '-' }}</td>
                                    <td class="text-center">{{ $item->intranatal_bb ?? '-' }}</td>
                                    <td class="text-center">{{ $item->prenatal_uk ?? '-' }}</td>
                                    <td>{{ $item->intranatal_kondisi_lahir ?? '-' }}</td>
                                @else
                                    <td>{{ date('d-m-Y', strtotime($item->tgl_lahir)) }}</td>
                                    <td class="text-center">
                                        {{ Carbon\Carbon::parse($tanggalAwal)->diffInMonths(Carbon\Carbon::parse($item->tgl_lahir)) }}
                                    </td>
                                @endif
                                @if($rujukanType === 'rujukan')
                                    <td>{{ $item->perujuk ?? $item->perujuk ?? '-' }}</td>
                                    <td>{{ $item->alamat_perujuk ?? '-' }}</td>
                                @else
                                    <td>{{ $item->asal_pasien ?? $item->tiba_diruang_rawat ?? '-' }}</td>
                                @endif
                                <td>{{ $item->keluhan_utama ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isNeonatal ? 13 : 11 }}" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection