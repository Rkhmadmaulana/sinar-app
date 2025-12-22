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
    
    .mb-3 {
        margin-bottom: 1rem;
    }
    
    .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        color: #fff;
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
    }
</style>

<div class="p-3">
    <h4>Detail Rekapitulasi Kunjungan</h4>
    
    <div class="info-box">
        <p class="font-weight-bold mb-1">Informasi Filter:</p>
        <ul class="info-list">
            <li>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</li>
            @if($specName)
                <li>Spesialisasi: {{ $specName }}</li>
            @endif
            @if($lokasi)
                <li>Lokasi: {{ $lokasi == 'Dalam' ? 'Dalam Kab/Kota' : 'Luar Kab/Kota' }}</li>
            @endif
            @if($gender)
                <li>Jenis Kelamin: {{ $gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</li>
            @endif
        </ul>
    </div>
    
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
                    <th>Lokasi</th>
                    <th>Poli</th>
                    <th>Diagnosa</th>
                    <th>Perujuk</th>
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
                        <td class="text-center">{{ $item->lokasi_kab == 'Dalam' ? 'Dalam Kab/Kota' : 'Luar Kab/Kota' }}</td>
                        <td>{{ $item->nm_poli }}</td>
                        <td>{{ $item->kd_penyakit }} - {{ $item->nm_penyakit }}</td>
                        <td>{{ $item->perujuk ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3" hidden>
        <p class="font-weight-bold">Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</p>
        <p class="font-weight-bold">Total Data: {{ count($data) }}</p>
        
        @if(isset($specName))
        <div class="info-box">
            <p class="font-weight-bold mb-1">Informasi Spesialisasi:</p>
            <ul class="info-list">
                <li>Nama: {{ $specName }}</li>
            </ul>
        </div>
        @endif
    </div>
</div>