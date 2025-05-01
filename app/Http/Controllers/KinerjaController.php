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
                'RB006' => 'kerapu',
                'RB001' => 'kakap',
                'RB002' => 'terakulu',
                'RB003' => 'balleraja',
                'RB004' => 'lobster',
                'RB005' => 'tenggiri',
                'RB007' => 'barunang',
                'RB008' => 'lumbaLumba'
            ];
            
            $kelasList = ['Kelas 1', 'Kelas 2', 'Kelas 3'];
            
            $totalKamar = [];


            $queryTempatTidur = DB::table('kamar as k')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(k.kd_kamar) as jumlah'))
                ->groupBy('b.kd_bangsal', 'k.kelas')
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
            $tempatTidur['total'] = $totalTempatTidur;

            // Total per kelas seluruh bangsal
            foreach ($kelasList as $kelas) {
                $sumKelas = $queryTempatTidur->where('kelas', $kelas)->sum('jumlah');
                $tempatTidur['total' . str_replace(' ', '', $kelas)] = $sumKelas;
            }


            // Query pasien awal per bangsal (unik per pasien)
            $queryAwalTotal = DB::table('kamar_inap as ki')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->where('ki.tgl_masuk', '<', $formattedTgl1)
                ->where(function($q) use ($formattedTgl1) {
                    $q->whereNull('ki.tgl_keluar')
                    ->orWhere('ki.tgl_keluar', '>=', $formattedTgl1);
                })
                ->select('b.kd_bangsal', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
                ->groupBy('b.kd_bangsal')
                ->get();

            // Query pasien awal per kelas
            $queryAwalKelas = DB::table('kamar_inap as ki')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->where('ki.tgl_masuk', '<', $formattedTgl1)
            ->where(function($q) use ($formattedTgl1) {
                $q->whereNull('ki.tgl_keluar')
                ->orWhere('ki.tgl_keluar', '>=', $formattedTgl1);
            })
            ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
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
            
           // Query pasien masuk per bangsal (unik per pasien)
            $queryMasukTotal = DB::table('kamar_inap as ki')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_masuk', [$formattedTgl1, $formattedTgl2])
                ->select('b.kd_bangsal', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
                ->groupBy('b.kd_bangsal')
                ->get();

            // Query pasien masuk per kelas dan bangsal (unik per pasien)
            $queryMasukKelas = DB::table('kamar_inap as ki')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_masuk', [$formattedTgl1, $formattedTgl2])
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
                ->groupBy('b.kd_bangsal', 'k.kelas')
                ->get();

            // Inisialisasi array hasil
            $pasienMasuk = [];
            $totalMasuk = 0;

            // Loop bangsal
            foreach ($bangsalList as $kd_bangsal => $alias) {
                // Ambil total dari query pertama (unik per pasien)
                $jumlah = $queryMasukTotal->where('kd_bangsal', $kd_bangsal)->first()->jumlah ?? 0;
                $pasienMasuk[$alias] = $jumlah;
                $totalMasuk += $jumlah;

                // Loop per kelas
                foreach ($kelasList as $kelas) {
                    $filtered = $queryMasukKelas
                        ->where('kd_bangsal', $kd_bangsal)
                        ->where('kelas', $kelas)
                        ->first();
                    $jumlahKelas = $filtered ? $filtered->jumlah : 0;

                    $pasienMasuk[$alias . str_replace(' ', '', $kelas)] = $jumlahKelas;
                }
            }

            // Total akhir
            $pasienMasuk['total'] = $totalMasuk;

            // Total per kelas (semua bangsal)
            foreach ($kelasList as $kelas) {
                $kelasTotal = $queryMasukKelas->where('kelas', $kelas)->sum('jumlah');
                $pasienMasuk['total' . str_replace(' ', '', $kelas)] = $kelasTotal;
            }



            // Ambil pasien yang memiliki entri kamar_inap lebih dari 1 (pindahan)
            $pasienPindahanRawat = DB::table('kamar_inap')
                ->select('no_rawat')
                ->groupBy('no_rawat')
                ->havingRaw('COUNT(*) > 1');

            // Ambil data pindahan per bangsal & kelas, unik per pasien
            $queryPindahan = DB::table('kamar_inap as ki')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_masuk', [$formattedTgl1, $formattedTgl2])
                ->whereIn('ki.no_rawat', $pasienPindahanRawat)
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
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

            
            // Ambil data pasien keluar karena pindah kamar
            $queryPindahKeluar = DB::table('kamar_inap as ki')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
                ->whereIn('ki.no_rawat', $pasienPindahanRawat) // hanya pasien yang memang pindah
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat, ki.kd_kamar) as jumlah')) // per kamar keluar
                ->groupBy('b.kd_bangsal', 'k.kelas')
                ->get();

            // Inisialisasi array pasien keluar pindahan
            $pasienKeluarPindahan = [];
            $totalKeluarPindah = 0;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                $total = $queryPindahKeluar->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
                $pasienKeluarPindahan[$alias] = $total;
                $totalKeluarPindah += $total;

                foreach ($kelasList as $kelas) {
                    $filtered = $queryPindahKeluar->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                    $jumlah = $filtered ? $filtered->jumlah : 0;
                    $pasienKeluarPindahan[$alias . str_replace(' ', '', $kelas)] = $jumlah;
                }
            }

            $pasienKeluarPindahan['total'] = $totalKeluarPindah;

            foreach ($kelasList as $kelas) {
                $sumKelas = $queryPindahKeluar->where('kelas', $kelas)->sum('jumlah');
                $pasienKeluarPindahan['total' . str_replace(' ', '', $kelas)] = $sumKelas;
            }


            
           
            $queryKeluarHidup = DB::table('kamar_inap as ki')
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
                'Sehat', 'Membaik', 'APS',
                'Atas Permintaan Sendiri', 'Atas Persetujuan Dokter'
            ])
            ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
            ->groupBy('b.kd_bangsal', 'k.kelas')
            ->get();

            $pasienKeluarHidup = [];
            $totalKeluarHidup = 0;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                $total = $queryKeluarHidup->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
                $pasienKeluarHidup[$alias] = $total;
                $totalKeluarHidup += $total;

                foreach ($kelasList as $kelas) {
                    $filtered = $queryKeluarHidup->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                    $jumlah = $filtered ? $filtered->jumlah : 0;
                    $pasienKeluarHidup[$alias . str_replace(' ', '', $kelas)] = $jumlah;
                }
            }

            $pasienKeluarHidup['total'] = $totalKeluarHidup;

            foreach ($kelasList as $kelas) {
                $sumKelas = $queryKeluarHidup->where('kelas', $kelas)->sum('jumlah');
                $pasienKeluarHidup['total' . str_replace(' ', '', $kelas)] = $sumKelas;
            }

             
            $queryMeninggal48Jam = DB::table('kamar_inap as ki')
                ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
                ->where('ki.stts_pulang', 'Meninggal')
                ->whereRaw("TIMESTAMPDIFF(HOUR, CONCAT(ki.tgl_masuk, ' ', rp.jam_reg), CONCAT(ki.tgl_keluar, ' 00:05:00')) < 48")
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
                ->groupBy('b.kd_bangsal', 'k.kelas')
                ->get();

            // Inisialisasi array
            $pasienMeninggal48Jam = [];
            $totalMeninggal48 = 0;

            // Total per bangsal dan kelas
            foreach ($bangsalList as $kd_bangsal => $alias) {
                $total = $queryMeninggal48Jam->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
                $pasienMeninggal48Jam[$alias] = $total;
                $totalMeninggal48 += $total;

                foreach ($kelasList as $kelas) {
                    $filtered = $queryMeninggal48Jam->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                    $jumlah = $filtered ? $filtered->jumlah : 0;
                    $pasienMeninggal48Jam[$alias . str_replace(' ', '', $kelas)] = $jumlah;
                }
            }

            // Total keseluruhan semua bangsal
            $pasienMeninggal48Jam['total'] = $totalMeninggal48;

            // Total per kelas (semua bangsal)
            foreach ($kelasList as $kelas) {
                $sumKelas = $queryMeninggal48Jam->where('kelas', $kelas)->sum('jumlah');
                $pasienMeninggal48Jam['total' . str_replace(' ', '', $kelas)] = $sumKelas;
            }

            
            $queryMeninggal48plus = DB::table('kamar_inap as ki')
                ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
                ->whereRaw('LOWER(ki.stts_pulang) = "meninggal"')
                ->whereRaw("TIMESTAMPDIFF(HOUR, CONCAT(ki.tgl_masuk, ' ', rp.jam_reg), CONCAT(ki.tgl_keluar, ' 00:05:00')) >= 48")
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
                ->groupBy('b.kd_bangsal', 'k.kelas')
                ->get();

            // Inisialisasi array hasil
            $pasienMeninggal48plus = [];
            $totalMeninggal48Plus = 0;

            // Hitung total per bangsal dan kelas
            foreach ($bangsalList as $kd_bangsal => $alias) {
                $total = $queryMeninggal48plus->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
                $pasienMeninggal48plus[$alias] = $total;
                $totalMeninggal48Plus += $total;

                foreach ($kelasList as $kelas) {
                    $filtered = $queryMeninggal48plus->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                    $jumlah = $filtered ? $filtered->jumlah : 0;
                    $pasienMeninggal48plus[$alias . str_replace(' ', '', $kelas)] = $jumlah;
                }
            }

            // Total seluruh bangsal
            $pasienMeninggal48plus['total'] = $totalMeninggal48Plus;

            // Total per kelas semua bangsal
            foreach ($kelasList as $kelas) {
                $sumKelas = $queryMeninggal48plus->where('kelas', $kelas)->sum('jumlah');
                $pasienMeninggal48plus['total' . str_replace(' ', '', $kelas)] = $sumKelas;
            }


            $queryMeninggalTotal = DB::table('kamar_inap as ki')
                ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
                ->whereBetween('ki.tgl_keluar', [$formattedTgl1, $formattedTgl2])
                ->whereRaw('LOWER(ki.stts_pulang) = "meninggal"')
                ->select('b.kd_bangsal', 'k.kelas', DB::raw('COUNT(DISTINCT ki.no_rawat) as jumlah'))
                ->groupBy('b.kd_bangsal', 'k.kelas')
                ->get();

            // Proses hasil ke array
            $pasienMeninggalTotal = [];
            $totalMeninggal = 0;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                $total = $queryMeninggalTotal->where('kd_bangsal', $kd_bangsal)->sum('jumlah');
                $pasienMeninggalTotal[$alias] = $total;
                $totalMeninggal += $total;

                foreach ($kelasList as $kelas) {
                    $filtered = $queryMeninggalTotal->where('kd_bangsal', $kd_bangsal)->where('kelas', $kelas)->first();
                    $jumlah = $filtered ? $filtered->jumlah : 0;
                    $pasienMeninggalTotal[$alias . str_replace(' ', '', $kelas)] = $jumlah;
                }
            }

            // Total semua bangsal
            $pasienMeninggalTotal['total'] = $totalMeninggal;

            // Total per kelas seluruh bangsal
            foreach ($kelasList as $kelas) {
                $sumKelas = $queryMeninggalTotal->where('kelas', $kelas)->sum('jumlah');
                $pasienMeninggalTotal['total' . str_replace(' ', '', $kelas)] = $sumKelas;
            }

            
            $allowedStatus = [
                'Sehat', 'Membaik', 'APS', 'Atas Permintaan Sendiri',
                'Atas Persetujuan Dokter', 'Meninggal'
            ];
            
            // Query utama, distinct no_rawat
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
                    $jumlah = $queryLamaDirawat
                        ->where('kd_bangsal', $kd_bangsal)
                        ->where('kelas', $kelas)
                        ->count(); // Jumlah pasien unik per bangsal + kelas
            
                    $lamaDirawat[$alias . str_replace(' ', '', $kelas)] = $jumlah;
                    $totalBangsal += $jumlah;
            
                    // Total per kelas di seluruh bangsal
                    $lamaDirawat['total' . str_replace(' ', '', $kelas)] = 
                        ($lamaDirawat['total' . str_replace(' ', '', $kelas)] ?? 0) + $jumlah;
                }
            
                $lamaDirawat[$alias] = $totalBangsal;
                $totalLamaDirawat += $totalBangsal;
            }
            
            $lamaDirawat['total'] = $totalLamaDirawat;




            $sisaPasien = [];

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $awal = $pasienAwal[$key] ?? 0;
                    $masuk = $pasienMasuk[$key] ?? 0;
                    $pindahan = $pasienPindahan[$key] ?? 0;
                    $keluarPindah = $pasienKeluarPindahan[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;
                    $meninggal = $pasienMeninggalTotal[$key] ?? 0;

                    $sisaPasien[$key] = ($awal + $masuk + $pindahan) - ($keluarPindah + $keluarHidup + $meninggal);
                }
            }

            // Total keseluruhan
            $sisaPasien['total'] = 0;
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $totalPerKelas = 0;

                foreach ($bangsalList as $kd_bangsal => $alias) {
                    $key = $alias . $keySuffix;
                    $totalPerKelas += $sisaPasien[$key] ?? 0;
                }

                $sisaPasien['total' . $keySuffix] = $totalPerKelas;
                $sisaPasien['total'] += $totalPerKelas;
            }




            $hariPerawatan = [];

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $awal = $pasienAwal[$key] ?? 0;
                    $masuk = $pasienMasuk[$key] ?? 0;
                    $pindahan = $pasienPindahan[$key] ?? 0;
                    $keluarPindah = $pasienKeluarPindahan[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;
                    $meninggal = $pasienMeninggalTotal[$key] ?? 0;

                    $hariPerawatan[$key] = ($awal + $masuk + $pindahan) - ($keluarPindah + $keluarHidup + $meninggal);
                }
            }


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

            $hariPerawatan = [
                'total' => 0
            ];
            
            foreach ($bangsalList as $kd_bangsal => $alias) {
                $totalPerBangsal = 0;
            
                foreach ($kelasList as $kelas) {
                    $filtered = $queryHariPerawatan->where('kd_bangsal', $kd_bangsal)
                                                   ->where('kelas', $kelas)
                                                   ->first();
            
                    $jumlah = $filtered ? (int) $filtered->jumlah_hari : 0;
                    $key = $alias . str_replace(' ', '', $kelas);
                    $hariPerawatan[$key] = $jumlah;
                    $totalPerBangsal += $jumlah;
            
                    $kelasKey = 'total' . str_replace(' ', '', $kelas);
                    $hariPerawatan[$kelasKey] = ($hariPerawatan[$kelasKey] ?? 0) + $jumlah;
                }
            
                $hariPerawatan[$alias] = $totalPerBangsal;
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
            $bor['total'] = 0;
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

                $bor['total'] += $bor['total' . $keySuffix];
            }




            $los = [];

            // Perhitungan LOS per bangsal dan kelas
            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $hariRawat = $hariPerawatan[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;
                    $meninggalTotal = $pasienMeninggalTotal[$key] ?? 0;

                    $denominator = $keluarHidup + $meninggalTotal;

                    $los[$key] = ($denominator > 0)
                        ? round($hariRawat / $denominator, 2)
                        : 0;
                }
            }

            // Perhitungan total LOS per kelas dan keseluruhan
            $los['total'] = 0;
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $totalHariRawat = 0;
                $totalKeluar = 0;

                foreach ($bangsalList as $kd_bangsal => $alias) {
                    $key = $alias . $keySuffix;
                    $totalHariRawat += $hariPerawatan[$key] ?? 0;
                    $totalKeluar += ($pasienKeluarHidup[$key] ?? 0) + ($pasienMeninggalTotal[$key] ?? 0);
                }

                $los['total' . $keySuffix] = ($totalKeluar > 0)
                    ? round($totalHariRawat / $totalKeluar, 2)
                    : 0;

                $los['total'] += $los['total' . $keySuffix];
            }


            // Hitung BTO (Bed Turn Over)
            $bto = [];

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $jumlahKeluar = ($pasienKeluarHidup[$key] ?? 0) + ($pasienMeninggalTotal[$key] ?? 0) + ($pasienKeluarPindahan[$key] ?? 0);
                    $jumlahTempatTidur = $tempatTidur[$key] ?? 0;

                    $bto[$key] = $jumlahTempatTidur > 0 ? round($jumlahKeluar / $jumlahTempatTidur, 2) : 0;
                }
            }

            // Total BTO semua bangsal
            $bto['total'] = 0;
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $totalPerKelas = 0;

                foreach ($bangsalList as $kd_bangsal => $alias) {
                    $key = $alias . $keySuffix;
                    $totalPerKelas += $bto[$key] ?? 0;
                }

                $bto['total' . $keySuffix] = $totalPerKelas;
                $bto['total'] += $totalPerKelas;
            }





            // Hitung TOI (Turn Over Interval)
            $toi = [];

            $jumlahHari = \Carbon\Carbon::parse($tgl2)->diffInDays(\Carbon\Carbon::parse($tgl1)) + 1;

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $tt = $tempatTidur[$key] ?? 0;
                    $hp = $hariPerawatan[$key] ?? 0;
                    $keluar = ($pasienKeluarHidup[$key] ?? 0) + ($pasienMeninggalTotal[$key] ?? 0) + ($pasienKeluarPindahan[$key] ?? 0);

                    $numerator = ($tt * $jumlahHari) - $hp;
                    $toi[$key] = $keluar > 0 ? round($numerator / $keluar, 2) : 0;
                }
            }

            // Total TOI semua bangsal
            $toi['total'] = 0;
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $totalPerKelas = 0;

                foreach ($bangsalList as $kd_bangsal => $alias) {
                    $key = $alias . $keySuffix;
                    $totalPerKelas += $toi[$key] ?? 0;
                }

                $toi['total' . $keySuffix] = $totalPerKelas;
                $toi['total'] += $totalPerKelas;
            }



            // Hitung NDR (Net Death Rate)
            $ndr = [];

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $meninggal48plus = $pasienMeninggal48plus[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;

                    $denominator = $keluarHidup + $meninggal48plus;

                    $ndr[$key] = $denominator > 0 ? round(($meninggal48plus / $denominator) * 100, 2) : 0;
                }
            }

            // Total NDR semua bangsal
            $ndr['total'] = 0;
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $totalPerKelas = 0;

                foreach ($bangsalList as $kd_bangsal => $alias) {
                    $key = $alias . $keySuffix;
                    $totalPerKelas += $ndr[$key] ?? 0;
                }

                $ndr['total' . $keySuffix] = $totalPerKelas;
                $ndr['total'] += $totalPerKelas;
            }



            // Hitung GDR (Gross Death Rate)
            $gdr = [];

            foreach ($bangsalList as $kd_bangsal => $alias) {
                foreach (array_merge([''], $kelasList) as $kelas) {
                    $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                    $key = $alias . $keySuffix;

                    $meninggalTotal = $pasienMeninggalTotal[$key] ?? 0;
                    $keluarHidup = $pasienKeluarHidup[$key] ?? 0;

                    $denominator = $keluarHidup + $meninggalTotal;

                    $gdr[$key] = $denominator > 0 ? round(($meninggalTotal / $denominator) * 100, 2) : 0;
                }
            }

            // Total GDR semua bangsal
            $gdr['total'] = 0;
            foreach (array_merge([''], $kelasList) as $kelas) {
                $keySuffix = $kelas === '' ? '' : str_replace(' ', '', $kelas);
                $totalPerKelas = 0;

                foreach ($bangsalList as $kd_bangsal => $alias) {
                    $key = $alias . $keySuffix;
                    $totalPerKelas += $gdr[$key] ?? 0;
                }

                $gdr['total' . $keySuffix] = $totalPerKelas;
                $gdr['total'] += $totalPerKelas;
            }





            //start return

                    return view('rm.kinerja.kinerja', [
                        // untuk mengirim data dalam form
                            'tgl1' => $formattedTgl1,
                            'tgl2' => $formattedTgl2,
                            'kelaskamar' => $kelaskamar,
                        // end form
                        'jmlpasien' => $chart->line2($dewasa,$bayi,$labelstat,$judul_line,$subjudul_line),//pemanggilan line chart
                        'jmllamapasien' => $chart->line2($dewasa_lama,$bayi_lama,$labelstat_lama,$judul_line_lama,$subjudul_line_lama),//pemanggilan line chart
                        'bor_dewasa' => $formatdewasa,//hasil bor dewasa
                        'bor_bayi' => $formatbayi,//hasil bor bayi
                        'alos_dewasa'=>  $formatalosdewasa, // Hasil Alos Dewasa
                        'alos_bayi'=> $formatalosbayi, // Hasil Alos Bayi
                        'bto_dewasa'=>  $formatbtodewasa, // Hasil BTO Dewasa
                        'bto_bayi'=> $formatbtobayi, // Hasil BTO Bayi
                        'gdr_dewasa'=>$formatgdrdewasa , // Hasil Gdr Dewasa
                        'gdr_bayi'=>$formatgdrbayi , // Hasil Gdr Bayi
                        'toi_dewasa'=>$formattoisdewasa , // Hasil Toi Dewasa
                        'toi_bayi'=>$formattoibayi , // Hasil Toi Bayi
                        'ndr_dewasa'=>$formatndrdewasa , // Hasil NDR Dewasa
                        'ndr_bayi'=>$formatndrbayi , // Hasil NDR Bayi

                        //parameter Perhitungan
                        'pasienper1haridewasa'=>$pasienper1haridewasa,
                        'pasienper1haribayi'=>$pasienper1haribayi,
                        'jmldewasa'=> $jml_dewasa ,
                        'jmlbayi'=> $jml_bayi ,
                        'pasienmatidewasalebih2hari'=> $pasienmatidewasalebih2hari ,
                        'pasienmatibayilebih2hari'=> $pasienmatibayilebih2hari ,
                        'pasienmatidewasakurang2hari'=> $pasienmatidewasakurang2hari ,
                        'pasienmatibayikurang2hari'=> $pasienmatibayikurang2hari ,
                        'jmldewasamati'=> $jml_dewasa_mati ,
                        'jmldewasamati'=> $jml_dewasa_mati ,
                        'jmlbayimati'=> $jml_bayi_mati ,
                        'jumlah_rawat_dewasa' => $lama_rawat_dewasa,
                        'jumlah_rawat_bayi' => $lama_rawat_bayi,
                        'los_dewasa' => $los_dewasa,
                        'los_bayi' => $los_bayi,
                        'jmlkamarbayi'=> $jmlkmrbayi ,
                        'jmlkamardewasa'=> $jmlkmrdewasa,
                        'days'=>$days,

                        'tempatTidur' => $tempatTidur,
                        'pasienAwal' => $pasienAwal,
                        'pasienMasuk' => $pasienMasuk,
                        'pasienPindahan' => $pasienPindahan,
                        'pasienKeluarPindahan' => $pasienKeluarPindahan,
                        'pasienKeluarHidup' => $pasienKeluarHidup,
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