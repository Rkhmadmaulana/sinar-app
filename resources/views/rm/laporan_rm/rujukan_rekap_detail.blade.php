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
    
    .font-weight-bold {
        font-weight: 600;
    }
    
    .info-box {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        border-radius: 0.25rem;
        padding: 0.75rem 1.25rem;
        margin-top: 1rem;
    }
    
    .info-list {
        margin-bottom: 0;
        padding-left: 1.5rem;
    }
    
    .text-center {
        text-align: center;
    }
    
    .mt-3 {
        margin-top: 1rem;
    }
</style>

<div class="p-3">
    <h4>Detail {{ $category == 'diterima_dari' ? 'Pasien yang Diterima Dari' : ($category == 'dirujuk_keluar' ? 'Pasien yang Dirujuk keluar': 'Pasien yang Dikembalikan Ke') }} {{ ucwords(str_replace('_', ' ', $source)) }}</h4>
    
    @if(isset($specName))
    <h5 class="mt-2">Spesialisasi: {{ $specName }}</h5>
    @endif
    
    <div class="table-responsive mt-3">
        <table class="detail-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Rawat</th>
                    <th>No RM</th>
                    <th>Nama Pasien</th>
                    <th>JK</th>
                    <th>Tgl Registrasi</th>
                    @if($category == 'diterima_dari')
                        <th>Perujuk</th>
                        <th>Alamat Perujuk</th>
                    @elseif($category == 'dirujuk_keluar') 
                        <th>Rujukan Masuk Awal</th>
                        <th>Rujukan Keluar Tujuan</th>   
                    @else
                        <th>Perujuk Asal</th>
                        <th>Tujuan Dirujuk</th>
                    @endif
                    <th>Poli</th>
                    <th>Diagnosa</th>
                    <th>Kategori Rujuk</th>
                    <th>Keterangan</th>
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
                        @if($category == 'diterima_dari')
                            <td>{{ $item->perujuk }}</td>
                            <td>{{ $item->alamat }}</td>
                        @else
                            <td>{{ $item->perujuk_asal }}</td>
                            <td>{{ $item->tujuan_dirujuk }}</td>
                        @endif
                        <td>{{ $item->nm_poli }}</td>
                        <td>{{ $item->kd_penyakit }} - {{ $item->nm_penyakit }}</td>
                        <td>{{ $item->kategori_rujuk }}</td>
                        <td>{{ $item->keterangan }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3" hidden>
        <p class="font-weight-bold">Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</p>
        <p class="font-weight-bold">Total Data: {{ count($data) }}</p>
        
        @if(isset($spesialisasiInfo))
        <div class="info-box">
            <p class="font-weight-bold mb-1">Informasi Spesialisasi:</p>
            <ul class="info-list">
                <li>Nama: {{ $spesialisasiInfo['nama'] }}</li>
                @if(isset($spesialisasiInfo['kd_poli']))
                <li>Kode Poli: {{ implode(', ', $spesialisasiInfo['kd_poli']) }}</li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>