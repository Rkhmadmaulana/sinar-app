<?php

namespace App\Http\Controllers;
// use ;
use Illuminate\Http\Request;
use App\Charts\Chart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KinerjaController extends Controller
{
    public function kinerja(Chart $chart,Request $request)
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
                $grafik_pasien_bayi = $this->getChartData2($tgl1, $tgl2,$kelaskamar);
                $grafik_pasien_dewasa = $this->getChartData($tgl1, $tgl2,$kelaskamar);

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
                $grafik_lama_dewasa = $this->getChartData3($tgl1, $tgl2,$kelaskamar);
                $grafik_lama_bayi = $this->getChartData4($tgl1, $tgl2,$kelaskamar);

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
                    ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                    ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                    ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien berumur 1900 hari atau lebih (dewasa)
                    ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                        return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]); // Filter tanggal keluar
                    })
                    ->when($kelaskamar, function ($query) use ($kelaskamar) {
                        return $query->where('a.kelas', $kelaskamar); // Filter kelas kamar (jika ada)
                    })
                    ->select(DB::raw('COUNT(a.lama) as total_lama')) // Hitung jumlah pasien yang memenuhi syarat
                    ->first();
                // end lama rawat dewasa
                // start lama rawat bayi
                    $lama_rawat_bayi = DB::table('kamar_inap as a')
                    ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                    ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                    ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Filter bayi dengan umur < 1900 hari
                    ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                        return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]); // Filter tanggal keluar
                    })
                    ->when($kelaskamar, function ($query) use ($kelaskamar) {
                        return $query->where('a.kelas', $kelaskamar); // Filter kelas kamar (jika ada)
                    })
                    ->select(DB::raw('COUNT(a.lama) as total_lama')) // Hitung jumlah pasien yang memenuhi syarat
                    ->first();
                // end lama rawat bayi
            // End Lama Rawat
            // Start Lama Rawat = 0
                    // start lama rawat dewasa
                    $lama_rawat_dewasa0 = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien berumur 1900 hari atau lebih (dewasa)
                ->where('a.lama', '!=', '0') // Lama rawat harus lebih dari 0
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]); // Filter tanggal keluar
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar); // Filter kelas kamar (jika ada)
                })
                ->select(DB::raw('COUNT(a.lama) as total_lama')) // Hitung jumlah pasien yang memenuhi syarat
                ->first();
                // end lama rawat dewasa
                // start lama rawat bayi
                $lama_rawat_bayi0 = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Filter bayi dengan umur < 1900 hari
                ->where('a.lama', '!=', '0') // Lama rawat harus lebih dari 0
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]); // Filter tanggal keluar
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar); // Filter kelas kamar (jika ada)
                })
                ->select(DB::raw('COUNT(a.lama) as total_lama')) // Hitung jumlah pasien yang memenuhi syarat
                ->first();
                // end lama rawat bayi
            // End Lama Rawat = 0
            $los_dewasa = $lama_rawat_dewasa0->total_lama + $lama_rawat_dewasa->total_lama; 
            $los_bayi = $lama_rawat_bayi0->total_lama + $lama_rawat_bayi->total_lama ;
            
            // Start Jumlah Pasien Hidup+Mati
                // start Jml dewasa
                $jml_dewasa = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien dewasa
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
                })
                ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
                ->first();
                // end Jml dewasa
                // start Jml bayi
                $jml_bayi = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
                })
                ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
                ->first();
                // end Jml bayi
            // End Jumlah Pasien Hidum+Mati

            // Start Jumlah Pasien Mati Seluruhnya
                // start Jml dewasa
                $jml_dewasa_mati = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien dewasa
                ->where('a.stts_pulang', '=', 'Meninggal')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
                })
                ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
                ->first();
                // end Jml dewasa
                // start Jml bayi
                $jml_bayi_mati = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                ->where('a.stts_pulang', '=', 'Meninggal')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
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
                        ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                        ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                        ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien dewasa
                        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                            return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                        })
                        ->when($kelaskamar, function ($query) use ($kelaskamar) {
                            return $query->where('a.kelas', $kelaskamar);
                        })
                        ->groupBy('a.no_rawat')
                        ->havingRaw('SUM(CASE WHEN a.stts_pulang = "Meninggal" THEN 1 ELSE 0 END) > 0')
                        ->havingRaw('SUM(a.lama) > 2');
                }, 'subquery')->count();
                // dewasa
                // bayi
                $pasienmatibayilebih2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
                    $query->select('a.no_rawat')
                        ->from('kamar_inap as a')
                        ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                        ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                        ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                            return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                        })
                        ->when($kelaskamar, function ($query) use ($kelaskamar) {
                            return $query->where('a.kelas', $kelaskamar);
                        })
                        ->groupBy('a.no_rawat')
                        ->havingRaw('SUM(CASE WHEN a.stts_pulang = "Meninggal" THEN 1 ELSE 0 END) > 0')
                        ->havingRaw('SUM(a.lama) > 2');
                }, 'subquery')->count();
                // bayi
            // end Jumlah pasien mati > 2 hari
            
            // start Jumlah pasien mati < 2 hari
                // dewasa
                $pasienmatidewasakurang2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
                    $query->select('a.no_rawat')
                        ->from('kamar_inap as a')
                        ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                        ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                        ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien dewasa
                        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                            return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                        })
                        ->when($kelaskamar, function ($query) use ($kelaskamar) {
                            return $query->where('a.kelas', $kelaskamar);
                        })
                        ->groupBy('a.no_rawat')
                        ->havingRaw('SUM(CASE WHEN a.stts_pulang = "Meninggal" THEN 1 ELSE 0 END) > 0')
                        ->havingRaw('SUM(a.lama) <= 2');
                }, 'subquery')->count();
                // dewasa
                // bayi
                $pasienmatibayikurang2hari = DB::table(function ($query) use ($tgl1, $tgl2, $kelaskamar) {
                    $query->select('a.no_rawat')
                        ->from('kamar_inap as a')
                        ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                        ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                        ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                        ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                            return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                        })
                        ->when($kelaskamar, function ($query) use ($kelaskamar) {
                            return $query->where('a.kelas', $kelaskamar);
                        })
                        ->groupBy('a.no_rawat')
                        ->havingRaw('SUM(CASE WHEN a.stts_pulang = "Meninggal" THEN 1 ELSE 0 END) > 0')
                        ->havingRaw('SUM(a.lama) < 2');
                }, 'subquery')->count();
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
                    $gdr_dewasa = ($totalMatiDewasa / $totalPasienDewasa ) * 1000;
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
                    $gdr_bayi = ($totalMatibayi / $totalPasienbayi ) * 1000;
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
                        $toi_dewasa = (($jmlkmrdewasa * $days) - $totalLamaDewasa) / $totalPasienDewasa ;
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
                    $ndr_dewasa = ($pasienmatidewasalebih2hari / $totalPasienDewasa ) * 1000;
                    $formatndrdewasa = number_format($ndr_dewasa, 2) . '%';
                    } else {
                    $formatndrdewasa = '0 %';
                    }
                // Dewasa
                // Bayi
                    if ($totalPasienbayi != 0) {
                    $ndr_bayi = ($pasienmatibayilebih2hari / $totalPasienbayi ) * 1000;
                    $formatndrbayi = number_format($ndr_bayi, 2) . '%';
                    } else {
                    $formatndrbayi = '0 %';
                    }
                // Bayi
            // end rumus NDR
            $pasienper1haridewasa = number_format($totalPasienDewasa / $days, 2);
            $pasienper1haribayi = number_format($totalPasienbayi / $days, 2);

            // START GAWIANKU

        $bangsalList = [
            'RB001' => 'kakap',
            'RB002' => 'terakulu',
            'RB003' => 'balleraja',
            'RB004' => 'lobster',
            'RB005' => 'tenggiri',
            'RB006' => 'kerapu',
            'RB007' => 'barunang',
            'RB008' => 'lumbaLumba',
            'RB009' => 'selasar',
            'RB011' => 'isolasi',
            'B0018' => 'picu',
            'RB012' => 'inkubator',
            'RB013' => 'box',
            'RB014' => 'infant'
        ];

        $kelasList = ['Kelas 1', 'Kelas 2', 'Kelas 3'];

        $totalKamar = [];


        $queryTempatTidur = DB::table('kamar')
            ->select('kd_bangsal', 'kelas', DB::raw('COUNT(*) as jumlah'))
            ->where('statusdata', '1')
            ->groupBy('kd_bangsal', 'kelas')
            ->orderBy('kd_bangsal')
            ->orderBy('kelas')
            ->get();

        // Inisialisasi array hasil
        $tempatTidur = [];
        $totalTempatTidur = 0;

        foreach ($bangsalList as $kd_bangsal => $alias) {
            $total = $queryTempatTidur->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
            $tempatTidur[$alias] = $total;
            $totalTempatTidur += $total;

            foreach ($kelasList as $kelas) {
                $filtered = $queryTempatTidur->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                $jumlah = $filtered ? $filtered->jumlah : 0;
                $tempatTidur[$alias . str_replace(' ', '', $kelas)] = $jumlah;
            }
        }

        // Total semua bangsal
        //$tempatTidur['total'] = $totalTempatTidur;
        $tempatTidur['total'] = 116;

        // Total per kelas seluruh bangsal
        foreach ($kelasList as $kelas) {
            $sumKelas = $queryTempatTidur->where('kelas', $kelas)->sum('jumlah');
            $tempatTidur['total' . str_replace(' ', '', $kelas)] = $sumKelas;
        }

        // 🔧 OVERRIDE MANUAL
        $overrideBeds = [
            'kerapu' => 20,
            'kerapuKelas1' => 6,
            'kerapuKelas2' => 6,
            'kerapuKelas3' => 8,
            'kakap' => 28,
            'kakapKelas1' => 3,
            'kakapKelas2' => 3,
            'kakapKelas3' => 22,
            'terakulu' => 18,
            'terakuluKelas1' => 3,
            'terakuluKelas2' => 4,
            'terakuluKelas3' => 11,
            'balleraja' => 19,
            'ballerajaKelas1' => 3,
            'ballerajaKelas2' => 8,
            'ballerajaKelas3' => 8,
            'tenggiri' => 10,
            'barunang' => 4,
            'lobster' => 8,
            'lumbaLumba' => 9,
        ];

        foreach ($overrideBeds as $key => $value) {
            $tempatTidur[$key] = $value;
        }


        // Query pasien awal per bangsal (unik per pasien)
        $queryAwalTotal = DB::table('kamar_inap as ki')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->where('ki.tgl_masuk', '<', $formattedTgl1)
            ->where(function ($q) use ($formattedTgl1) {
                $q->whereNull('ki.tgl_keluar')
                    ->orWhere('ki.tgl_keluar', '>=', $formattedTgl1);
            })
            ->select('b.kd_bangsal', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
            ->groupBy('b.kd_bangsal')
            ->get();

        // Query pasien awal per kelas (fix posisi terakhir per pasien)
        $subquery = DB::table('kamar_inap as ki2')
            ->select(DB::raw('MAX(ki2.tgl_masuk) as max_masuk'), 'ki2.no_rawat')
            ->where('ki2.tgl_masuk', '<', $formattedTgl1)
            ->groupBy('ki2.no_rawat');

        $queryAwalKelas = DB::table('kamar_inap as ki')
            ->joinSub($subquery, 'last_ki', function ($join) {
                $join->on('ki.no_rawat', '=', 'last_ki.no_rawat')
                    ->on('ki.tgl_masuk', '=', 'last_ki.max_masuk');
            })
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->where(function ($q) use ($formattedTgl1) {
                $q->whereNull('ki.tgl_keluar')
                    ->orWhere('ki.tgl_keluar', '>=', $formattedTgl1);
            })
            ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('b.kd_bangsal', 'k.kelas')
            ->get();

        // Inisialisasi array hasil
        $pasienAwal = [];
        $totalAwal = 0;

        // Loop per bangsal
        foreach ($bangsalList as $kd_bangsal => $alias) {
            // Ambil total dari query pertama (unik no_rawat)
            $jumlah = $queryAwalTotal->where('kd_bangsal', $kd_bangsal)->first()->jumlah ?? 0;
            $pasienAwal[$alias] = $jumlah;
            $totalAwal += $jumlah;

            // Loop per kelas
            foreach ($kelasList as $kelas) {
                $filtered = $queryAwalKelas
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->first();
                $jumlahKelas = $filtered ? $filtered->jumlah : 0;

                $pasienAwal[$alias . str_replace(' ', '', $kelas)] = $jumlahKelas;
            }
        }

        // Total akhir
        $pasienAwal['total'] = $totalAwal;

        // Total per kelas (semua bangsal)
        foreach ($kelasList as $kelas) {
            $kelasTotal = $queryAwalKelas->where('kelas', $kelas)->sum('jumlah');
            $pasienAwal['total' . str_replace(' ', '', $kelas)] = $kelasTotal;
        }

        // Ambil tgl_masuk pertama pasien dalam periode (subquery)
        $subMasuk = DB::table('kamar_inap as ki')
        ->select(DB::raw('MIN(ki.tgl_masuk) as tgl_masuk'), 'ki.no_rawat')
        ->whereBetween('ki.tgl_masuk', [$formattedTgl1, $formattedTgl2])
        ->groupBy('ki.no_rawat');

        // Query pasien masuk per bangsal (unik per pasien) — termasuk jenis kelamin
        $queryMasukTotal = DB::table('kamar_inap as ki')
        ->joinSub($subMasuk, 'sub', function ($join) {
            $join->on('ki.no_rawat', '=', 'sub.no_rawat')
                ->on('ki.tgl_masuk', '=', 'sub.tgl_masuk');
        })
        ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
        ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
        ->select('b.kd_bangsal', 'p.jk', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
        ->groupBy('b.kd_bangsal', 'p.jk')
        ->get(); // Collection of rows: kd_bangsal, jk, jumlah

        // Query pasien masuk per kelas dan bangsal (unik per pasien) — termasuk jenis kelamin
        $queryMasukKelas = DB::table('kamar_inap as ki')
        ->joinSub($subMasuk, 'sub', function ($join) {
            $join->on('ki.no_rawat', '=', 'sub.no_rawat')
                ->on('ki.tgl_masuk', '=', 'sub.tgl_masuk');
        })
        ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
        ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
        ->select('b.kd_bangsal', 'k.kelas', 'p.jk', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
        ->groupBy('b.kd_bangsal', 'k.kelas', 'p.jk')
        ->get(); // Collection of rows: kd_bangsal, kelas, jk, jumlah

        // Inisialisasi array hasil
        $pasienMasuk = [];
        $totalMasukL = 0;
        $totalMasukP = 0;

        // Loop bangsal
        foreach ($bangsalList as $kd_bangsal => $alias) {
        // Laki-laki untuk bangsal ini
        $rowL = $queryMasukTotal->where('kd_bangsal', $kd_bangsal)->where('jk', 'L')->first();
        $jumlahL = $rowL ? (int)$rowL->jumlah : 0;
        $pasienMasuk[$alias . '_L'] = $jumlahL;
        $totalMasukL += $jumlahL;

        // Perempuan untuk bangsal ini
        $rowP = $queryMasukTotal->where('kd_bangsal', $kd_bangsal)->where('jk', 'P')->first();
        $jumlahP = $rowP ? (int)$rowP->jumlah : 0;
        $pasienMasuk[$alias . '_P'] = $jumlahP;
        $totalMasukP += $jumlahP;

        // Loop per kelas
        foreach ($kelasList as $kelas) {
            $filteredL = $queryMasukKelas
                ->where('kd_bangsal', $kd_bangsal)
                ->where('kelas', $kelas)
                ->where('jk', 'L')
                ->first();
            $jumlahKelasL = $filteredL ? (int)$filteredL->jumlah : 0;
            $pasienMasuk[$alias . str_replace(' ', '', $kelas) . '_L'] = $jumlahKelasL;

            $filteredP = $queryMasukKelas
                ->where('kd_bangsal', $kd_bangsal)
                ->where('kelas', $kelas)
                ->where('jk', 'P')
                ->first();
            $jumlahKelasP = $filteredP ? (int)$filteredP->jumlah : 0;
            $pasienMasuk[$alias . str_replace(' ', '', $kelas) . '_P'] = $jumlahKelasP;
        }
        }

        // Total akhir per gender
        $pasienMasuk['total_L'] = $totalMasukL;
        $pasienMasuk['total_P'] = $totalMasukP;

        // Total per kelas (semua bangsal) per gender
        foreach ($kelasList as $kelas) {
        $kelasTotalL = $queryMasukKelas->where('kelas', $kelas)->where('jk', 'L')->sum('jumlah');
        $pasienMasuk['total' . str_replace(' ', '', $kelas) . '_L'] = (int)$kelasTotalL;

        $kelasTotalP = $queryMasukKelas->where('kelas', $kelas)->where('jk', 'P')->sum('jumlah');
        $pasienMasuk['total' . str_replace(' ', '', $kelas) . '_P'] = (int)$kelasTotalP;
        }

        // (Optional) Total keseluruhan gabungan L+P
        $pasienMasuk['total'] = ($pasienMasuk['total_L'] ?? 0) + ($pasienMasuk['total_P'] ?? 0);



       // Ambil rawat inap dengan lebih dari satu entri (pasien yang pindah ruangan)
       $pasienPindahanRawat = DB::table('kamar_inap')
       ->select('no_rawat')
       ->groupBy('no_rawat')
       ->havingRaw('COUNT(*) > 1');

        // Ambil hanya entri kedua, ketiga, dst. dari pasien pindahan (bukan entri pertama)
        $subPindahan = DB::table('kamar_inap as ki')
            ->select('ki.no_rawat', 'ki.tgl_masuk', DB::raw('ROW_NUMBER() OVER (PARTITION BY ki.no_rawat ORDER BY ki.tgl_masuk) as rn'))
            ->whereBetween('ki.tgl_masuk', [$formattedTgl1, $formattedTgl2]);

        $subPindahan = DB::table(DB::raw("({$subPindahan->toSql()}) as sub"))
            ->mergeBindings($subPindahan)
            ->where('sub.rn', '>', 1);

        // Query data pindahan (masuk ke kamar baru) per bangsal dan kelas
        $queryPindahan = DB::table('kamar_inap as ki')
            ->joinSub($subPindahan, 'sub', function ($join) {
                $join->on('ki.no_rawat', '=', 'sub.no_rawat')
                    ->on('ki.tgl_masuk', '=', 'sub.tgl_masuk');
            })
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('b.kd_bangsal', 'k.kelas')
            ->get();

        // Inisialisasi hasil akhir
        $pasienPindahan = [];
        $totalPindahan = 0;

        // Hitung per bangsal dan kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {
            $jumlah = $queryPindahan->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
            $pasienPindahan[$alias] = $jumlah;
            $totalPindahan += $jumlah;

            foreach ($kelasList as $kelas) {
                $filtered = $queryPindahan->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                $jumlahKelas = $filtered ? $filtered->jumlah : 0;
                $pasienPindahan[$alias . str_replace(' ', '', $kelas)] = $jumlahKelas;
            }
        }

        // Total semua bangsal
        $pasienPindahan['total'] = $totalPindahan;

        // Total per kelas untuk semua bangsal
        foreach ($kelasList as $kelas) {
            $sumKelas = $queryPindahan->where('kelas', $kelas)->sum('jumlah');
            $pasienPindahan['total' . str_replace(' ', '', $kelas)] = $sumKelas;
        }


        // Subquery: cari tgl_masuk terakhir (kamar akhir)
        $subqueryLastKamar = DB::table('kamar_inap')
            ->select(DB::raw('MAX(tgl_masuk)'))
            ->whereColumn('no_rawat', 'ki.no_rawat');

        // Ambil semua entri keluar karena pindah (bukan entri terakhir)
        $queryPindahKeluar = DB::table('kamar_inap as ki')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
            ->whereIn('ki.no_rawat', $pasienPindahanRawat)
            ->where('ki.tgl_masuk', '<', $subqueryLastKamar)   // hanya entri sebelum kamar terakhir
            ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(*) as jumlah'))  // ← tanpa DISTINCT
            ->groupBy('b.kd_bangsal', 'k.kelas')
            ->get();

        // Inisialisasi array hasil
        $pasienKeluarPindahan = [];
        $totalKeluarPindah = 0;

        foreach ($bangsalList as $kd_bangsal => $alias) {
            $jumlah = $queryPindahKeluar->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
            $pasienKeluarPindahan[$alias] = $jumlah;
            $totalKeluarPindah += $jumlah;

            foreach ($kelasList as $kelas) {
                $filtered = $queryPindahKeluar
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas',      $kelas)
                    ->first();
                $pasienKeluarPindahan[$alias . str_replace(' ', '', $kelas)] =
                    $filtered ? $filtered->jumlah : 0;
            }
        }

        // Total akhir (per entry)
        $pasienKeluarPindahan['total'] = $totalKeluarPindah;
        // Total per kelas (semua bangsal)
        foreach ($kelasList as $kelas) {
            $pasienKeluarPindahan['total' . str_replace(' ', '', $kelas)] =
                $queryPindahKeluar->where('kelas', $kelas)->sum('jumlah');
        }

        // Query pasien keluar hidup (entri terakhir per no_rawat) dengan jenis kelamin
        $queryKeluarHidup = DB::table('kamar_inap as ki')
        ->join(DB::raw("(
            SELECT no_rawat, MAX(tgl_keluar) AS max_tgl_keluar
            FROM kamar_inap
            WHERE tgl_keluar BETWEEN '$formattedTgl1' AND '$formattedTgl2'
            GROUP BY no_rawat
        ) AS last_rawat"), function ($join) {
            $join->on('ki.no_rawat', '=', 'last_rawat.no_rawat')
                ->on('ki.tgl_keluar', '=', 'last_rawat.max_tgl_keluar');
        })
        ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
        ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
        ->whereIn('ki.stts_pulang', [
            'Sehat',
            'Membaik',
            'APS',
            'Atas Permintaan Sendiri',
            'Atas Persetujuan Dokter'
        ])
        ->whereIn('b.kd_bangsal', array_keys($bangsalList)) // 🔥 INI PERUBAHANNYA
        ->select(
            'b.kd_bangsal',
            'k.kelas',
            'p.jk',
            DB::raw('COUNT(DISTINCT ki.no_rawat) AS jumlah')
        )
        ->groupBy('b.kd_bangsal', 'k.kelas', 'p.jk')
        ->get();

        // ==============================
        // === PROSES ARRAY HASIL =======
        // ==============================
        // Inisialisasi array hasil
        $pasienKeluarHidup = [];
        $totalKeluarHidup = 0;

        // Hitung total per bangsal dan kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {

            // Total semua jenis kelamin di bangsal
            $total = $queryKeluarHidup->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
            $pasienKeluarHidup[$alias] = $total;

            // Total per jenis kelamin di bangsal
            $totalL = $queryKeluarHidup->where('kd_bangsal', $kd_bangsal)->where('jk', 'L')->sum('jumlah');
            $totalP = $queryKeluarHidup->where('kd_bangsal', $kd_bangsal)->where('jk', 'P')->sum('jumlah');

            $pasienKeluarHidup["{$alias}_L"] = $totalL;
            $pasienKeluarHidup["{$alias}_P"] = $totalP;

            // Per kelas
            foreach ($kelasList as $kelas) {
                $filtered = $queryKeluarHidup
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas);

                $jumlah = $filtered->sum('jumlah');
                $pasienKeluarHidup[$alias . str_replace(' ', '', $kelas)] = $jumlah;

                // Per kelas + jenis kelamin
                $jumlahL = $filtered->where('jk', 'L')->sum('jumlah');
                $jumlahP = $filtered->where('jk', 'P')->sum('jumlah');

                $keyBase = $alias . str_replace(' ', '', $kelas);

                $pasienKeluarHidup["{$keyBase}_L"] = $jumlahL;
                $pasienKeluarHidup["{$keyBase}_P"] = $jumlahP;
            }
        }

        // Total keseluruhan
        $pasienKeluarHidup['total'] = $totalKeluarHidup;

        // Total per kelas semua bangsal
        foreach ($kelasList as $kelas) {
            $sumKelas = $queryKeluarHidup->where('kelas', $kelas)->sum('jumlah');
            $pasienKeluarHidup['total' . str_replace(' ', '', $kelas)] = $sumKelas;

            // total kelas per JK
            $sumL = $queryKeluarHidup->where('kelas', $kelas)->where('jk', 'L')->sum('jumlah');
            $sumP = $queryKeluarHidup->where('kelas', $kelas)->where('jk', 'P')->sum('jumlah');

            $keyBase = 'total' . str_replace(' ', '', $kelas);

            $pasienKeluarHidup["{$keyBase}_L"] = $sumL;
            $pasienKeluarHidup["{$keyBase}_P"] = $sumP;
        }


        // Total keseluruhan (L + P)
        $pasienKeluarHidup['total'] = $totalKeluarHidup;

        // Total per kelas semua bangsal (L + P)
        foreach ($kelasList as $kelas) {
            foreach (['L', 'P'] as $jk) {

                $total = $queryKeluarHidup
                    ->where('kelas', $kelas)
                    ->where('jk', $jk)
                    ->sum('jumlah');

                // contoh: totalKelas1_L, totalKelas1_P
                $suffix = str_replace(' ', '', $kelas) . '_' . $jk;

                $pasienKeluarHidup['total' . $suffix] = $total;
            }
        }


        $pasienKeluarHidup['total_L'] = $queryKeluarHidup->where('jk', 'L')->sum('jumlah');
        $pasienKeluarHidup['total_P'] = $queryKeluarHidup->where('jk', 'P')->sum('jumlah');

        $pasienKeluarHidup['total'] =
            $pasienKeluarHidup['total_L'] + $pasienKeluarHidup['total_P'];

        // Query pasien pulang tidak standar (APS, Pulang Paksa) berdasarkan entri terakhir per no_rawat
        $queryPulangTidakStandar = DB::table('kamar_inap as ki')
        ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
        ->join(DB::raw("(
            SELECT no_rawat, MAX(tgl_keluar) as max_tgl_keluar
            FROM kamar_inap
            WHERE tgl_keluar BETWEEN '$formattedTgl1' AND '$formattedTgl2'
            GROUP BY no_rawat
        ) as last_rawat"), function ($join) {
            $join->on('ki.no_rawat', '=', 'last_rawat.no_rawat')
                ->on('ki.tgl_keluar', '=', 'last_rawat.max_tgl_keluar');
        })
        ->whereIn('ki.stts_pulang', [
            'APS',
            'Atas Permintaan Sendiri',
            'Pulang Paksa'
        ])
        ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
        ->groupBy('b.kd_bangsal', 'k.kelas')
        ->get();

        // Inisialisasi array hasil
        $pasienPulangTidakStandar = [];
        $totalPulangTidakStandar = 0;

        // Hitung total per bangsal dan kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {
            $total = $queryPulangTidakStandar->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
            $pasienPulangTidakStandar[$alias] = $total;
            $totalPulangTidakStandar += $total;

            foreach ($kelasList as $kelas) {
                $filtered = $queryPulangTidakStandar->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                $jumlah = $filtered ? $filtered->jumlah : 0;
                $pasienPulangTidakStandar[$alias . str_replace(' ', '', $kelas)] = $jumlah;
            }
        }

        // Total keseluruhan
        $pasienPulangTidakStandar['total'] = $totalPulangTidakStandar;

        // Total per kelas semua bangsal
        foreach ($kelasList as $kelas) {
            $sumKelas = $queryPulangTidakStandar->where('kelas', $kelas)->sum('jumlah');
            $pasienPulangTidakStandar['total' . str_replace(' ', '', $kelas)] = $sumKelas;
        }

        // PULANG HARI SAMA
        $queryPulangHariSama = DB::table('kamar_inap as ki')
        ->join('kamar as k',      'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b',    'k.kd_bangsal','=', 'b.kd_bangsal')
        // Join subquery entri terakhir per no_rawat (hindari duplikasi)
        ->join(DB::raw("(
            SELECT no_rawat, MAX(tgl_keluar) as max_tgl_keluar
            FROM kamar_inap
            WHERE tgl_keluar BETWEEN '$formattedTgl1' AND '$formattedTgl2'
            GROUP BY no_rawat
        ) as last_rawat"), function ($join) {
            $join->on('ki.no_rawat',   '=', 'last_rawat.no_rawat')
                ->on('ki.tgl_keluar', '=', 'last_rawat.max_tgl_keluar');
        })
        // Kondisi: keluar di hari yang sama ATAU kurang dari 24 jam sejak masuk
        ->where(function($q) {
            $q->whereRaw('DATE(ki.tgl_masuk) = DATE(ki.tgl_keluar)')
            ->orWhereRaw('TIMESTAMPDIFF(HOUR, ki.tgl_masuk, ki.tgl_keluar) < 24');
        })
        ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
        ->groupBy('b.kd_bangsal', 'k.kelas')
        ->get();

        $pasienPulangHariSama = [];
        $totalPulangHariSama = 0;

        foreach ($bangsalList as $kd_bangsal => $alias) {
            $subtotal = $queryPulangHariSama
                ->where('kd_bangsal', $kd_bangsal)
                ->sum('jumlah');
            $pasienPulangHariSama[$alias] = $subtotal;
            $totalPulangHariSama += $subtotal;

            foreach ($kelasList as $kelas) {
                $row = $queryPulangHariSama
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->first();
                $pasienPulangHariSama[$alias . str_replace(' ', '', $kelas)] = $row ? $row->jumlah : 0;
            }
        }

        $pasienPulangHariSama['total'] = $totalPulangHariSama;

        foreach ($kelasList as $kelas) {
            $sumKelas = $queryPulangHariSama->where('kelas', $kelas)->sum('jumlah');
            $pasienPulangHariSama['total' . str_replace(' ', '', $kelas)] = $sumKelas;
        }



        // Subquery: ambil tgl_keluar kematian pertama < 48 jam per pasien
        $subqueryMeninggal48 = DB::table('kamar_inap as ki2')
        ->select(
            'ki2.no_rawat',
            DB::raw('MAX(ki2.tgl_keluar) as min_tgl_keluar') // ← nama kolom tetap
        )
        ->whereBetween('ki2.tgl_keluar', [$formattedTgl1, $formattedTgl2])
        ->whereRaw('LOWER(ki2.stts_pulang) = "meninggal"')
        ->groupBy('ki2.no_rawat')
        ->havingRaw("
            TIMESTAMPDIFF(
                HOUR,
                MIN(CONCAT(ki2.tgl_masuk, ' ', ki2.jam_masuk)),
                MAX(CONCAT(ki2.tgl_keluar, ' ', ki2.jam_keluar))
            ) < 48
        ");

        $queryMeninggal48Jam = DB::table('kamar_inap as ki')
        ->joinSub($subqueryMeninggal48, 'kematian', function ($join) {
            $join->on('ki.no_rawat', '=', 'kematian.no_rawat')
                ->on('ki.tgl_keluar', '=', 'kematian.min_tgl_keluar');
        })
        ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
        ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
        ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2]) // 🔒 KUNCI BULAN
        ->whereRaw('LOWER(ki.stts_pulang) = "meninggal"')
        ->whereIn('b.kd_bangsal', array_keys($bangsalList))
        ->select(
            'b.kd_bangsal',
            'k.kelas',
            'p.jk',
            DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah')
        )
        ->groupBy('b.kd_bangsal', 'k.kelas', 'p.jk')
        ->get();

        // Inisialisasi array
        $pasienMeninggal48Jam = [];
        $totalMeninggal48 = 0;

        // Loop bangsal
        foreach ($bangsalList as $kd_bangsal => $alias) {

        // Total laki + perempuan
        $totalL = $queryMeninggal48Jam->where('kd_bangsal', $kd_bangsal)->where('jk','L')->sum('jumlah');
        $totalP = $queryMeninggal48Jam->where('kd_bangsal', $kd_bangsal)->where('jk','P')->sum('jumlah');

        $pasienMeninggal48Jam[$alias . "_L"] = $totalL;
        $pasienMeninggal48Jam[$alias . "_P"] = $totalP;

        $totalMeninggal48 += ($totalL + $totalP);

        // Per kelas
        foreach ($kelasList as $kelas) {

            $jumlahL = $queryMeninggal48Jam
                ->where('kd_bangsal', $kd_bangsal)
                ->where('kelas', $kelas)
                ->where('jk','L')
                ->sum('jumlah');

            $jumlahP = $queryMeninggal48Jam
                ->where('kd_bangsal', $kd_bangsal)
                ->where('kelas', $kelas)
                ->where('jk','P')
                ->sum('jumlah');

            $keyBase = $alias . str_replace(' ', '', $kelas); // contoh: kerapuKelas1

            $pasienMeninggal48Jam[$keyBase . "_L"] = $jumlahL;
            $pasienMeninggal48Jam[$keyBase . "_P"] = $jumlahP;
        }
        }

        // Total keseluruhan
        $pasienMeninggal48Jam['total_L'] = $queryMeninggal48Jam->where('jk','L')->sum('jumlah');
        $pasienMeninggal48Jam['total_P'] = $queryMeninggal48Jam->where('jk','P')->sum('jumlah');
        $pasienMeninggal48Jam['total']   = $pasienMeninggal48Jam['total_L'] + $pasienMeninggal48Jam['total_P'];

        // Total kelas (semua bangsal)
        foreach ($kelasList as $kelas) {

        $kelasL = $queryMeninggal48Jam->where('kelas',$kelas)->where('jk','L')->sum('jumlah');
        $kelasP = $queryMeninggal48Jam->where('kelas',$kelas)->where('jk','P')->sum('jumlah');

        $key = 'total' . str_replace(' ', '', $kelas);

        $pasienMeninggal48Jam[$key . "_L"] = $kelasL;
        $pasienMeninggal48Jam[$key . "_P"] = $kelasP;
        }


        // Subquery: cari tgl_keluar kematian pertama ≥ 48 jam per pasien
        $subqueryMeninggal48Plus = DB::table('kamar_inap as ki2')
        ->select(
            'ki2.no_rawat',
            DB::raw('MAX(ki2.tgl_keluar) as min_tgl_keluar') // nama kolom disamakan
        )
        ->whereBetween('ki2.tgl_keluar', [$formattedTgl1, $formattedTgl2])
        ->whereRaw('LOWER(ki2.stts_pulang) = "meninggal"')
        ->groupBy('ki2.no_rawat')
        ->havingRaw("
            TIMESTAMPDIFF(
                HOUR,
                MIN(CONCAT(ki2.tgl_masuk, ' ', ki2.jam_masuk)),
                MAX(CONCAT(ki2.tgl_keluar, ' ', ki2.jam_keluar))
            ) >= 48
        ");


        // Query utama: join ke subquery kematianPlus
        $queryMeninggal48plus = DB::table('kamar_inap as ki')
        ->joinSub($subqueryMeninggal48Plus, 'kematian', function ($join) {
            $join->on('ki.no_rawat', '=', 'kematian.no_rawat')
                ->on('ki.tgl_keluar', '=', 'kematian.min_tgl_keluar');
        })
        ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
        ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
        ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
        ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
        ->whereRaw('LOWER(ki.stts_pulang) = "meninggal"')
        ->whereIn('b.kd_bangsal', array_keys($bangsalList))
        ->select(
            'b.kd_bangsal',
            'k.kelas',
            'p.jk',
            DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah')
        )
        ->groupBy('b.kd_bangsal', 'k.kelas', 'p.jk')
        ->get();

        // Inisialisasi SEKALI
        $pasienMeninggal48plus = [];

        // =====================
        // LOOP PER BANGSAL
        // =====================
        foreach ($bangsalList as $kd_bangsal => $alias) {

            // ---- total per bangsal ----
            $totalL = $queryMeninggal48plus
                ->where('kd_bangsal', $kd_bangsal)
                ->where('jk', 'L')
                ->sum('jumlah');

            $totalP = $queryMeninggal48plus
                ->where('kd_bangsal', $kd_bangsal)
                ->where('jk', 'P')
                ->sum('jumlah');

            $pasienMeninggal48plus[$alias . '_L'] = $totalL;
            $pasienMeninggal48plus[$alias . '_P'] = $totalP;

            // ---- per kelas per bangsal ----
            foreach ($kelasList as $kelas) {

                $keyBase = $alias . str_replace(' ', '', $kelas);

                $jumlahL = $queryMeninggal48plus
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->where('jk', 'L')
                    ->sum('jumlah');

                $jumlahP = $queryMeninggal48plus
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->where('jk', 'P')
                    ->sum('jumlah');

                $pasienMeninggal48plus[$keyBase . '_L'] = $jumlahL;
                $pasienMeninggal48plus[$keyBase . '_P'] = $jumlahP;
            }
        }

            $pasienMeninggal48plus['total_L'] =
            $queryMeninggal48plus->where('jk', 'L')->sum('jumlah');
        
            $pasienMeninggal48plus['total_P'] =
                $queryMeninggal48plus->where('jk', 'P')->sum('jumlah');
            
            $pasienMeninggal48plus['total'] =
                $pasienMeninggal48plus['total_L']
                + $pasienMeninggal48plus['total_P'];

        // Meninggal total (1y6m till changes)
        $queryMeninggalTotal = DB::table('kamar_inap as ki')
            ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
            ->whereRaw('LOWER(ki.stts_pulang) = "meninggal"')
            ->select('b.kd_bangsal', 'k.kelas', 'p.jk', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
            ->groupBy('b.kd_bangsal', 'k.kelas', 'p.jk')
            ->get();

            

            

        // Proses hasil ke array
        $pasienMeninggalTotal = [];
        $totalMeninggal = 0;

        foreach ($bangsalList as $kd_bangsal => $alias) {

            // Total L dan P per bangsal
            $totalL = $queryMeninggalTotal
                ->where('kd_bangsal', $kd_bangsal)
                ->where('jk', 'L')
                ->sum('jumlah');

            $totalP = $queryMeninggalTotal
                ->where('kd_bangsal', $kd_bangsal)
                ->where('jk', 'P')
                ->sum('jumlah');

            $pasienMeninggalTotal[$alias . '_L'] = $totalL;
            $pasienMeninggalTotal[$alias . '_P'] = $totalP;

            $totalMeninggal += ($totalL + $totalP);

            // Per kelas → dipisah L & P
            foreach ($kelasList as $kelas) {

                $jumlahL = $queryMeninggalTotal
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->where('jk', 'L')
                    ->sum('jumlah');

                $jumlahP = $queryMeninggalTotal
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->where('jk', 'P')
                    ->sum('jumlah');

                $keyBase = $alias . str_replace(' ', '', $kelas);

                $pasienMeninggalTotal[$keyBase . '_L'] = $jumlahL;
                $pasienMeninggalTotal[$keyBase . '_P'] = $jumlahP;
            }
        }

        // Total semua bangsal
        $pasienMeninggalTotal['total_L'] = $queryMeninggalTotal->where('jk', 'L')->sum('jumlah');
        $pasienMeninggalTotal['total_P'] = $queryMeninggalTotal->where('jk', 'P')->sum('jumlah');
        $pasienMeninggalTotal['total']   = $pasienMeninggalTotal['total_L'] + $pasienMeninggalTotal['total_P'];

        // Total per kelas semua bangsal
        foreach ($kelasList as $kelas) {
            $key = 'total' . str_replace(' ', '', $kelas);

            $pasienMeninggalTotal[$key . '_L'] = $queryMeninggalTotal->where('kelas', $kelas)->where('jk','L')->sum('jumlah');
            $pasienMeninggalTotal[$key . '_P'] = $queryMeninggalTotal->where('kelas', $kelas)->where('jk','P')->sum('jumlah');
        }


        $allowedStatus = [
            'Sehat',
            'Membaik',
            'APS',
            'Atas Permintaan Sendiri',
            'Atas Persetujuan Dokter',
            'Meninggal'
        ];
        
        // Query utama, distinct no_rawat + lama rawat minimal 1 hari
        $queryLamaDirawat = DB::table('kamar_inap as ki')
            ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->select(
                'ki.no_rawat',
                'b.kd_bangsal',
                'k.kelas',
                DB::raw('MIN(ki.tgl_masuk) as tgl_masuk'),
                DB::raw('MAX(ki.tgl_keluar) as tgl_keluar')
            )
            ->whereIn('ki.stts_pulang', $allowedStatus)
            ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
            ->groupBy('ki.no_rawat', 'b.kd_bangsal', 'k.kelas')
            ->get();

        $lamaDirawat = [];
        $totalLamaDirawat = 0;

        foreach ($bangsalList as $kd_bangsal => $alias) {
            $totalBangsal = 0;

            foreach ($kelasList as $kelas) {
                $jumlah = 0;

                $filtered = $queryLamaDirawat
                    ->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas);

                foreach ($filtered as $row) {
                    $masuk = \Carbon\Carbon::parse($row->tgl_masuk);
                    $keluar = \Carbon\Carbon::parse($row->tgl_keluar);
                    $hariRawat = max($masuk->diffInDays($keluar), 1); // GREATEST(…, 1)
                    $jumlah += $hariRawat;
                }

                $key = $alias . str_replace(' ', '', $kelas);
                $lamaDirawat[$key] = $jumlah;
                $totalBangsal += $jumlah;

                // Total per kelas seluruh bangsal
                $kelasKey = 'total' . str_replace(' ', '', $kelas);
                $lamaDirawat[$kelasKey] = ($lamaDirawat[$kelasKey] ?? 0) + $jumlah;
            }

            $lamaDirawat[$alias] = $totalBangsal;
            $totalLamaDirawat += $totalBangsal;
        }

        $lamaDirawat['total'] = $totalLamaDirawat;


        // Inisialisasi array sisa pasien
        $sisaPasien = [
            'total' => 0
        ];

        // Inisialisasi total per kelas
        foreach ($kelasList as $kelas) {
            $kelasKey = 'total' . str_replace(' ', '', $kelas);
            $sisaPasien[$kelasKey] = 0;
        }

        // Hitung sisa pasien per bangsal dan per kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {
            $totalPerBangsal = 0;

            // Hitung per kelas
            foreach ($kelasList as $kelas) {
                $keySuffix = str_replace(' ', '', $kelas);
                $key = $alias . $keySuffix;

                $awal          = $pasienAwal[$key] ?? 0;
                $masuk         = ($pasienMasuk["{$alias}_L"] ?? 0) + ($pasienMasuk["{$alias}_P"] ?? 0);
                $pindahan      = $pasienPindahan[$key] ?? 0;
                $keluarPindah  = $pasienKeluarPindahan[$key] ?? 0;
                $keluarHidup   = ($pasienKeluarHidup["{$alias}_L"] ?? 0) + ($pasienKeluarHidup["{$alias}_P"] ?? 0);
                $meninggal     = ($pasienMeninggalTotal["{$key}_L"] ?? 0) + ($pasienMeninggalTotal["{$key}_P"] ?? 0);

                // Hitung sisa pasien untuk bangsal+kelas ini
                $sisa = ($awal + $masuk + $pindahan) - ($keluarPindah + $keluarHidup + $meninggal);
                $sisaPasien[$key] = $sisa;
                $totalPerBangsal += $sisa;

                // Tambah ke total per kelas
                $kelasKey = 'total' . $keySuffix;
                $sisaPasien[$kelasKey] += $sisa;
            }

            // Hitung total per bangsal (semua kelas)
            $totalSemua = 0;
            $keyTotalBangsal = $alias; // tanpa suffix kelas
            
            // Ambil data untuk bangsal tanpa kelas (jika ada)
            $awal          = $pasienAwal[$keyTotalBangsal] ?? 0;
            $masuk         = ($pasienMasuk["{$alias}_L"] ?? 0) + ($pasienMasuk["{$alias}_P"] ?? 0);
            $pindahan      = $pasienPindahan[$keyTotalBangsal] ?? 0;
            $keluarPindah  = $pasienKeluarPindahan[$keyTotalBangsal] ?? 0;
            $keluarHidup   = ($pasienKeluarHidup["{$alias}_L"] ?? 0) + ($pasienKeluarHidup["{$alias}_P"] ?? 0);
            $meninggal     = ($pasienMeninggalTotal["{$key}_L"] ?? 0) + ($pasienMeninggalTotal["{$key}_P"] ?? 0);

            $totalSemua    = ($awal + $masuk + $pindahan) - ($keluarPindah + $keluarHidup + $meninggal);
            
            // Set total per bangsal
            $sisaPasien[$alias] = $totalSemua;
            
            // Tambah ke grand total
            $sisaPasien['total'] += $totalSemua;
        }



        // Query untuk menghitung hari perawatan dari database asli
        $queryHariPerawatan = DB::table('kamar_inap as ki')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->select(
                'b.kd_bangsal',
                'k.kelas',
                DB::raw("
                    SUM(
                        DATEDIFF(
                            LEAST(COALESCE(ki.tgl_keluar, '$formattedTgl2'), '$formattedTgl2'),
                            GREATEST(ki.tgl_masuk, '$formattedTgl1')
                        ) + 1
                    ) as jumlah_hari
                ")
            )
            ->where(function ($query) use ($formattedTgl1, $formattedTgl2) {
                $query->where('ki.tgl_masuk', '<=', $formattedTgl2)
                    ->where(function ($q) use ($formattedTgl1) {
                        $q->whereNull('ki.tgl_keluar')
                            ->orWhere('ki.tgl_keluar', '>=', $formattedTgl1);
                    });
            })
            ->groupBy('b.kd_bangsal', 'k.kelas')
            ->get();

        // Inisialisasi array hari perawatan
        $hariPerawatan = [
            'total' => 0
        ];

        // Inisialisasi total per kelas
        foreach ($kelasList as $kelas) {
            $kelasKey = 'total' . str_replace(' ', '', $kelas);
            $hariPerawatan[$kelasKey] = 0;
        }

        // Proses data hasil query
        foreach ($bangsalList as $kd_bangsal => $alias) {
            $totalPerBangsal = 0;

            foreach ($kelasList as $kelas) {
                // Cari data dari hasil query
                $filtered = $queryHariPerawatan->where('kd_bangsal', $kd_bangsal)
                    ->where('kelas', $kelas)
                    ->first();

                $jumlah = $filtered ? (int) $filtered->jumlah_hari : 0;
                
                // Set data per bangsal per kelas
                $key = $alias . str_replace(' ', '', $kelas);
                $hariPerawatan[$key] = $jumlah;
                $totalPerBangsal += $jumlah;

                // Tambah ke total per kelas
                $kelasKey = 'total' . str_replace(' ', '', $kelas);
                $hariPerawatan[$kelasKey] += $jumlah;
            }

            // Set total per bangsal
            $hariPerawatan[$alias] = $totalPerBangsal;
            
            // Tambah ke grand total
            $hariPerawatan['total'] += $totalPerBangsal;
        }
          


        // Hitung jumlah hari dalam rentang tanggal
        $jumlahHari = Carbon::parse($formattedTgl2)->diffInDays(Carbon::parse($formattedTgl1)) + 1;

        $bor = [];

        // Perhitungan BOR% per bangsal dan kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $key = $alias . $keySuffix;

                $bed = $tempatTidur[$key] ?? 0;
                $hariRawat = $hariPerawatan[$key] ?? 0;

                $bor[$key] = ($bed > 0 && $jumlahHari > 0)
                    ? round(($hariRawat / ($bed * $jumlahHari)) * 100, 2)
                    : 0;
            }
        }

        // Perhitungan total BOR per kelas dan keseluruhan
        $totalHariRawatSemua = 0;
        $totalBedSemua = 0;

        foreach (array_merge([''], $kelasList) as $kelas) {
            $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
            $totalHariRawat = 0;
            $totalBed = 0;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                $key = $alias . $keySuffix;
                $totalHariRawat += $hariPerawatan[$key] ?? 0;
                $totalBed += $tempatTidur[$key] ?? 0;
            }

            $bor['total' . $keySuffix] = ($totalBed > 0 && $jumlahHari > 0)
                ? round(($totalHariRawat / ($totalBed * $jumlahHari)) * 100, 2)
                : 0;

            $totalHariRawatSemua += $totalHariRawat;
            $totalBedSemua += $totalBed;
        }

        $bor['total'] = ($totalBedSemua > 0 && $jumlahHari > 0)
            ? round(($totalHariRawatSemua / ($totalBedSemua * $jumlahHari)) * 100, 2)
            : 0;


            $los = [];

            // 1) LOS per bangsal + kelas
            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key       = $alias . $keySuffix;
            
                    // Numerator: total hari rawat unik per pasien
                    $hariRawat     = $lamaDirawat[$key] ?? 0;
                    // Denominator: pasien keluar hidup + meninggal
                    $keluarHidup   = $pasienKeluarHidup[$key] ?? 0;
                    $meninggalTotal= $pasienMeninggalTotal[$key] ?? 0;
                    $denominator   = $keluarHidup + $meninggalTotal;
            
                    $los[$key] = $denominator > 0
                        ? round($hariRawat / $denominator, 2)
                        : 0;
                }
            }
            
            // 2) LOS total keseluruhan
            $totalHariRawatAll = array_sum($lamaDirawat);
            $totalKeluarAll    = array_sum($pasienKeluarHidup) + array_sum($pasienMeninggalTotal);
            
            $los['total'] = $totalKeluarAll > 0
                ? round($totalHariRawatAll / $totalKeluarAll, 2)
                : 0;


        // Hitung BTO
        $bto = [];

        $totalKeluarAll   = 0;
        $totalTempatTidur = 0;

        // 1) BTO per bangsal + kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {
            foreach (array_merge([''], $kelasList) as $kelas) {

                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $key       = $alias . $keySuffix;

                // Numerator: total pasien keluar hidup + meninggal
                $jumlahKeluar = ($pasienKeluarHidup[$key] ?? 0) + ($pasienMeninggalTotal[$key] ?? 0);

                // Denominator: jumlah tempat tidur
                $jumlahTempatTidur = $tempatTidur[$key] ?? 0;

                // Rumus BTO
                $bto[$key] = $jumlahTempatTidur > 0
                    ? round($jumlahKeluar / $jumlahTempatTidur, 2)
                    : 0;

                // Akumulasi total global
                $totalKeluarAll   += $jumlahKeluar;
                $totalTempatTidur += $jumlahTempatTidur;
            }
        }

        // 2) Total tanpa filter kelas (grand total)
        $bto['total'] = $totalTempatTidur > 0
            ? round($totalKeluarAll / $totalTempatTidur, 2)
            : 0;


        // Hitung TOI
        $toi = [];

        $jumlahHari = \Carbon\Carbon::parse($tgl2)->diffInDays(\Carbon\Carbon::parse($tgl1)) + 1;

        $totalTT = 0;
        $totalHP = 0;
        $totalKeluar = 0;

        // 1) TOI per bangsal + kelas
        foreach ($bangsalList as $kd_bangsal => $alias) {
            foreach (array_merge([''], $kelasList) as $kelas) {

                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $key       = $alias . $keySuffix;

                $tt      = $tempatTidur[$key] ?? 0;
                $hp      = $hariPerawatan[$key] ?? 0;
                $keluar  = ($pasienKeluarHidup[$key] ?? 0) + ($pasienMeninggalTotal[$key] ?? 0);

                // Rumus TOI
                $numerator = ($tt * $jumlahHari) - $hp;

                $toi[$key] = $keluar > 0
                    ? round($numerator / $keluar, 2)
                    : 0;

                // Akumulasi total global
                $totalTT     += $tt;
                $totalHP     += $hp;
                $totalKeluar += $keluar;
            }
        }

        // 2) Total global (tanpa filter)
        $numeratorTotal = ($totalTT * $jumlahHari) - $totalHP;

        $toi['total'] = $totalKeluar > 0
            ? round($numeratorTotal / $totalKeluar, 2)
            : 0;


        // Hitung NDR (Net Death Rate)
        $ndr = [];

        foreach ($bangsalList as $kd_bangsal => $alias) {
            foreach (array_merge([''], $kelasList) as $kelas) {
                foreach (['L', 'P'] as $jk) {

                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix . "_" . $jk;

                    $meninggal48 = $pasienMeninggal48plus[$key] ?? 0;
                    $totalMeninggal = $pasienMeninggalTotal[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;

                    $denominator = $keluarHidup + $totalMeninggal;

                    $ndr[$key] = $denominator > 0
                        ? round(($meninggal48 / $denominator) * 1000, 2)
                        : 0;
                }
            }
        }

        // Hitung total L & total P (Global Tanpa kelas)
        foreach (['L','P'] as $jk) {
            $meninggal48plus = $pasienMeninggal48plus["total_$jk"] ?? 0;
            $totalMeninggal = $pasienMeninggalTotal["total_$jk"] ?? 0;
            $keluarHidup    = $pasienKeluarHidup["total_$jk"] ?? 0;
        
            $denominator = $keluarHidup + $totalMeninggal;
        
            $ndr["total_$jk"] = $denominator > 0
                ? round(($meninggal48plus / $denominator) * 1000, 2)
                : 0;
        }

        // Total NDR dihitung global (bukan menjumlahkan per ruangan)
        $meninggal48plus = $pasienMeninggal48plus['total'] ?? 0;
        $totalMeninggal = $pasienMeninggalTotal['total'] ?? 0;
        $keluarHidup    = $pasienKeluarHidup['total'] ?? 0;

        $denominator = $keluarHidup + $totalMeninggal;

        $ndr['total'] = $denominator > 0
            ? round(($meninggal48plus / $denominator) * 1000, 2)
            : 0;



        // Hitung GDR (Gross Death Rate)
        $gdr = [];

        foreach ($bangsalList as $kd_bangsal => $alias) {
            foreach (array_merge([''], $kelasList) as $kelas) {
                foreach (['L', 'P'] as $jk) {

                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix . "_" . $jk;

                    $meninggalTotal = $pasienMeninggalTotal[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;

                    $denominator = $keluarHidup + $meninggalTotal;

                    $gdr[$key] = $denominator > 0
                        ? round(($meninggalTotal / $denominator) * 1000, 2)
                        : 0;
                }
            }
        }

        // Total global (L dan P digabung)
        $totalMeninggal = array_sum($pasienMeninggalTotal);
        $totalKeluarHidup = array_sum($pasienKeluarHidup);
        $totalDenominator = $totalMeninggal + $totalKeluarHidup;

        $gdr['total'] = $totalDenominator > 0
            ? round(($totalMeninggal / $totalDenominator) * 1000, 2)
            : 0;

        // Per kelas total semua bangsal
        foreach ($kelasList as $kelas) {
            $keySuffix = str_replace(' ', '', $kelas);

            $totalMeninggalKelas = 0;
            $totalKeluarHidupKelas = 0;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (['L', 'P'] as $jk) {
                    $key = $alias . $keySuffix . "_" . $jk;
                    $totalMeninggalKelas += $pasienMeninggalTotal[$key] ?? 0;
                    $totalKeluarHidupKelas += $pasienKeluarHidup[$key] ?? 0;
                }
            }

            $totalKelasDenominator = $totalMeninggalKelas + $totalKeluarHidupKelas;

            $gdr['total' . $keySuffix] = $totalKelasDenominator > 0
                ? round(($totalMeninggalKelas / $totalKelasDenominator) * 1000, 2)
                : 0;
        }


        // Total global (L dan P digabung)
        $totalMeninggal = array_sum($pasienMeninggalTotal);
        $totalKeluarHidup = array_sum($pasienKeluarHidup);
        $totalDenominator = $totalMeninggal + $totalKeluarHidup;

        $gdr['total'] = $totalDenominator > 0
            ? round(($totalMeninggal / $totalDenominator) * 1000, 2)
            : 0;

        // Per kelas total semua bangsal
        foreach ($kelasList as $kelas) {
            $keySuffix = str_replace(' ', '', $kelas);

            $totalMeninggalKelas = 0;
            $totalKeluarHidupKelas = 0;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (['L', 'P'] as $jk) {

                    $key = $alias . $keySuffix . "_" . $jk;

                    $totalMeninggalKelas += $pasienMeninggalTotal[$key] ?? 0;
                    $totalKeluarHidupKelas += $pasienKeluarHidup[$key] ?? 0;
                }
            }

            $totalKelasDenominator = $totalMeninggalKelas + $totalKeluarHidupKelas;

            $gdr['total' . $keySuffix] = $totalKelasDenominator > 0
                ? round(($totalMeninggalKelas / $totalKelasDenominator) * 1000, 2)
                : 0;
        }

        // =====================
        // Total Global Per Jenis Kelamin (L & P)
        // =====================
        foreach (['L', 'P'] as $jk) {

            $totalMeninggalJK = 0;
            $totalKeluarHidupJK = 0;

            // loop semua key pasien meninggal
            foreach ($pasienMeninggalTotal as $key => $value) {
                if (str_ends_with($key, "_$jk")) {
                    $totalMeninggalJK += $value;
                }
            }

            // loop semua key pasien keluar hidup
            foreach ($pasienKeluarHidup as $key => $value) {
                if (str_ends_with($key, "_$jk")) {
                    $totalKeluarHidupJK += $value;
                }
            }

            $denominatorJK = $totalMeninggalJK + $totalKeluarHidupJK;

            // hasil akhir: total_L dan total_P
            $gdr["total_$jk"] = $denominatorJK > 0
                ? round(($totalMeninggalJK / $denominatorJK) * 1000, 2)
                : 0;
        }


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

            'tempatTidur' => $tempatTidur,
            'pasienAwal' => $pasienAwal,
            'pasienMasuk' => $pasienMasuk,
            'pasienPindahan' => $pasienPindahan,
            'pasienKeluarPindahan' => $pasienKeluarPindahan,
            'pasienKeluarHidup' => $pasienKeluarHidup,
            'pasienPulangTidakStandar' => $pasienPulangTidakStandar,
            'pasienPulangHariSama' => $pasienPulangHariSama,
            'pasienMeninggal48Jam' => $pasienMeninggal48Jam,
            'pasienMeninggal48plus' => $pasienMeninggal48plus,
            'pasienMeninggalTotal' => $pasienMeninggalTotal,
            'lamaDirawat' => $lamaDirawat,
            'sisaPasien' => $sisaPasien,


            'hariPerawatan' => $hariPerawatan,
            'bor' => $bor,
            'los' => $los,
            'bto' => $bto,
            'toi' => $toi,
            'ndr' => $ndr,
            'gdr' => $gdr,
        ]);
        //end return  
    }
    
    
    // END GAWIANKU

    
    public function setjumlahbed(Request $request)
    {   
        $bed_dewasa = $request->input('bed_dewasa');
        $bed_bayi = $request->input('bed_bayi');
        
        DB::table('jumlah_kamar')
        ->where('kamar','kamar_dewasa')
        ->update([
            'jumlah' => $bed_dewasa   
        ]);
        DB::table('jumlah_kamar')
        ->where('kamar','kamar_bayi')
        ->update([
            'jumlah' => $bed_bayi   
        ]);
        return redirect()->route('kinerja');
    }

    // line chart
        private function getChartData($tgl1, $tgl2,$kelaskamar)
        {
            return DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien dewasa
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
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
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
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
        
        private function getChartData3($tgl1, $tgl2, $kelaskamar)
        {
            return DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) >= 1900') // Hanya pasien dewasa
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
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
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat') // JOIN ke reg_periksa
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis') // JOIN ke pasien
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->when($kelaskamar, function ($query) use ($kelaskamar) {
                    return $query->where('a.kelas', $kelaskamar);
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