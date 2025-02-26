<?php

namespace App\Http\Controllers;

use App\Charts\Chart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RajalController extends Controller
{
    public function poliklinik(Chart $chart,Request $request)
    {   
            $tgl1 = $request->input('tgl1');
            $tgl2 = $request->input('tgl2');
            $kdpoli = $request->input('poli');
            $kddokter= $request->input('dokter');
            $cara_bayarpj = $request->input('cara_bayar');
            $status = $request->input('status');
            $tombol = $request->input('tombol');

            // pilihan poli
            $pilihan_poli = DB::table('poliklinik')
            ->where('kd_poli','!=', 'HDL')
            ->where('kd_poli','!=', 'LAB')
            ->where('kd_poli','!=', 'RAD')
            ->where('kd_poli','!=', 'IGDK')
            ->where('kd_poli','!=', 'MCU')
            ->where('kd_poli','!=', 'IRM')
            ->select('kd_poli','nm_poli')
            ->get();
            // end pilihan poli

            // pilihan dokter
            $pilihan_dokter = DB::table('dokter')
            ->join('jadwal', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('jadwal.kd_poli', $kdpoli);
            })
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select('dokter.kd_dokter','dokter.nm_dokter')
            ->get();
            // end pilihan dokter

            // start Pilihan Cara Bayar
            $pilihan_cara_bayar = DB::table('penjab')
            ->select('kd_pj','png_jawab')
            ->get();
            // end Pilihan Cara Bayar

            // Start Line Chart
            $grafik_umum = $this->getChartData('PJ2', $tgl1, $tgl2, $kdpoli, $kddokter, $status);
            $grafik_bpjs = $this->getChartData('BPJ', $tgl1, $tgl2, $kdpoli, $kddokter, $status);

            // Sort data based on year and month
            $grafik_umum = $grafik_umum->sortBy(['year', 'month'])->values();
            $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month'])->values();

            // Merge data based on year and month
            $mergedData = $grafik_umum->map(function ($item) use ($grafik_bpjs) {
                $bpjsData = $grafik_bpjs->where('month', $item->month)->first();
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => $item->month_name,
                    'umum_total' => $item->total,
                    'bpjs_total' => $bpjsData ? $bpjsData->total : 0,
                ];
            });

            $judul_line = 'Data Kunjungan Umum dan BPJS';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

            $umum = $mergedData->pluck('umum_total')->toArray();
            $bpjs = $mergedData->pluck('bpjs_total')->toArray();
            $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        // Start Pie Chart Poli
        $poli = DB::table('reg_periksa')
        ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->where('reg_periksa.kd_poli','!=', 'HDL')
        ->where('reg_periksa.kd_poli','!=', 'LAB')
        ->where('reg_periksa.kd_poli','!=', 'RAD')
        ->where('reg_periksa.kd_poli','!=', 'IGDK')
        ->where('reg_periksa.kd_poli','!=', 'MCU')
        ->where('reg_periksa.kd_poli','!=', 'IRM')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('poliklinik.kd_poli','poliklinik.nm_poli') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(poliklinik.nm_poli, 20) as nama_poli'), DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data = $poli->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum = array_sum($data);
        
        // Calculate the percentage for each kd_poli
        $percentages = array_map(function ($value) use ($totalSum) {
            return round(($value / $totalSum) * 100, 2);
        }, $data);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result = collect($poli)->map(function ($item, $key) use ($percentages) {
            return [
                'nama_poli' => $item->nama_poli,
                'total' => $item->total,
                'percentage' => $percentages[$key],
            ];
        });

        $labels = collect($result)->map(function ($item) {
        return $item['nama_poli'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();


        $judul_pie_poli='Data Kunjungan Per Poli';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }

        $warnapoli=( [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
            '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
        ]);
        // End Pie Chart

        //Start Data Kabupaten
        $sql_kab = DB::table('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
        ->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('kabupaten.nm_kab') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(kabupaten.nm_kab, 30) as kab'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sql_kab = $sql_kab->pluck('total')->toArray();

        $totalSum_kab = array_sum($data_sql_kab);

        // Calculate the percentage for each kd_poli
        $percentages_kab = array_map(function ($value) use ($totalSum_kab) {
            return round(($value / $totalSum_kab) * 100, 2);
        }, $data_sql_kab);

        // Combine kd_poli, total, and percentage into a new collection
        $result_kab = collect($sql_kab)->map(function ($item, $key) use ($percentages_kab) {
            return [
                'nama_kab' => $item->kab,
                'total_kab' => $item->total,
                'percentage_kab' => $percentages_kab[$key],
            ];
        });
        $labels_kab = collect($result_kab)->map(function ($item) {
            return $item['nama_kab'] . ': ' .$item['total_kab']  .'('. $item['percentage_kab'] . '%)';
        })->toArray();


        $judul_pie_sql_kab = 'Data Kunjungan Per Kabupaten';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }

        $warna_sql_Kabupaten = (['#FFD700']);
        //End Data Kabupaten

        // Start Bar Kecamatan
        $sqlkecamatan = DB::table('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
        ->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('kecamatan.nm_kec') // Menambahkan klausa groupBy
        ->select(DB::raw('kecamatan.nm_kec as kecamatan'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();
        $data_kecamatan = $sqlkecamatan->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_kecamatan = array_sum($data_kecamatan);
        
        // Calculate the percentage for each kd_poli
        $percentages_kecamatan = array_map(function ($value) use ($totalSum_kecamatan) {
            return round(($value / $totalSum_kecamatan) * 100, 2);
        }, $data_kecamatan);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_kecamatan = collect($sqlkecamatan)->map(function ($item, $key) use ($percentages_kecamatan) {
            return [
                'nama_kecamatan' => $item->kecamatan,
                'total_kecamatan' => $item->total,
                'percentage_kecamatan' => $percentages_kecamatan[$key],
            ];
        });

        $percentages_kecamatan = collect($result_kecamatan)->pluck('percentage_kecamatan')->toArray();
        $labels_kecamatan = collect($result_kecamatan)->map(function ($item) {
        return $item['nama_kecamatan'] . ': ' . $item['total_kecamatan'] . '(' . $item['percentage_kecamatan'] . '%)';
        })->toArray();


        $judul_pie_kecamatan='Data Kunjungan Per Kecamatan';
        $subjudul_pie_kecamatan = '';
        $warnakec=( ['#ADFF2F']);
        // End Bar Kecamatan

        //Start Data kelurahan
        $sql_kel = DB::table('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
        ->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('kelurahan.nm_kel') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(kelurahan.nm_kel, 30) as kel'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sql_kel = $sql_kel->pluck('total')->toArray();

        $totalSum_kel = array_sum($data_sql_kel);

        // Calculate the percentage for each kd_poli
        $percentages_kel = array_map(function ($value) use ($totalSum_kel) {
            return round(($value / $totalSum_kel) * 100, 2);
        }, $data_sql_kel);

        // Combine kd_poli, total, and percentage into a new collection
        $result_kel = collect($sql_kel)->map(function ($item, $key) use ($percentages_kel) {
            return [
                'nama_kel' => $item->kel,
                'total_kel' => $item->total,
                'percentage_kel' => $percentages_kel[$key],
            ];
        });

        $labels_kel = collect($result_kel)->map(function ($item) {
            return $item['nama_kel'] . ': '.$item['total_kel'] .'('. $item['percentage_kel'] . '% )';
        })->toArray();


        $judul_pie_sql_kel = 'Data Kunjungan Per Kelurahan';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sql_kelurahan = (['#4169E1']);
        //End Data kelurahan

        // Start Pie Chart Cara Bayar
        $sqlcarabayar = DB::table('reg_periksa')
        ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->where('reg_periksa.kd_poli','!=', 'HDL')
        ->where('reg_periksa.kd_poli','!=', 'LAB')
        ->where('reg_periksa.kd_poli','!=', 'RAD')
        ->where('reg_periksa.kd_poli','!=', 'IGDK')
        ->where('reg_periksa.kd_poli','!=', 'MCU')
        ->where('reg_periksa.kd_poli','!=', 'IRM')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('kd_pj') // Menambahkan klausa groupBy
        ->select(DB::raw('kd_pj as cara_bayar'), DB::raw('count(*) as total'))
        ->get();
        $data_carabayar = $sqlcarabayar->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_carabayar = array_sum($data_carabayar);
        
        // Calculate the percentage for each kd_poli
        $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
            return round(($value / $totalSum_carabayar) * 100, 2);
        }, $data_carabayar);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_carabayar = collect($sqlcarabayar)->map(function ($item, $key) use ($percentages_carabayar) {
            return [
                'nama_carabayar' => $item->cara_bayar,
                'total_carabayar' => $item->total,
                'percentage_carabayar' => $percentages_carabayar[$key],
            ];
        });

        $percentages_carabayar = collect($result_carabayar)->pluck('percentage_carabayar')->toArray();
        $labels_carabayar = collect($result_carabayar)->map(function ($item) {
        return $item['nama_carabayar'] . ': ' . $item['total_carabayar'] . '(' . $item['percentage_carabayar'] . '%)';
        })->toArray();


        $judul_pie_cara_bayar='Data Kunjungan Cara Bayar';
        $subjudul_pie_cara_bayar = '';
        $datacara_bayar=$percentages_carabayar;
        $labelcara_bayar=$labels_carabayar; 
        $warnabayar=( [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
            '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
        ]);
        // End Pie Chart

        //start prosedur
        $sqlprosedur = DB::table('reg_periksa')
        ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
        ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->where('reg_periksa.kd_poli', '!=', 'HDL')
        ->where('reg_periksa.kd_poli', '!=', 'LAB')
        ->where('reg_periksa.kd_poli', '!=', 'RAD')
        ->where('reg_periksa.kd_poli', '!=', 'IGDK')
        ->where('reg_periksa.kd_poli', '!=', 'MCU')
        ->where('reg_periksa.kd_poli', '!=', 'IRM')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('icd9.kode','icd9.deskripsi_pendek') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(icd9.deskripsi_pendek, 30) as nama'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sqlprosedur = $sqlprosedur->pluck('total')->toArray();

        $totalSumprosedur = array_sum($data_sqlprosedur);

        // Calculate the percentage for each kd_poli
        $percentagesprosedur = array_map(function ($value) use ($totalSumprosedur) {
            return round(($value / $totalSumprosedur) * 100, 2);
        }, $data_sqlprosedur);

        // Combine kd_poli, total, and percentage into a new collection
        $resultprosedur = collect($sqlprosedur)->map(function ($item, $key) use ($percentagesprosedur) {
            return [
                'namaprosedur' => $item->nama,
                'totalprosedur' => $item->total,
                'percentageprosedur' => $percentagesprosedur[$key],
            ];
        });

        $labelsprosedur = collect($resultprosedur)->map(function ($item) {
            return $item['namaprosedur'] . ': '.$item['totalprosedur'] .'('. $item['percentageprosedur'] . '% )';
        })->toArray();


        $judul_pie_sqlprosedur = 'Data Prosedur (ICD9)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqlprosedur = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sqlprosedur = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sqlprosedur = (['#0da168']);
        //end prosedur
    
        //start diagnosa
        $sqldiagnosa = DB::table('reg_periksa')
        ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
        ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->where('reg_periksa.kd_poli', '!=', 'HDL')
        ->where('reg_periksa.kd_poli', '!=', 'LAB')
        ->where('reg_periksa.kd_poli', '!=', 'RAD')
        ->where('reg_periksa.kd_poli', '!=', 'IGDK')
        ->where('reg_periksa.kd_poli', '!=', 'MCU')
        ->where('reg_periksa.kd_poli', '!=', 'IRM')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('penyakit.kd_penyakit','penyakit.nm_penyakit') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(penyakit.nm_penyakit, 30) as nama'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sqldiagnosa = $sqldiagnosa->pluck('total')->toArray();

        $totalSumdiagnosa = array_sum($data_sqldiagnosa);

        // Calculate the percentage for each kd_poli
        $percentagesdiagnosa = array_map(function ($value) use ($totalSumdiagnosa) {
            return round(($value / $totalSumdiagnosa) * 100, 2);
        }, $data_sqldiagnosa);

        // Combine kd_poli, total, and percentage into a new collection
        $resultdiagnosa = collect($sqldiagnosa)->map(function ($item, $key) use ($percentagesdiagnosa) {
            return [
                'namadiagnosa' => $item->nama,
                'totaldiagnosa' => $item->total,
                'percentagediagnosa' => $percentagesdiagnosa[$key],
            ];
        });

        $labelsdiagnosa = collect($resultdiagnosa)->map(function ($item) {
            return $item['namadiagnosa'] . ': '.$item['totaldiagnosa'] .'('. $item['percentagediagnosa'] . '% )';
        })->toArray();


        $judul_pie_sqldiagnosa = 'Data Diagnosa (ICD10)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sqldiagnosa = (['#9ea10d']);
        //end diagnosa

        // Start Pie Chart Status
        $sqlstts = DB::table('reg_periksa')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('stts', 'Sudah')
                    ->orWhere('stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('kd_pj', 'UMU')
                    ->orWhere('kd_pj', 'BPJ');
            });
        })
        ->groupBy('stts') // Menambahkan klausa groupBy
        ->select('stts', DB::raw('count(*) as total'))
        ->get();
        $datastts = $sqlstts->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumstts = array_sum($datastts);
        
        // Calculate the percentage for each kd_poli
        $percentagesstts = array_map(function ($value) use ($totalSumstts) {
            return round(($value / $totalSumstts) * 100, 2);
        }, $datastts);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultstts = collect($sqlstts)->map(function ($item, $key) use ($percentagesstts) {
            return [
                'stts' => $item->stts,
                'total' => $item->total,
                'percentage' => $percentagesstts[$key],
            ];
        });

        $percentagesstts = collect($resultstts)->pluck('percentage')->toArray();
        $labelsstts = collect($resultstts)->map(function ($item) {
        return $item['stts'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();

            $judul_pie_stts='Data Kunjungan Per Status';
            $subjudul_pie_stts = '';
            $datastts=$percentagesstts;
            $labelstts=$labelsstts;
            $warnastts=(['#7FFF00','#DC143C']);
        // End Pie Chart

        //Start perujuk
        $sql_rujuk_masuk = DB::table('reg_periksa')
        ->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->where('reg_periksa.kd_poli', '!=', 'HDL')
        ->where('reg_periksa.kd_poli', '!=', 'LAB')
        ->where('reg_periksa.kd_poli', '!=', 'RAD')
        ->where('reg_periksa.kd_poli', '!=', 'IGDK')
        ->where('reg_periksa.kd_poli', '!=', 'MCU')
        ->where('reg_periksa.kd_poli', '!=', 'IRM')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('rujuk_masuk.perujuk') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(rujuk_masuk.perujuk, 30) as perujuk'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(30)
        ->get();

        $data_sql_rujuk_masuk = $sql_rujuk_masuk->pluck('total')->toArray();

        $totalSum_rujuk_masuk = array_sum($data_sql_rujuk_masuk);

        // Calculate the percentage for each kd_poli
        $percentages_rujuk_masuk = array_map(function ($value) use ($totalSum_rujuk_masuk) {
            return round(($value / $totalSum_rujuk_masuk) * 100, 2);
        }, $data_sql_rujuk_masuk);

        // Combine kd_poli, total, and percentage into a new collection
        $result_rujuk_masuk = collect($sql_rujuk_masuk)->map(function ($item, $key) use ($percentages_rujuk_masuk) {
            return [
                'nama_rujuk_masuk' => $item->perujuk,
                'total_rujuk_masuk' => $item->total,
                'percentage_rujuk_masuk' => $percentages_rujuk_masuk[$key],
            ];
        });

        $labels_rujuk_masuk = collect($result_rujuk_masuk)->map(function ($item) {
            return $item['nama_rujuk_masuk'] . ': '. $item['total_rujuk_masuk'] .'('. $item['percentage_rujuk_masuk'] . '% )';
        })->toArray();


        $warnaperujuk = ['#00FFFF','#3cb371'];
        $judul_pie_sql_rujuk_masuk = 'Data Perujuk Masuk';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        //End perujuk

        // Start Pie Chart Dokter 
        $sqldokter = DB::table('reg_periksa')
        ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.kd_pj', 'UMU')
                    ->orWhere('reg_periksa.kd_pj', 'BPJ');
            });
        })
        ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
        ->select(DB::raw('LEFT(dokter.nm_dokter, 20) as nama'), DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $datadokter = $sqldokter->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumdokter = array_sum($datadokter);
        
        // Calculate the percentage for each kd_poli
        $percentagesdokter = array_map(function ($value) use ($totalSumdokter) {
            return round(($value / $totalSumdokter) * 100, 2);
        }, $datadokter);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultdokter = collect($sqldokter)->map(function ($item, $key) use ($percentagesdokter) {
            return [
                'nama_dokter' => $item->nama,
                'total_dokter' => $item->total,
                'percentage_dokter' => $percentagesdokter[$key],
            ];
        });

        $percentagesdokter = collect($resultdokter)->pluck('percentage_dokter')->toArray();
        $labels_dokter = collect($resultdokter)->map(function ($item) {
        return $item['nama_dokter'] . ': ' . $item['total_dokter'] . '(' . $item['percentage_dokter'] . '%)';
        })->toArray();

        $judul_pie_dokter='Data Kunjungan Per Dokter';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $datadokter=$percentagesdokter;
        $labeldokter=$labels_dokter;
        $warnadokter=( [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
            '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
        ]);
        // End Pie Chart

        // Start Bar Chart Pasien Lama Baru
        $sqlstts_daftar = DB::table('reg_periksa')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('stts', $status);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('kd_pj', 'UMU')
                    ->orWhere('kd_pj', 'BPJ');
            });
        })
        ->groupBy('stts_daftar') // Menambahkan klausa groupBy
        ->select('stts_daftar', DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data_stts_daftar = $sqlstts_daftar->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_stts_daftar = array_sum($data_stts_daftar);
        
        // Calculate the percentage for each kd_poli
        $percentages_stts_daftar = array_map(function ($value) use ($totalSum_stts_daftar) {
            return round(($value / $totalSum_stts_daftar) * 100, 2);
        }, $data_stts_daftar);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_stts_daftar = collect($sqlstts_daftar)->map(function ($item, $key) use ($percentages_stts_daftar) {
            return [
                'nama_stts_daftar' => $item->stts_daftar,
                'total_stts_daftar' => $item->total,
                'percentage_stts_daftar' => $percentages_stts_daftar[$key],
            ];
        });

        $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
        return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
        })->toArray();

        $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';
        $subjudul_bar_stts_daftar = '';
        $warnastts_daftar = ['#3cb371','#ffa500'];

        // End Bar Chart Pasien Lama Baru
    
        // Start Bar Chart JK
        $sqljk = DB::table('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('pasien.jk') // Menambahkan klausa groupBy
        ->select('pasien.jk as jk', DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data_jk = $sqljk->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_jk = array_sum($data_jk);
        
        // Calculate the percentage for each kd_poli
        $percentages_jk = array_map(function ($value) use ($totalSum_jk) {
            return round(($value / $totalSum_jk) * 100, 2);
        }, $data_jk);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_jk = collect($sqljk)->map(function ($item, $key) use ($percentages_jk) {
            return [
                'nama_jk' => $item->jk,
                'total_jk' => $item->total,
                'percentage_jk' => $percentages_jk[$key],
            ];
        });

        $labels_jk = collect($result_jk)->map(function ($item) {
        return $item['nama_jk'] . ' : ' . $item['total_jk'] . '(' . $item['percentage_jk'] . '%)';
        })->toArray();
        

        $judul_bar_jk = 'Data Kunjungan Jenis Kelamin';
        $subjudul_bar_jk = '';
        $warnajk = ['#ffa500','#3cb371'];
        // End Bar Chart JK

        // start pelayanan chart
        $pelayanan = DB::table(DB::raw('(
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_dr
            UNION
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_drpr
            UNION
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_pr 
        ) as r'))
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'r.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli','!=', 'HDL')
            ->where('reg_periksa.kd_poli','!=', 'LAB')
            ->where('reg_periksa.kd_poli','!=', 'RAD')
            ->where('reg_periksa.kd_poli','!=', 'IGDK')
            ->where('reg_periksa.kd_poli','!=', 'MCU')
            ->where('reg_periksa.kd_poli','!=', 'IRM')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->rightJoin('jns_perawatan as j', 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy('j.nm_perawatan')
            ->select([
                'j.nm_perawatan',
                DB::raw('COUNT(j.nm_perawatan) as total')
            ])
            ->orderby('total', 'desc')
            ->limit(10)
            ->get();
    
        $datapel = $pelayanan->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumpel = array_sum($datapel);
        
        // Calculate the percentage for each kd_poli
        $percentagespel = array_map(function ($value) use ($totalSumpel) {
            return round(($value / $totalSumpel) * 100, 2);
        }, $datapel);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultpel = collect($pelayanan)->map(function ($item, $key) use ($percentagespel) {
            return [
                'nama_pel' => $item->nm_perawatan,
                'total' => $item->total,
                'percentage' => $percentagespel[$key],
            ];
        });

        $labelspel = collect($resultpel)->map(function ($item) {
        return $item['nama_pel'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();


        $judul_pie_pel='Data Trend Pelayanan Poliklinik';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }

        $warnapel=( [
            '#008FFB'
        ]);
        // end pelayanan chart



        //start return
        return view('rm.rajal.poliklinik', [
            // untuk mengirim data dalam form
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'kdpoli' => $kdpoli,
            'kddokter' => $kddokter,
            'kd_pj' => $cara_bayarpj,
            'status' => $status,
            // end form
            'pilihan_dokter' => $pilihan_dokter,
            'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
            'pilihan_poli' => $pilihan_poli,
            //kunjungan
            'bpjs' => $bpjs, 
            'umum' => $umum, 
            'labelstat' => $labelstat, 
            'judul_line' => $judul_line, 
            'subjudul_line' => $subjudul_line ,
            'data' => $data, // Contoh: [10, 20, 30]
            'labels' => $labels, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_poli' => $judul_pie_poli, // Contoh: "Judul Chart"
            'subjudul_pie_poli' => $subjudul_pie_poli, // Contoh: "Subjudul Chart"
            'warnapoli' => $warnapoli ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //kabupaten
            'data_sql_kab' => $data_sql_kab, // Contoh: [10, 20, 30]
            'labels_kab' => $labels_kab, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_kab' => $judul_pie_sql_kab, // Contoh: "Judul Chart"
            'subjudul_pie_sql_kab' => $subjudul_pie_sql_kab, // Contoh: "Subjudul Chart"
            'warna_sql_Kabupaten' => $warna_sql_Kabupaten ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //kecamatan
            'data_kecamatan' => $data_kecamatan, // Contoh: [10, 20, 30]
            'labels_kecamatan' => $labels_kecamatan, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_kecamatan' => $judul_pie_kecamatan, // Contoh: "Judul Chart"
            'subjudul_pie_kecamatan' => $subjudul_pie_kecamatan, // Contoh: "Subjudul Chart"
            'warnakec' => $warnakec ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //Kelurahan
            'data_sql_kel' => $data_sql_kel, // Contoh: [10, 20, 30]
            'labels_kel' => $labels_kel, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_kel' => $judul_pie_sql_kel, // Contoh: "Judul Chart"
            'subjudul_pie_sql_kel' => $subjudul_pie_sql_kel, // Contoh: "Subjudul Chart"
            'warna_sql_kelurahan' => $warna_sql_kelurahan ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //cara bayar
            'datacara_bayar' => $datacara_bayar, // Contoh: [10, 20, 30]
            'labelcara_bayar' => $labelcara_bayar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_cara_bayar' => $judul_pie_cara_bayar, // Contoh: "Judul Chart"
            'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar, // Contoh: "Subjudul Chart"
            'warnabayar' => $warnabayar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //prosedur
            'data_sqlprosedur' => $data_sqlprosedur, // Contoh: [10, 20, 30]
            'labelsprosedur' => $labelsprosedur, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sqlprosedur' => $judul_pie_sqlprosedur, // Contoh: "Judul Chart"
            'subjudul_pie_sqlprosedur' => $subjudul_pie_sqlprosedur, // Contoh: "Subjudul Chart"
            'warna_sqlprosedur' => $warna_sqlprosedur ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //diagnosa
            'data_sqldiagnosa' => $data_sqldiagnosa, // Contoh: [10, 20, 30]
            'labelsdiagnosa' => $labelsdiagnosa, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sqldiagnosa' => $judul_pie_sqldiagnosa, // Contoh: "Judul Chart"
            'subjudul_pie_sqldiagnosa' => $subjudul_pie_sqldiagnosa, // Contoh: "Subjudul Chart"
            'warna_sqldiagnosa' => $warna_sqldiagnosa ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //stts
            'datastts' => $datastts, // Contoh: [10, 20, 30]
            'labelsstts' => $labelsstts, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_stts' => $judul_pie_stts, // Contoh: "Judul Chart"
            'subjudul_pie_stts' => $subjudul_pie_stts, // Contoh: "Subjudul Chart"
            'warnastts' => $warnastts ,// Contoh: ["#FF4560", "#00E396", "#008FFB"] 
            //Perujuk
            'data_sql_rujuk_masuk' => $data_sql_rujuk_masuk, // Contoh: [10, 20, 30]
            'labels_rujuk_masuk' => $labels_rujuk_masuk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_rujuk_masuk' => $judul_pie_sql_rujuk_masuk, // Contoh: "Judul Chart"
            'subjudul_pie_sql_rujuk_masuk' => $subjudul_pie_sql_rujuk_masuk, // Contoh: "Subjudul Chart"
            'warnaperujuk' => $warnaperujuk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //dokter
            'datadokter' => $datadokter, // Contoh: [10, 20, 30]
            'labeldokter' => $labeldokter, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_dokter' => $judul_pie_dokter, // Contoh: "Judul Chart"
            'subjudul_pie_dokter' => $subjudul_pie_dokter, // Contoh: "Subjudul Chart"
            'warnadokter' => $warnadokter ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //stts_daftar
            'data_stts_daftar' => $data_stts_daftar, // Contoh: [10, 20, 30]
            'labels_stts_daftar' => $labels_stts_daftar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_bar_stts_daftar' => $judul_bar_stts_daftar, // Contoh: "Judul Chart"
            'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar, // Contoh: "Subjudul Chart"
            'warnastts_daftar' => $warnastts_daftar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //JK
            'data_jk' => $data_jk, // Contoh: [10, 20, 30]
            'labels_jk' => $labels_jk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_bar_jk' => $judul_bar_jk, // Contoh: "Judul Chart"
            'subjudul_bar_jk' => $subjudul_bar_jk, // Contoh: "Subjudul Chart"
            'warnajk' => $warnajk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //pelayanan
            'datapel' => $datapel, 
            'labelspel' => $labelspel, 
            'judul_pie_pel' => $judul_pie_pel, 
            'subjudul_pie_pel' => $subjudul_pie_pel, 
            'warnapel' => $warnapel ,
        ]);
    //end return
    }


    //start seluruh poli khusus
    public function allpoliklinikkhusus(Chart $chart,Request $request,$kd_poli)
    {   
        $tgl1 = $request->input('tgl1');
        $tgl2 = $request->input('tgl2');
        $kdpoli = $kd_poli ;
        $kddokter= $request->input('dokter');
        $cara_bayarpj = $request->input('cara_bayar');
        $status = $request->input('status');
        $tombol = $request->input('tombol');
        
    // pilihan dokter
        $pilihan_dokter = DB::table('dokter')
        ->join('jadwal', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('jadwal.kd_poli', $kdpoli);
        })
        ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
        ->select('dokter.kd_dokter','dokter.nm_dokter')
        ->get();
    // end pilihan dokter
    
    // start Pilihan Cara Bayar
        $pilihan_cara_bayar = DB::table('penjab')
        ->select('kd_pj','png_jawab')
        ->get();
    // end Pilihan Cara Bayar

        // Start Pie Chart Poli --Data Kunjungan Per Poli
            $poli = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('poliklinik.kd_poli','poliklinik.nm_poli') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(poliklinik.nm_poli, 20) as nama_poli'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data = $poli->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum = array_sum($data);
            
            // Calculate the percentage for each kd_poli
            $percentages = array_map(function ($value) use ($totalSum) {
                return round(($value / $totalSum) * 100, 2);
            }, $data);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result = collect($poli)->map(function ($item, $key) use ($percentages) {
                return [
                    'nama_poli' => $item->nama_poli,
                    'total' => $item->total,
                    'percentage' => $percentages[$key],
                ];
            });

            $percentages = collect($result)->pluck('percentage')->toArray();
            $labels = collect($result)->map(function ($item) {
            return $item['nama_poli'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();


            $judul_pie_poli='Data Kunjungan Per Poli';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $data=$percentages;
            $label=$labels;
            $warnapoli=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
            //$label=$poli ->pluck('nama_poli')->toArray();
        // End Pie Chart

        //Start perujuk
            $sql_rujuk_masuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('rujuk_masuk.perujuk') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(rujuk_masuk.perujuk, 30) as perujuk'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(30)
            ->get();

            $data_sql_rujuk_masuk = $sql_rujuk_masuk->pluck('total')->toArray();

            $totalSum_rujuk_masuk = array_sum($data_sql_rujuk_masuk);

            // Calculate the percentage for each kd_poli
            $percentages_rujuk_masuk = array_map(function ($value) use ($totalSum_rujuk_masuk) {
                return round(($value / $totalSum_rujuk_masuk) * 100, 2);
            }, $data_sql_rujuk_masuk);

            // Combine kd_poli, total, and percentage into a new collection
            $result_rujuk_masuk = collect($sql_rujuk_masuk)->map(function ($item, $key) use ($percentages_rujuk_masuk) {
                return [
                    'nama_rujuk_masuk' => $item->perujuk,
                    'total_rujuk_masuk' => $item->total,
                    'percentage_rujuk_masuk' => $percentages_rujuk_masuk[$key],
                ];
            });
            $percentages_rujuk_masuk = collect($result_rujuk_masuk)->pluck('percentage_rujuk_masuk')->toArray();
            $labels_rujuk_masuk = collect($result_rujuk_masuk)->map(function ($item) {
                return $item['nama_rujuk_masuk'] . ': '. $item['total_rujuk_masuk'] .'('. $item['percentage_rujuk_masuk'] . '% )';
            })->toArray();


            $warnaperujuk = ['#00FFFF','#3cb371'];
            $judul_pie_sql_rujuk_masuk = 'Data Perujuk Masuk';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
        //End perujuk

        // Start Line Chart
            $grafik_umum = $this->getChartData2('PJ2', $tgl1, $tgl2, $kdpoli, $kddokter, $status);
            $grafik_bpjs = $this->getChartData2('BPJ', $tgl1, $tgl2, $kdpoli, $kddokter, $status);

                // Sort data based on year and month
                $umumMonths = $grafik_umum->pluck('month')->unique();
                $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

                $grafik_umum = $grafik_umum->sortBy(['year', 'month']);
                $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month']);

                // Extract unique months for umum and bpjs
                $umumMonths = $grafik_umum->pluck('month')->unique();
                $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

                // Combine umum and bpjs months, get unique values, and sort them
                $allMonths = collect($umumMonths->merge($bpjsMonths)->unique()->sortBy(['year', 'month']));

                // Merge data based on year and month
                $mergedData = $allMonths->map(function ($month) use ($grafik_umum, $grafik_bpjs) {
                    $umumData = $grafik_umum->where('month', $month)->first();
                    $bpjsData = $grafik_bpjs->where('month', $month)->first();

                    return [
                        'year' => $umumData ? $umumData->year : $bpjsData->year,
                        'month' => $month,
                        'month_name' => $umumData ? $umumData->month_name : $bpjsData->month_name,
                        'umum_total' => $umumData ? $umumData->total : null,
                        'bpjs_total' => $bpjsData ? $bpjsData->total : null,
                    ];
                });

            // Merge data based on year and month
            $judul_line = 'Data Kunjungan Umum dan BPJS';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

                $umum = $mergedData->pluck('umum_total')->toArray();
                $bpjs = $mergedData->pluck('bpjs_total')->toArray();
                $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        //Start Data Kabupaten
            $sql_kab = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kabupaten.nm_kab') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kabupaten.nm_kab, 30) as kab'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kab = $sql_kab->pluck('total')->toArray();

            $totalSum_kab = array_sum($data_sql_kab);

            // Calculate the percentage for each kd_poli
            $percentages_kab = array_map(function ($value) use ($totalSum_kab) {
                return round(($value / $totalSum_kab) * 100, 2);
            }, $data_sql_kab);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kab = collect($sql_kab)->map(function ($item, $key) use ($percentages_kab) {
                return [
                    'nama_kab' => $item->kab,
                    'total_kab' => $item->total,
                    'percentage_kab' => $percentages_kab[$key],
                ];
            });
            $labels_kab = collect($result_kab)->map(function ($item) {
                return $item['nama_kab'] . ': ' .$item['total_kab']  .'('. $item['percentage_kab'] . '%)';
            })->toArray();


            $judul_pie_sql_kab = 'Data Kunjungan Per Kabupaten';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

            $warna_sql_Kabupaten = (['#FFD700']);
        //End Data Kabupaten

        // Start Bar Kecamatan
            $sqlkecamatan = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kecamatan.nm_kec') // Menambahkan klausa groupBy
            ->select(DB::raw('kecamatan.nm_kec as kecamatan'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();
            $data_kecamatan = $sqlkecamatan->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_kecamatan = array_sum($data_kecamatan);
            
            // Calculate the percentage for each kd_poli
            $percentages_kecamatan = array_map(function ($value) use ($totalSum_kecamatan) {
                return round(($value / $totalSum_kecamatan) * 100, 2);
            }, $data_kecamatan);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_kecamatan = collect($sqlkecamatan)->map(function ($item, $key) use ($percentages_kecamatan) {
                return [
                    'nama_kecamatan' => $item->kecamatan,
                    'total_kecamatan' => $item->total,
                    'percentage_kecamatan' => $percentages_kecamatan[$key],
                ];
            });

            $labels_kecamatan = collect($result_kecamatan)->map(function ($item) {
            return $item['nama_kecamatan'] . ': ' . $item['total_kecamatan'] . '(' . $item['percentage_kecamatan'] . '%)';
            })->toArray();


            $judul_pie_kecamatan='Data Kunjungan Per Kecamatan';
            $subjudul_pie_kecamatan = '';
            $warnakec=( ['#ADFF2F']);
        // End Bar Kecamatan

        //Start Data kelurahan
            $sql_kel = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kelurahan.nm_kel') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kelurahan.nm_kel, 30) as kel'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kel = $sql_kel->pluck('total')->toArray();

            $totalSum_kel = array_sum($data_sql_kel);

            // Calculate the percentage for each kd_poli
            $percentages_kel = array_map(function ($value) use ($totalSum_kel) {
                return round(($value / $totalSum_kel) * 100, 2);
            }, $data_sql_kel);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kel = collect($sql_kel)->map(function ($item, $key) use ($percentages_kel) {
                return [
                    'nama_kel' => $item->kel,
                    'total_kel' => $item->total,
                    'percentage_kel' => $percentages_kel[$key],
                ];
            });

            $labels_kel = collect($result_kel)->map(function ($item) {
                return $item['nama_kel'] . ': '.$item['total_kel'] .'('. $item['percentage_kel'] . '% )';
            })->toArray();


            $judul_pie_sql_kel = 'Data Kunjungan kelurahan';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warna_sql_kelurahan = (['#4169E1']);
        //End Data kelurahan

        // Start Pie Chart Dokter 
            $sqldokter = DB::table('reg_periksa')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.kd_pj', 'UMU')
                        ->orWhere('reg_periksa.kd_pj', 'BPJ');
                });
            })
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select(DB::raw('LEFT(dokter.nm_dokter, 20) as nama'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $datadokter = $sqldokter->pluck('total')->toArray();

            // Calculate the total sum
            $totalSumdokter = array_sum($datadokter);
            
            // Calculate the percentage for each kd_poli
            $percentagesdokter = array_map(function ($value) use ($totalSumdokter) {
                return round(($value / $totalSumdokter) * 100, 2);
            }, $datadokter);
            
            // Combine kd_poli, total, and percentage into a new collection
            $resultdokter = collect($sqldokter)->map(function ($item, $key) use ($percentagesdokter) {
                return [
                    'nama_dokter' => $item->nama,
                    'total_dokter' => $item->total,
                    'percentage_dokter' => $percentagesdokter[$key],
                ];
            });

            $percentagesdokter = collect($resultdokter)->pluck('percentage_dokter')->toArray();
            $labels_dokter = collect($resultdokter)->map(function ($item) {
            return $item['nama_dokter'] . ': ' . $item['total_dokter'] . '(' . $item['percentage_dokter'] . '%)';
            })->toArray();

            $judul_pie_dokter='Data Kunjungan Per Dokter';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $datadokter=$percentagesdokter;
            $labeldokter=$labels_dokter;
            $warnadokter=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
        // End Pie Chart

        // Start Pie Chart Cara Bayar
        $sqlcarabayar = DB::table('reg_periksa')
        ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('kd_pj') // Menambahkan klausa groupBy
        ->select(DB::raw('kd_pj as cara_bayar'), DB::raw('count(*) as total'))
        ->get();
        $data_carabayar = $sqlcarabayar->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_carabayar = array_sum($data_carabayar);
        
        // Calculate the percentage for each kd_poli
        $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
            return round(($value / $totalSum_carabayar) * 100, 2);
        }, $data_carabayar);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_carabayar = collect($sqlcarabayar)->map(function ($item, $key) use ($percentages_carabayar) {
            return [
                'nama_carabayar' => $item->cara_bayar,
                'total_carabayar' => $item->total,
                'percentage_carabayar' => $percentages_carabayar[$key],
            ];
        });

        $percentages_carabayar = collect($result_carabayar)->pluck('percentage_carabayar')->toArray();
        $labels_carabayar = collect($result_carabayar)->map(function ($item) {
        return $item['nama_carabayar'] . ': ' . $item['total_carabayar'] . '(' . $item['percentage_carabayar'] . '%)';
        })->toArray();


        $judul_pie_cara_bayar='Data Kunjungan Cara Bayar';
        $subjudul_pie_cara_bayar = '';
        $datacara_bayar=$percentages_carabayar;
        $labelcara_bayar=$labels_carabayar; 
        $warnabayar=( [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
            '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
        ]);
    // End Pie Chart

    // Start Pie Chart Status
        $sqlstts = DB::table('reg_periksa')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('stts', 'Sudah')
                    ->orWhere('stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('kd_pj', 'UMU')
                    ->orWhere('kd_pj', 'BPJ');
            });
        })
        ->groupBy('stts') // Menambahkan klausa groupBy
        ->select('stts', DB::raw('count(*) as total'))
        ->get();
        $datastts = $sqlstts->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumstts = array_sum($datastts);
        
        // Calculate the percentage for each kd_poli
        $percentagesstts = array_map(function ($value) use ($totalSumstts) {
            return round(($value / $totalSumstts) * 100, 2);
        }, $datastts);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultstts = collect($sqlstts)->map(function ($item, $key) use ($percentagesstts) {
            return [
                'stts' => $item->stts,
                'total' => $item->total,
                'percentage' => $percentagesstts[$key],
            ];
        });

        $percentagesstts = collect($resultstts)->pluck('percentage')->toArray();
        $labelsstts = collect($resultstts)->map(function ($item) {
        return $item['stts'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();

            $judul_pie_stts='Data Kunjungan Per Status';
            $subjudul_pie_stts = '';
            $datastts=$percentagesstts;
            $labelstts=$labelsstts;
            $warnastts=(['#7FFF00','#DC143C']);
    // End Pie Chart
    
    // Start Bar Chart Pasien Lama Baru
        $sqlstts_daftar = DB::table('reg_periksa')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('stts', 'Sudah')
                    ->orWhere('stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('kd_pj', 'UMU')
                    ->orWhere('kd_pj', 'BPJ');
            });
        })
        ->groupBy('stts_daftar') // Menambahkan klausa groupBy
        ->select('stts_daftar', DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data_stts_daftar = $sqlstts_daftar->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_stts_daftar = array_sum($data_stts_daftar);
        
        // Calculate the percentage for each kd_poli
        $percentages_stts_daftar = array_map(function ($value) use ($totalSum_stts_daftar) {
            return round(($value / $totalSum_stts_daftar) * 100, 2);
        }, $data_stts_daftar);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_stts_daftar = collect($sqlstts_daftar)->map(function ($item, $key) use ($percentages_stts_daftar) {
            return [
                'nama_stts_daftar' => $item->stts_daftar,
                'total_stts_daftar' => $item->total,
                'percentage_stts_daftar' => $percentages_stts_daftar[$key],
            ];
        });

        $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
        return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
        })->toArray();

        $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';
        $subjudul_bar_stts_daftar = '';
        $warnastts_daftar = ['#3cb371','#ffa500'];
    // End Bar Chart Pasien Lama Baru
    
    // Start Bar Chart JK
        $sqljk = DB::table('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('pasien.jk') // Menambahkan klausa groupBy
        ->select('pasien.jk as jk', DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data_jk = $sqljk->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_jk = array_sum($data_jk);
        
        // Calculate the percentage for each kd_poli
        $percentages_jk = array_map(function ($value) use ($totalSum_jk) {
            return round(($value / $totalSum_jk) * 100, 2);
        }, $data_jk);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_jk = collect($sqljk)->map(function ($item, $key) use ($percentages_jk) {
            return [
                'nama_jk' => $item->jk,
                'total_jk' => $item->total,
                'percentage_jk' => $percentages_jk[$key],
            ];
        });

        $labels_jk = collect($result_jk)->map(function ($item) {
        return $item['nama_jk'] . ' : ' . $item['total_jk'] . '(' . $item['percentage_jk'] . '%)';
        })->toArray();
        
        $judul_bar_jk = 'Data Kunjungan Jenkel';
        $subjudul_bar_jk = '';
        $warnajk = ['#ffa500','#3cb371'];
    // End Bar Chart JK

    // start pelayanan chart
        $pelayanan = DB::table(DB::raw('(
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_dr
            UNION
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_drpr
            UNION
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_pr 
        ) as r'))
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'r.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli','!=', 'HDL')
            ->where('reg_periksa.kd_poli','!=', 'LAB')
            ->where('reg_periksa.kd_poli','!=', 'RAD')
            ->where('reg_periksa.kd_poli','!=', 'IGDK')
            ->where('reg_periksa.kd_poli','!=', 'MCU')
            ->where('reg_periksa.kd_poli','!=', 'IRM')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->rightJoin('jns_perawatan as j', 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy('j.nm_perawatan')
            ->select([
                'j.nm_perawatan',
                DB::raw('COUNT(j.nm_perawatan) as total')
            ])
            ->orderby('total', 'desc')
            ->limit(10)
            ->get();

        $datapel = $pelayanan->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumpel = array_sum($datapel);
        
        // Calculate the percentage for each kd_poli
        $percentagespel = array_map(function ($value) use ($totalSumpel) {
            return round(($value / $totalSumpel) * 100, 2);
        }, $datapel);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultpel = collect($pelayanan)->map(function ($item, $key) use ($percentagespel) {
            return [
                'nama_pel' => $item->nm_perawatan,
                'total' => $item->total,
                'percentage' => $percentagespel[$key],
            ];
        });

        $labelspel = collect($resultpel)->map(function ($item) {
        return $item['nama_pel'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();


        $judul_pie_pel='Data Trend Pelayanan Poliklinik';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }

        $warnapel=( [
            '#008FFB'
        ]);
    // end pelayanan chart

    //start prosedur
        $sqlprosedur = DB::table('reg_periksa')
        ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
        ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('icd9.kode','icd9.deskripsi_pendek') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(icd9.deskripsi_pendek, 30) as nama'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sqlprosedur = $sqlprosedur->pluck('total')->toArray();

        $totalSumprosedur = array_sum($data_sqlprosedur);

        // Calculate the percentage for each kd_poli
        $percentagesprosedur = array_map(function ($value) use ($totalSumprosedur) {
            return round(($value / $totalSumprosedur) * 100, 2);
        }, $data_sqlprosedur);

        // Combine kd_poli, total, and percentage into a new collection
        $resultprosedur = collect($sqlprosedur)->map(function ($item, $key) use ($percentagesprosedur) {
            return [
                'namaprosedur' => $item->nama,
                'totalprosedur' => $item->total,
                'percentageprosedur' => $percentagesprosedur[$key],
            ];
        });

        $labelsprosedur = collect($resultprosedur)->map(function ($item) {
            return $item['namaprosedur'] . ': '.$item['totalprosedur'] .'('. $item['percentageprosedur'] . '% )';
        })->toArray();


        $judul_pie_sqlprosedur = 'Data Prosedur (ICD9)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqlprosedur = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sqlprosedur = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sqlprosedur = (['#0da168']);
    //end prosedur

    //start diagnosa
        $sqldiagnosa = DB::table('reg_periksa')
        ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
        ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('penyakit.kd_penyakit','penyakit.nm_penyakit') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(penyakit.nm_penyakit, 30) as nama'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sqldiagnosa = $sqldiagnosa->pluck('total')->toArray();

        $totalSumdiagnosa = array_sum($data_sqldiagnosa);

        // Calculate the percentage for each kd_poli
        $percentagesdiagnosa = array_map(function ($value) use ($totalSumdiagnosa) {
            return round(($value / $totalSumdiagnosa) * 100, 2);
        }, $data_sqldiagnosa);

        // Combine kd_poli, total, and percentage into a new collection
        $resultdiagnosa = collect($sqldiagnosa)->map(function ($item, $key) use ($percentagesdiagnosa) {
            return [
                'namadiagnosa' => $item->nama,
                'totaldiagnosa' => $item->total,
                'percentagediagnosa' => $percentagesdiagnosa[$key],
            ];
        });

        $labelsdiagnosa = collect($resultdiagnosa)->map(function ($item) {
            return $item['namadiagnosa'] . ': '.$item['totaldiagnosa'] .'('. $item['percentagediagnosa'] . '% )';
        })->toArray();


        $judul_pie_sqldiagnosa = 'Data diagnosa (ICD10)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sqldiagnosa = (['#9ea10d']);
    //end diagnosa
        

        //start return
        return view('rm.rajal.poliklinikkhusus', [
            // untuk mengirim data dalam form
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'kddokter' => $kddokter,
            'kd_pj' => $cara_bayarpj,
            'status' => $status,
            // end form
            'pilihan_dokter' => $pilihan_dokter,
            'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
            //kunjungan
            'bpjs' => $bpjs, 
            'umum' => $umum, 
            'labelstat' => $labelstat, 
            'judul_line' => $judul_line, 
            'subjudul_line' => $subjudul_line ,
            //poli
            'data' => $data, // Contoh: [10, 20, 30]
            'labels' => $labels, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_poli' => $judul_pie_poli, // Contoh: "Judul Chart"
            'subjudul_pie_poli' => $subjudul_pie_poli, // Contoh: "Subjudul Chart"
            'warnapoli' => $warnapoli ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //Perujuk
            'data_sql_rujuk_masuk' => $data_sql_rujuk_masuk, // Contoh: [10, 20, 30]
            'labels_rujuk_masuk' => $labels_rujuk_masuk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_rujuk_masuk' => $judul_pie_sql_rujuk_masuk, // Contoh: "Judul Chart"
            'subjudul_pie_sql_rujuk_masuk' => $subjudul_pie_sql_rujuk_masuk, // Contoh: "Subjudul Chart"
            'warnaperujuk' => $warnaperujuk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //kabupaten
            'data_sql_kab' => $data_sql_kab, // Contoh: [10, 20, 30]
            'labels_kab' => $labels_kab, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_kab' => $judul_pie_sql_kab, // Contoh: "Judul Chart"
            'subjudul_pie_sql_kab' => $subjudul_pie_sql_kab, // Contoh: "Subjudul Chart"
            'warna_sql_Kabupaten' => $warna_sql_Kabupaten ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //kecamatan
            'data_kecamatan' => $data_kecamatan, // Contoh: [10, 20, 30]
            'labels_kecamatan' => $labels_kecamatan, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_kecamatan' => $judul_pie_kecamatan, // Contoh: "Judul Chart"
            'subjudul_pie_kecamatan' => $subjudul_pie_kecamatan, // Contoh: "Subjudul Chart"
            'warnakec' => $warnakec ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //Kelurahan
            'data_sql_kel' => $data_sql_kel, // Contoh: [10, 20, 30]
            'labels_kel' => $labels_kel, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_kel' => $judul_pie_sql_kel, // Contoh: "Judul Chart"
            'subjudul_pie_sql_kel' => $subjudul_pie_sql_kel, // Contoh: "Subjudul Chart"
            'warna_sql_kelurahan' => $warna_sql_kelurahan ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //dokter
            'datadokter' => $datadokter, // Contoh: [10, 20, 30]
            'labeldokter' => $labeldokter, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_dokter' => $judul_pie_dokter, // Contoh: "Judul Chart"
            'subjudul_pie_dokter' => $subjudul_pie_dokter, // Contoh: "Subjudul Chart"
            'warnadokter' => $warnadokter ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //stts
            'datacara_bayar' => $datacara_bayar, // Contoh: [10, 20, 30]
            'labelcara_bayar' => $labelcara_bayar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_cara_bayar' => $judul_pie_cara_bayar, // Contoh: "Judul Chart"
            'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar, // Contoh: "Subjudul Chart"
            'warnabayar' => $warnabayar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //stts
            'datastts' => $datastts, // Contoh: [10, 20, 30]
            'labelsstts' => $labelsstts, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_stts' => $judul_pie_stts, // Contoh: "Judul Chart"
            'subjudul_pie_stts' => $subjudul_pie_stts, // Contoh: "Subjudul Chart"
            'warnastts' => $warnastts ,// Contoh: ["#FF4560", "#00E396", "#008FFB"] 
            //stts_daftar
            'data_stts_daftar' => $data_stts_daftar, // Contoh: [10, 20, 30]
            'labels_stts_daftar' => $labels_stts_daftar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_bar_stts_daftar' => $judul_bar_stts_daftar, // Contoh: "Judul Chart"
            'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar, // Contoh: "Subjudul Chart"
            'warnastts_daftar' => $warnastts_daftar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //JK
            'data_jk' => $data_jk, // Contoh: [10, 20, 30]
            'labels_jk' => $labels_jk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_bar_jk' => $judul_bar_jk, // Contoh: "Judul Chart"
            'subjudul_bar_jk' => $subjudul_bar_jk, // Contoh: "Subjudul Chart"
            'warnajk' => $warnajk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //pelayanan
            'datapel' => $datapel, 
            'labelspel' => $labelspel, 
            'judul_pie_pel' => $judul_pie_pel, 
            'subjudul_pie_pel' => $subjudul_pie_pel, 
            'warnapel' => $warnapel ,
            //prosedur
            'data_sqlprosedur' => $data_sqlprosedur, // Contoh: [10, 20, 30]
            'labelsprosedur' => $labelsprosedur, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sqlprosedur' => $judul_pie_sqlprosedur, // Contoh: "Judul Chart"
            'subjudul_pie_sqlprosedur' => $subjudul_pie_sqlprosedur, // Contoh: "Subjudul Chart"
            'warna_sqlprosedur' => $warna_sqlprosedur ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //diagnosa
            'data_sqldiagnosa' => $data_sqldiagnosa, // Contoh: [10, 20, 30]
            'labelsdiagnosa' => $labelsdiagnosa, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sqldiagnosa' => $judul_pie_sqldiagnosa, // Contoh: "Judul Chart"
            'subjudul_pie_sqldiagnosa' => $subjudul_pie_sqldiagnosa, // Contoh: "Subjudul Chart"
            'warna_sqldiagnosa' => $warna_sqldiagnosa ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
        ]);
    //end return
    }
    //End seluruh poli khusus    


    //start seluruh poli khusus
    public function igdk(Chart $chart,Request $request,$kd_poli = null)
    {   
        $tgl1 = $request->input('tgl1');
        $tgl2 = $request->input('tgl2');
        $kdpoli = $kd_poli ;
        $kddokter= $request->input('dokter');
        $cara_bayarpj = $request->input('cara_bayar');
        $status = $request->input('status');
        $tombol = $request->input('tombol');
        
    // pilihan dokter
        $pilihan_dokter = DB::table('dokter')
        ->join('reg_periksa', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
        ->where('reg_periksa.kd_poli', 'IGDK') // Pastikan hanya menampilkan IGDK
        ->whereIn('reg_periksa.kd_dokter', ['D15', 'D17', 'dr.sofi'])
        ->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2])
        ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
        ->select('dokter.kd_dokter','dokter.nm_dokter')
        ->get();
    // end pilihan dokter
    
    // start Pilihan Cara Bayar
        $pilihan_cara_bayar = DB::table('penjab')
        ->select('kd_pj','png_jawab')
        ->get();
    // end Pilihan Cara Bayar

        // Start Pie Chart Poli --Data Kunjungan Per Poli
            $poli = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('poliklinik.kd_poli','poliklinik.nm_poli') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(poliklinik.nm_poli, 20) as nama_poli'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data = $poli->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum = array_sum($data);
            
            // Calculate the percentage for each kd_poli
            $percentages = array_map(function ($value) use ($totalSum) {
                return round(($value / $totalSum) * 100, 2);
            }, $data);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result = collect($poli)->map(function ($item, $key) use ($percentages) {
                return [
                    'nama_poli' => $item->nama_poli,
                    'total' => $item->total,
                    'percentage' => $percentages[$key],
                ];
            });

            $percentages = collect($result)->pluck('percentage')->toArray();
            $labels = collect($result)->map(function ($item) {
            return $item['nama_poli'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();


            $judul_pie_poli='Data Kunjungan Per Poli';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $data=$percentages;
            $label=$labels;
            $warnapoli=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
            //$label=$poli ->pluck('nama_poli')->toArray();
        // End Pie Chart

        //Start perujuk
            $sql_rujuk_masuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('rujuk_masuk.perujuk') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(rujuk_masuk.perujuk, 30) as perujuk'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(30)
            ->get();

            $data_sql_rujuk_masuk = $sql_rujuk_masuk->pluck('total')->toArray();

            $totalSum_rujuk_masuk = array_sum($data_sql_rujuk_masuk);

            // Calculate the percentage for each kd_poli
            $percentages_rujuk_masuk = array_map(function ($value) use ($totalSum_rujuk_masuk) {
                return round(($value / $totalSum_rujuk_masuk) * 100, 2);
            }, $data_sql_rujuk_masuk);

            // Combine kd_poli, total, and percentage into a new collection
            $result_rujuk_masuk = collect($sql_rujuk_masuk)->map(function ($item, $key) use ($percentages_rujuk_masuk) {
                return [
                    'nama_rujuk_masuk' => $item->perujuk,
                    'total_rujuk_masuk' => $item->total,
                    'percentage_rujuk_masuk' => $percentages_rujuk_masuk[$key],
                ];
            });
            $percentages_rujuk_masuk = collect($result_rujuk_masuk)->pluck('percentage_rujuk_masuk')->toArray();
            $labels_rujuk_masuk = collect($result_rujuk_masuk)->map(function ($item) {
                return $item['nama_rujuk_masuk'] . ': '. $item['total_rujuk_masuk'] .'('. $item['percentage_rujuk_masuk'] . '% )';
            })->toArray();


            $warnaperujuk = ['#00FFFF','#3cb371'];
            $judul_pie_sql_rujuk_masuk = 'Data Perujuk Masuk';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
        //End perujuk

        // Start Line Chart
            $grafik_umum = $this->getChartData5('PJ2', $tgl1, $tgl2, $kdpoli, $kddokter, $status);
            $grafik_bpjs = $this->getChartData5('BPJ', $tgl1, $tgl2, $kdpoli, $kddokter, $status);

                // Sort data based on year and month
                $umumMonths = $grafik_umum->pluck('month')->unique();
                $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

                $grafik_umum = $grafik_umum->sortBy(['year', 'month']);
                $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month']);

                // Extract unique months for umum and bpjs
                $umumMonths = $grafik_umum->pluck('month')->unique();
                $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

                // Combine umum and bpjs months, get unique values, and sort them
                $allMonths = collect($umumMonths->merge($bpjsMonths)->unique()->sortBy(['year', 'month']));

                // Merge data based on year and month
                $mergedData = $allMonths->map(function ($month) use ($grafik_umum, $grafik_bpjs) {
                    $umumData = $grafik_umum->where('month', $month)->first();
                    $bpjsData = $grafik_bpjs->where('month', $month)->first();

                    return [
                        'year' => $umumData ? $umumData->year : $bpjsData->year,
                        'month' => $month,
                        'month_name' => $umumData ? $umumData->month_name : $bpjsData->month_name,
                        'umum_total' => $umumData ? $umumData->total : null,
                        'bpjs_total' => $bpjsData ? $bpjsData->total : null,
                    ];
                });

            // Merge data based on year and month
            $judul_line = 'Data Kunjungan Umum dan BPJS';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

                $umum = $mergedData->pluck('umum_total')->toArray();
                $bpjs = $mergedData->pluck('bpjs_total')->toArray();
                $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        //Start Data Kabupaten
            $sql_kab = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kabupaten.nm_kab') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kabupaten.nm_kab, 30) as kab'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kab = $sql_kab->pluck('total')->toArray();

            $totalSum_kab = array_sum($data_sql_kab);

            // Calculate the percentage for each kd_poli
            $percentages_kab = array_map(function ($value) use ($totalSum_kab) {
                return round(($value / $totalSum_kab) * 100, 2);
            }, $data_sql_kab);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kab = collect($sql_kab)->map(function ($item, $key) use ($percentages_kab) {
                return [
                    'nama_kab' => $item->kab,
                    'total_kab' => $item->total,
                    'percentage_kab' => $percentages_kab[$key],
                ];
            });
            $labels_kab = collect($result_kab)->map(function ($item) {
                return $item['nama_kab'] . ': ' .$item['total_kab']  .'('. $item['percentage_kab'] . '%)';
            })->toArray();


            $judul_pie_sql_kab = 'Data Kunjungan Per Kabupaten';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

            $warna_sql_Kabupaten = (['#FFD700']);
        //End Data Kabupaten

        // Start Bar Kecamatan
            $sqlkecamatan = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kecamatan.nm_kec') // Menambahkan klausa groupBy
            ->select(DB::raw('kecamatan.nm_kec as kecamatan'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();
            $data_kecamatan = $sqlkecamatan->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_kecamatan = array_sum($data_kecamatan);
            
            // Calculate the percentage for each kd_poli
            $percentages_kecamatan = array_map(function ($value) use ($totalSum_kecamatan) {
                return round(($value / $totalSum_kecamatan) * 100, 2);
            }, $data_kecamatan);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_kecamatan = collect($sqlkecamatan)->map(function ($item, $key) use ($percentages_kecamatan) {
                return [
                    'nama_kecamatan' => $item->kecamatan,
                    'total_kecamatan' => $item->total,
                    'percentage_kecamatan' => $percentages_kecamatan[$key],
                ];
            });

            $labels_kecamatan = collect($result_kecamatan)->map(function ($item) {
            return $item['nama_kecamatan'] . ': ' . $item['total_kecamatan'] . '(' . $item['percentage_kecamatan'] . '%)';
            })->toArray();


            $judul_pie_kecamatan='Data Kunjungan Per Kecamatan';
            $subjudul_pie_kecamatan = '';
            $warnakec=( ['#ADFF2F']);
        // End Bar Kecamatan

        //Start Data kelurahan
            $sql_kel = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kelurahan.nm_kel') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kelurahan.nm_kel, 30) as kel'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kel = $sql_kel->pluck('total')->toArray();

            $totalSum_kel = array_sum($data_sql_kel);

            // Calculate the percentage for each kd_poli
            $percentages_kel = array_map(function ($value) use ($totalSum_kel) {
                return round(($value / $totalSum_kel) * 100, 2);
            }, $data_sql_kel);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kel = collect($sql_kel)->map(function ($item, $key) use ($percentages_kel) {
                return [
                    'nama_kel' => $item->kel,
                    'total_kel' => $item->total,
                    'percentage_kel' => $percentages_kel[$key],
                ];
            });

            $labels_kel = collect($result_kel)->map(function ($item) {
                return $item['nama_kel'] . ': '.$item['total_kel'] .'('. $item['percentage_kel'] . '% )';
            })->toArray();


            $judul_pie_sql_kel = 'Data Kunjungan kelurahan';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warna_sql_kelurahan = (['#4169E1']);
        //End Data kelurahan

        // Start Pie Chart Dokter 
            $sqldokter = DB::table('reg_periksa')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->join('pemeriksaan_ralan', 'pemeriksaan_ralan.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.kd_poli', 'IGDK') // Pastikan hanya menampilkan IGDK
            ->whereIn('reg_periksa.kd_dokter', ['D15', 'D17', 'dr.sofi']) // Pastikan kode dokter benar
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->where(function ($query) {
                $query->where('pemeriksaan_ralan.pemeriksaan', 'LIKE', '%hami%')
                      ->orWhere('pemeriksaan_ralan.penilaian', 'LIKE', '%hami%')
                      ->orWhere('pemeriksaan_ralan.keluhan', 'LIKE', '%hami%');
            })
            // ->where('reg_periksa.penilaian', 'LIKE', '%hamil%')
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select(DB::raw('LEFT(dokter.nm_dokter, 20) as nama'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
    
            $datadokter = $sqldokter->pluck('total')->toArray();

            // Calculate the total sum
            $totalSumdokter = array_sum($datadokter);
            
            // Calculate the percentage for each kd_poli
            $percentagesdokter = array_map(function ($value) use ($totalSumdokter) {
                return round(($value / $totalSumdokter) * 100, 2);
            }, $datadokter);
            
            // Combine kd_poli, total, and percentage into a new collection
            $resultdokter = collect($sqldokter)->map(function ($item, $key) use ($percentagesdokter) {
                return [
                    'nama_dokter' => $item->nama,
                    'total_dokter' => $item->total,
                    'percentage_dokter' => $percentagesdokter[$key],
                ];
            });

            $percentagesdokter = collect($resultdokter)->pluck('percentage_dokter')->toArray();
            $labels_dokter = collect($resultdokter)->map(function ($item) {
            return $item['nama_dokter'] . ': ' . $item['total_dokter'] . '(' . $item['percentage_dokter'] . '%)';
            })->toArray();

            $judul_pie_dokter='Data Kunjungan Ibu Hamil';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $datadokter=$percentagesdokter;
            $labeldokter=$labels_dokter;
            $warnadokter=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
        // End Pie Chart

        // Start Pie Chart Cara Bayar
        $sqlcarabayar = DB::table('reg_periksa')
        ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('kd_pj') // Menambahkan klausa groupBy
        ->select(DB::raw('kd_pj as cara_bayar'), DB::raw('count(*) as total'))
        ->get();
        $data_carabayar = $sqlcarabayar->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_carabayar = array_sum($data_carabayar);
        
        // Calculate the percentage for each kd_poli
        $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
            return round(($value / $totalSum_carabayar) * 100, 2);
        }, $data_carabayar);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_carabayar = collect($sqlcarabayar)->map(function ($item, $key) use ($percentages_carabayar) {
            return [
                'nama_carabayar' => $item->cara_bayar,
                'total_carabayar' => $item->total,
                'percentage_carabayar' => $percentages_carabayar[$key],
            ];
        });

        $percentages_carabayar = collect($result_carabayar)->pluck('percentage_carabayar')->toArray();
        $labels_carabayar = collect($result_carabayar)->map(function ($item) {
        return $item['nama_carabayar'] . ': ' . $item['total_carabayar'] . '(' . $item['percentage_carabayar'] . '%)';
        })->toArray();


        $judul_pie_cara_bayar='Data Kunjungan Cara Bayar';
        $subjudul_pie_cara_bayar = '';
        $datacara_bayar=$percentages_carabayar;
        $labelcara_bayar=$labels_carabayar; 
        $warnabayar=( [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
            '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
        ]);
    // End Pie Chart

    // Start Pie Chart Status
        $sqlstts = DB::table('reg_periksa')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('stts', 'Sudah')
                    ->orWhere('stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('kd_pj', 'UMU')
                    ->orWhere('kd_pj', 'BPJ');
            });
        })
        ->groupBy('stts') // Menambahkan klausa groupBy
        ->select('stts', DB::raw('count(*) as total'))
        ->get();
        $datastts = $sqlstts->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumstts = array_sum($datastts);
        
        // Calculate the percentage for each kd_poli
        $percentagesstts = array_map(function ($value) use ($totalSumstts) {
            return round(($value / $totalSumstts) * 100, 2);
        }, $datastts);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultstts = collect($sqlstts)->map(function ($item, $key) use ($percentagesstts) {
            return [
                'stts' => $item->stts,
                'total' => $item->total,
                'percentage' => $percentagesstts[$key],
            ];
        });

        $percentagesstts = collect($resultstts)->pluck('percentage')->toArray();
        $labelsstts = collect($resultstts)->map(function ($item) {
        return $item['stts'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();

            $judul_pie_stts='Data Kunjungan Per Status';
            $subjudul_pie_stts = '';
            $datastts=$percentagesstts;
            $labelstts=$labelsstts;
            $warnastts=(['#7FFF00','#DC143C']);
    // End Pie Chart
    
    // Start Bar Chart Pasien Lama Baru
        $sqlstts_daftar = DB::table('reg_periksa')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('stts', 'Sudah')
                    ->orWhere('stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('kd_pj', $cara_bayarpj);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('kd_pj', 'UMU')
                    ->orWhere('kd_pj', 'BPJ');
            });
        })
        ->groupBy('stts_daftar') // Menambahkan klausa groupBy
        ->select('stts_daftar', DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data_stts_daftar = $sqlstts_daftar->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_stts_daftar = array_sum($data_stts_daftar);
        
        // Calculate the percentage for each kd_poli
        $percentages_stts_daftar = array_map(function ($value) use ($totalSum_stts_daftar) {
            return round(($value / $totalSum_stts_daftar) * 100, 2);
        }, $data_stts_daftar);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_stts_daftar = collect($sqlstts_daftar)->map(function ($item, $key) use ($percentages_stts_daftar) {
            return [
                'nama_stts_daftar' => $item->stts_daftar,
                'total_stts_daftar' => $item->total,
                'percentage_stts_daftar' => $percentages_stts_daftar[$key],
            ];
        });

        $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
        return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
        })->toArray();

        $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';
        $subjudul_bar_stts_daftar = '';
        $warnastts_daftar = ['#3cb371','#ffa500'];
    // End Bar Chart Pasien Lama Baru
    
    // Start Bar Chart JK
        $sqljk = DB::table('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
        ->where('status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('pasien.jk') // Menambahkan klausa groupBy
        ->select('pasien.jk as jk', DB::raw('count(*) as total'))
        ->orderBy(DB::raw('count(*)'), 'desc')
        ->get();
        $data_jk = $sqljk->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_jk = array_sum($data_jk);
        
        // Calculate the percentage for each kd_poli
        $percentages_jk = array_map(function ($value) use ($totalSum_jk) {
            return round(($value / $totalSum_jk) * 100, 2);
        }, $data_jk);
        
        // Combine kd_poli, total, and percentage into a new collection
        $result_jk = collect($sqljk)->map(function ($item, $key) use ($percentages_jk) {
            return [
                'nama_jk' => $item->jk,
                'total_jk' => $item->total,
                'percentage_jk' => $percentages_jk[$key],
            ];
        });

        $labels_jk = collect($result_jk)->map(function ($item) {
        return $item['nama_jk'] . ' : ' . $item['total_jk'] . '(' . $item['percentage_jk'] . '%)';
        })->toArray();
        
        $judul_bar_jk = 'Data Kunjungan Jenkel';
        $subjudul_bar_jk = '';
        $warnajk = ['#ffa500','#3cb371'];
    // End Bar Chart JK

    // start pelayanan chart
        $pelayanan = DB::table(DB::raw('(
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_dr
            UNION
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_drpr
            UNION
            SELECT no_rawat, kd_jenis_prw
            FROM rawat_jl_pr 
        ) as r'))
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'r.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli','!=', 'HDL')
            ->where('reg_periksa.kd_poli','!=', 'LAB')
            ->where('reg_periksa.kd_poli','!=', 'RAD')
            ->where('reg_periksa.kd_poli','!=', 'IGDK')
            ->where('reg_periksa.kd_poli','!=', 'MCU')
            ->where('reg_periksa.kd_poli','!=', 'IRM')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->rightJoin('jns_perawatan as j', 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy('j.nm_perawatan')
            ->select([
                'j.nm_perawatan',
                DB::raw('COUNT(j.nm_perawatan) as total')
            ])
            ->orderby('total', 'desc')
            ->limit(10)
            ->get();

        $datapel = $pelayanan->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumpel = array_sum($datapel);
        
        // Calculate the percentage for each kd_poli
        $percentagespel = array_map(function ($value) use ($totalSumpel) {
            return round(($value / $totalSumpel) * 100, 2);
        }, $datapel);
        
        // Combine kd_poli, total, and percentage into a new collection
        $resultpel = collect($pelayanan)->map(function ($item, $key) use ($percentagespel) {
            return [
                'nama_pel' => $item->nm_perawatan,
                'total' => $item->total,
                'percentage' => $percentagespel[$key],
            ];
        });

        $labelspel = collect($resultpel)->map(function ($item) {
        return $item['nama_pel'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();


        $judul_pie_pel='Data Trend Pelayanan Poliklinik';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }

        $warnapel=( [
            '#008FFB'
        ]);
    // end pelayanan chart

    //start prosedur
        $sqlprosedur = DB::table('reg_periksa')
        ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
        ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('icd9.kode','icd9.deskripsi_pendek') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(icd9.deskripsi_pendek, 30) as nama'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sqlprosedur = $sqlprosedur->pluck('total')->toArray();

        $totalSumprosedur = array_sum($data_sqlprosedur);

        // Calculate the percentage for each kd_poli
        $percentagesprosedur = array_map(function ($value) use ($totalSumprosedur) {
            return round(($value / $totalSumprosedur) * 100, 2);
        }, $data_sqlprosedur);

        // Combine kd_poli, total, and percentage into a new collection
        $resultprosedur = collect($sqlprosedur)->map(function ($item, $key) use ($percentagesprosedur) {
            return [
                'namaprosedur' => $item->nama,
                'totalprosedur' => $item->total,
                'percentageprosedur' => $percentagesprosedur[$key],
            ];
        });

        $labelsprosedur = collect($resultprosedur)->map(function ($item) {
            return $item['namaprosedur'] . ': '.$item['totalprosedur'] .'('. $item['percentageprosedur'] . '% )';
        })->toArray();


        $judul_pie_sqlprosedur = 'Data Prosedur (ICD9)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqlprosedur = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sqlprosedur = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sqlprosedur = (['#0da168']);
    //end prosedur

    //start diagnosa
        $sqldiagnosa = DB::table('reg_periksa')
        ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
        ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
        ->where('reg_periksa.status_lanjut', 'Ralan')
        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
        }, function ($query) {
            return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        })
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('reg_periksa.kd_poli', $kdpoli);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('reg_periksa.stts', $status);
        }, function ($query) {
            return $query->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')
                    ->orWhere('reg_periksa.stts', 'Batal');
            });
        })
        ->when($kddokter, function ($query) use ($kddokter) {
            return $query->where('reg_periksa.kd_dokter', $kddokter);
        })
        ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
            return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
        })
        ->groupBy('penyakit.kd_penyakit','penyakit.nm_penyakit') // Menambahkan klausa groupBy
        ->select(DB::raw('LEFT(penyakit.nm_penyakit, 30) as nama'), DB::raw('count(*) as total'))
        ->orderBy('total', 'desc')
        ->limit(20)
        ->get();


        $data_sqldiagnosa = $sqldiagnosa->pluck('total')->toArray();

        $totalSumdiagnosa = array_sum($data_sqldiagnosa);

        // Calculate the percentage for each kd_poli
        $percentagesdiagnosa = array_map(function ($value) use ($totalSumdiagnosa) {
            return round(($value / $totalSumdiagnosa) * 100, 2);
        }, $data_sqldiagnosa);

        // Combine kd_poli, total, and percentage into a new collection
        $resultdiagnosa = collect($sqldiagnosa)->map(function ($item, $key) use ($percentagesdiagnosa) {
            return [
                'namadiagnosa' => $item->nama,
                'totaldiagnosa' => $item->total,
                'percentagediagnosa' => $percentagesdiagnosa[$key],
            ];
        });

        $labelsdiagnosa = collect($resultdiagnosa)->map(function ($item) {
            return $item['namadiagnosa'] . ': '.$item['totaldiagnosa'] .'('. $item['percentagediagnosa'] . '% )';
        })->toArray();


        $judul_pie_sqldiagnosa = 'Data diagnosa (ICD10)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
        } else {
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
        }
        $warna_sqldiagnosa = (['#9ea10d']);
    //end diagnosa
        

        //start return
        return view('rm.rajal.igdk', [
            // untuk mengirim data dalam form
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'kddokter' => $kddokter,
            'kd_pj' => $cara_bayarpj,
            'status' => $status,
            // end form
            'pilihan_dokter' => $pilihan_dokter,
            'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
            //kunjungan
            'bpjs' => $bpjs, 
            'umum' => $umum, 
            'labelstat' => $labelstat, 
            'judul_line' => $judul_line, 
            'subjudul_line' => $subjudul_line ,
            //poli
            'data' => $data, // Contoh: [10, 20, 30]
            'labels' => $labels, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_poli' => $judul_pie_poli, // Contoh: "Judul Chart"
            'subjudul_pie_poli' => $subjudul_pie_poli, // Contoh: "Subjudul Chart"
            'warnapoli' => $warnapoli ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //Perujuk
            'data_sql_rujuk_masuk' => $data_sql_rujuk_masuk, // Contoh: [10, 20, 30]
            'labels_rujuk_masuk' => $labels_rujuk_masuk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_rujuk_masuk' => $judul_pie_sql_rujuk_masuk, // Contoh: "Judul Chart"
            'subjudul_pie_sql_rujuk_masuk' => $subjudul_pie_sql_rujuk_masuk, // Contoh: "Subjudul Chart"
            'warnaperujuk' => $warnaperujuk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //kabupaten
            'data_sql_kab' => $data_sql_kab, // Contoh: [10, 20, 30]
            'labels_kab' => $labels_kab, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_kab' => $judul_pie_sql_kab, // Contoh: "Judul Chart"
            'subjudul_pie_sql_kab' => $subjudul_pie_sql_kab, // Contoh: "Subjudul Chart"
            'warna_sql_Kabupaten' => $warna_sql_Kabupaten ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //kecamatan
            'data_kecamatan' => $data_kecamatan, // Contoh: [10, 20, 30]
            'labels_kecamatan' => $labels_kecamatan, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_kecamatan' => $judul_pie_kecamatan, // Contoh: "Judul Chart"
            'subjudul_pie_kecamatan' => $subjudul_pie_kecamatan, // Contoh: "Subjudul Chart"
            'warnakec' => $warnakec ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //Kelurahan
            'data_sql_kel' => $data_sql_kel, // Contoh: [10, 20, 30]
            'labels_kel' => $labels_kel, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sql_kel' => $judul_pie_sql_kel, // Contoh: "Judul Chart"
            'subjudul_pie_sql_kel' => $subjudul_pie_sql_kel, // Contoh: "Subjudul Chart"
            'warna_sql_kelurahan' => $warna_sql_kelurahan ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //dokter
            'datadokter' => $datadokter, // Contoh: [10, 20, 30]
            'labeldokter' => $labeldokter, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_dokter' => $judul_pie_dokter, // Contoh: "Judul Chart"
            'subjudul_pie_dokter' => $subjudul_pie_dokter, // Contoh: "Subjudul Chart"
            'warnadokter' => $warnadokter ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //stts
            'datacara_bayar' => $datacara_bayar, // Contoh: [10, 20, 30]
            'labelcara_bayar' => $labelcara_bayar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_cara_bayar' => $judul_pie_cara_bayar, // Contoh: "Judul Chart"
            'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar, // Contoh: "Subjudul Chart"
            'warnabayar' => $warnabayar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //stts
            'datastts' => $datastts, // Contoh: [10, 20, 30]
            'labelsstts' => $labelsstts, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_stts' => $judul_pie_stts, // Contoh: "Judul Chart"
            'subjudul_pie_stts' => $subjudul_pie_stts, // Contoh: "Subjudul Chart"
            'warnastts' => $warnastts ,// Contoh: ["#FF4560", "#00E396", "#008FFB"] 
            //stts_daftar
            'data_stts_daftar' => $data_stts_daftar, // Contoh: [10, 20, 30]
            'labels_stts_daftar' => $labels_stts_daftar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_bar_stts_daftar' => $judul_bar_stts_daftar, // Contoh: "Judul Chart"
            'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar, // Contoh: "Subjudul Chart"
            'warnastts_daftar' => $warnastts_daftar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //JK
            'data_jk' => $data_jk, // Contoh: [10, 20, 30]
            'labels_jk' => $labels_jk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_bar_jk' => $judul_bar_jk, // Contoh: "Judul Chart"
            'subjudul_bar_jk' => $subjudul_bar_jk, // Contoh: "Subjudul Chart"
            'warnajk' => $warnajk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //pelayanan
            'datapel' => $datapel, 
            'labelspel' => $labelspel, 
            'judul_pie_pel' => $judul_pie_pel, 
            'subjudul_pie_pel' => $subjudul_pie_pel, 
            'warnapel' => $warnapel ,
            //prosedur
            'data_sqlprosedur' => $data_sqlprosedur, // Contoh: [10, 20, 30]
            'labelsprosedur' => $labelsprosedur, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sqlprosedur' => $judul_pie_sqlprosedur, // Contoh: "Judul Chart"
            'subjudul_pie_sqlprosedur' => $subjudul_pie_sqlprosedur, // Contoh: "Subjudul Chart"
            'warna_sqlprosedur' => $warna_sqlprosedur ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            //diagnosa
            'data_sqldiagnosa' => $data_sqldiagnosa, // Contoh: [10, 20, 30]
            'labelsdiagnosa' => $labelsdiagnosa, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
            'judul_pie_sqldiagnosa' => $judul_pie_sqldiagnosa, // Contoh: "Judul Chart"
            'subjudul_pie_sqldiagnosa' => $subjudul_pie_sqldiagnosa, // Contoh: "Subjudul Chart"
            'warna_sqldiagnosa' => $warna_sqldiagnosa ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
        ]);
    //end return
    }
    //End IGDK

    //start HDL
    public function hdl(Chart $chart,Request $request,$kd_poli = null)
    {   
        $tgl1 = $request->input('tgl1');
        $tgl2 = $request->input('tgl2');
        $kdpoli = $kd_poli ;
        $kddokter= $request->input('dokter');
        $cara_bayarpj = $request->input('cara_bayar');
        $status = $request->input('status');
        $tombol = $request->input('tombol');
        
    // pilihan dokter
        $pilihan_dokter = DB::table('dokter')
        ->join('jadwal', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
        ->when($kdpoli, function ($query) use ($kdpoli) {
            return $query->where('jadwal.kd_poli', $kdpoli);
        })
        ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
        ->select('dokter.kd_dokter','dokter.nm_dokter')
        ->get();
    // end pilihan dokter
    
    // start Pilihan Cara Bayar
        $pilihan_cara_bayar = DB::table('penjab')
        ->select('kd_pj','png_jawab')
        ->get();
    // end Pilihan Cara Bayar

        // Start Pie Chart Poli --Data Kunjungan Per Poli
            $poli = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('poliklinik.kd_poli','poliklinik.nm_poli') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(poliklinik.nm_poli, 20) as nama_poli'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data = $poli->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum = array_sum($data);
            
            // Calculate the percentage for each kd_poli
            $percentages = array_map(function ($value) use ($totalSum) {
                return round(($value / $totalSum) * 100, 2);
            }, $data);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result = collect($poli)->map(function ($item, $key) use ($percentages) {
                return [
                    'nama_poli' => $item->nama_poli,
                    'total' => $item->total,
                    'percentage' => $percentages[$key],
                ];
            });

            $percentages = collect($result)->pluck('percentage')->toArray();
            $labels = collect($result)->map(function ($item) {
            return $item['nama_poli'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();


            $judul_pie_poli='Data Kunjungan Per Poli';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $data=$percentages;
            $label=$labels;
            $warnapoli=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
            //$label=$poli ->pluck('nama_poli')->toArray();
        // End Pie Chart

        //Start perujuk
            $sql_rujuk_masuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('rujuk_masuk.perujuk') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(rujuk_masuk.perujuk, 30) as perujuk'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(30)
            ->get();

            $data_sql_rujuk_masuk = $sql_rujuk_masuk->pluck('total')->toArray();

            $totalSum_rujuk_masuk = array_sum($data_sql_rujuk_masuk);

            // Calculate the percentage for each kd_poli
            $percentages_rujuk_masuk = array_map(function ($value) use ($totalSum_rujuk_masuk) {
                return round(($value / $totalSum_rujuk_masuk) * 100, 2);
            }, $data_sql_rujuk_masuk);

            // Combine kd_poli, total, and percentage into a new collection
            $result_rujuk_masuk = collect($sql_rujuk_masuk)->map(function ($item, $key) use ($percentages_rujuk_masuk) {
                return [
                    'nama_rujuk_masuk' => $item->perujuk,
                    'total_rujuk_masuk' => $item->total,
                    'percentage_rujuk_masuk' => $percentages_rujuk_masuk[$key],
                ];
            });
            $percentages_rujuk_masuk = collect($result_rujuk_masuk)->pluck('percentage_rujuk_masuk')->toArray();
            $labels_rujuk_masuk = collect($result_rujuk_masuk)->map(function ($item) {
                return $item['nama_rujuk_masuk'] . ': '. $item['total_rujuk_masuk'] .'('. $item['percentage_rujuk_masuk'] . '% )';
            })->toArray();


            $warnaperujuk = ['#00FFFF','#3cb371'];
            $judul_pie_sql_rujuk_masuk = 'Data Perujuk Masuk';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
        //End perujuk

        // Start Line Chart
            $grafik_umum = $this->getChartData4('PJ2', $tgl1, $tgl2, $kdpoli, $kddokter, $status);
            $grafik_bpjs = $this->getChartData4('BPJ', $tgl1, $tgl2, $kdpoli, $kddokter, $status);

                // Sort data based on year and month
                $umumMonths = $grafik_umum->pluck('month')->unique();
                $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

                $grafik_umum = $grafik_umum->sortBy(['year', 'month']);
                $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month']);

                // Extract unique months for umum and bpjs
                $umumMonths = $grafik_umum->pluck('month')->unique();
                $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

                // Combine umum and bpjs months, get unique values, and sort them
                $allMonths = collect($umumMonths->merge($bpjsMonths)->unique()->sortBy(['year', 'month']));

                // Merge data based on year and month
                $mergedData = $allMonths->map(function ($month) use ($grafik_umum, $grafik_bpjs) {
                    $umumData = $grafik_umum->where('month', $month)->first();
                    $bpjsData = $grafik_bpjs->where('month', $month)->first();

                    return [
                        'year' => $umumData ? $umumData->year : $bpjsData->year,
                        'month' => $month,
                        'month_name' => $umumData ? $umumData->month_name : $bpjsData->month_name,
                        'umum_total' => $umumData ? $umumData->total : null,
                        'bpjs_total' => $bpjsData ? $bpjsData->total : null,
                    ];
                });

            // Merge data based on year and month
            $judul_line = 'Data Kunjungan Umum dan BPJS';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

                $umum = $mergedData->pluck('umum_total')->toArray();
                $bpjs = $mergedData->pluck('bpjs_total')->toArray();
                $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        //Start Data Kabupaten
            $sql_kab = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kabupaten.nm_kab') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kabupaten.nm_kab, 30) as kab'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kab = $sql_kab->pluck('total')->toArray();

            $totalSum_kab = array_sum($data_sql_kab);

            // Calculate the percentage for each kd_poli
            $percentages_kab = array_map(function ($value) use ($totalSum_kab) {
                return round(($value / $totalSum_kab) * 100, 2);
            }, $data_sql_kab);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kab = collect($sql_kab)->map(function ($item, $key) use ($percentages_kab) {
                return [
                    'nama_kab' => $item->kab,
                    'total_kab' => $item->total,
                    'percentage_kab' => $percentages_kab[$key],
                ];
            });
            $labels_kab = collect($result_kab)->map(function ($item) {
                return $item['nama_kab'] . ': ' .$item['total_kab']  .'('. $item['percentage_kab'] . '%)';
            })->toArray();


            $judul_pie_sql_kab = 'Data Kunjungan Per Kabupaten';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

            $warna_sql_Kabupaten = (['#FFD700']);
        //End Data Kabupaten

        // Start Bar Kecamatan
            $sqlkecamatan = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kecamatan.nm_kec') // Menambahkan klausa groupBy
            ->select(DB::raw('kecamatan.nm_kec as kecamatan'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();
            $data_kecamatan = $sqlkecamatan->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_kecamatan = array_sum($data_kecamatan);
            
            // Calculate the percentage for each kd_poli
            $percentages_kecamatan = array_map(function ($value) use ($totalSum_kecamatan) {
                return round(($value / $totalSum_kecamatan) * 100, 2);
            }, $data_kecamatan);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_kecamatan = collect($sqlkecamatan)->map(function ($item, $key) use ($percentages_kecamatan) {
                return [
                    'nama_kecamatan' => $item->kecamatan,
                    'total_kecamatan' => $item->total,
                    'percentage_kecamatan' => $percentages_kecamatan[$key],
                ];
            });

            $labels_kecamatan = collect($result_kecamatan)->map(function ($item) {
            return $item['nama_kecamatan'] . ': ' . $item['total_kecamatan'] . '(' . $item['percentage_kecamatan'] . '%)';
            })->toArray();


            $judul_pie_kecamatan='Data Kunjungan Per Kecamatan';
            $subjudul_pie_kecamatan = '';
            $warnakec=( ['#ADFF2F']);
        // End Bar Kecamatan

        //Start Data kelurahan
            $sql_kel = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kelurahan.nm_kel') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kelurahan.nm_kel, 30) as kel'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kel = $sql_kel->pluck('total')->toArray();

            $totalSum_kel = array_sum($data_sql_kel);

            // Calculate the percentage for each kd_poli
            $percentages_kel = array_map(function ($value) use ($totalSum_kel) {
                return round(($value / $totalSum_kel) * 100, 2);
            }, $data_sql_kel);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kel = collect($sql_kel)->map(function ($item, $key) use ($percentages_kel) {
                return [
                    'nama_kel' => $item->kel,
                    'total_kel' => $item->total,
                    'percentage_kel' => $percentages_kel[$key],
                ];
            });

            $labels_kel = collect($result_kel)->map(function ($item) {
                return $item['nama_kel'] . ': '.$item['total_kel'] .'('. $item['percentage_kel'] . '% )';
            })->toArray();


            $judul_pie_sql_kel = 'Data Kunjungan kelurahan';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warna_sql_kelurahan = (['#4169E1']);
        //End Data kelurahan

        // Start Pie Chart Dokter 
            $sqldokter = DB::table('reg_periksa')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.kd_pj', 'PJ2')
                        ->orWhere('reg_periksa.kd_pj', 'BPJ');
                });
            })
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select(DB::raw('LEFT(dokter.nm_dokter, 20) as nama'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $datadokter = $sqldokter->pluck('total')->toArray();

            // Calculate the total sum
            $totalSumdokter = array_sum($datadokter);
            
            // Calculate the percentage for each kd_poli
            $percentagesdokter = array_map(function ($value) use ($totalSumdokter) {
                return round(($value / $totalSumdokter) * 100, 2);
            }, $datadokter);
            
            // Combine kd_poli, total, and percentage into a new collection
            $resultdokter = collect($sqldokter)->map(function ($item, $key) use ($percentagesdokter) {
                return [
                    'nama_dokter' => $item->nama,
                    'total_dokter' => $item->total,
                    'percentage_dokter' => $percentagesdokter[$key],
                ];
            });

            $percentagesdokter = collect($resultdokter)->pluck('percentage_dokter')->toArray();
            $labels_dokter = collect($resultdokter)->map(function ($item) {
            return $item['nama_dokter'] . ': ' . $item['total_dokter'] . '(' . $item['percentage_dokter'] . '%)';
            })->toArray();

            $judul_pie_dokter='Data Kunjungan Per Dokter';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $datadokter=$percentagesdokter;
            $labeldokter=$labels_dokter;
            $warnadokter=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
        // End Pie Chart

            // Start Pie Chart Cara Bayar
            $sqlcarabayar = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kd_pj') // Menambahkan klausa groupBy
            ->select(DB::raw('kd_pj as cara_bayar'), DB::raw('count(*) as total'))
            ->get();
            $data_carabayar = $sqlcarabayar->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_carabayar = array_sum($data_carabayar);
            
            // Calculate the percentage for each kd_poli
            $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
                return round(($value / $totalSum_carabayar) * 100, 2);
            }, $data_carabayar);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_carabayar = collect($sqlcarabayar)->map(function ($item, $key) use ($percentages_carabayar) {
                return [
                    'nama_carabayar' => $item->cara_bayar,
                    'total_carabayar' => $item->total,
                    'percentage_carabayar' => $percentages_carabayar[$key],
                ];
            });

            $percentages_carabayar = collect($result_carabayar)->pluck('percentage_carabayar')->toArray();
            $labels_carabayar = collect($result_carabayar)->map(function ($item) {
            return $item['nama_carabayar'] . ': ' . $item['total_carabayar'] . '(' . $item['percentage_carabayar'] . '%)';
            })->toArray();


            $judul_pie_cara_bayar='Data Kunjungan Cara Bayar';
            $subjudul_pie_cara_bayar = '';
            $datacara_bayar=$percentages_carabayar;
            $labelcara_bayar=$labels_carabayar; 
            $warnabayar=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
        // End Pie Chart

        // Start Pie Chart Status
            $sqlstts = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('stts', 'Sudah')
                        ->orWhere('stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('kd_pj', 'PJ2')
                        ->orWhere('kd_pj', 'BPJ');
                });
            })
            ->groupBy('stts') // Menambahkan klausa groupBy
            ->select('stts', DB::raw('count(*) as total'))
            ->get();
            $datastts = $sqlstts->pluck('total')->toArray();

            // Calculate the total sum
            $totalSumstts = array_sum($datastts);
            
            // Calculate the percentage for each kd_poli
            $percentagesstts = array_map(function ($value) use ($totalSumstts) {
                return round(($value / $totalSumstts) * 100, 2);
            }, $datastts);
            
            // Combine kd_poli, total, and percentage into a new collection
            $resultstts = collect($sqlstts)->map(function ($item, $key) use ($percentagesstts) {
                return [
                    'stts' => $item->stts,
                    'total' => $item->total,
                    'percentage' => $percentagesstts[$key],
                ];
            });

            $percentagesstts = collect($resultstts)->pluck('percentage')->toArray();
            $labelsstts = collect($resultstts)->map(function ($item) {
            return $item['stts'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();

                $judul_pie_stts='Data Kunjungan Per Status';
                $subjudul_pie_stts = '';
                $datastts=$percentagesstts;
                $labelstts=$labelsstts;
                $warnastts=(['#7FFF00','#DC143C']);
        // End Pie Chart
        
        // Start Bar Chart Pasien Lama Baru
            $sqlstts_daftar = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('stts', 'Sudah')
                        ->orWhere('stts', 'Batal');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('kd_pj', 'PJ2')
                        ->orWhere('kd_pj', 'BPJ');
                });
            })
            ->groupBy('stts_daftar') // Menambahkan klausa groupBy
            ->select('stts_daftar', DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data_stts_daftar = $sqlstts_daftar->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_stts_daftar = array_sum($data_stts_daftar);
            
            // Calculate the percentage for each kd_poli
            $percentages_stts_daftar = array_map(function ($value) use ($totalSum_stts_daftar) {
                return round(($value / $totalSum_stts_daftar) * 100, 2);
            }, $data_stts_daftar);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_stts_daftar = collect($sqlstts_daftar)->map(function ($item, $key) use ($percentages_stts_daftar) {
                return [
                    'nama_stts_daftar' => $item->stts_daftar,
                    'total_stts_daftar' => $item->total,
                    'percentage_stts_daftar' => $percentages_stts_daftar[$key],
                ];
            });

            $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
            return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
            })->toArray();

            $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';
            $subjudul_bar_stts_daftar = '';
            $warnastts_daftar = ['#3cb371','#ffa500'];
        // End Bar Chart Pasien Lama Baru
        
        // Start Bar Chart JK
            $sqljk = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->where('status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('pasien.jk') // Menambahkan klausa groupBy
            ->select('pasien.jk as jk', DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data_jk = $sqljk->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_jk = array_sum($data_jk);
            
            // Calculate the percentage for each kd_poli
            $percentages_jk = array_map(function ($value) use ($totalSum_jk) {
                return round(($value / $totalSum_jk) * 100, 2);
            }, $data_jk);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_jk = collect($sqljk)->map(function ($item, $key) use ($percentages_jk) {
                return [
                    'nama_jk' => $item->jk,
                    'total_jk' => $item->total,
                    'percentage_jk' => $percentages_jk[$key],
                ];
            });

            $labels_jk = collect($result_jk)->map(function ($item) {
            return $item['nama_jk'] . ' : ' . $item['total_jk'] . '(' . $item['percentage_jk'] . '%)';
            })->toArray();
            
            $judul_bar_jk = 'Data Kunjungan Jenkel';
            $subjudul_bar_jk = '';
            $warnajk = ['#ffa500','#3cb371'];
        // End Bar Chart JK        

            //start return
            return view('rm.rajal.hemodialisa', [
                // untuk mengirim data dalam form
                'tgl1' => $tgl1,
                'tgl2' => $tgl2,
                'kddokter' => $kddokter,
                'kd_pj' => $cara_bayarpj,
                'status' => $status,
                // end form
                'pilihan_dokter' => $pilihan_dokter,
                'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
                //kunjungan
                'bpjs' => $bpjs, 
                'umum' => $umum, 
                'labelstat' => $labelstat, 
                'judul_line' => $judul_line, 
                'subjudul_line' => $subjudul_line ,
                //poli
                'data' => $data, // Contoh: [10, 20, 30]
                'labels' => $labels, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_poli' => $judul_pie_poli, // Contoh: "Judul Chart"
                'subjudul_pie_poli' => $subjudul_pie_poli, // Contoh: "Subjudul Chart"
                'warnapoli' => $warnapoli ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //Perujuk
                'data_sql_rujuk_masuk' => $data_sql_rujuk_masuk, // Contoh: [10, 20, 30]
                'labels_rujuk_masuk' => $labels_rujuk_masuk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_sql_rujuk_masuk' => $judul_pie_sql_rujuk_masuk, // Contoh: "Judul Chart"
                'subjudul_pie_sql_rujuk_masuk' => $subjudul_pie_sql_rujuk_masuk, // Contoh: "Subjudul Chart"
                'warnaperujuk' => $warnaperujuk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //kabupaten
                'data_sql_kab' => $data_sql_kab, // Contoh: [10, 20, 30]
                'labels_kab' => $labels_kab, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_sql_kab' => $judul_pie_sql_kab, // Contoh: "Judul Chart"
                'subjudul_pie_sql_kab' => $subjudul_pie_sql_kab, // Contoh: "Subjudul Chart"
                'warna_sql_Kabupaten' => $warna_sql_Kabupaten ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //kecamatan
                'data_kecamatan' => $data_kecamatan, // Contoh: [10, 20, 30]
                'labels_kecamatan' => $labels_kecamatan, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_kecamatan' => $judul_pie_kecamatan, // Contoh: "Judul Chart"
                'subjudul_pie_kecamatan' => $subjudul_pie_kecamatan, // Contoh: "Subjudul Chart"
                'warnakec' => $warnakec ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //Kelurahan
                'data_sql_kel' => $data_sql_kel, // Contoh: [10, 20, 30]
                'labels_kel' => $labels_kel, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_sql_kel' => $judul_pie_sql_kel, // Contoh: "Judul Chart"
                'subjudul_pie_sql_kel' => $subjudul_pie_sql_kel, // Contoh: "Subjudul Chart"
                'warna_sql_kelurahan' => $warna_sql_kelurahan ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //dokter
                'datadokter' => $datadokter, // Contoh: [10, 20, 30]
                'labeldokter' => $labeldokter, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_dokter' => $judul_pie_dokter, // Contoh: "Judul Chart"
                'subjudul_pie_dokter' => $subjudul_pie_dokter, // Contoh: "Subjudul Chart"
                'warnadokter' => $warnadokter ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //stts
                'datacara_bayar' => $datacara_bayar, // Contoh: [10, 20, 30]
                'labelcara_bayar' => $labelcara_bayar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_cara_bayar' => $judul_pie_cara_bayar, // Contoh: "Judul Chart"
                'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar, // Contoh: "Subjudul Chart"
                'warnabayar' => $warnabayar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //stts
                'datastts' => $datastts, // Contoh: [10, 20, 30]
                'labelsstts' => $labelsstts, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_pie_stts' => $judul_pie_stts, // Contoh: "Judul Chart"
                'subjudul_pie_stts' => $subjudul_pie_stts, // Contoh: "Subjudul Chart"
                'warnastts' => $warnastts ,// Contoh: ["#FF4560", "#00E396", "#008FFB"] 
                //stts_daftar
                'data_stts_daftar' => $data_stts_daftar, // Contoh: [10, 20, 30]
                'labels_stts_daftar' => $labels_stts_daftar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_bar_stts_daftar' => $judul_bar_stts_daftar, // Contoh: "Judul Chart"
                'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar, // Contoh: "Subjudul Chart"
                'warnastts_daftar' => $warnastts_daftar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                //JK
                'data_jk' => $data_jk, // Contoh: [10, 20, 30]
                'labels_jk' => $labels_jk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                'judul_bar_jk' => $judul_bar_jk, // Contoh: "Judul Chart"
                'subjudul_bar_jk' => $subjudul_bar_jk, // Contoh: "Subjudul Chart"
                'warnajk' => $warnajk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
            ]);
        //end return
        }
        //End HDL

    //start seluruh poli penunjang
    public function lab(Chart $chart,Request $request, $kd_poli =  null)
    {   
            $tgl1 = $request->input('tgl1');
            $tgl2 = $request->input('tgl2');
            $kdpoli = $kd_poli;
            $kddokter= $request->input('dokter');
            $cara_bayarpj = $request->input('cara_bayar');
            $status = $request->input('status');
            $tombol = $request->input('tombol');

        // pilihan dokter
            $pilihan_dokter = DB::table('dokter')
            ->join('jadwal', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('jadwal.kd_poli', $kdpoli);
            })
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select('dokter.kd_dokter','dokter.nm_dokter')
            ->get();
        // end pilihan dokter
        
        // start Pilihan Cara Bayar
            $pilihan_cara_bayar = DB::table('penjab')
            ->select('kd_pj','png_jawab')
            ->get();
        // end Pilihan Cara Bayar

        // Start Line Chart
            $grafik_umum = $this->getChartData6('PJ2', $tgl1, $tgl2, $kdpoli, $kddokter, $status);
            $grafik_bpjs = $this->getChartData6('BPJ', $tgl1, $tgl2, $kdpoli, $kddokter, $status);

            // Sort data based on year and month
            $umumMonths = $grafik_umum->pluck('month')->unique();
            $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

            $grafik_umum = $grafik_umum->sortBy(['year', 'month']);
            $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month']);

            // Extract unique months for umum and bpjs
            $umumMonths = $grafik_umum->pluck('month')->unique();
            $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

            // Combine umum and bpjs months, get unique values, and sort them
            $allMonths = collect($umumMonths->merge($bpjsMonths)->unique()->sortBy(['year', 'month']));

            // Merge data based on year and month
            $mergedData = $allMonths->map(function ($month) use ($grafik_umum, $grafik_bpjs) {
                $umumData = $grafik_umum->where('month', $month)->first();
                $bpjsData = $grafik_bpjs->where('month', $month)->first();

                return [
                    'year' => $umumData ? $umumData->year : $bpjsData->year,
                    'month' => $month,
                    'month_name' => $umumData ? $umumData->month_name : $bpjsData->month_name,
                    'umum_total' => $umumData ? $umumData->total : null,
                        'bpjs_total' => $bpjsData ? $bpjsData->total : null,
                    ];
                });

                // Merge data based on year and month
            $judul_line = 'Data Kunjungan Umum dan BPJS';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

                $umum = $mergedData->pluck('umum_total')->toArray();
                $bpjs = $mergedData->pluck('bpjs_total')->toArray();
                $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        // Start Pie Chart Poli
            $poli = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('poliklinik.kd_poli','poliklinik.nm_poli') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(poliklinik.nm_poli, 20) as nama_poli'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data = $poli->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum = array_sum($data);
            
            // Calculate the percentage for each kd_poli
            $percentages = array_map(function ($value) use ($totalSum) {
                return round(($value / $totalSum) * 100, 2);
            }, $data);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result = collect($poli)->map(function ($item, $key) use ($percentages) {
                return [
                    'nama_poli' => $item->nama_poli,
                    'total' => $item->total,
                    'percentage' => $percentages[$key],
                ];
            });

            $labels = collect($result)->map(function ($item) {
            return $item['nama_poli'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();


            $judul_pie_poli='Data Kunjungan Per Poli';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warnapoli=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
            //$label=$poli ->pluck('nama_poli')->toArray();
        // End Pie Chart
        
        // Start Pie Chart Dokter 
            $sqldokter = DB::table('reg_periksa')
                ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
                ->where('reg_periksa.status_lanjut', 'Ralan')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
                }, function ($query) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
                })
                ->when($kdpoli, function ($query) use ($kdpoli) {
                    return $query->where('reg_periksa.kd_poli', $kdpoli);
                })
                ->when($kddokter, function ($query) use ($kddokter) {
                    return $query->where('reg_periksa.kd_dokter', $kddokter);
                })
                ->when($status, function ($query) use ($status) {
                    return $query->where('reg_periksa.stts', $status);
                })
                ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                    return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
                }, function ($query) {
                    return $query->where(function ($query) {
                        $query->where('reg_periksa.kd_pj', 'UMU')
                            ->orWhere('reg_periksa.kd_pj', 'BPJ');
                    });
                })
                ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
                ->select(DB::raw('LEFT(dokter.nm_dokter, 20) as nama'), DB::raw('count(*) as total'))
                ->orderBy(DB::raw('count(*)'), 'desc')
                ->get();
                $datadokter = $sqldokter->pluck('total')->toArray();

                // Calculate the total sum
                $totalSumdokter = array_sum($datadokter);
                
                // Calculate the percentage for each kd_poli
                $percentagesdokter = array_map(function ($value) use ($totalSumdokter) {
                    return round(($value / $totalSumdokter) * 100, 2);
                }, $datadokter);
                
                // Combine kd_poli, total, and percentage into a new collection
                $resultdokter = collect($sqldokter)->map(function ($item, $key) use ($percentagesdokter) {
                    return [
                        'nama_dokter' => $item->nama,
                        'total_dokter' => $item->total,
                        'percentage_dokter' => $percentagesdokter[$key],
                    ];
                });
        
                $percentagesdokter = collect($resultdokter)->pluck('percentage_dokter')->toArray();
                $labels_dokter = collect($resultdokter)->map(function ($item) {
                return $item['nama_dokter'] . ': ' . $item['total_dokter'] . '(' . $item['percentage_dokter'] . '%)';
                })->toArray();
        
                $judul_pie_dokter='Data Kunjungan Per Dokter';
                if (!empty($tgl1) && !empty($tgl2)) {
                    $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
                } else {
                    $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
                }
                $datadokter=$percentagesdokter;
                $labeldokter=$labels_dokter;
                $warnadokter=( [
                    '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                    '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
                ]);
        // End Pie Chart

        // Start Pie Chart Cara Bayar
            $sqlcarabayar = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kd_pj') // Menambahkan klausa groupBy
            ->select(DB::raw('kd_pj as cara_bayar'), DB::raw('count(*) as total'))
            ->get();
            $data_carabayar = $sqlcarabayar->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_carabayar = array_sum($data_carabayar);
            
            // Calculate the percentage for each kd_poli
            $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
                return round(($value / $totalSum_carabayar) * 100, 2);
            }, $data_carabayar);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_carabayar = collect($sqlcarabayar)->map(function ($item, $key) use ($percentages_carabayar) {
                return [
                    'nama_carabayar' => $item->cara_bayar,
                    'total_carabayar' => $item->total,
                    'percentage_carabayar' => $percentages_carabayar[$key],
                ];
            });

            $percentages_carabayar = collect($result_carabayar)->pluck('percentage_carabayar')->toArray();
            $labels_carabayar = collect($result_carabayar)->map(function ($item) {
            return $item['nama_carabayar'] . ': ' . $item['total_carabayar'] . '(' . $item['percentage_carabayar'] . '%)';
            })->toArray();


            $judul_pie_cara_bayar='Data Kunjungan Cara Bayar';
            $subjudul_pie_cara_bayar = '';
            $datacara_bayar=$percentages_carabayar;
            $labelcara_bayar=$labels_carabayar; 
            $warnabayar=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
        // End Pie Chart

        // Start Pie Chart Status
            $sqlstts = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('kd_pj', 'UMU')
                        ->orWhere('kd_pj', 'BPJ');
                });
            })
            ->groupBy('stts') // Menambahkan klausa groupBy
            ->select('stts', DB::raw('count(*) as total'))
            ->get();
            $datastts = $sqlstts->pluck('total')->toArray();

            // Calculate the total sum
            $totalSumstts = array_sum($datastts);
            
            // Calculate the percentage for each kd_poli
            $percentagesstts = array_map(function ($value) use ($totalSumstts) {
                return round(($value / $totalSumstts) * 100, 2);
            }, $datastts);
            
            // Combine kd_poli, total, and percentage into a new collection
            $resultstts = collect($sqlstts)->map(function ($item, $key) use ($percentagesstts) {
                return [
                    'stts' => $item->stts,
                    'total' => $item->total,
                    'percentage' => $percentagesstts[$key],
                ];
            });

            $percentagesstts = collect($resultstts)->pluck('percentage')->toArray();
            $labelsstts = collect($resultstts)->map(function ($item) {
            return $item['stts'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();

                $judul_pie_stts='Data Kunjungan Per Status';
                $subjudul_pie_stts = '';
                $datastts=$percentagesstts;
                $labelstts=$labelsstts;
                $warnastts=(['#696969','#7FFF00','#DC143C']);
        // End Pie Chart
        
        // Start Bar Chart Pasien Lama Baru
            $sqlstts_daftar = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('kd_pj', 'UMU')
                        ->orWhere('kd_pj', 'BPJ');
                });
            })
            ->groupBy('stts_daftar') // Menambahkan klausa groupBy
            ->select('stts_daftar', DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data_stts_daftar = $sqlstts_daftar->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_stts_daftar = array_sum($data_stts_daftar);
            
            // Calculate the percentage for each kd_poli
            $percentages_stts_daftar = array_map(function ($value) use ($totalSum_stts_daftar) {
                return round(($value / $totalSum_stts_daftar) * 100, 2);
            }, $data_stts_daftar);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_stts_daftar = collect($sqlstts_daftar)->map(function ($item, $key) use ($percentages_stts_daftar) {
                return [
                    'nama_stts_daftar' => $item->stts_daftar,
                    'total_stts_daftar' => $item->total,
                    'percentage_stts_daftar' => $percentages_stts_daftar[$key],
                ];
            });

            $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
            return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
            })->toArray();

            $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';
            $subjudul_bar_stts_daftar = '';
            $warnastts_daftar = ['#3cb371','#ffa500'];

        // End Bar Chart Pasien Lama Baru
        
        // Start Bar Chart JK
            $sqljk = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->where('status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('pasien.jk') // Menambahkan klausa groupBy
            ->select('pasien.jk as jk', DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data_jk = $sqljk->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_jk = array_sum($data_jk);
            
            // Calculate the percentage for each kd_poli
            $percentages_jk = array_map(function ($value) use ($totalSum_jk) {
                return round(($value / $totalSum_jk) * 100, 2);
            }, $data_jk);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_jk = collect($sqljk)->map(function ($item, $key) use ($percentages_jk) {
                return [
                    'nama_jk' => $item->jk,
                    'total_jk' => $item->total,
                    'percentage_jk' => $percentages_jk[$key],
                ];
            });

            $labels_jk = collect($result_jk)->map(function ($item) {
            return $item['nama_jk'] . ' : ' . $item['total_jk'] . '(' . $item['percentage_jk'] . '%)';
            })->toArray();
            

            $judul_bar_jk = 'Data Kunjungan Jenkel';
            $subjudul_bar_jk = '';
            $warnajk = ['#ffa500','#3cb371'];
        // End Bar Chart JK

        // Start Data Kunjungan Pasien
            $datapasien= DB::table('reg_periksa')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->select('reg_periksa.no_rawat','reg_periksa.stts','reg_periksa.kd_pj','pasien.nm_pasien','poliklinik.nm_poli','dokter.nm_dokter')
            ->get();
        // End Data Kunjungan Pasien
        
        //Start perujuk
            $sql_rujuk_masuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('rujuk_masuk.perujuk') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(rujuk_masuk.perujuk, 30) as perujuk'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(30)
            ->get();

            $data_sql_rujuk_masuk = $sql_rujuk_masuk->pluck('total')->toArray();

            $totalSum_rujuk_masuk = array_sum($data_sql_rujuk_masuk);

            // Calculate the percentage for each kd_poli
            $percentages_rujuk_masuk = array_map(function ($value) use ($totalSum_rujuk_masuk) {
                return round(($value / $totalSum_rujuk_masuk) * 100, 2);
            }, $data_sql_rujuk_masuk);

            // Combine kd_poli, total, and percentage into a new collection
            $result_rujuk_masuk = collect($sql_rujuk_masuk)->map(function ($item, $key) use ($percentages_rujuk_masuk) {
                return [
                    'nama_rujuk_masuk' => $item->perujuk,
                    'total_rujuk_masuk' => $item->total,
                    'percentage_rujuk_masuk' => $percentages_rujuk_masuk[$key],
                ];
            });
            $percentages_rujuk_masuk = collect($result_rujuk_masuk)->pluck('percentage_rujuk_masuk')->toArray();
            $labels_rujuk_masuk = collect($result_rujuk_masuk)->map(function ($item) {
                return $item['nama_rujuk_masuk'] . ': '. $item['total_rujuk_masuk'] .'('. $item['percentage_rujuk_masuk'] . '% )';
            })->toArray();

            $data_sql_rujuk_masuk = $percentages_rujuk_masuk;
            $labels_sql_rujuk_masuk = $labels_rujuk_masuk;
            $warnaperujuk = ['#00FFFF','#3cb371'];
            $judul_pie_sql_rujuk_masuk = 'Data Perujuk Masuk';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
        //End perujuk
        
        //Start Data Kabupaten
            $sql_kab = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kabupaten.nm_kab') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kabupaten.nm_kab, 30) as kab'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kab = $sql_kab->pluck('total')->toArray();

            $totalSum_kab = array_sum($data_sql_kab);

            // Calculate the percentage for each kd_poli
            $percentages_kab = array_map(function ($value) use ($totalSum_kab) {
                return round(($value / $totalSum_kab) * 100, 2);
            }, $data_sql_kab);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kab = collect($sql_kab)->map(function ($item, $key) use ($percentages_kab) {
                return [
                    'nama_kab' => $item->kab,
                    'total_kab' => $item->total,
                    'percentage_kab' => $percentages_kab[$key],
                ];
            });
            $labels_kab = collect($result_kab)->map(function ($item) {
                return $item['nama_kab'] . ': ' .$item['total_kab']  .'('. $item['percentage_kab'] . '%)';
            })->toArray();


            $judul_pie_sql_kab = 'Data Kunjungan Per Kabupaten';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

            $warna_sql_Kabupaten = (['#FFD700']);
        //End Data Kabupaten

        // Start Bar Kecamatan
            $sqlkecamatan = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kecamatan.nm_kec') // Menambahkan klausa groupBy
            ->select(DB::raw('kecamatan.nm_kec as kecamatan'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();
            $data_kecamatan = $sqlkecamatan->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_kecamatan = array_sum($data_kecamatan);
            
            // Calculate the percentage for each kd_poli
            $percentages_kecamatan = array_map(function ($value) use ($totalSum_kecamatan) {
                return round(($value / $totalSum_kecamatan) * 100, 2);
            }, $data_kecamatan);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_kecamatan = collect($sqlkecamatan)->map(function ($item, $key) use ($percentages_kecamatan) {
                return [
                    'nama_kecamatan' => $item->kecamatan,
                    'total_kecamatan' => $item->total,
                    'percentage_kecamatan' => $percentages_kecamatan[$key],
                ];
            });

            $percentages_kecamatan = collect($result_kecamatan)->pluck('percentage_kecamatan')->toArray();
            $labels_kecamatan = collect($result_kecamatan)->map(function ($item) {
            return $item['nama_kecamatan'] . ': ' . $item['total_kecamatan'] . '(' . $item['percentage_kecamatan'] . '%)';
            })->toArray();


            $judul_pie_kecamatan='Data Kunjungan Per Kecamatan';
            $subjudul_pie_kecamatan = '';
            $warnakec=( ['#ADFF2F']);
        // End Bar Kecamatan

        //Start Data kelurahan
            $sql_kel = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kelurahan.nm_kel') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kelurahan.nm_kel, 30) as kel'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kel = $sql_kel->pluck('total')->toArray();

            $totalSum_kel = array_sum($data_sql_kel);

            // Calculate the percentage for each kd_poli
            $percentages_kel = array_map(function ($value) use ($totalSum_kel) {
                return round(($value / $totalSum_kel) * 100, 2);
            }, $data_sql_kel);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kel = collect($sql_kel)->map(function ($item, $key) use ($percentages_kel) {
                return [
                    'nama_kel' => $item->kel,
                    'total_kel' => $item->total,
                    'percentage_kel' => $percentages_kel[$key],
                ];
            });

            $labels_kel = collect($result_kel)->map(function ($item) {
                return $item['nama_kel'] . ': '.$item['total_kel'] .'('. $item['percentage_kel'] . '% )';
            })->toArray();


            $judul_pie_sql_kel = 'Data Kunjungan kelurahan';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warna_sql_kelurahan = (['#4169E1']);
        //End Data kelurahan
        
        // start trend pelayanan
            $tableName1 = ($kdpoli == 'LAB') ? 'periksa_lab as r' : 'periksa_radiologi as r';
            $tableName2 = ($kdpoli == 'LAB') ? 'jns_perawatan_lab as j' : 'jns_perawatan_radiologi as j';
            $pelayanan = DB::table('reg_periksa')
            ->join($tableName1, 'r.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->rightJoin($tableName2, 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy( 'j.nm_perawatan')
                ->select([
                    'j.nm_perawatan',
                    DB::raw('COUNT(r.kd_jenis_prw) as total')
                ])
                ->limit(20)
                ->orderBy('total', 'desc')
                ->get();

                $datapel = $pelayanan->pluck('total')->toArray();

                // Calculate the total sum
                $totalSumpel = array_sum($datapel);
                
                // Calculate the percentage for each kd_poli
                $percentagespel = array_map(function ($value) use ($totalSumpel) {
                    return round(($value / $totalSumpel) * 100, 2);
                }, $datapel);
                
                // Combine kd_poli, total, and percentage into a new collection
                $resultpel = collect($pelayanan)->map(function ($item, $key) use ($percentagespel) {
                    return [
                        'nama_pel' => $item->nm_perawatan,
                        'total' => $item->total,
                        'percentage' => $percentagespel[$key],
                    ];
                });

                $labelspel = collect($resultpel)->map(function ($item) {
                return $item['nama_pel'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
                })->toArray();


                $judul_pie_pel='Data Trend Pelayanan Poliklinik';
                if (!empty($tgl1) && !empty($tgl2)) {
                    $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
                } else {
                    $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
                }

                $warnapel=( [
                    '#008FFB'
                ]);
        // end trend pelayanan

        //start return
                return view('rm.rajal.lab', [
                    // untuk mengirim data dalam form
                    'tgl1' => $tgl1,
                    'tgl2' => $tgl2,
                    'kddokter' => $kddokter,
                    'kd_pj' => $cara_bayarpj,
                    'status' => $status,
                    // end form
            
                    //pelayanan2
                        'datapel' => $datapel, 
                        'labelspel' => $labelspel, 
                        'judul_pie_pel' => $judul_pie_pel, 
                        'subjudul_pie_pel' => $subjudul_pie_pel, 
                        'warnapel' => $warnapel ,
                    //kunjungan
                        'bpjs' => $bpjs, 
                        'umum' => $umum, 
                        'labelstat' => $labelstat, 
                        'judul_line' => $judul_line, 
                        'subjudul_line' => $subjudul_line ,
                    //dokter
                        'datadokter' => $datadokter, // Contoh: [10, 20, 30]
                        'labeldokter' => $labeldokter, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_dokter' => $judul_pie_dokter, // Contoh: "Judul Chart"
                        'subjudul_pie_dokter' => $subjudul_pie_dokter, // Contoh: "Subjudul Chart"
                        'warnadokter' => $warnadokter ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //cara bayar
                        'datacara_bayar' => $datacara_bayar, // Contoh: [10, 20, 30]
                        'labelcara_bayar' => $labelcara_bayar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_cara_bayar' => $judul_pie_cara_bayar, // Contoh: "Judul Chart"
                        'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar, // Contoh: "Subjudul Chart"
                        'warnabayar' => $warnabayar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //stts
                        'datastts' => $datastts, // Contoh: [10, 20, 30]
                        'labelsstts' => $labelsstts, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_stts' => $judul_pie_stts, // Contoh: "Judul Chart"
                        'subjudul_pie_stts' => $subjudul_pie_stts, // Contoh: "Subjudul Chart"
                        'warnastts' => $warnastts ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //stts_daftar
                        'data_stts_daftar' => $data_stts_daftar, // Contoh: [10, 20, 30]
                        'labels_stts_daftar' => $labels_stts_daftar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_bar_stts_daftar' => $judul_bar_stts_daftar, // Contoh: "Judul Chart"
                        'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar, // Contoh: "Subjudul Chart"
                        'warnastts_daftar' => $warnastts_daftar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //JK
                        'data_jk' => $data_jk, // Contoh: [10, 20, 30]
                        'labels_jk' => $labels_jk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_bar_jk' => $judul_bar_jk, // Contoh: "Judul Chart"
                        'subjudul_bar_jk' => $subjudul_bar_jk, // Contoh: "Subjudul Chart"
                        'warnajk' => $warnajk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //Perujuk
                        'data_sql_rujuk_masuk' => $data_sql_rujuk_masuk, // Contoh: [10, 20, 30]
                        'labels_rujuk_masuk' => $labels_rujuk_masuk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_sql_rujuk_masuk' => $judul_pie_sql_rujuk_masuk, // Contoh: "Judul Chart"
                        'subjudul_pie_sql_rujuk_masuk' => $subjudul_pie_sql_rujuk_masuk, // Contoh: "Subjudul Chart"
                        'warnaperujuk' => $warnaperujuk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //poli
                        'data' => $data, // Contoh: [10, 20, 30]
                        'labels' => $labels, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_poli' => $judul_pie_poli, // Contoh: "Judul Chart"
                        'subjudul_pie_poli' => $subjudul_pie_poli, // Contoh: "Subjudul Chart"
                        'warnapoli' => $warnapoli ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //kabupaten
                        'data_sql_kab' => $data_sql_kab, // Contoh: [10, 20, 30]
                        'labels_kab' => $labels_kab, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_sql_kab' => $judul_pie_sql_kab, // Contoh: "Judul Chart"
                        'subjudul_pie_sql_kab' => $subjudul_pie_sql_kab, // Contoh: "Subjudul Chart"
                        'warna_sql_Kabupaten' => $warna_sql_Kabupaten ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //kecamatan
                        'data_kecamatan' => $data_kecamatan, // Contoh: [10, 20, 30]
                        'labels_kecamatan' => $labels_kecamatan, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_kecamatan' => $judul_pie_kecamatan, // Contoh: "Judul Chart"
                        'subjudul_pie_kecamatan' => $subjudul_pie_kecamatan, // Contoh: "Subjudul Chart"
                        'warnakec' => $warnakec ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //Kelurahan
                        'data_sql_kel' => $data_sql_kel, // Contoh: [10, 20, 30]
                        'labels_kel' => $labels_kel, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_sql_kel' => $judul_pie_sql_kel, // Contoh: "Judul Chart"
                        'subjudul_pie_sql_kel' => $subjudul_pie_sql_kel, // Contoh: "Subjudul Chart"
                        'warna_sql_kelurahan' => $warna_sql_kelurahan ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    'pilihan_dokter' => $pilihan_dokter,
                    'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
                    'pelayanan' => $pelayanan,
                ]);
            } 
        // end return  
    //end seluruh poli penunjang 

    //start seluruh poli penunjang
    public function radiologi(Chart $chart,Request $request, $kd_poli = null)
    {   
            $tgl1 = $request->input('tgl1');
            $tgl2 = $request->input('tgl2');
            $kdpoli = $kd_poli;
            $kddokter= $request->input('dokter');
            $cara_bayarpj = $request->input('cara_bayar');
            $status = $request->input('status');
            $tombol = $request->input('tombol');

        // pilihan dokter
            $pilihan_dokter = DB::table('dokter')
            ->join('jadwal', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('jadwal.kd_poli', $kdpoli);
            })
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select('dokter.kd_dokter','dokter.nm_dokter')
            ->get();
        // end pilihan dokter
        
        // start Pilihan Cara Bayar
            $pilihan_cara_bayar = DB::table('penjab')
            ->select('kd_pj','png_jawab')
            ->get();
        // end Pilihan Cara Bayar

        // Start Line Chart
            $grafik_umum = $this->getChartData7('PJ2', $tgl1, $tgl2, $kdpoli, $kddokter, $status);
            $grafik_bpjs = $this->getChartData7('BPJ', $tgl1, $tgl2, $kdpoli, $kddokter, $status);

            // Sort data based on year and month
            $umumMonths = $grafik_umum->pluck('month')->unique();
            $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

            $grafik_umum = $grafik_umum->sortBy(['year', 'month']);
            $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month']);

            // Extract unique months for umum and bpjs
            $umumMonths = $grafik_umum->pluck('month')->unique();
            $bpjsMonths = $grafik_bpjs->pluck('month')->unique();

            // Combine umum and bpjs months, get unique values, and sort them
            $allMonths = collect($umumMonths->merge($bpjsMonths)->unique()->sortBy(['year', 'month']));

            // Merge data based on year and month
            $mergedData = $allMonths->map(function ($month) use ($grafik_umum, $grafik_bpjs) {
                $umumData = $grafik_umum->where('month', $month)->first();
                $bpjsData = $grafik_bpjs->where('month', $month)->first();

                return [
                    'year' => $umumData ? $umumData->year : $bpjsData->year,
                    'month' => $month,
                    'month_name' => $umumData ? $umumData->month_name : $bpjsData->month_name,
                    'umum_total' => $umumData ? $umumData->total : null,
                        'bpjs_total' => $bpjsData ? $bpjsData->total : null,
                    ];
                });

                // Merge data based on year and month
            $judul_line = 'Data Kunjungan Umum dan BPJS';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_line = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

                $umum = $mergedData->pluck('umum_total')->toArray();
                $bpjs = $mergedData->pluck('bpjs_total')->toArray();
                $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        // Start Pie Chart Poli
            $poli = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'RAD') // Pastikan hanya menampilkan RAD 
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('poliklinik.kd_poli','poliklinik.nm_poli') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(poliklinik.nm_poli, 20) as nama_poli'), DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data = $poli->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum = array_sum($data);
            
            // Calculate the percentage for each kd_poli
            $percentages = array_map(function ($value) use ($totalSum) {
                return round(($value / $totalSum) * 100, 2);
            }, $data);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result = collect($poli)->map(function ($item, $key) use ($percentages) {
                return [
                    'nama_poli' => $item->nama_poli,
                    'total' => $item->total,
                    'percentage' => $percentages[$key],
                ];
            });

            $labels = collect($result)->map(function ($item) {
            return $item['nama_poli'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();


            $judul_pie_poli='Data Kunjungan Per Poli';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_poli = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warnapoli=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
            //$label=$poli ->pluck('nama_poli')->toArray();
        // End Pie Chart
        
        // Start Pie Chart Dokter 
            $sqldokter = DB::table('reg_periksa')
                ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
                ->where('reg_periksa.status_lanjut', 'Ralan')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
                }, function ($query) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
                })
                ->when($kdpoli, function ($query) use ($kdpoli) {
                    return $query->where('reg_periksa.kd_poli', $kdpoli);
                })
                ->when($kddokter, function ($query) use ($kddokter) {
                    return $query->where('reg_periksa.kd_dokter', $kddokter);
                })
                ->when($status, function ($query) use ($status) {
                    return $query->where('reg_periksa.stts', $status);
                })
                ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                    return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
                }, function ($query) {
                    return $query->where(function ($query) {
                        $query->where('reg_periksa.kd_pj', 'UMU')
                            ->orWhere('reg_periksa.kd_pj', 'BPJ');
                    });
                })
                ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
                ->select(DB::raw('LEFT(dokter.nm_dokter, 20) as nama'), DB::raw('count(*) as total'))
                ->orderBy(DB::raw('count(*)'), 'desc')
                ->get();
                $datadokter = $sqldokter->pluck('total')->toArray();

                // Calculate the total sum
                $totalSumdokter = array_sum($datadokter);
                
                // Calculate the percentage for each kd_poli
                $percentagesdokter = array_map(function ($value) use ($totalSumdokter) {
                    return round(($value / $totalSumdokter) * 100, 2);
                }, $datadokter);
                
                // Combine kd_poli, total, and percentage into a new collection
                $resultdokter = collect($sqldokter)->map(function ($item, $key) use ($percentagesdokter) {
                    return [
                        'nama_dokter' => $item->nama,
                        'total_dokter' => $item->total,
                        'percentage_dokter' => $percentagesdokter[$key],
                    ];
                });
        
                $percentagesdokter = collect($resultdokter)->pluck('percentage_dokter')->toArray();
                $labels_dokter = collect($resultdokter)->map(function ($item) {
                return $item['nama_dokter'] . ': ' . $item['total_dokter'] . '(' . $item['percentage_dokter'] . '%)';
                })->toArray();
        
                $judul_pie_dokter='Data Kunjungan Per Dokter';
                if (!empty($tgl1) && !empty($tgl2)) {
                    $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
                } else {
                    $subjudul_pie_dokter = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
                }
                $datadokter=$percentagesdokter;
                $labeldokter=$labels_dokter;
                $warnadokter=( [
                    '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                    '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
                ]);
        // End Pie Chart

        // Start Pie Chart Cara Bayar
            $sqlcarabayar = DB::table('reg_periksa')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kd_pj') // Menambahkan klausa groupBy
            ->select(DB::raw('kd_pj as cara_bayar'), DB::raw('count(*) as total'))
            ->get();
            $data_carabayar = $sqlcarabayar->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_carabayar = array_sum($data_carabayar);
            
            // Calculate the percentage for each kd_poli
            $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
                return round(($value / $totalSum_carabayar) * 100, 2);
            }, $data_carabayar);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_carabayar = collect($sqlcarabayar)->map(function ($item, $key) use ($percentages_carabayar) {
                return [
                    'nama_carabayar' => $item->cara_bayar,
                    'total_carabayar' => $item->total,
                    'percentage_carabayar' => $percentages_carabayar[$key],
                ];
            });

            $percentages_carabayar = collect($result_carabayar)->pluck('percentage_carabayar')->toArray();
            $labels_carabayar = collect($result_carabayar)->map(function ($item) {
            return $item['nama_carabayar'] . ': ' . $item['total_carabayar'] . '(' . $item['percentage_carabayar'] . '%)';
            })->toArray();


            $judul_pie_cara_bayar='Data Kunjungan Cara Bayar';
            $subjudul_pie_cara_bayar = '';
            $datacara_bayar=$percentages_carabayar;
            $labelcara_bayar=$labels_carabayar; 
            $warnabayar=( [
                '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0', '#80effe',
                '#0077B5', '#ff6384', '#c9cbcf', '#0057ff', '00a9f4', '#2ccdc9', '#5e72e4'
            ]);
        // End Pie Chart

        // Start Pie Chart Status
            $sqlstts = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('kd_pj', 'UMU')
                        ->orWhere('kd_pj', 'BPJ');
                });
            })
            ->groupBy('stts') // Menambahkan klausa groupBy
            ->select('stts', DB::raw('count(*) as total'))
            ->get();
            $datastts = $sqlstts->pluck('total')->toArray();

            // Calculate the total sum
            $totalSumstts = array_sum($datastts);
            
            // Calculate the percentage for each kd_poli
            $percentagesstts = array_map(function ($value) use ($totalSumstts) {
                return round(($value / $totalSumstts) * 100, 2);
            }, $datastts);
            
            // Combine kd_poli, total, and percentage into a new collection
            $resultstts = collect($sqlstts)->map(function ($item, $key) use ($percentagesstts) {
                return [
                    'stts' => $item->stts,
                    'total' => $item->total,
                    'percentage' => $percentagesstts[$key],
                ];
            });

            $percentagesstts = collect($resultstts)->pluck('percentage')->toArray();
            $labelsstts = collect($resultstts)->map(function ($item) {
            return $item['stts'] . ': ' . $item['total'] . '(' . $item['percentage'] . '%)';
            })->toArray();

                $judul_pie_stts='Data Kunjungan Per Status';
                $subjudul_pie_stts = '';
                $datastts=$percentagesstts;
                $labelstts=$labelsstts;
                $warnastts=(['#696969','#7FFF00','#DC143C']);
        // End Pie Chart
        
        // Start Bar Chart Pasien Lama Baru
            $sqlstts_daftar = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('kd_pj', $cara_bayarpj);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('kd_pj', 'UMU')
                        ->orWhere('kd_pj', 'BPJ');
                });
            })
            ->groupBy('stts_daftar') // Menambahkan klausa groupBy
            ->select('stts_daftar', DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data_stts_daftar = $sqlstts_daftar->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_stts_daftar = array_sum($data_stts_daftar);
            
            // Calculate the percentage for each kd_poli
            $percentages_stts_daftar = array_map(function ($value) use ($totalSum_stts_daftar) {
                return round(($value / $totalSum_stts_daftar) * 100, 2);
            }, $data_stts_daftar);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_stts_daftar = collect($sqlstts_daftar)->map(function ($item, $key) use ($percentages_stts_daftar) {
                return [
                    'nama_stts_daftar' => $item->stts_daftar,
                    'total_stts_daftar' => $item->total,
                    'percentage_stts_daftar' => $percentages_stts_daftar[$key],
                ];
            });

            $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
            return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
            })->toArray();

            $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';
            $subjudul_bar_stts_daftar = '';
            $warnastts_daftar = ['#3cb371','#ffa500'];

        // End Bar Chart Pasien Lama Baru
        
        // Start Bar Chart JK
            $sqljk = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->where('status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('pasien.jk') // Menambahkan klausa groupBy
            ->select('pasien.jk as jk', DB::raw('count(*) as total'))
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->get();
            $data_jk = $sqljk->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_jk = array_sum($data_jk);
            
            // Calculate the percentage for each kd_poli
            $percentages_jk = array_map(function ($value) use ($totalSum_jk) {
                return round(($value / $totalSum_jk) * 100, 2);
            }, $data_jk);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_jk = collect($sqljk)->map(function ($item, $key) use ($percentages_jk) {
                return [
                    'nama_jk' => $item->jk,
                    'total_jk' => $item->total,
                    'percentage_jk' => $percentages_jk[$key],
                ];
            });

            $labels_jk = collect($result_jk)->map(function ($item) {
            return $item['nama_jk'] . ' : ' . $item['total_jk'] . '(' . $item['percentage_jk'] . '%)';
            })->toArray();
            

            $judul_bar_jk = 'Data Kunjungan Jenkel';
            $subjudul_bar_jk = '';
            $warnajk = ['#ffa500','#3cb371'];
        // End Bar Chart JK

        // Start Data Kunjungan Pasien
            $datapasien= DB::table('reg_periksa')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->select('reg_periksa.no_rawat','reg_periksa.stts','reg_periksa.kd_pj','pasien.nm_pasien','poliklinik.nm_poli','dokter.nm_dokter')
            ->get();
        // End Data Kunjungan Pasien
        
        //Start perujuk
            $sql_rujuk_masuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('rujuk_masuk.perujuk') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(rujuk_masuk.perujuk, 30) as perujuk'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(30)
            ->get();

            $data_sql_rujuk_masuk = $sql_rujuk_masuk->pluck('total')->toArray();

            $totalSum_rujuk_masuk = array_sum($data_sql_rujuk_masuk);

            // Calculate the percentage for each kd_poli
            $percentages_rujuk_masuk = array_map(function ($value) use ($totalSum_rujuk_masuk) {
                return round(($value / $totalSum_rujuk_masuk) * 100, 2);
            }, $data_sql_rujuk_masuk);

            // Combine kd_poli, total, and percentage into a new collection
            $result_rujuk_masuk = collect($sql_rujuk_masuk)->map(function ($item, $key) use ($percentages_rujuk_masuk) {
                return [
                    'nama_rujuk_masuk' => $item->perujuk,
                    'total_rujuk_masuk' => $item->total,
                    'percentage_rujuk_masuk' => $percentages_rujuk_masuk[$key],
                ];
            });
            $percentages_rujuk_masuk = collect($result_rujuk_masuk)->pluck('percentage_rujuk_masuk')->toArray();
            $labels_rujuk_masuk = collect($result_rujuk_masuk)->map(function ($item) {
                return $item['nama_rujuk_masuk'] . ': '. $item['total_rujuk_masuk'] .'('. $item['percentage_rujuk_masuk'] . '% )';
            })->toArray();

            $data_sql_rujuk_masuk = $percentages_rujuk_masuk;
            $labels_sql_rujuk_masuk = $labels_rujuk_masuk;
            $warnaperujuk = ['#00FFFF','#3cb371'];
            $judul_pie_sql_rujuk_masuk = 'Data Perujuk Masuk';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_rujuk_masuk = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
        //End perujuk
        
        //Start Data Kabupaten
            $sql_kab = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kabupaten.nm_kab') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kabupaten.nm_kab, 30) as kab'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kab = $sql_kab->pluck('total')->toArray();

            $totalSum_kab = array_sum($data_sql_kab);

            // Calculate the percentage for each kd_poli
            $percentages_kab = array_map(function ($value) use ($totalSum_kab) {
                return round(($value / $totalSum_kab) * 100, 2);
            }, $data_sql_kab);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kab = collect($sql_kab)->map(function ($item, $key) use ($percentages_kab) {
                return [
                    'nama_kab' => $item->kab,
                    'total_kab' => $item->total,
                    'percentage_kab' => $percentages_kab[$key],
                ];
            });
            $labels_kab = collect($result_kab)->map(function ($item) {
                return $item['nama_kab'] . ': ' .$item['total_kab']  .'('. $item['percentage_kab'] . '%)';
            })->toArray();


            $judul_pie_sql_kab = 'Data Kunjungan Per Kabupaten';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kab = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }

            $warna_sql_Kabupaten = (['#FFD700']);
        //End Data Kabupaten

        // Start Bar Kecamatan
            $sqlkecamatan = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')),date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kecamatan.nm_kec') // Menambahkan klausa groupBy
            ->select(DB::raw('kecamatan.nm_kec as kecamatan'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();
            $data_kecamatan = $sqlkecamatan->pluck('total')->toArray();

            // Calculate the total sum
            $totalSum_kecamatan = array_sum($data_kecamatan);
            
            // Calculate the percentage for each kd_poli
            $percentages_kecamatan = array_map(function ($value) use ($totalSum_kecamatan) {
                return round(($value / $totalSum_kecamatan) * 100, 2);
            }, $data_kecamatan);
            
            // Combine kd_poli, total, and percentage into a new collection
            $result_kecamatan = collect($sqlkecamatan)->map(function ($item, $key) use ($percentages_kecamatan) {
                return [
                    'nama_kecamatan' => $item->kecamatan,
                    'total_kecamatan' => $item->total,
                    'percentage_kecamatan' => $percentages_kecamatan[$key],
                ];
            });

            $percentages_kecamatan = collect($result_kecamatan)->pluck('percentage_kecamatan')->toArray();
            $labels_kecamatan = collect($result_kecamatan)->map(function ($item) {
            return $item['nama_kecamatan'] . ': ' . $item['total_kecamatan'] . '(' . $item['percentage_kecamatan'] . '%)';
            })->toArray();


            $judul_pie_kecamatan='Data Kunjungan Per Kecamatan';
            $subjudul_pie_kecamatan = '';
            $warnakec=( ['#ADFF2F']);
        // End Bar Kecamatan

        //Start Data kelurahan
            $sql_kel = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('reg_periksa.kd_poli', $kdpoli);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->groupBy('kelurahan.nm_kel') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(kelurahan.nm_kel, 30) as kel'), DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();


            $data_sql_kel = $sql_kel->pluck('total')->toArray();

            $totalSum_kel = array_sum($data_sql_kel);

            // Calculate the percentage for each kd_poli
            $percentages_kel = array_map(function ($value) use ($totalSum_kel) {
                return round(($value / $totalSum_kel) * 100, 2);
            }, $data_sql_kel);

            // Combine kd_poli, total, and percentage into a new collection
            $result_kel = collect($sql_kel)->map(function ($item, $key) use ($percentages_kel) {
                return [
                    'nama_kel' => $item->kel,
                    'total_kel' => $item->total,
                    'percentage_kel' => $percentages_kel[$key],
                ];
            });

            $labels_kel = collect($result_kel)->map(function ($item) {
                return $item['nama_kel'] . ': '.$item['total_kel'] .'('. $item['percentage_kel'] . '% )';
            })->toArray();


            $judul_pie_sql_kel = 'Data Kunjungan kelurahan';
            if (!empty($tgl1) && !empty($tgl2)) {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
            } else {
                $subjudul_pie_sql_kel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
            }
            $warna_sql_kelurahan = (['#4169E1']);
        //End Data kelurahan
        
        // start trend pelayanan
            $tableName1 = ($kdpoli == 'LAB') ? 'periksa_lab as r' : 'periksa_radiologi as r';
            $tableName2 = ($kdpoli == 'LAB') ? 'jns_perawatan_lab as j' : 'jns_perawatan_radiologi as j';
            $pelayanan = DB::table('reg_periksa')
            ->join($tableName1, 'r.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('reg_periksa.kd_dokter', $kddokter);
            })
            ->when($cara_bayarpj, function ($query) use ($cara_bayarpj) {
                return $query->where('reg_periksa.kd_pj', $cara_bayarpj);
            })
            ->rightJoin($tableName2, 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy( 'j.nm_perawatan')
                ->select([
                    'j.nm_perawatan',
                    DB::raw('COUNT(r.kd_jenis_prw) as total')
                ])
                ->limit(20)
                ->orderBy('total', 'desc')
                ->get();

                $datapel = $pelayanan->pluck('total')->toArray();

                // Calculate the total sum
                $totalSumpel = array_sum($datapel);
                
                // Calculate the percentage for each kd_poli
                $percentagespel = array_map(function ($value) use ($totalSumpel) {
                    return round(($value / $totalSumpel) * 100, 2);
                }, $datapel);
                
                // Combine kd_poli, total, and percentage into a new collection
                $resultpel = collect($pelayanan)->map(function ($item, $key) use ($percentagespel) {
                    return [
                        'nama_pel' => $item->nm_perawatan,
                        'total' => $item->total,
                        'percentage' => $percentagespel[$key],
                    ];
                });

                $labelspel = collect($resultpel)->map(function ($item) {
                return $item['nama_pel'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
                })->toArray();


                $judul_pie_pel='Data Trend Pelayanan Poliklinik';
                if (!empty($tgl1) && !empty($tgl2)) {
                    $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime($tgl1)) . ' S/D ' . date('d F Y', strtotime($tgl2));
                } else {
                    $subjudul_pie_pel = 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));
                }

                $warnapel=( [
                    '#008FFB'
                ]);
        // end trend pelayanan

        //start return
                return view('rm.rajal.radiologi', [
                    // untuk mengirim data dalam form
                    'tgl1' => $tgl1,
                    'tgl2' => $tgl2,
                    'kddokter' => $kddokter,
                    'kd_pj' => $cara_bayarpj,
                    'status' => $status,
                    // end form
            
                    //pelayanan2
                        'datapel' => $datapel, 
                        'labelspel' => $labelspel, 
                        'judul_pie_pel' => $judul_pie_pel, 
                        'subjudul_pie_pel' => $subjudul_pie_pel, 
                        'warnapel' => $warnapel ,
                    //kunjungan
                        'bpjs' => $bpjs, 
                        'umum' => $umum, 
                        'labelstat' => $labelstat, 
                        'judul_line' => $judul_line, 
                        'subjudul_line' => $subjudul_line ,
                    //dokter
                        'datadokter' => $datadokter, // Contoh: [10, 20, 30]
                        'labeldokter' => $labeldokter, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_dokter' => $judul_pie_dokter, // Contoh: "Judul Chart"
                        'subjudul_pie_dokter' => $subjudul_pie_dokter, // Contoh: "Subjudul Chart"
                        'warnadokter' => $warnadokter ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //cara bayar
                        'datacara_bayar' => $datacara_bayar, // Contoh: [10, 20, 30]
                        'labelcara_bayar' => $labelcara_bayar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_cara_bayar' => $judul_pie_cara_bayar, // Contoh: "Judul Chart"
                        'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar, // Contoh: "Subjudul Chart"
                        'warnabayar' => $warnabayar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //stts
                        'datastts' => $datastts, // Contoh: [10, 20, 30]
                        'labelsstts' => $labelsstts, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_stts' => $judul_pie_stts, // Contoh: "Judul Chart"
                        'subjudul_pie_stts' => $subjudul_pie_stts, // Contoh: "Subjudul Chart"
                        'warnastts' => $warnastts ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //stts_daftar
                        'data_stts_daftar' => $data_stts_daftar, // Contoh: [10, 20, 30]
                        'labels_stts_daftar' => $labels_stts_daftar, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_bar_stts_daftar' => $judul_bar_stts_daftar, // Contoh: "Judul Chart"
                        'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar, // Contoh: "Subjudul Chart"
                        'warnastts_daftar' => $warnastts_daftar ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //JK
                        'data_jk' => $data_jk, // Contoh: [10, 20, 30]
                        'labels_jk' => $labels_jk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_bar_jk' => $judul_bar_jk, // Contoh: "Judul Chart"
                        'subjudul_bar_jk' => $subjudul_bar_jk, // Contoh: "Subjudul Chart"
                        'warnajk' => $warnajk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //Perujuk
                        'data_sql_rujuk_masuk' => $data_sql_rujuk_masuk, // Contoh: [10, 20, 30]
                        'labels_rujuk_masuk' => $labels_rujuk_masuk, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_sql_rujuk_masuk' => $judul_pie_sql_rujuk_masuk, // Contoh: "Judul Chart"
                        'subjudul_pie_sql_rujuk_masuk' => $subjudul_pie_sql_rujuk_masuk, // Contoh: "Subjudul Chart"
                        'warnaperujuk' => $warnaperujuk ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //poli
                        'data' => $data, // Contoh: [10, 20, 30]
                        'labels' => $labels, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_poli' => $judul_pie_poli, // Contoh: "Judul Chart"
                        'subjudul_pie_poli' => $subjudul_pie_poli, // Contoh: "Subjudul Chart"
                        'warnapoli' => $warnapoli ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //kabupaten
                        'data_sql_kab' => $data_sql_kab, // Contoh: [10, 20, 30]
                        'labels_kab' => $labels_kab, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_sql_kab' => $judul_pie_sql_kab, // Contoh: "Judul Chart"
                        'subjudul_pie_sql_kab' => $subjudul_pie_sql_kab, // Contoh: "Subjudul Chart"
                        'warna_sql_Kabupaten' => $warna_sql_Kabupaten ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //kecamatan
                        'data_kecamatan' => $data_kecamatan, // Contoh: [10, 20, 30]
                        'labels_kecamatan' => $labels_kecamatan, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_kecamatan' => $judul_pie_kecamatan, // Contoh: "Judul Chart"
                        'subjudul_pie_kecamatan' => $subjudul_pie_kecamatan, // Contoh: "Subjudul Chart"
                        'warnakec' => $warnakec ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    //Kelurahan
                        'data_sql_kel' => $data_sql_kel, // Contoh: [10, 20, 30]
                        'labels_kel' => $labels_kel, // Contoh: ["Kecamatan A", "Kecamatan B", "Kecamatan C"]
                        'judul_pie_sql_kel' => $judul_pie_sql_kel, // Contoh: "Judul Chart"
                        'subjudul_pie_sql_kel' => $subjudul_pie_sql_kel, // Contoh: "Subjudul Chart"
                        'warna_sql_kelurahan' => $warna_sql_kelurahan ,// Contoh: ["#FF4560", "#00E396", "#008FFB"]
                    'pilihan_dokter' => $pilihan_dokter,
                    'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
                    'pelayanan' => $pelayanan,
                ]);
            } 
        // end return  
    //end seluruh poli penunjang 

    private function getChartData($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('kd_poli', 'not like', '%HDL%')
            ->where('kd_poli', 'not like', '%LAB%')
            ->where('kd_poli', 'not like', '%RAD%')
            ->where('kd_poli', 'not like', '%IGDK%')
            ->where('kd_poli', 'not like', '%MCU%')
            ->where('kd_poli', 'not like', '%IRM%')
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Batal');
                });
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    // End Khusus Line Chart seluruh poli kecuali 6 poli
    // Khusus Line Chart 1 Poli 
    private function getChartData2($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    // End Khusus Line Chart 1 Poli

    private function getChartData3($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($kdpoli, function ($query) use ($kdpoli) {
                return $query->where('kd_poli', $kdpoli);
            })
            ->when($kddokter, function ($query) use ($kddokter) {
                return $query->where('kd_dokter', $kddokter);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    private function getChartData4($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'HDL') // Pastikan hanya menampilkan HDL
            ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    // End Khusus Line Chart 1 Poli

    private function getChartData5($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'IGDK') // Pastikan hanya menampilkan IGDK
            // ->whereIn('reg_periksa.kd_dokter', ['D57'])
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    // End Khusus Line Chart 1 Poli

    private function getChartData6($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'lab') // Pastikan hanya menampilkan LAB
            // ->whereIn('reg_periksa.kd_dokter', ['D59'])
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    // End Khusus Line Chart 1 Poli

    private function getChartData7($kd_pj, $tgl1, $tgl2, $kdpoli, $kddokter, $status)
    {
        return DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.kd_poli', 'RAD') // Pastikan hanya menampilkan RAD
            // ->whereIn('reg_periksa.kd_dokter', ['D3', 'D46'])
            ->where('kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('tgl_registrasi', [$tgl1, $tgl2]);
            }, function ($query) {
                return $query->whereBetween('tgl_registrasi', [now()->startOfMonth(), now()]);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('reg_periksa.stts', $status);
            }, function ($query) {
                return $query->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')
                        ->orWhere('reg_periksa.stts', 'Belum');
                });
            })
            ->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select('kd_pj', 
                    DB::raw('YEAR(tgl_registrasi) as year'),
                    DB::raw('MONTH(tgl_registrasi) as month'), 
                    DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    // End Khusus Line Chart 1 Poli
}
