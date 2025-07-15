@extends('layout.app')

@section('content')
<div class="container">
    <h3>Morbiditas Pasien Rawat Jalan</h3>
    <form method="POST" action="{{ route('morbiditas-rawat-jalan') }}"></form>
            @csrf
        <div class="form-group">
            <label for="tanggal_awal">Tanggal Awal:</label>
            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
        </div>
        <div class="form-group">
            <label for="tanggal_akhir">Tanggal Akhir:</label>
            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode ICD</th>
                    <th>Diagnosa Penyakit</th>
                    <th><1 hr (L)</th>
                    <th><1 hr (P)</th>
                    <th>1-23 hr (L)</th>
                    <th>1-23 hr (P)</th>
                    <th>8-28 hr (L)</th>
                    <th>8-28 hr (P)</th>
                    <th>2-<3 bln (L)</th>
                    <th>2-<3 bln (P)</th>
                    <th>3-<6 bln (L)</th>
                    <th>3-<6 bln (P)</th>
                    <th>6-<11 bln (L)</th>
                    <th>6-<11 bln (P)</th>
                    <th>1-4 th (L)</th>
                    <th>1-4 th (P)</th>
                    <th>5-9 (L)</th>
                    <th>5-9 (P)</th>
                    <th>10-14 (L)</th>
                    <th>10-14 (P)</th>
                    <th>15-19 (L)</th>
                    <th>15-19 (P)</th>
                    <th>20-24 (L)</th>
                    <th>20-24 (P)</th>
                    <th>25-29 (L)</th>
                    <th>25-29 (P)</th>
                    <th>30-34 (L)</th>
                    <th>30-34 (P)</th>
                    <th>35-39 (L)</th>
                    <th>35-39 (P)</th>
                    <th>40-44 (L)</th>
                    <th>40-44 (P)</th>
                    <th>45-49 (L)</th>
                    <th>45-49 (P)</th>
                    <th>50-54 (L)</th>
                    <th>50-54 (P)</th>
                    <th>55-59 (L)</th>
                    <th>55-59 (P)</th>
                    <th>60-64 (L)</th>
                    <th>60-64 (P)</th>
                    <th>65-69 (L)</th>
                    <th>65-69 (P)</th>
                    <th>70-74 (L)</th>
                    <th>70-74 (P)</th>
                    <th>75-79 (L)</th>
                    <th>75-79 (P)</th>
                    <th>80-84 (L)</th>
                    <th>80-84 (P)</th>
                    <th>>=85 (L)</th>
                    <th>>=85 (P)</th>
                    <th>Total (L)</th>
                    <th>Total (P)</th>
                    <th>Total Kasus Baru</th>
                    <th>Total Kunjungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item->kd_penyakit }}</td>
                        <td>{{ $item->nm_penyakit }}</td>
                        <td>{{ $item->kurang_1hr_L }}</td>
                        <td>{{ $item->kurang_1hr_P }}</td>
                        <td>{{ $item->age_1_23hr_L }}</td>
                        <td>{{ $item->age_1_23hr_P }}</td>
                        <td>{{ $item->age_8_28hr_L }}</td>
                        <td>{{ $item->age_8_28hr_P }}</td>
                        <td>{{ $item->age_2_3bln_L }}</td>
                        <td>{{ $item->age_2_3bln_P }}</td>
                        <td>{{ $item->age_3_6bln_L }}</td>
                        <td>{{ $item->age_3_6bln_P }}</td>
                        <td>{{ $item->age_6_11bln_L }}</td>
                        <td>{{ $item->age_6_11bln_P }}</td>
                        <td>{{ $item->age_1_4th_L }}</td>
                        <td>{{ $item->age_1_4th_P }}</td>
                        <td>{{ $item->age_5_9_L }}</td>
                        <td>{{ $item->age_5_9_P }}</td>
                        <td>{{ $item->age_10_14_L }}</td>
                        <td>{{ $item->age_10_14_P }}</td>
                        <td>{{ $item->age_15_19_L }}</td>
                        <td>{{ $item->age_15_19_P }}</td>
                        <td>{{ $item->age_20_24_L }}</td>
                        <td>{{ $item->age_20_24_P }}</td>
                        <td>{{ $item->age_25_29_L }}</td>
                        <td>{{ $item->age_25_29_P }}</td>
                        <td>{{ $item->age_30_34_L }}</td>
                        <td>{{ $item->age_30_34_P }}</td>
                        <td>{{ $item->age_35_39_L }}</td>
                        <td>{{ $item->age_35_39_P }}</td>
                        <td>{{ $item->age_40_44_L }}</td>
                        <td>{{ $item->age_40_44_P }}</td>
                        <td>{{ $item->age_45_49_L }}</td>
                        <td>{{ $item->age_45_49_P }}</td>
                        <td>{{ $item->age_50_54_L }}</td>
                        <td>{{ $item->age_50_54_P }}</td>
                        <td>{{ $item->age_55_59_L }}</td>
                        <td>{{ $item->age_55_59_P }}</td>
                        <td>{{ $item->age_60_64_L }}</td>
                        <td>{{ $item->age_60_64_P }}</td>
                        <td>{{ $item->age_65_69_L }}</td>
                        <td>{{ $item->age_65_69_P }}</td>
                        <td>{{ $item->age_70_74_L }}</td>
                        <td>{{ $item->age_70_74_P }}</td>
                        <td>{{ $item->age_75_79_L }}</td>
                        <td>{{ $item->age_75_79_P }}</td>
                        <td>{{ $item->age_80_84_L }}</td>
                        <td>{{ $item->age_80_84_P }}</td>
                        <td>{{ $item->lebih_85_L }}</td>
                        <td>{{ $item->lebih_85_P }}</td>
                        <td>{{ $item->total_L }}</td>
                        <td>{{ $item->total_P }}</td>
                        <td>{{ $item->total_kasus_baru }}</td>
                        <td>{{ $item->total_kunjungan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection