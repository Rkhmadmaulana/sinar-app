@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RL 3.11 Rekapitulasi Kegiatan Pelayanan Gigi dan Mulut</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.rl311') }}" method="GET" class="mb-4">
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

            <div class="mb-3">
                <a href="{{ route('laporan.rl311') }}?download_pdf=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <!--
                <a href="{{ route('laporan.rl311') }}?download_excel=1&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}"
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
                -->
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Periode: {{ date('d-m-Y', strtotime($tanggalAwal)) }} s/d {{ date('d-m-Y', strtotime($tanggalAkhir)) }}</h5>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 10%">No.</th>
                            <th style="width: 70%">Jenis Kegiatan</th>
                            <th class="text-center" style="width: 20%">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $no => $item)
                            @if($no == 99)
                                <tr class="table-warning font-weight-bold">
                                    <td class="text-center">{{ $no }}</td>
                                    <td><strong>{{ $item['nama'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $item['jumlah'] }}</strong></td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-center">{{ $no }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    <td class="text-center">
                                        @if($item['jumlah'] > 0)
                                            <a href="{{ route('laporan.rl311.detail') }}?kategori={{ $no }}&tanggal_awal={{ $tanggalAwal }}&tanggal_akhir={{ $tanggalAkhir }}" 
                                               target="_blank" style="color: #000;">
                                                {{ $item['jumlah'] }}
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
                <h6>Catatan Pengisian Formulir RL 3.11:</h6>
                <ol class="small">
                    <li>Data yang dilaporkan merupakan jumlah kegiatan pelayanan sesuai dengan jenis kegiatan pelayanan yang diberikan kepada pasien pada saat kunjungan di Poliklinik Gigi dan Mulut di RS.</li>
                    <li>Tumpatan merupakan semua tumpatan yang bersifat permanen baik amalgam maupun sintetik.</li>
                    <li>Pengobatan pulpa merupakan semua tindakan yang dimaksudkan untuk pengobatan pulpa secara langsung, termasuk pemberian eugenol, pulp capping, prosedur dalam mummifikasi, dan exterpasi (semua tindakan dalam endodontic).</li>
                    <li>Pencabutan merupakan semua tindakan pencabutan gigi secara biasa, bukan tindakan yang digolongkan tindakan operatif.</li>
                    <li>Pengobatan abses merupakan semua tindakan/usaha yang ditujukan untuk mengobati abses dengan antibiotik, baik secara 45rematu, suntikan, per oral, tanpa tindakan yang digolongkan tindakan operatif.</li>
                    <li>Pembersihan karang gigi merupakan semua kegiatan membersihkan karang gigi untuk Rahang Atas (RA) maupun Rahang Bawah (RB).</li>
                    <li>Prothese lengkap termasuk dari bahan 45rematu maupun logam.</li>
                    <li>Prothese 45rematur termasuk prothese sadel, prothese 45rematur, yang terbuat dari bahan-bahan baik akrilik maupun logam, dengan menggunakan fasilitas unit 45remat gigi.</li>
                    <li>Prothese cekat termasuk inlay, mahkota, dan jembatan dengan memakai bahan akrilik maupun porselen, logam, dan lain-lain.</li>
                    <li>Orthodonti adalah perawatan untuk merapikan gigi dan bertujuan mendapatkan oklusi yang optimal dan fungsional dengan tetap mengutamakan nilai estetika gigi. Selain itu, dilakukan bagaimana pencegahan dan memperbaiki susunan gigi atau gusi yang tidak teratur</li>
                    <li>Jacket/Bridge adalah metode untuk memperbaikan gigi, digunakan sebagai "jembatan" yang mengisi kekosongan dari gigi yang hilang, yang ditopang oleh gigi alami atau gigi palsu.</li>
                    <li>Bedah Mulut adalah 45rematur bedah di area rongga mulut.</li>
                    <li>Implan Gigi adalah 45rematur penanaman akar gigi buatan pada rahang untuk menopang mahkota gigi buatan.</li>
                    <li>Penyakit Mulut diisi dengan kegiatan menangani pasien dengan kelainan atau penyakit di rongga mulut dan jaringan sekitarnya.</li>
                </ol>
            </div>
            -->
        </div>
    </div>
</div>
@endsection