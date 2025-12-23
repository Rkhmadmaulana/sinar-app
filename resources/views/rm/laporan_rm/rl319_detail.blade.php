@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Detail {{ $kategoriNama }} - {{ $tipeNama }}</h4>
            <p class="mb-0">
                <strong>Periode:</strong> {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}
            </p>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Total Data:</strong> {{ count($data) }}</p>
            </div>
            
            <div class="table-responsive">
                @if($tipe == 'ranap_pasien')
                    {{-- Pasien Rawat Inap --}}
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>No Rawat</th>
                                <th>No RM</th>
                                <th>Nama Pasien</th>
                                <th class="text-center">JK</th>
                                <th>Tgl Registrasi</th>
                                <th>Tgl Masuk</th>
                                <th>Tgl Keluar</th>
                                <th class="text-center">Lama</th>
                                <th>Kamar</th>
                                <th>Cara Bayar</th>
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
                                    <td>{{ date('d-m-Y', strtotime($item->tgl_masuk)) }}</td>
                                    <td>{{ date('d-m-Y', strtotime($item->tgl_keluar)) }}</td>
                                    <td class="text-center">{{ $item->lama }} hari</td>
                                    <td>{{ $item->kd_kamar }}</td>
                                    <td>{{ $item->cara_bayar }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($tipe == 'rajal_lab')
                    {{-- Laboratorium --}}
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>No Order</th>
                                <th>No Rawat</th>
                                <th>No RM</th>
                                <th>Nama Pasien</th>
                                <th class="text-center">JK</th>
                                <th>Tgl Permintaan</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Dokter Perujuk</th>
                                <th>Cara Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->noorder }}</td>
                                    <td>{{ $item->no_rawat }}</td>
                                    <td>{{ $item->no_rkm_medis }}</td>
                                    <td>{{ $item->nm_pasien }}</td>
                                    <td class="text-center">{{ $item->jk }}</td>
                                    <td>{{ date('d-m-Y', strtotime($item->tgl_permintaan)) }}</td>
                                    <td>{{ $item->jam_permintaan }}</td>
                                    <td>{{ $item->status }}</td>
                                    <td>{{ $item->dokter_perujuk }}</td>
                                    <td>{{ $item->cara_bayar }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($tipe == 'rajal_rad')
                    {{-- Radiologi --}}
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>No Order</th>
                                <th>No Rawat</th>
                                <th>No RM</th>
                                <th>Nama Pasien</th>
                                <th class="text-center">JK</th>
                                <th>Tgl Permintaan</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Dokter Perujuk</th>
                                <th>Cara Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->noorder }}</td>
                                    <td>{{ $item->no_rawat }}</td>
                                    <td>{{ $item->no_rkm_medis }}</td>
                                    <td>{{ $item->nm_pasien }}</td>
                                    <td class="text-center">{{ $item->jk }}</td>
                                    <td>{{ date('d-m-Y', strtotime($item->tgl_permintaan)) }}</td>
                                    <td>{{ $item->jam_permintaan }}</td>
                                    <td>{{ $item->status }}</td>
                                    <td>{{ $item->dokter_perujuk }}</td>
                                    <td>{{ $item->cara_bayar }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($tipe == 'rajal_lain')
                    {{-- Rawat Jalan (Poliklinik) --}}
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>No Rawat</th>
                                <th>No RM</th>
                                <th>Nama Pasien</th>
                                <th class="text-center">JK</th>
                                <th>Tgl Registrasi</th>
                                <th>Jam</th>
                                <th>Poliklinik</th>
                                <th>Cara Bayar</th>
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
                                    <td>{{ $item->jam_reg }}</td>
                                    <td>{{ $item->nm_poli }}</td>
                                    <td>{{ $item->cara_bayar }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
            
            <div class="mt-3">
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .card-header, nav, .sidebar, .main-header, .main-footer, #header {
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