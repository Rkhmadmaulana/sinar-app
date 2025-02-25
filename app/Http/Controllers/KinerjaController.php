<?php

namespace App\Http\Controllers;
// use ;
use Illuminate\Http\Request;
use App\Charts\Chart;
use Illuminate\Support\Facades\DB;

class KinerjaController extends Controller
{
    public function kinerja(Chart $chart, Request $request)
    {
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
        $kelaskamar = $request->input('kelas');
        //start jml kamar
        $jmlkmrdewasaModel = DB::table('jumlah_kamar')->where('kamar', 'kamar_dewasa')->first();
        $jmlkmrdewasa = $jmlkmrdewasaModel ? $jmlkmrdewasaModel->jumlah : 0;
        $jmlkmrbayiModel = DB::table('jumlah_kamar')->where('kamar', 'kamar_bayi')->first();
        $jmlkmrbayi = $jmlkmrbayiModel ? $jmlkmrbayiModel->jumlah : 0;
        //end jml kamar

        // Start selisih tanggal
        $interval = $tgl1->diff($tgl2);
        $days = $interval->days + 1; // Gunakan properti days untuk mendapatkan total jumlah hari    
        // End selisih tanggal

        // Start line chart pasien
        $grafik_pasien_bayi = $this->getChartData2($tgl1, $tgl2, $kelaskamar);
        $grafik_pasien_dewasa = $this->getChartData($tgl1, $tgl2, $kelaskamar);

        // Sort data based on year and month
        $grafik_pasien_bayi = $grafik_pasien_bayi->sortBy(['year', 'month'])->values();
        $grafik_pasien_dewasa = $grafik_pasien_dewasa->sortBy(['year', 'month'])->values();

        // Gabungkan data berdasarkan tahun dan bulan
        $mergedData = $grafik_pasien_dewasa->map(function ($item) use ($grafik_pasien_bayi) {
            $bayiData = $grafik_pasien_bayi->where('month', $item->month)->first();

            $dewasa_total = $item->total;
            $bayi_total = $bayiData ? $bayiData->total : 0;

            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => $item->month_name,
                'dewasa_total' => $dewasa_total,
                'bayi_total' => $bayi_total,
            ];
        });


