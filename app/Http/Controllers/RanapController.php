<?php

namespace App\Http\Controllers;

use App\Charts\Chart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RanapController extends Controller
{
    public function ranap(Chart $chart, Request $request)
    {
        // Start Value Form 
        //format tanggal untuk membuat selisih tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }

        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }

        // Format the dates
        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        $kodekamar = $request->input('kode_kamar');
        $kodepj = $request->input('kodepj');
        // End Value Form

        // start Pilihan Cara Bayar
        $pilihan_cara_bayar = DB::table('penjab')
            ->select('kd_pj', 'png_jawab')
            ->get();
        // end Pilihan Cara Bayar

        //start panggil kamar
        $pilihan_kamar = DB::table('bangsal')
            ->select('kd_bangsal', 'nm_bangsal')
            ->get();
        //end panggil kamar

        // Start Line Chart
        $grafik_umum = $this->getChartData('PJ2', $tgl1, $tgl2, $kodekamar);
        $grafik_bpjs = $this->getChartData('BPJ', $tgl1, $tgl2, $kodekamar);
        $grafik_inhealth = $this->getChartData('PJ3', $tgl1, $tgl2, $kodekamar);
        $grafik_jamkesda = $this->getChartData('PJ4', $tgl1, $tgl2, $kodekamar);
        $grafik_bkk = $this->getChartData('PJ7', $tgl1, $tgl2, $kodekamar);
        $grafik_pjkn = $this->getChartData('PJ8', $tgl1, $tgl2, $kodekamar);

        // Sort data based on year and month
        $grafik_umum = $grafik_umum->sortBy(['year', 'month'])->values();
        $grafik_bpjs = $grafik_bpjs->sortBy(['year', 'month'])->values();
        $grafik_inhealth = $grafik_inhealth->sortBy(['year', 'month'])->values();
        $grafik_jamkesda = $grafik_jamkesda->sortBy(['year', 'month'])->values();
        $grafik_bkk = $grafik_bkk->sortBy(['year', 'month'])->values();
        $grafik_pjkn = $grafik_pjkn->sortBy(['year', 'month'])->values();

        // Merge data based on year and month
        $mergedData = $grafik_umum->map(function ($item) use ($grafik_bpjs, $grafik_inhealth, $grafik_jamkesda, $grafik_bkk, $grafik_pjkn) {
            $bpjsData = $grafik_bpjs->where('month', $item->month)->first();
            $inhealthData = $grafik_inhealth->where('month', $item->month)->first();
            $jamkesdaData = $grafik_jamkesda->where('month', $item->month)->first();
            $bkkData = $grafik_bkk->where('month', $item->month)->first();
            $pjknData = $grafik_pjkn->where('month', $item->month)->first();
            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => $item->month_name,
                'umum_total' => $item->total,
                'bpjs_total' => $bpjsData ? $bpjsData->total : 0,
                'inhealth_total' => $inhealthData ? $inhealthData->total : 0,
                'jamkesda_total' => $jamkesdaData ? $jamkesdaData->total : 0,
                'bkk_total' => $bkkData ? $bkkData->total : 0,
                'pjkn_total' => $pjknData ? $pjknData->total : 0,
            ];
        });

        $judul_line = 'Data Kunjungan Pasien';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_line = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_line = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $umum = $mergedData->pluck('umum_total')->toArray();
        $bpjs = $mergedData->pluck('bpjs_total')->toArray();
        $inhealth = $mergedData->pluck('inhealth_total')->toArray();
        $jamkesda = $mergedData->pluck('jamkesda_total')->toArray();
        $bkk = $mergedData->pluck('bkk_total')->toArray();
        $pjkn = $mergedData->pluck('pjkn_total')->toArray();
        $labelstat = $mergedData->pluck('month_name')->toArray();
        // End Line Chart

        // START Chart Cara Bayar
        $jmlranap = DB::table('reg_periksa as b')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'b.no_rawat')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->groupBy('b.kd_pj')
            ->select(DB::raw('b.kd_pj as cara_bayar'), DB::raw('COUNT(DISTINCT a.no_rawat)  as total'))
            ->orderBy(DB::raw('COUNT(DISTINCT a.no_rawat)'), 'desc')
            ->get();
        $data_carabayar = $jmlranap->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_carabayar = array_sum($data_carabayar);

        // Calculate the percentage for each kd_poli
        $percentages_carabayar = array_map(function ($value) use ($totalSum_carabayar) {
            return round(($value / $totalSum_carabayar) * 100, 2);
        }, $data_carabayar);

        // Combine kd_poli, total, and percentage into a new collection
        $result_carabayar = collect($jmlranap)->map(function ($item, $key) use ($percentages_carabayar) {
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


        $judul_pie_cara_bayar = 'Data Kunjungan Cara Bayar';
        $subjudul_pie_cara_bayar = '';
        $datacara_bayar = $percentages_carabayar;
        $labelcara_bayar = $labels_carabayar;
        $warnabayar = ([
            '#008FFB',
            '#00E396',
            '#feb019',
            '#ff455f',
            '#775dd0',
            '#80effe',
            '#0077B5',
            '#ff6384',
            '#c9cbcf',
            '#0057ff',
            '00a9f4',
            '#2ccdc9',
            '#5e72e4'
        ]);
        // END  Chart Cara Bayar

        // Start Line Chart Kelas Kamar
        $grafik_kelas3 = $this->getChartData2('Kelas 3', $kodepj, $tgl1, $tgl2, $kodekamar);
        $grafik_kelas2 = $this->getChartData2('Kelas 2', $kodepj, $tgl1, $tgl2, $kodekamar);
        $grafik_kelas1 = $this->getChartData2('Kelas 1', $kodepj, $tgl1, $tgl2, $kodekamar);
        $grafik_utama = $this->getChartData2('Kelas Utama', $kodepj, $tgl1, $tgl2, $kodekamar);
        $grafik_vip = $this->getChartData2('Kelas VIP', $kodepj, $tgl1, $tgl2, $kodekamar);
        $grafik_vvip = $this->getChartData2('Kelas VVIP', $kodepj, $tgl1, $tgl2, $kodekamar);

        // Sort data based on year and month
        $grafik_kelas3 = $grafik_kelas3->sortBy(['year', 'month'])->values();
        $grafik_kelas2 = $grafik_kelas2->sortBy(['year', 'month'])->values();
        $grafik_kelas1 = $grafik_kelas1->sortBy(['year', 'month'])->values();
        $grafik_utama = $grafik_utama->sortBy(['year', 'month'])->values();
        $grafik_vip = $grafik_vip->sortBy(['year', 'month'])->values();
        $grafik_vvip = $grafik_vvip->sortBy(['year', 'month'])->values();

        // Merge data based on year and month
        $mergedDatakelas = $grafik_kelas3->map(function ($item) use ($grafik_kelas2, $grafik_kelas1, $grafik_utama, $grafik_vip, $grafik_vvip) {
            $kelas1Data = $grafik_kelas1->where('month', $item->month)->first();
            $kelas2Data = $grafik_kelas2->where('month', $item->month)->first();
            $utamaData = $grafik_utama->where('month', $item->month)->first();
            $vipData = $grafik_vip->where('month', $item->month)->first();
            $vvipData = $grafik_vvip->where('month', $item->month)->first();
            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => $item->month_name,
                'kelas3_total' => $item->total,
                'kelas2_total' => $kelas2Data ? $kelas2Data->total : 0,
                'kelas1_total' => $kelas1Data ? $kelas1Data->total : 0,
                'utama_total' => $utamaData ? $utamaData->total : 0,
                'vip_total' => $vipData ? $vipData->total : 0,
                'vvip_total' => $vvipData ? $vvipData->total : 0,
            ];
        });

        $judul_linekelas = 'Data Kunjungan Pasien Per Kelas';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_linekelas = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_linekelas = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $kelas3 = $mergedDatakelas->pluck('kelas3_total')->toArray();
        $kelas2 = $mergedDatakelas->pluck('kelas2_total')->toArray();
        $kelas1 = $mergedDatakelas->pluck('kelas1_total')->toArray();
        $utama = $mergedDatakelas->pluck('utama_total')->toArray();
        $vip = $mergedDatakelas->pluck('vip_total')->toArray();
        $vvip = $mergedDatakelas->pluck('vvip_total')->toArray();
        $labelstatkelas = $mergedDatakelas->pluck('month_name')->toArray();
        // End Line Chart Kelas Kamar

        // START Chart Kelas
        $jmlranapkelas = DB::table('reg_periksa as b')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'b.no_rawat')
            ->join('kamar as c', 'c.kd_kamar', '=', 'a.kd_kamar')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->groupBy('c.kelas')
            ->select(DB::raw('c.kelas as kelas'), DB::raw('COUNT(a.no_rawat)  as total'))
            ->orderBy(DB::raw('COUNT(a.no_rawat)'), 'desc')
            ->get();
        $data_kelas = $jmlranapkelas->pluck('total')->toArray();

        // Calculate the total sum
        $totalSum_kelas = array_sum($data_kelas);

        // Calculate the percentage for each kd_poli
        $percentages_kelas = array_map(function ($value) use ($totalSum_kelas) {
            return round(($value / $totalSum_kelas) * 100, 2);
        }, $data_kelas);

        // Combine kd_poli, total, and percentage into a new collection
        $result_kelas = collect($jmlranapkelas)->map(function ($item, $key) use ($percentages_kelas) {
            return [
                'nama_kelas' => $item->kelas,
                'total_kelas' => $item->total,
                'percentage_kelas' => $percentages_kelas[$key],
            ];
        });

        $percentages_kelas = collect($result_kelas)->pluck('percentage_kelas')->toArray();
        $labels_kelas = collect($result_kelas)->map(function ($item) {
            return $item['nama_kelas'] . ': ' . $item['total_kelas'] . '(' . $item['percentage_kelas'] . '%)';
        })->toArray();


        $judul_pie_kelas = 'Data Kunjungan Pasien Per Kelas';
        $subjudul_pie_kelas = '';
        $datakelas = $percentages_kelas;
        $labelkelas = $labels_kelas;
        $warnakelas = ([
            '#008FFB',
            '#00E396',
            '#feb019',
            '#ff455f',
            '#775dd0',
            '#80effe',
            '#0077B5',
            '#ff6384',
            '#c9cbcf',
            '#0057ff',
            '00a9f4',
            '#2ccdc9',
            '#5e72e4'
        ]);
        // END  Chart Kelas

        //start prosedur
        $sqlprosedur = DB::table('reg_periksa as b')
            ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'b.no_rawat')
            ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode')
            ->join('kamar_inap', 'kamar_inap.no_rawat', '=', 'prosedur_pasien.no_rawat')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('kamar_inap.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('kamar_inap.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->groupBy('icd9.kode', 'icd9.deskripsi_pendek') // Menambahkan klausa groupBy
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
            return $item['namaprosedur'] . ': ' . $item['totalprosedur'] . '(' . $item['percentageprosedur'] . '% )';
        })->toArray();


        $judul_pie_sqlprosedur = 'Data Prosedur (ICD9)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqlprosedur = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_pie_sqlprosedur = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $warna_sqlprosedur = (['#0da168']);
        //end prosedur

        //start diagnosa
        $sqldiagnosa = DB::table('reg_periksa as b')
            ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'b.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
            ->join('kamar_inap', 'kamar_inap.no_rawat', '=', 'diagnosa_pasien.no_rawat')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('kamar_inap.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('kamar_inap.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->groupBy('penyakit.kd_penyakit', 'penyakit.nm_penyakit') // Menambahkan klausa groupBy
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
            return $item['namadiagnosa'] . ': ' . $item['totaldiagnosa'] . '(' . $item['percentagediagnosa'] . '% )';
        })->toArray();


        $judul_pie_sqldiagnosa = 'Data diagnosa (ICD10)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_sqldiagnosa = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_pie_sqldiagnosa = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $warna_sqldiagnosa = (['#9ea10d']);
        //end diagnosa

        // start Dokter chart
        $pelayanandokter = DB::table('reg_periksa as b')
            ->join('rawat_inap_drpr as r', 'r.no_rawat', '=', 'b.no_rawat')
            ->leftJoin('dokter as j', 'r.kd_dokter', '=', 'j.kd_dokter')
            ->select(
                'j.nm_dokter',
                DB::raw('COUNT(j.nm_dokter) as total')
            )
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('r.tgl_perawatan', [$tgl1, $tgl2]);
            })
            ->groupBy('j.nm_dokter')
            ->orderByDesc('total')
            // ->limit(20)
            ->get();

        $datapeldokter = $pelayanandokter->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumpeldokter = array_sum($datapeldokter);

        // Calculate the percentage for each kd_poli
        $percentagespeldokter = array_map(function ($value) use ($totalSumpeldokter) {
            return round(($value / $totalSumpeldokter) * 100, 2);
        }, $datapeldokter);

        // Combine kd_poli, total, and percentage into a new collection
        $resultpeldokter = collect($pelayanandokter)->map(function ($item, $key) use ($percentagespeldokter) {
            return [
                'nama_dokter' => $item->nm_dokter,
                'total' => $item->total,
                'percentage' => $percentagespeldokter[$key],
            ];
        });

        $labelspeldokter = collect($resultpeldokter)->map(function ($item) {
            return $item['nama_dokter'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();


        $judul_pie_peldokter = 'Data Trend Pelayanan Dokter Ranap';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_peldokter = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_pie_peldokter = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $warnapeldokter = ([
            '#b9eabb'
        ]);
        // end Dokter chart

        // start prw chart
        $pelayananprw = DB::table('reg_periksa as b')
            ->join('rawat_inap_drpr as r', 'r.no_rawat', '=', 'b.no_rawat')
            ->select('r.no_rawat', 'r.nip', 'r.tgl_perawatan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('r.tgl_perawatan', [$tgl1, $tgl2]);
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->rightJoin('petugas as j', 'r.nip', '=', 'j.nip')
            ->groupBy('j.nama')
            ->select([
                'j.nama',
                DB::raw('COUNT(j.nama) as total')
            ])
            ->orderby('total', 'desc')
            ->limit(20)
            ->get();

        $datapelprw = $pelayananprw->pluck('total')->toArray();

        // Calculate the total sum
        $totalSumpelprw = array_sum($datapelprw);

        // Calculate the percentage for each kd_poli
        $percentagespelprw = array_map(function ($value) use ($totalSumpelprw) {
            return round(($value / $totalSumpelprw) * 100, 2);
        }, $datapelprw);

        // Combine kd_poli, total, and percentage into a new collection
        $resultpelprw = collect($pelayananprw)->map(function ($item, $key) use ($percentagespelprw) {
            return [
                'nama_prw' => $item->nama,
                'total' => $item->total,
                'percentage' => $percentagespelprw[$key],
            ];
        });

        $labelspelprw = collect($resultpelprw)->map(function ($item) {
            return $item['nama_prw'] . ' : ' . $item['total'] . '(' . $item['percentage'] . '%)';
        })->toArray();


        $judul_pie_pelprw = 'Data Trend Pelayanan Perawat Ranap';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_pelprw = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_pie_pelprw = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $warnapelprw = ([
            '#a4ebff'
        ]);
        // end prw chart


        // Start Bar Chart Pasien Lama Baru
        $sqlstts_daftar = DB::table('reg_periksa as b')
            ->join('kamar_inap', 'kamar_inap.no_rawat', '=', 'b.no_rawat')
            ->where('status_lanjut', 'Ranap')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('kamar_inap.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('kamar_inap.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
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

        $percentages_stts_daftar = collect($result_stts_daftar)->pluck('percentage_stts_daftar')->toArray();
        $labels_stts_daftar = collect($result_stts_daftar)->map(function ($item) {
            return $item['nama_stts_daftar'] . ': ' . $item['total_stts_daftar'] . '(' . $item['percentage_stts_daftar'] . '%)';
        })->toArray();

        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_bar_stts_daftar = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_bar_adime = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $judul_bar_stts_daftar = 'Data Kunjungan Pasien Lama dan Baru';

        $warnastts_daftar = ['#3cb371', '#ffa500'];
        // End Bar Chart Pasien Lama Baru

        // start pelayanan chart
        $pelayanan = DB::table(DB::raw('(
        SELECT no_rawat, kd_jenis_prw
        FROM rawat_inap_dr
        UNION ALL
        SELECT no_rawat, kd_jenis_prw
        FROM rawat_inap_drpr
        UNION ALL
        SELECT no_rawat, kd_jenis_prw
        FROM rawat_inap_pr 
        ) as r'))
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'r.no_rawat')
            ->join('reg_periksa as b', 'b.no_rawat', '=', 'a.no_rawat')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->rightJoin('jns_perawatan_inap as j', 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy('j.nm_perawatan')
            ->select([
                'j.nm_perawatan',
                DB::raw('COUNT(j.nm_perawatan) as total')
            ])
            ->orderby('total', 'desc')
            ->limit(20)
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


        $judul_pie_pel = 'Data Trend Pelayanan Ranap';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_pie_pel = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_pie_pel = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }
        $warnapel = ([
            '#6699cc'
        ]);
        // end pelayanan chart

        // Start Bar Chart ADIME
        $sqlTotalCatatanGizi = DB::table('catatan_adime_gizi')
            ->whereNotNull('no_rawat')
            ->whereBetween('tanggal', [$tgl1, $tgl2])
            ->select(DB::raw('count(*) as total'))
            ->first();

        $data_adime = [$sqlTotalCatatanGizi->total]; // Simpan dalam array agar bisa diolah

        // Hitung total sum
        $totalSum_adime = array_sum($data_adime);

        $percentages_adime = array_map(function ($value) use ($totalSum_adime) {
            return $totalSum_adime > 0 ? round(($value / $totalSum_adime) * 100, 2) : 0;
        }, $data_adime);

        $result_adime = collect($data_adime)->map(function ($item, $key) use ($percentages_adime) {
            return [
                'nama_adime' => 'Total Catatan Gizi',
                'total_adime' => $item,
                'percentage_adime' => $percentages_adime[$key],
            ];
        });

        $percentages_adime = collect($result_adime)->pluck('percentage_adime')->toArray();
        $labels_adime = collect($result_adime)->map(function ($item) {
            return $item['nama_adime'] . ': ' . $item['total_adime'] . '(' . $item['percentage_adime'] . '%)';
        })->toArray();

        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_bar_adime = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_bar_adime = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $judul_bar_adime = 'Data Adime Gizi';

        $warnastts_adime = ['#a4ebff'];
        // End Bar Chart Adime

        return view('rm.ranap.ranap', [
            // untuk mengirim data dalam form
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'kodekamar' => $kodekamar,
            'kodepj' => $kodepj,
            // end form
            'pilihan_cara_bayar' =>  $pilihan_cara_bayar,
            'pilihan_kamar' => $pilihan_kamar,
            //kunjungan
            'jamkesda' => $jamkesda,
            'bkk' => $bkk,
            'pjkn' => $pjkn,
            'inhealth' => $inhealth,
            'bpjs' => $bpjs,
            'umum' => $umum,
            'labelstat' => $labelstat,
            'judul_line' => $judul_line,
            'subjudul_line' => $subjudul_line,
            //cara_bayar
            'datacara_bayar' => $datacara_bayar,
            'labelcara_bayar' => $labelcara_bayar,
            'judul_pie_cara_bayar' => $judul_pie_cara_bayar,
            'subjudul_pie_cara_bayar' => $subjudul_pie_cara_bayar,
            'warnabayar' => $warnabayar,
            //kunjungankelas
            'vvip' => $vvip,
            'vip' => $vip,
            'utama' => $utama,
            'kelas1' => $kelas1,
            'kelas2' => $kelas2,
            'kelas3' => $kelas3,
            'labelstatkelas' => $labelstatkelas,
            'judul_linekelas' => $judul_linekelas,
            'subjudul_linekelas' => $subjudul_linekelas,
            //kelas
            'datakelas' => $datakelas,
            'labelkelas' => $labelkelas,
            'judul_pie_kelas' => $judul_pie_kelas,
            'subjudul_pie_kelas' => $subjudul_pie_kelas,
            'warnakelas' => $warnakelas,

            //prosedur
            'data_sqlprosedur' => $data_sqlprosedur,
            'labelsprosedur' => $labelsprosedur,
            'judul_pie_sqlprosedur' => $judul_pie_sqlprosedur,
            'subjudul_pie_sqlprosedur' => $subjudul_pie_sqlprosedur,
            'warna_sqlprosedur' => $warna_sqlprosedur,
            //diagnosa
            'data_sqldiagnosa' => $data_sqldiagnosa,
            'labelsdiagnosa' => $labelsdiagnosa,
            'judul_pie_sqldiagnosa' => $judul_pie_sqldiagnosa,
            'subjudul_pie_sqldiagnosa' => $subjudul_pie_sqldiagnosa,
            'warna_sqldiagnosa' => $warna_sqldiagnosa,
            //pelayanandokter
            'datapeldokter' => $datapeldokter,
            'labelspeldokter' => $labelspeldokter,
            'judul_pie_peldokter' => $judul_pie_peldokter,
            'subjudul_pie_peldokter' => $subjudul_pie_peldokter,
            'warnapeldokter' => $warnapeldokter,
            //pelayananprw
            'datapelprw' => $datapelprw,
            'labelspelprw' => $labelspelprw,
            'judul_pie_pelprw' => $judul_pie_pelprw,
            'subjudul_pie_pelprw' => $subjudul_pie_pelprw,
            'warnapelprw' => $warnapelprw,
            //pelayanan
            'datapel' => $datapel,
            'labelspel' => $labelspel,
            'judul_pie_pel' => $judul_pie_pel,
            'subjudul_pie_pel' => $subjudul_pie_pel,
            'warnapel' => $warnapel,
            //status
            'data_stts_daftar' => $data_stts_daftar,
            'labels_stts_daftar' => $labels_stts_daftar,
            'judul_bar_stts_daftar' => $judul_bar_stts_daftar,
            'subjudul_bar_stts_daftar' => $subjudul_bar_stts_daftar,
            'warnastts_daftar' => $warnastts_daftar,
            //adime
            'totalCatatanGizi' => $sqlTotalCatatanGizi,
            'judul_bar_adime' => $judul_bar_adime,
            'subjudul_bar_adime' => $subjudul_bar_adime,
            'labels_adime' => $labels_adime,
            'data_adime' => $data_adime,
            'warnastts_adime' => $warnastts_adime
        ]);
    }

    // Khusus Line Chart
    //start cara bayar
    private function getChartData($kd_pj, $tgl1, $tgl2, $kodekamar)
    {
        return DB::table('reg_periksa as b')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'b.no_rawat')
            ->where('b.kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->groupBy('kd_pj', DB::raw('YEAR(a.tgl_masuk)'), DB::raw('MONTH(a.tgl_masuk)'))
            ->select(
                'kd_pj',
                DB::raw('YEAR(a.tgl_masuk) as year'),
                DB::raw('MONTH(a.tgl_masuk) as month'),
                DB::raw('COUNT(DISTINCT b.no_rawat) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    //end cara bayar
    //start Kelas Kamar
    private function getChartData2($kelas, $kodepj, $tgl1, $tgl2, $kodekamar)
    {
        return DB::table('reg_periksa as b')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'b.no_rawat')
            ->join('kamar as c', 'a.kd_kamar', '=', 'c.kd_kamar')
            ->where('c.kelas', $kelas)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })

            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->groupBy('c.kelas', DB::raw('YEAR(a.tgl_masuk)'), DB::raw('MONTH(a.tgl_masuk)'))
            ->select(
                'c.kelas',
                DB::raw('YEAR(a.tgl_masuk) as year'),
                DB::raw('MONTH(a.tgl_masuk) as month'),
                DB::raw('COUNT( b.no_rawat) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    //end Kelas Kamar

}
