@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RL 3.19 Rekapitulasi Cara Bayar</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rl319') }}" method="GET" class="mb-4">
                <div class="row align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_awal" class="mb-0 mr-2">Dari&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" 
                               value="{{ $tanggalAwal ?? now()->startOfYear()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                        <label for="tanggal_akhir" class="mb-0 mr-2">Sampai&nbsp;&nbsp;</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                               value="{{ $tanggalAkhir ?? now()->endOfYear()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>

            <div class="mb-3">
                <a href="{{ route('laporan.rl319') }}?download_pdf=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ route('laporan.rl319') }}?download_excel=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th rowspan="2" class="text-center align-middle" style="width: 5%">No</th>
                            <th rowspan="2" class="align-middle" style="width: 25%">Cara Pembayaran</th>
                            <th colspan="2" class="text-center">Pasien Rawat Inap</th>
                            <th rowspan="2" class="text-center align-middle" style="width: 10%">Jumlah Pasien Rawat Jalan</th>
                            <th colspan="3" class="text-center">Jumlah Pasien Rawat Jalan</th>
                        </tr>
                        <tr>
                            <th class="text-center" style="width: 10%">Jumlah Pasien Keluar</th>
                            <th class="text-center" style="width: 10%">Jumlah Lama Dirawat</th>
                            <th class="text-center" style="width: 10%">Laboratorium</th>
                            <th class="text-center" style="width: 10%">Radiologi</th>
                            <th class="text-center" style="width: 10%">Lain-lain</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $no => $item)
                            @if($no == 99)
                                <tr class="table-warning font-weight-bold">
                                    <td class="text-center">{{ $item['no'] }}</td>
                                    <td><strong>{{ $item['nama'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['ranap_pasien'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['ranap_lama'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_total'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_lab'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_rad'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_lain'] }}</strong></td>
                                </tr>
                            @elseif($no == 2 || $no == 4)
                                {{-- Baris subtotal Asuransi dan Gratis --}}
                                <tr class="table-secondary font-weight-bold">
                                    <td class="text-center">{{ $item['no'] }}</td>
                                    <td><strong>{{ $item['nama'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['ranap_pasien'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['ranap_lama'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_total'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_lab'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_rad'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['rajal_lain'] }}</strong></td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-center">{{ $item['no'] }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    <td class="text-center">
                                        @if($item['ranap_pasien'] > 0)
                                            <a href="{{ route('laporan.rl319.detail') }}?kategori={{ $no }}&tipe=ranap_pasien&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                            target="_blank" style="color: #000;">
                                                {{ $item['ranap_pasien'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item['ranap_lama'] }}</td>
                                    <td class="text-center">{{ $item['rajal_total'] }}</td>
                                    <td class="text-center">
                                        @if($item['rajal_lab'] > 0)
                                            <a href="{{ route('laporan.rl319.detail') }}?kategori={{ $no }}&tipe=rajal_lab&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                            target="_blank" style="color: #000;">
                                                {{ $item['rajal_lab'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['rajal_rad'] > 0)
                                            <a href="{{ route('laporan.rl319.detail') }}?kategori={{ $no }}&tipe=rajal_rad&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                            target="_blank" style="color: #000;">
                                                {{ $item['rajal_rad'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['rajal_lain'] > 0)
                                            <a href="{{ route('laporan.rl319.detail') }}?kategori={{ $no }}&tipe=rajal_lain&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                            target="_blank" style="color: #000;">
                                                {{ $item['rajal_lain'] }}
                                            </a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!--
            <div class="mt-4">
                <h6><strong>Catatan:</strong></h6>
                <ol style="font-size: 0.9em;">
                    <li>Data yang dilaporkan berupa cara pembayaran yang dilakukan oleh pasien di rawat inap, rawat darurat, maupun rawat jalan, termasuk unit penunjang seperti laboratorium, radiologi, dan lainnya yang sudah keluar dari rumah sakit.</li>
                    <li>Jumlah Pasien Keluar diisi dengan jumlah pasien rawat inap yang sudah keluar, baik hidup maupun mati, selama periode satu tahun, sesuai cara pembayarannya.</li>
                    <li>Jumlah Lama Dirawat diisi dengan jumlah lama dirawat seluruh pasien rawat inap yang sudah keluar, baik hidup maupun mati, selama periode satu tahun, sesuai cara pembayarannya.</li>
                    <li>Jumlah pasien rawat jalan merupakan penjumlahan jumlah pasien yang keluar dari laboratorium, radiologi, dan lainnya selama periode satu tahun, sesuai cara pembayarannya.</li>
                    <li>Membayar sendiri diisi dengan jumlah pasien yang membayar sendiri baik dengan tunai maupun non tunai tanpa adanya peran serta dari pihak ke-3.</li>
                    <li>Asuransi terdiri dari Asuransi JKN (BPJS Kesehatan), Asuransi Pemerintah Daerah (Jamkesda), Asuransi Pemerintah Lainnya, dan Asuransi Swasta.</li>
                </ol>
            </div>
            -->

        </div>
    </div>
</div>
@endsection