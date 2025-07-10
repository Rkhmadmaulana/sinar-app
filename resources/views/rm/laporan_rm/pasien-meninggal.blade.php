@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                    
                    <div class="card-tools">
                        <form action="{{ route('laporan.pasien-meninggal') }}" method="GET" class="form-inline">
                            <div class="row align-items-center mb-3">
                                <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                                    <label for="tanggal_awal" class="mb-0 mr-2">Dari&nbsp;&nbsp;</label>
                                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
                                </div>
                                <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                                    <label for="tanggal_akhir" class="mb-0 mr-2">Sampai&nbsp;&nbsp;</label>
                                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                                </div>
                                <div class="col-auto d-flex align-items-center flex-nowrap mr-2 mb-2">
                                    <label for="bangsal" class="mb-0 mr-2">Bangsal&nbsp;&nbsp;</label>
                                    <select name="bangsal" id="bangsal" class="form-control">
                                        <option value="">Semua Bangsal</option>
                                        @foreach($daftarBangsal as $b)
                                            <option value="{{ $b->kd_bangsal }}" {{ $bangsal == $b->kd_bangsal ? 'selected' : '' }}>
                                                {{ $b->nm_bangsal }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                
                <div class="card-body">
                    @if(count($data) > 0)
                        <div class="mb-3" style="display:none;">
                            <a href="{{ route('laporan.pasien-meninggal.pdf', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'bangsal' => $bangsal]) }}" 
                               class="btn btn-danger" target="_blank">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </a>
                            <a href="{{ route('laporan.pasien-meninggal.excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'bangsal' => $bangsal]) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Download Excel
                            </a>
                        </div>
                        
                        <h5>Tabel Data Pasien Meninggal</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Rekam Medis</th>
                                        <th>Nama Pasien</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Tanggal Wafat</th>
                                        <th>Umur</th>
                                        <th>Penanggung Jawab</th>
                                        <th>Meninggal < 48 Jam</th>
                                        <th>Meninggal >= 48 Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            <td>{{ $item->no }}</td>
                                            <td>{{ $item->tgl_masuk }}</td>
                                            <td>{{ $item->no_rkm_medis }}</td>
                                            <td>{{ $item->nm_pasien }}</td>
                                            <td>{{ $item->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                            <td>{{ $item->tgl_lahir }}</td>
                                            <td>{{ $item->tgl_keluar }}</td>
                                            <td>{{ $item->umur !== null ? $item->umur : '-' }}</td>
                                            <td>{{ $item->png_jawab }}</td>
                                            <td class="text-center">{{ $item->meninggal_kurang_48jam }}</td>
                                            <td class="text-center">{{ $item->meninggal_lebih_48jam }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="3">Total Pasien</th>
                                        <th colspan="8">{{ count($data) }} Pasien</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <h5 class="mt-4">Tabel Ringkasan Diagnosa</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Diagnosa</th>
                                        <th colspan="2" class="text-center">Jenis Kelamin</th>
                                        <th colspan="11" class="text-center">Kelompok Umur</th>
                                        <th colspan="2" class="text-center">Meninggal</th>
                                    </tr>
                                    <tr>
                                        <th>L</th>
                                        <th>P</th>
                                        <th><1</th>
                                        <th><4</th>
                                        <th><9</th>
                                        <th><14</th>
                                        <th><19</th>
                                        <th><44</th>
                                        <th><54</th>
                                        <th><59</th>
                                        <th><69</th>
                                        <th>≥70</th>
                                        <th>Null</th>
                                        <th><48 Jam</th>
                                        <th>≥48 Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($totalData as $item)
                                        <tr>
                                            <td>{{ $item['no'] }}</td>
                                            <td>{{ $item['diagnosa'] }}</td>
                                            <td class="text-center">{{ $item['laki'] > 0 ? $item['laki'] : ''  }}</td>
                                            <td class="text-center">{{ $item['perempuan'] > 0 ? $item['perempuan'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_1'] > 0 ? $item['umur_lt_1'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_4'] > 0 ? $item['umur_lt_4'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_9'] > 0 ? $item['umur_lt_9'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_14'] > 0 ? $item['umur_lt_14'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_19'] > 0 ? $item['umur_lt_19'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_44'] > 0 ? $item['umur_lt_44'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_54'] > 0 ? $item['umur_lt_54'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_59'] > 0 ? $item['umur_lt_59'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_69'] > 0 ? $item['umur_lt_69'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_lt_70'] > 0 ? $item['umur_lt_70'] : '' }}</td>
                                            <td class="text-center">{{ $item['umur_null'] > 0 ? $item['umur_null'] : '' }}</td>
                                            <td class="text-center">{{ $item['meninggal_kurang_48jam'] > 0 ? $item['meninggal_kurang_48jam'] : '' }}</td>
                                            <td class="text-center">{{ $item['meninggal_lebih_48jam'] > 0 ? $item['meninggal_lebih_48jam'] : '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Total</td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('laki') > 0 ? collect($totalData)->sum('laki') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('perempuan') > 0 ? collect($totalData)->sum('perempuan') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_1') > 0 ? collect($totalData)->sum('umur_lt_1') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_4') > 0 ? collect($totalData)->sum('umur_lt_4') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_9') > 0 ? collect($totalData)->sum('umur_lt_9') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_14') > 0 ? collect($totalData)->sum('umur_lt_14') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_19') > 0 ? collect($totalData)->sum('umur_lt_19') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_44') > 0 ? collect($totalData)->sum('umur_lt_44') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_54') > 0 ? collect($totalData)->sum('umur_lt_54') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_59') > 0 ? collect($totalData)->sum('umur_lt_59') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_69') > 0 ? collect($totalData)->sum('umur_lt_69') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_lt_70') > 0 ? collect($totalData)->sum('umur_lt_70') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('umur_null') > 0 ? collect($totalData)->sum('umur_null') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('meninggal_kurang_48jam') > 0 ? collect($totalData)->sum('meninggal_kurang_48jam') : '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ collect($totalData)->sum('meninggal_lebih_48jam') > 0 ? collect($totalData)->sum('meninggal_lebih_48jam') : '' }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Silahkan pilih rentang tanggal dan klik tombol Tampilkan untuk melihat data pasien meninggal.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script loaded!");
    
    // Fungsi untuk memeriksa kedua input tanggal dan mengaktifkan dropdown bangsal
    function checkDates() {
        var tanggalAwal = document.getElementById('tanggal_awal').value;
        var tanggalAkhir = document.getElementById('tanggal_akhir').value;
        var bangsalSelect = document.getElementById('bangsal');
        
        console.log("Checking dates:", tanggalAwal, tanggalAkhir);
        
        if (tanggalAwal && tanggalAkhir) {
            // Jika kedua tanggal telah diisi
            bangsalSelect.disabled = false;
            
            // Menggunakan Fetch API untuk mendapatkan data bangsal
            fetch("{{ route('laporan.get-bangsal-meninggal') }}?tanggal_awal=" + tanggalAwal + "&tanggal_akhir=" + tanggalAkhir)
                .then(response => response.json())
                .then(data => {
                    console.log("Received data:", data);
                    
                    // Kosongkan dropdown bangsal
                    bangsalSelect.innerHTML = '';
                    
                    // Tambahkan opsi default
                    var defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.text = 'Semua Bangsal';
                    bangsalSelect.appendChild(defaultOption);
                    
                    // Tambahkan opsi bangsal dari response
                    data.data.forEach(function(item) {
                        var option = document.createElement('option');
                        option.value = item.kd_bangsal;
                        option.text = item.nm_bangsal;
                        bangsalSelect.appendChild(option);
                    });
                    
                   
                })
                .catch(error => console.error('Error:', error));
        } else {
            // Jika salah satu atau kedua tanggal belum diisi
            bangsalSelect.disabled = true;
        }
    }
    
    // Panggil fungsi saat halaman dimuat
    //checkDates();
    
    // Tambahkan event listener untuk input tanggal
    document.getElementById('tanggal_awal').addEventListener('change', function() {
        console.log("Tanggal awal changed!");
        checkDates();
    });
    
    document.getElementById('tanggal_akhir').addEventListener('change', function() {
        console.log("Tanggal akhir changed!");
        checkDates();
    });
});
</script>
@endsection