        $judul_line = 'Grafik Pasien (Hidup + Mati)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_line = $tgl1->format('d F Y') . ' - ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_line = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }


        $bayi = $mergedData->pluck('bayi_total')->toArray();
        $dewasa = $mergedData->pluck('dewasa_total')->toArray();
        $labelstat = $mergedData->pluck('month_name')->toArray();

        // end line chart pasien

        // Start line chart Lama Rawat
        $grafik_lama_dewasa = $this->getChartData3($tgl1, $tgl2, $kelaskamar);
        $grafik_lama_bayi = $this->getChartData4($tgl1, $tgl2, $kelaskamar);

        // Sort data based on year and month
        $grafik_lama_dewasa = $grafik_lama_dewasa->sortBy(['year', 'month'])->values();
        $grafik_lama_bayi = $grafik_lama_bayi->sortBy(['year', 'month'])->values();

        // Gabungkan data berdasarkan tahun dan bulan
        $mergedData_lama = $grafik_lama_dewasa->map(function ($item) use ($grafik_lama_bayi) {
            $dewasaData = $grafik_lama_bayi->where('month', $item->month)->first();

            $bayi_total = $item->total;
            $dewasa_total = $dewasaData ? $dewasaData->total : 0;

            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => $item->month_name,
                'bayi_total' => $bayi_total,
                'dewasa_total' => $dewasa_total,
            ];
        });


        $judul_line_lama = 'Grafik lama (Hidup + Mati)';
        if (!empty($tgl1) && !empty($tgl2)) {
            $subjudul_line_lama = $tgl1->format('d F Y') . ' - ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $subjudul_line_lama = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }


        $dewasa_lama = $mergedData_lama->pluck('dewasa_total')->toArray();
        $bayi_lama = $mergedData_lama->pluck('bayi_total')->toArray();
        $labelstat_lama = $mergedData_lama->pluck('month_name')->toArray();
        // end line chart Lama Rawat                

        // Start Lama Rawat
        // start lama rawat dewasa
        $lama_rawat_dewasa = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('SUM(a.lama) as total_lama'))
            ->first();
        // end lama rawat dewasa
        // start lama rawat bayi
        $lama_rawat_bayi = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('SUM(a.lama) as total_lama'))
            ->first();
        // end lama rawat bayi
        // End Lama Rawat
        // Start Lama Rawat = 0
        // start lama rawat dewasa
        $lama_rawat_dewasa0 = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->where('a.lama', '!=', '0')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('COUNT(a.lama) as total_lama'))
            ->first();
        // end lama rawat dewasa
        // start lama rawat bayi
        $lama_rawat_bayi0 = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '=', 'EDEL')
            ->where('a.lama', '!=', '0')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('COUNT(a.lama) as total_lama'))
            ->first();
        // end lama rawat bayi
        // End Lama Rawat = 0
        $los_dewasa = $lama_rawat_dewasa0->total_lama + $lama_rawat_dewasa->total_lama;
        $los_bayi = $lama_rawat_bayi0->total_lama + $lama_rawat_bayi->total_lama;

        // Start Jumlah Pasien Hidum+Mati
        // start Jml dewasa
        $jml_dewasa = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->first();
        // end Jml dewasa
        // start Jml bayi
        $jml_bayi = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->first();
        // end Jml bayi
        // End Jumlah Pasien Hidum+Mati

        // Start Jumlah Pasien Mati Seluruhnya
        // start Jml dewasa
        $jml_dewasa_mati = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->where('a.stts_pulang', '=', 'Meninggal')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->first();
        // end Jml dewasa
        // start Jml bayi
        $jml_bayi_mati = DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '=', 'EDEL')
            ->where('a.stts_pulang', '=', 'Meninggal')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->first();
        // end Jml bayi
        // End Jumlah Pasien Mati Seluruhnya

        // start Jumlah pasien mati > 2 hari
        // dewasa
        $pasienmatidewasalebih2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
            $query->select('a.no_rawat')
                ->from('kamar_inap as a')
                ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
                ->where('b.kd_bangsal', '!=', 'EDEL')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('b.kelas', $kelaskamar);
                })
                ->groupBy('a.no_rawat')
                ->havingRaw('SUM(CASE WHEN a.stts_pulang = "meninggal" THEN 1 ELSE 0 END) > 0')
                ->havingRaw('SUM(a.lama) > 2');
        }, 'subquery')
            ->count();
        // dewasa
        // bayi
        $pasienmatibayilebih2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
            $query->select('a.no_rawat')
                ->from('kamar_inap as a')
                ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
                ->where('b.kd_bangsal', '=', 'EDEL')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('b.kelas', $kelaskamar);
                })
                ->groupBy('a.no_rawat')
                ->havingRaw('SUM(CASE WHEN a.stts_pulang = "meninggal" THEN 1 ELSE 0 END) > 0')
                ->havingRaw('SUM(a.lama) > 2');
        }, 'subquery')
            ->count();
        // bayi
        // end Jumlah pasien mati > 2 hari

        // start Jumlah pasien mati < 2 hari
        // dewasa
        $pasienmatidewasakurang2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
            $query->select('a.no_rawat')
                ->from('kamar_inap as a')
                ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
                ->where('b.kd_bangsal', '!=', 'EDEL')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('b.kelas', $kelaskamar);
                })
                ->groupBy('a.no_rawat')
                ->havingRaw('SUM(CASE WHEN a.stts_pulang = "meninggal" THEN 1 ELSE 0 END) > 0')
                ->havingRaw('SUM(a.lama) <= 2');
        }, 'subquery')
            ->count();
        // dewasa
        // bayi
        $pasienmatibayikurang2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
            $query->select('a.no_rawat')
                ->from('kamar_inap as a')
                ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
                ->where('b.kd_bangsal', '=', 'EDEL')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('b.kelas', $kelaskamar);
                })
                ->groupBy('a.no_rawat')
                ->havingRaw('SUM(CASE WHEN a.stts_pulang = "meninggal" THEN 1 ELSE 0 END) > 0')
                ->havingRaw('SUM(a.lama) < 2');
        }, 'subquery')
            ->count();
        // bayi
        // end Jumlah pasien mati < 2 hari

        // start rumus BOR
        // Dewasa
        // Assuming $lama_rawat_dewasa is an object with a 'total_lama' property
        $totalLamaDewasa = $lama_rawat_dewasa->total_lama ?? 0;

        // Ensure $totalLamaDewasa is a numeric value
        $totalLamaDewasa = is_numeric($totalLamaDewasa) ? $totalLamaDewasa : 0;

        // Calculate the percentage
        if ($jmlkmrdewasa != 0 && $days != 0) {
            $bor_dewasa = ($totalLamaDewasa / ($jmlkmrdewasa * $days)) * 100;
            $formatdewasa = number_format($bor_dewasa, 2) . '%';
        } else {
            $formatdewasa = '0%'; // Atau penanganan lainnya sesuai kebutuhan aplikasi Anda
        }
        // Dewasa
        // Bayi
        // Assuming $lama_rawat_bayi is an object with a 'total_lama' property
        $totalLamabayi = $lama_rawat_bayi->total_lama ?? 0;

        // Ensure $totalLamabayi is a numeric value
        $totalLamaBayi = is_numeric($totalLamabayi) ? $totalLamabayi : 0;

        // Calculate the percentage
        if ($jmlkmrbayi != 0 && $days != 0) {
            $bor_bayi = ($totalLamaBayi / ($jmlkmrbayi * $days)) * 100;
            $formatbayi = number_format($bor_bayi, 2) . '%';
        } else {
            $formatbayi = '0%'; // Atau penanganan lainnya sesuai kebutuhan aplikasi Anda
        }
        // Bayi
        // end rumus BOR

        // start rumus ALOS & BTO
        // Dewasa
        $totalPasienDewasa = $jml_dewasa->total ?? 0;
        $totalPasienDewasa = is_numeric($totalPasienDewasa) ? $totalPasienDewasa : 0;
        // rumus alos
        // Memeriksa apakah $totalPasienDewasa tidak sama dengan nol sebelum melakukan pembagian
        if ($totalPasienDewasa != 0) {
            // Jika tidak sama dengan nol, hitung alos_dewasa
            $alos_dewasa = $los_dewasa / $totalPasienDewasa;
            $formatalosdewasa = number_format($alos_dewasa, 2);
        } else {
            // Jika $totalPasienDewasa sama dengan nol, tetapkan nilai default atau lakukan tindakan yang sesuai
            $formatalosdewasa = "0"; // Atau sesuaikan dengan tindakan yang Anda inginkan
        }
        // rumus BTO
        if ($jmlkmrdewasa != 0) {
            $bto_dewasa = $totalPasienDewasa / $jmlkmrdewasa;
            $formatbtodewasa = number_format($bto_dewasa, 2);
        } else {
            $formatbtodewasa = '0'; // Atau penanganan lainnya sesuai kebutuhan aplikasi Anda
        }
        // Dewasa
        // Bayi
        $totalPasienbayi = $jml_bayi->total ?? 0;
        $totalPasienbayi = is_numeric($totalPasienbayi) ? $totalPasienbayi : 0;
        // rumus alos
        // Memeriksa apakah $totalPasienbayi tidak sama dengan nol sebelum melakukan pembagian
        if ($totalPasienbayi != 0) {
            // Jika tidak sama dengan nol, hitung alos_bayi
            $alos_bayi = $los_bayi / $totalPasienbayi;
            $formatalosbayi = number_format($alos_bayi, 2);
        } else {
            // Jika $totalPasienbayi sama dengan nol, tetapkan nilai default atau lakukan tindakan yang sesuai
            $formatalosbayi = "0"; // Atau sesuaikan dengan tindakan yang Anda inginkan
        }
        // rumus BTO
        if ($jmlkmrbayi != 0) {
            $bto_bayi = $totalPasienbayi / $jmlkmrbayi;
            $formatbtobayi = number_format($bto_bayi, 2);
        } else {
            $formatbtobayi = '0'; // Atau penanganan lainnya sesuai kebutuhan aplikasi Anda
        }
        // Bayi        
        // end rumus ALOS & BTO

        // start rumus GDR
        // Dewasa  
        $totalMatiDewasa = $jml_dewasa_mati->total ?? 0;
        $totalMatiDewasa = is_numeric($totalMatiDewasa) ? $totalMatiDewasa : 0;
        // rumus
        if ($totalPasienDewasa != 0) {
            $gdr_dewasa = ($totalMatiDewasa / $totalPasienDewasa) * 1000;
            $formatgdrdewasa = number_format($gdr_dewasa, 2) . '%';
        } else {
            $formatgdrdewasa = '0 %';
        }
        // Dewasa
        // Bayi
        $totalMatibayi = $jml_bayi_mati->total ?? 0;
        $totalMatibayi = is_numeric($totalMatibayi) ? $totalMatibayi : 0;
        // rumus
        if ($totalPasienbayi != 0) {
            $gdr_bayi = ($totalMatibayi / $totalPasienbayi) * 1000;
            $formatgdrbayi = number_format($gdr_bayi, 2) . '%';
        } else {
            $formatgdrbayi = '0 %';
        }
        // Bayi
        // end rumus GDR

        // start rumus TOI
        // Dewasa
        if ($totalPasienDewasa != 0) {
            // Jika tidak sama dengan nol, hitung dewasa
            $toi_dewasa = (($jmlkmrdewasa * $days) - $totalLamaDewasa) / $totalPasienDewasa;
            $formattoisdewasa = number_format($toi_dewasa, 2);
        } else {
            // Jika $totalPasienDewasa sama dengan nol, tetapkan nilai default atau lakukan tindakan yang sesuai
            $formattoisdewasa = "0"; // Atau sesuaikan dengan tindakan yang Anda inginkan
        }
        // Dewasa
        // Bayi
        if ($totalPasienbayi != 0) {
            // Jika tidak sama dengan nol, hitung dewasa
            $toi_bayi = (($jmlkmrbayi * $days) - $totalLamabayi) / $totalPasienbayi;
            $formattoibayi = number_format($toi_bayi, 2);
        } else {
            // Jika $totalPasienDewasa sama dengan nol, tetapkan nilai default atau lakukan tindakan yang sesuai
            $formattoibayi = "0"; // Atau sesuaikan dengan tindakan yang Anda inginkan
        }
        // Bayi
        // end rumus TOI

        // start rumus NDR
        // Dewasa  
        if ($totalPasienDewasa != 0) {
            $ndr_dewasa = ($pasienmatidewasalebih2hari / $totalPasienDewasa) * 1000;
            $formatndrdewasa = number_format($ndr_dewasa, 2) . '%';
        } else {
            $formatndrdewasa = '0 %';
        }
        // Dewasa
        // Bayi
        if ($totalPasienbayi != 0) {
            $ndr_bayi = ($pasienmatibayilebih2hari / $totalPasienbayi) * 1000;
            $formatndrbayi = number_format($ndr_bayi, 2) . '%';
        } else {
            $formatndrbayi = '0 %';
        }
        // Bayi
        // end rumus NDR
        $pasienper1haridewasa = number_format($totalPasienDewasa / $days, 2);
        $pasienper1haribayi = number_format($totalPasienbayi / $days, 2);

        //start return

        return view('rm.kinerja.kinerja', [
            // untuk mengirim data dalam form
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'kelaskamar' => $kelaskamar,
            // end form
            'jmlpasien' => $chart->line2($dewasa, $bayi, $labelstat, $judul_line, $subjudul_line), //pemanggilan line chart
            'jmllamapasien' => $chart->line2($dewasa_lama, $bayi_lama, $labelstat_lama, $judul_line_lama, $subjudul_line_lama), //pemanggilan line chart
            'bor_dewasa' => $formatdewasa, //hasil bor dewasa
            'bor_bayi' => $formatbayi, //hasil bor bayi
            'alos_dewasa' =>  $formatalosdewasa, // Hasil Alos Dewasa
            'alos_bayi' => $formatalosbayi, // Hasil Alos Bayi
            'bto_dewasa' =>  $formatbtodewasa, // Hasil BTO Dewasa
            'bto_bayi' => $formatbtobayi, // Hasil BTO Bayi
            'gdr_dewasa' => $formatgdrdewasa, // Hasil Gdr Dewasa
            'gdr_bayi' => $formatgdrbayi, // Hasil Gdr Bayi
            'toi_dewasa' => $formattoisdewasa, // Hasil Toi Dewasa
            'toi_bayi' => $formattoibayi, // Hasil Toi Bayi
            'ndr_dewasa' => $formatndrdewasa, // Hasil NDR Dewasa
            'ndr_bayi' => $formatndrbayi, // Hasil NDR Bayi

            //parameter Perhitungan
            'pasienper1haridewasa' => $pasienper1haridewasa,
            'pasienper1haribayi' => $pasienper1haribayi,
            'jmldewasa' => $jml_dewasa,
            'jmlbayi' => $jml_bayi,
            'pasienmatidewasalebih2hari' => $pasienmatidewasalebih2hari,
            'pasienmatibayilebih2hari' => $pasienmatibayilebih2hari,
            'pasienmatidewasakurang2hari' => $pasienmatidewasakurang2hari,
            'pasienmatibayikurang2hari' => $pasienmatibayikurang2hari,
            'jmldewasamati' => $jml_dewasa_mati,
            'jmldewasamati' => $jml_dewasa_mati,
            'jmlbayimati' => $jml_bayi_mati,
            'jumlah_rawat_dewasa' => $lama_rawat_dewasa,
            'jumlah_rawat_bayi' => $lama_rawat_bayi,
            'los_dewasa' => $los_dewasa,
            'los_bayi' => $los_bayi,
            'jmlkamarbayi' => $jmlkmrbayi,
            'jmlkamardewasa' => $jmlkmrdewasa,
            'days' => $days,
        ]);
        //end return  
    }
    public function setjumlahbed(Request $request)
    {
        $bed_dewasa = $request->input('bed_dewasa');
        $bed_bayi = $request->input('bed_bayi');

        DB::table('jumlah_kamar')
            ->where('kamar', 'kamar_dewasa')
            ->update([
                'jumlah' => $bed_dewasa
            ]);
        DB::table('jumlah_kamar')
            ->where('kamar', 'kamar_bayi')
            ->update([
                'jumlah' => $bed_bayi
            ]);
        return redirect()->route('kinerja');
    }

    // line chart
    private function getChartData($tgl1, $tgl2, $kelaskamar)
    {
        return DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->groupBy(DB::raw('YEAR(a.tgl_keluar)'), DB::raw('MONTH(a.tgl_keluar)'))
            ->select(
                DB::raw('YEAR(a.tgl_keluar) as year'),
                DB::raw('MONTH(a.tgl_keluar) as month'),
                DB::raw('count(DISTINCT a.no_rawat) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    private function getChartData2($tgl1, $tgl2, $kelaskamar)
    {
        return DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->groupBy(DB::raw('YEAR(a.tgl_keluar)'), DB::raw('MONTH(a.tgl_keluar)'))
            ->select(
                DB::raw('YEAR(a.tgl_keluar) as year'),
                DB::raw('MONTH(a.tgl_keluar) as month'),
                DB::raw('sum(a.lama) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    private function getChartData3($tgl1, $tgl2, $kelaskamar)
    {
        return DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '!=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->groupBy(DB::raw('YEAR(a.tgl_keluar)'), DB::raw('MONTH(a.tgl_keluar)'))
            ->select(
                DB::raw('YEAR(a.tgl_keluar) as year'),
                DB::raw('MONTH(a.tgl_keluar) as month'),
                DB::raw('sum(a.lama) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }
    private function getChartData4($tgl1, $tgl2, $kelaskamar)
    {
        return DB::table('kamar_inap as a')
            ->join('kamar as b', 'b.kd_kamar', '=', 'a.kd_kamar')
            ->where('b.kd_bangsal', '=', 'EDEL')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->when($kelaskamar, function ($query) use ($kelaskamar) {
                return $query->where('b.kelas', $kelaskamar);
            })
            ->groupBy(DB::raw('YEAR(a.tgl_keluar)'), DB::raw('MONTH(a.tgl_keluar)'))
            ->select(
                DB::raw('YEAR(a.tgl_keluar) as year'),
                DB::raw('MONTH(a.tgl_keluar) as month'),
                DB::raw('sum(a.lama) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    // line chart
}
