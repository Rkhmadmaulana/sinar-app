<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{ 
    public function kunjunganrajal(Request $request)
    {   
            //format tanggal
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
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
            //end format tanggal
            
            // start SQL ANGGOTA POLRI
                $sqlanggotapolri = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('c.golongan_polri', '=', '1')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as anggota_polri'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_anggota_polri'))
                ->first();
            // end SQL ANGGOTA POLRI

            // start SQL ANGGOTA PNS
                $sqlanggotapns = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.golongan_polri', '=', '2')
                        ->orwhere('c.golongan_polri', '=', '7')
                        ->orwhere('c.golongan_polri', '=', '8')
                        ->orwhere('c.golongan_polri', '=', '10');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as anggota_pns'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_anggota_pns'))
                ->first();
            // end SQL ANGGOTA PNS

            // start SQL Keluarga Polri
                $sqlanggotakelpolri = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.golongan_polri', '=', '9')
                        ->orwhere('c.golongan_polri', '=', '3');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as anggota_kel_polri'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_kel_polri'))
                ->first();
            // end SQL Keluarga Polri

            // start SQL ANGGOTA SISWA DIKBANG
                $sqlanggotadikbang = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.golongan_polri', '=', '4')
                        ->orwhere('c.golongan_polri', '=', '6');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as siswa_dikbang'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_siswa_dikbang'))
                ->first();
            // end SQL ANGGOTA SISWA DIKBANG
            
            // start SQL ANGGOTA SISWA DIKTUK
                $sqlanggotadiktuk = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('c.golongan_polri', '=', '5')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as siswa_diktuk'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_siswa_diktuk'))
                ->first();
            // end SQL ANGGOTA SISWA DIKTUK
            
            // Start Total pasien bpjs khusus
                $pasien_total_khusus_pengunjung =   
                $sqlanggotapolri->anggota_polri + 
                $sqlanggotapns->anggota_pns +  
                $sqlanggotadikbang->siswa_dikbang  + 
                $sqlanggotadiktuk->siswa_diktuk  + 
                $sqlanggotakelpolri->anggota_kel_polri 
                ;
                
                $pasien_total_khusus_kunjungan =   
                $sqlanggotapolri->kunjungan_anggota_polri + 
                $sqlanggotapns->kunjungan_anggota_pns +  
                $sqlanggotadikbang->kunjungan_siswa_dikbang  + 
                $sqlanggotadiktuk->kunjungan_siswa_diktuk  +  
                $sqlanggotakelpolri->kunjungan_kel_polri 
                ;
            // End Total pasien bpjs khusus

            // start SQL pasien bpjs
                $sqlpasienbpjs = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'BPJ')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as pasienbpjs'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_pasienbpjs'))
                ->first();
                $total_pengunjung_bpjs= $sqlpasienbpjs->pasienbpjs - $pasien_total_khusus_pengunjung;
                $total_kunjungan_bpjs= $sqlpasienbpjs->kunjungan_pasienbpjs - $pasien_total_khusus_kunjungan;
            // end SQL pasien bpjs
            
            // start SQL pasien UMUM
                $sqlpasienumum = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '=', 'UMU')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as pasienumum'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_pasienumum'))
                ->first();
            // end SQL pasien UMUM

            // start SQL pasien other
                $sqlpasienother = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.status_lanjut', '=', 'Ralan')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as pasienother'),DB::raw('COUNT(a.no_rkm_medis) as kunjungan_pasienother'))
                ->first();
            // end SQL pasien other

            $total_pengunjung =   $pasien_total_khusus_pengunjung + $total_pengunjung_bpjs + $sqlpasienumum->pasienumum + $sqlpasienother->pasienother ;
            $total_kunjungan =   $pasien_total_khusus_kunjungan + $total_kunjungan_bpjs+ $sqlpasienumum->kunjungan_pasienumum + $sqlpasienother->kunjungan_pasienother;

        return view('rm.laporan_rm.kunjungan_rajal',[
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggotapolri' =>$sqlanggotapolri,
            'anggotapns' =>$sqlanggotapns,
            'anggotakelpolri' =>$sqlanggotakelpolri,
            'dikbang' =>$sqlanggotadikbang,
            'diktuk' =>$sqlanggotadiktuk,
            'pasien_umum' =>$sqlpasienumum,
            'pasien_other' =>$sqlpasienother,
            'total_pengunjung_bpjs' =>$total_pengunjung_bpjs,
            'total_kunjungan_bpjs' =>$total_kunjungan_bpjs,
            'total_pengunjung'=>$total_pengunjung,
            'total_kunjungan'=>$total_kunjungan,
        ]);
    }

    public function kunjunganranap(Request $request)
    {   
        //format tanggal
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
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // start SQL ANGGOTA POLRI
            $sqlanggotapolri = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.golongan_polri', '=', '1')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as anggota_polri'))
            ->first();
        // end SQL ANGGOTA POLRI
        // start SQL ANGGOTA PNS
            $sqlanggotapns = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '2')
                    ->orwhere('c.golongan_polri', '=', '7')
                    ->orwhere('c.golongan_polri', '=', '8')
                    ->orwhere('c.golongan_polri', '=', '10');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as anggota_pns'))
            ->first();
        // end SQL ANGGOTA PNS

        // start SQL Keluarga Polri
            $sqlanggotakelpolri = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '3')
                    ->orwhere('c.golongan_polri', '=', '9');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as anggota_kel_polri'))
            ->first();
        // end SQL Keluarga Polri

        // start SQL ANGGOTA SISWA DIKBANG
            $sqlanggotadikbang = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '4')
                    ->orwhere('c.golongan_polri', '=', '6');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as siswa_dikbang'))
            ->first();
        // end SQL ANGGOTA SISWA DIKBANG
        // start SQL ANGGOTA SISWA DIKTUK
            $sqlanggotadiktuk = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.golongan_polri', '=', '5')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as siswa_diktuk'))
            ->first();
        // end SQL ANGGOTA SISWA DIKTUK
        
        // Start Total pasien bpjs khusus
            $pasien_total_khusus_pengunjung =   
            $sqlanggotapolri->anggota_polri + 
            $sqlanggotapns->anggota_pns +  
            $sqlanggotadikbang->siswa_dikbang  + 
            $sqlanggotadiktuk->siswa_diktuk  +  
            $sqlanggotakelpolri->anggota_kel_polri
            ;
        // End Total pasien bpjs khusus

        // start SQL pasien bpjs
            $sqlpasienbpjs = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pasienbpjs'))
            ->first();
            $total_pengunjung_bpjs= $sqlpasienbpjs->pasienbpjs - $pasien_total_khusus_pengunjung;
        // end SQL pasien bpjs
        
        // start SQL pasien UMUM
            $sqlpasienumum = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'UMU')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pasienumum'))
            ->first();
        // end SQL pasien UMUM

        // start SQL pasien other
            $sqlpasienother = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d','d.no_rawat','=','a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(a.no_rkm_medis) as pasienother'))
            ->first();
        // end SQL pasien other
        $total_pengunjung =   $pasien_total_khusus_pengunjung + $total_pengunjung_bpjs + $sqlpasienumum->pasienumum + $sqlpasienother->pasienother ;
        return view('rm.laporan_rm.kunjungan_ranap',[
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggotapolri' =>$sqlanggotapolri,
            'anggotapns' =>$sqlanggotapns,
            'anggotakelpolri' =>$sqlanggotakelpolri,
            'dikbang' =>$sqlanggotadikbang,
            'diktuk' =>$sqlanggotadiktuk,
            'pasien_umum' =>$sqlpasienumum,
            'pasien_other' =>$sqlpasienother,
            'total_pengunjung_bpjs' =>$total_pengunjung_bpjs,
            'total_pengunjung'=>$total_pengunjung,
            
        ]);
    }

    public function penyakitterbanyak(Request $request)
    {
        //format tanggal
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
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // Start Penyakit terbanyak Ranap
            $sqldiagnosa = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as b','b.no_rawat','=','a.no_rawat')
            ->join('penyakit as c', 'c.kd_penyakit', '=', 'b.kd_penyakit')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->groupBy('c.kd_penyakit','c.nm_penyakit') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(c.nm_penyakit, 30) as nama'),'c.kd_penyakit as kode', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        // End Penyakit terbanyak Ranap

        // Start Penyakit terbanyak Ralan
            $sqldiagnosaralan = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as b','b.no_rawat','=','a.no_rawat')
            ->join('penyakit as c', 'c.kd_penyakit', '=', 'b.kd_penyakit')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->groupBy('c.kd_penyakit','c.nm_penyakit') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(c.nm_penyakit, 30) as nama'),'c.kd_penyakit as kode', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        // End Penyakit terbanyak Ralan

        // start SQL pasien Baru
            $sqlpasienbaru = DB::table('reg_periksa as a')
            ->where('a.stts_daftar', '=', 'Baru')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pasienbaru'))
            ->first();
        // end SQL pasien Baru
        return view('rm.laporan_rm.penyakit_terbanyak',[
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            
            'diagnosa'=> $sqldiagnosa,
            'diagnosa_ralan'=> $sqldiagnosaralan,
            'pasien_baru'=> $sqlpasienbaru,
        ]);
    }

    public function penyakitmenular(Request $request)
    {

        //format tanggal
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
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // START HIV
            // START ANGGOTA 
                $sqlanggotahiv = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnshiv = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbanghiv = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '6')
                        ->orwhere('b.golongan_polri', '=', '4');
                })
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktukhiv = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolrihiv = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '9')
                        ->orwhere('b.golongan_polri', '=', '3');
                })
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END Kel polri

            // START Umum
                $sqlumuhiv = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_hiv =   
                    $sqlanggotahiv->hiv + 
                    $sqlpnshiv->hiv +  
                    $sqldikbanghiv->hiv  + 
                    $sqldiktukhiv->hiv  + 
                    $sqlkelpolrihiv->hiv 
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjshiv = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
                $total_bpjshiv= $sqlbpjshiv->hiv - $total_khusus_hiv  ;
            // END bpjs
            // START lainnya
                $sqllainnyahiv = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where('c.kd_penyakit', 'like', '%B20%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
                ->first();
            // END lainnya
            $total_hiv = $total_khusus_hiv + $total_bpjshiv + $sqllainnyahiv->hiv + $sqlumuhiv->hiv;
        // END HIV

        // START tb
            // START ANGGOTA 
                $sqlanggotatb = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnstb = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbangtb = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '4')
                        ->orwhere('b.golongan_polri', '=', '6');
                })
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktuktb = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolritb = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '9')
                        ->orwhere('b.golongan_polri', '=', '3');
                })
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END Kel polri
            
            // START Umum
                $sqlumutb = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_tb =   
                    $sqlanggotatb->tb + 
                    $sqlpnstb->tb +  
                    $sqldikbangtb->tb  + 
                    $sqldiktuktb->tb  +
                    $sqlkelpolritb->tb 
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjstb = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
                $total_bpjstb= $sqlbpjstb->tb - $total_khusus_tb  ;
            // END bpjs
            // START lainnya
                $sqllainnyatb = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where('c.kd_penyakit', 'like', '%A15%')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
                ->first();
            // END lainnya
            $total_tb = $total_khusus_tb + $total_bpjstb + $sqllainnyatb->tb + $sqlumutb->tb;
        // END tb

        // START malaria
            // START ANGGOTA 
                $sqlanggotamalaria = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnsmalaria = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbangmalaria = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '4')
                        ->orwhere('b.golongan_polri', '=', '6');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktukmalaria = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolrimalaria = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '9')
                        ->orwhere('b.golongan_polri', '=', '3');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END Kel polri

            // START Umum
                $sqlumumalaria = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_malaria =   
                    $sqlanggotamalaria->malaria + 
                    $sqlpnsmalaria->malaria +  
                    $sqldikbangmalaria->malaria  + 
                    $sqldiktukmalaria->malaria  + 
                    $sqlkelpolrimalaria->malaria 
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjsmalaria = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
                $total_bpjsmalaria= $sqlbpjsmalaria->malaria - $total_khusus_malaria  ;
            // END bpjs
            // START lainnya
                $sqllainnyamalaria = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B50%')
                        ->orWhere('c.kd_penyakit', 'like', '%B51%')
                        ->orWhere('c.kd_penyakit', 'like', '%B52%')
                        ->orWhere('c.kd_penyakit', 'like', '%B53%')
                        ->orWhere('c.kd_penyakit', 'like', '%B54%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
                ->first();
            // END lainnya
            $total_malaria = $total_khusus_malaria + $total_bpjsmalaria + $sqllainnyamalaria->malaria + $sqlumumalaria->malaria;
        // END malaria

        // START dbd
            // START ANGGOTA 
                $sqlanggotadbd = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnsdbd = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbangdbd = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '4')
                        ->orwhere('b.golongan_polri', '=', '6');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktukdbd = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolridbd = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '9')
                        ->orwhere('b.golongan_polri', '=', '3');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END Kel polri

            // START Umum
                $sqlumudbd = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_dbd =   
                    $sqlanggotadbd->dbd + 
                    $sqlpnsdbd->dbd +  
                    $sqldikbangdbd->dbd  + 
                    $sqldiktukdbd->dbd  +
                    $sqlkelpolridbd->dbd 
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjsdbd = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
                $total_bpjsdbd= $sqlbpjsdbd->dbd - $total_khusus_dbd  ;
            // END bpjs
            // START lainnya
                $sqllainnyadbd = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%A90%')
                        ->orWhere('c.kd_penyakit', 'like', '%A91%')
                        ->orWhere('c.kd_penyakit', 'like', '%A92%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
                ->first();
            // END lainnya
            $total_dbd = $total_khusus_dbd + $total_bpjsdbd + $sqllainnyadbd->dbd + $sqlumudbd->dbd;
        // END dbd
        
        // START pms
            // START ANGGOTA 
                $sqlanggotapms = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnspms = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbangpms = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '4')
                        ->orwhere('b.golongan_polri', '=', '6');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktukpms = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolripms = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '9')
                        ->orwhere('b.golongan_polri', '=', '3');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END Kel polri

            // START Umum
                $sqlumupms = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_pms =   
                    $sqlanggotapms->pms + 
                    $sqlpnspms->pms +  
                    $sqldikbangpms->pms  + 
                    $sqldiktukpms->pms  + 
                    $sqlkelpolripms->pms 
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjspms = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
                $total_bpjspms= $sqlbpjspms->pms - $total_khusus_pms  ;
            // END bpjs
            // START lainnya
                $sqllainnyapms = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%Q50%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                        ->orWhere('c.kd_penyakit', 'like', '%Q56%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
                ->first();
            // END lainnya
            $total_pms = $total_khusus_pms + $total_bpjspms + $sqllainnyapms->pms + $sqlumupms->pms;
        // END pms

        // START hepatitis
            // START ANGGOTA 
                $sqlanggotahepatitis = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnshepatitis = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbanghepatitis = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '4')
                        ->orwhere('b.golongan_polri', '=', '6');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktukhepatitis = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolrihepatitis = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '3')
                        ->orwhere('b.golongan_polri', '=', '9');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END Kel polri
            
            // START Umum
                $sqlumuhepatitis = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_hepatitis =   
                    $sqlanggotahepatitis->hepatitis + 
                    $sqlpnshepatitis->hepatitis +  
                    $sqldikbanghepatitis->hepatitis  + 
                    $sqldiktukhepatitis->hepatitis  +
                    $sqlkelpolrihepatitis->hepatitis
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjshepatitis = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
                $total_bpjshepatitis= $sqlbpjshepatitis->hepatitis - $total_khusus_hepatitis  ;
            // END bpjs
            // START lainnya
                $sqllainnyahepatitis = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B15%')
                        ->orWhere('c.kd_penyakit', 'like', '%B16%')
                        ->orWhere('c.kd_penyakit', 'like', '%B17%')
                        ->orWhere('c.kd_penyakit', 'like', '%B18%')
                        ->orWhere('c.kd_penyakit', 'like', '%B19%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
                ->first();
            // END lainnya
            $total_hepatitis = $total_khusus_hepatitis + $total_bpjshepatitis + $sqllainnyahepatitis->hepatitis + $sqlumuhepatitis->hepatitis;
        // END hepatitis
        
        // START covid
            // START ANGGOTA 
                $sqlanggotacovid = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '1')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END ANGGOTA

            // START pns 
                $sqlpnscovid = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '2')
                        ->orwhere('b.golongan_polri', '=', '7')
                        ->orwhere('b.golongan_polri', '=', '8')
                        ->orwhere('b.golongan_polri', '=', '10');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END pns

            // START Dikbang
                $sqldikbangcovid = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '4')
                        ->orwhere('b.golongan_polri', '=', '6');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END Dikbang

            // START Diktuk
                $sqldiktukcovid = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where('b.golongan_polri', '=', '5')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END Diktuk

            // START Kel polri
                $sqlkelpolricovid = DB::table('reg_periksa as a')
                ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('b.golongan_polri', '=', '9')
                        ->orwhere('b.golongan_polri', '=', '3');
                })
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END Kel polri

            // START Umum
                $sqlumucovid = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'UMU')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END Umum

            //start total bpjs khusus
                $total_khusus_covid =   
                    $sqlanggotacovid->covid + 
                    $sqlpnscovid->covid +  
                    $sqldikbangcovid->covid  + 
                    $sqldiktukcovid->covid  + 
                    $sqlkelpolricovid->covid 
                    ;
            //end total bpjs khusus

            // START bpjs
                $sqlbpjscovid = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
                $total_bpjscovid= $sqlbpjscovid->covid - $total_khusus_covid  ;
            // END bpjs
            // START lainnya
                $sqllainnyacovid = DB::table('reg_periksa as a')
                ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
                ->where('a.kd_pj', '!=', 'UMU')
                ->where('a.kd_pj', '!=', 'BPJ')
                ->where(function ($query) {
                    $query->where('c.kd_penyakit', 'like', '%B34.2%')
                        ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
                ->first();
            // END lainnya
            $total_covid = $total_khusus_covid + $total_bpjscovid + $sqllainnyacovid->covid + $sqlumucovid->covid;
        // END covid

        return view('rm.laporan_rm.penyakit_menular',[ 
        
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggotahiv'=> $sqlanggotahiv,'pnshiv'=> $sqlpnshiv,'dikbanghiv'=> $sqldikbanghiv,'diktukhiv'=> $sqldiktukhiv,'kelpolrihiv'=> $sqlkelpolrihiv,'umumhiv'=> $sqlumuhiv,'bpjshiv'=>$total_bpjshiv,'otherhiv'=>$sqllainnyahiv,'total_hiv'=>$total_hiv,
            
            'anggotatb'=> $sqlanggotatb,'pnstb'=> $sqlpnstb,'dikbangtb'=> $sqldikbangtb,'diktuktb'=> $sqldiktuktb,'kelpolritb'=> $sqlkelpolritb,'umumtb'=> $sqlumutb,'bpjstb'=>$total_bpjstb,'othertb'=>$sqllainnyatb,'total_tb'=>$total_tb,
            
            'anggotamalaria'=> $sqlanggotamalaria,'pnsmalaria'=> $sqlpnsmalaria,'dikbangmalaria'=> $sqldikbangmalaria,'diktukmalaria'=> $sqldiktukmalaria,'kelpolrimalaria'=> $sqlkelpolrimalaria,'umummalaria'=> $sqlumumalaria,'bpjsmalaria'=>$total_bpjsmalaria,'othermalaria'=>$sqllainnyamalaria,'total_malaria'=>$total_malaria,

            'anggotadbd'=> $sqlanggotadbd,'pnsdbd'=> $sqlpnsdbd,'dikbangdbd'=> $sqldikbangdbd,'diktukdbd'=> $sqldiktukdbd,'kelpolridbd'=> $sqlkelpolridbd,'umumdbd'=> $sqlumudbd,'bpjsdbd'=>$total_bpjsdbd,'otherdbd'=>$sqllainnyadbd,'total_dbd'=>$total_dbd,

            'anggotapms'=> $sqlanggotapms,'pnspms'=> $sqlpnspms,'dikbangpms'=> $sqldikbangpms,'diktukpms'=> $sqldiktukpms,'kelpolripms'=> $sqlkelpolripms,'umumpms'=> $sqlumupms,'bpjspms'=>$total_bpjspms,'otherpms'=>$sqllainnyapms,'total_pms'=>$total_pms,
            
            'anggotahepatitis'=> $sqlanggotahepatitis,'pnshepatitis'=> $sqlpnshepatitis,'dikbanghepatitis'=> $sqldikbanghepatitis,'diktukhepatitis'=> $sqldiktukhepatitis,'kelpolrihepatitis'=> $sqlkelpolrihepatitis,'umumhepatitis'=> $sqlumuhepatitis,'bpjshepatitis'=>$total_bpjshepatitis,'otherhepatitis'=>$sqllainnyahepatitis,'total_hepatitis'=>$total_hepatitis,

            'anggotacovid'=> $sqlanggotacovid,'pnscovid'=> $sqlpnscovid,'dikbangcovid'=> $sqldikbangcovid,'diktukcovid'=> $sqldiktukcovid,'kelpolricovid'=> $sqlkelpolricovid,'umumcovid'=> $sqlumucovid,'bpjscovid'=>$total_bpjscovid,'othercovid'=>$sqllainnyacovid,'total_covid'=>$total_covid,
        ]);
    }

    public function igd(Request $request)
    {
        //format tanggal
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
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // Start macam kasus Igd
            $sqligd = DB::table('reg_periksa as a')
            ->join('data_triase_igd as b','b.no_rawat','=','a.no_rawat')
            ->join('master_triase_macam_kasus as c','c.kode_kasus','=','b.kode_kasus')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->groupBy('c.macam_kasus') // Menambahkan klausa groupBy
            ->select('c.macam_kasus as kasus', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->get();
        // End macam kasus Igd

        return view('rm.laporan_rm.laporan_igd',[ 
        
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'igd'=>$sqligd,
        ]);

    }
    
    public function kematian(Request $request)
    {
        //format tanggal
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
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // Start Pasien Meninggal Anggota
            $meninggal_anggota = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND pasien_polri.golongan_polri = "1"
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND pasien_polri.golongan_polri = "1"
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal Anggota

        // Start Pasien Meninggal PNS
            $meninggal_pns = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND (pasien_polri.golongan_polri = "2" OR pasien_polri.golongan_polri = "7" OR pasien_polri.golongan_polri = "8" OR pasien_polri.golongan_polri = "10")
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND (pasien_polri.golongan_polri = "2" OR pasien_polri.golongan_polri = "7" OR pasien_polri.golongan_polri = "8" OR pasien_polri.golongan_polri = "10")
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal pns

        // Start Pasien Meninggal keluarga
            $meninggal_keluarga = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND (pasien_polri.golongan_polri = "3" OR pasien_polri.golongan_polri = "9" )
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND (pasien_polri.golongan_polri = "3" OR pasien_polri.golongan_polri = "9" )
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal keluarga

        // Start Pasien Meninggal Dikbang
            $meninggal_dikbang = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND (pasien_polri.golongan_polri = "4" OR pasien_polri.golongan_polri = "6" )
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND (pasien_polri.golongan_polri = "4" OR pasien_polri.golongan_polri = "6" )
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal Dikbang

        // Start Pasien Meninggal diktuk
            $meninggal_diktuk = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND pasien_polri.golongan_polri = "5" 
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND pasien_polri.golongan_polri = "5"
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal diktuk

        // Start Pasien Meninggal umum
            $meninggal_umum = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                where stts = "Meninggal"
                AND kd_pj = "UMU"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                where stts_pulang = "Meninggal"
                AND kd_pj = "UMU"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal umum
        
        // START Total BPJS KHUSUS
            $total_bpjs_khusus= $meninggal_anggota->total+$meninggal_pns->total+$meninggal_keluarga->total+$meninggal_dikbang->total+$meninggal_diktuk->total;
        // END Total BPJS KHUSUS
        // Start Pasien Meninggal bpjs
            $meninggal_bpjs = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                where stts = "Meninggal"
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                where stts_pulang = "Meninggal"
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
            $total_bpjs = $meninggal_bpjs->total - $total_bpjs_khusus; 
        // End Pasien Meninggal bpjs
        
        // Start Pasien Meninggal lainnya
            $meninggal_lainnya = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                where stts = "Meninggal"
                AND kd_pj != "UMU"
                AND kd_pj != "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                where stts_pulang = "Meninggal"
                AND kd_pj != "UMU"
                AND kd_pj != "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal lainnya
            $total_meninggal = $total_bpjs_khusus + $meninggal_umum->total + $total_bpjs + $meninggal_lainnya->total ;
        return view('rm.laporan_rm.laporan_kematian',[ 
        
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggota' => $meninggal_anggota,
            'pns' => $meninggal_pns,
            'keluarga' => $meninggal_keluarga,
            'dikbang' => $meninggal_dikbang,
            'diktuk' => $meninggal_diktuk,
            'umum' => $meninggal_umum,
            'bpjs' => $total_bpjs,
            'lainnya' => $meninggal_lainnya,
            'total' => $total_meninggal,
        ]);
    }

    public function pertumbuhan(Request $request)
    {
        //format tanggal
                // Get input values
                $tgl1Input = $request->input('tgl1');
                $tgl2Input = $request->input('tgl2');

                // Check if $tgl1 is empty, if so, set it to the first day of the current month
                if (empty($tgl1Input)) {
                    $tgl1 = new \DateTime(date('Y-m-01'));

                    // Modifikasi tanggal untuk mundur satu bulan
                    $tglSebelum = clone $tgl1; // Salin objek untuk tanggal sebelumnya
                    $tglSebelum->modify('-1 month');

                    // Format tanggal sebagai 'Y-m-01' untuk mendapatkan awal bulan sebulan sebelumnya
                    $tglAwalSebelum = clone $tglSebelum; // Salin objek untuk tanggal awal bulan sebelumnya
                    $tglAwalSebelum->modify('first day of this month');

                    // Format tanggal sebagai 'Y-m-t' untuk mendapatkan akhir bulan sebulan sebelumnya
                    $tglAkhirSebelum = clone $tglSebelum; // Salin objek untuk tanggal akhir bulan sebelumnya
                    $tglAkhirSebelum->modify('last day of this month');

                    // Format tanggal sebelumnya sebagai 'd F Y'
                    $formattedTglAwalSebelum = $tglAwalSebelum->format('d F Y');
                    $formattedTglAkhirSebelum = $tglAkhirSebelum->format('d F Y');

                } else {
                    $tgl1 = new \DateTime($tgl1Input);

                    // Modifikasi tanggal untuk mundur satu bulan
                    $tglSebelum = clone $tgl1; // Salin objek untuk tanggal sebelumnya
                    $tglSebelum->modify('-1 month');

                    // Format tanggal sebagai 'Y-m-01' untuk mendapatkan awal bulan sebulan sebelumnya
                    $tglAwalSebelum = clone $tglSebelum; // Salin objek untuk tanggal awal bulan sebelumnya
                    $tglAwalSebelum->modify('first day of this month');

                    // Format tanggal sebagai 'Y-m-t' untuk mendapatkan akhir bulan sebulan sebelumnya
                    $tglAkhirSebelum = clone $tglSebelum; // Salin objek untuk tanggal akhir bulan sebelumnya
                    $tglAkhirSebelum->modify('last day of this month');

                    // Format tanggal sebelumnya sebagai 'd F Y'
                    $formattedTglAwalSebelum = $tglAwalSebelum->format('d F Y');
                    $formattedTglAkhirSebelum = $tglAkhirSebelum->format('d F Y');

                }
                // Check if $tgl2 is empty, if so, set it to today's date
                if (empty($tgl2Input)) {
                    $tgl2 = new \DateTime();
                } else {
                    $tgl2 = new \DateTime($tgl2Input);
                }
                // Format the dates
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // Start Rawat Jalan
            $sqlrajalnow = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '!=', 'IGDK')
            // ->where('a.kd_poli', '!=', 'IRM')
            ->where('a.kd_poli', '!=', 'RAD')
            ->where('a.kd_poli', '!=', 'LAB')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

            $sqlrajalsebelum = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '!=', 'IGDK')
            // ->where('a.kd_poli', '!=', 'IRM')
            ->where('a.kd_poli', '!=', 'RAD')
            ->where('a.kd_poli', '!=', 'LAB')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

            $pertumbuhan_ralan = number_format((($sqlrajalnow->total-$sqlrajalsebelum->total ) / $sqlrajalsebelum->total) * 100 , 2);
        // End Rawat Jalan

        // Start Rawat Inap
            $sqlranapnow = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

            $sqlranapsebelum = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

            $pertumbuhan_ranap = number_format((($sqlranapnow->total-$sqlranapsebelum->total ) / $sqlranapsebelum->total) * 100 , 2);
        // End Rawat Inap

        // Start IGD
            $sqligdnow = DB::table('reg_periksa as a')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '=', 'IGDK')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

            $sqligdsebelum = DB::table('reg_periksa as a')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '=', 'IGDK')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

            $pertumbuhan_igd = number_format((($sqligdnow->total-$sqligdsebelum->total ) / $sqligdsebelum->total) * 100 , 2);
        // End IGD

        // Start IRM
            // $sqlirmnow = DB::table('reg_periksa as a')
            // ->where('a.status_lanjut', '=', 'Ralan')
            // ->where('a.stts', '!=', 'Batal')
            // ->where('a.kd_poli', '=', 'IRM')
            // ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            //     return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            // })
            // ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            // ->orderBy('total', 'desc')
            // ->first();

            // $sqlirmsebelum = DB::table('reg_periksa as a')
            // ->where('a.status_lanjut', '=', 'Ralan')
            // ->where('a.stts', '!=', 'Batal')
            // ->where('a.kd_poli', '=', 'IRM')
            // ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
            //     return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            // })
            // ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            // ->orderBy('total', 'desc')
            // ->first();

            // $pertumbuhan_irm = number_format((($sqlirmnow->total-$sqlirmsebelum->total ) / $sqlirmsebelum->total) * 100 , 2);
        // End IRM

        // Start Lab
            $sqllabnow = DB::table('periksa_lab as a')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_periksa', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

            $sqllabsebelum = DB::table('periksa_lab as a')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_periksa', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

            $pertumbuhan_lab = number_format((($sqllabnow->total - $sqllabsebelum->total) / $sqllabsebelum->total) * 100, 2);
        // End Lab

        // Start rad
            $sqlradnow = DB::table('periksa_radiologi as a')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_periksa', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

            $sqlradsebelum = DB::table('periksa_radiologi as a')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_periksa', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

            $pertumbuhan_rad = number_format((($sqlradnow->total - $sqlradsebelum->total) / $sqlradsebelum->total) * 100, 2);
        // End rad

        // Start operasi
            // $sqloperasinow = DB::table('operasi as a')
            // ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
            //     return $query->whereBetween('a.tgl_operasi', [$tgl1, $tgl2]);
            // })
            // ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_operasi)) as total'))
            // ->first();

            // $sqloperasisebelum = DB::table('operasi as a')
            // ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
            //     return $query->whereBetween('a.tgl_operasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            // })
            // ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_operasi)) as total'))
            // ->first();

            // $pertumbuhan_operasi = number_format((($sqloperasinow->total - $sqloperasisebelum->total) / $sqloperasisebelum->total) * 100, 2);
        // End operasi

        return view('rm.laporan_rm.pertumbuhan',[ 
        
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'dari'=>$formattedTglAwalSebelum,
            'sampai'=>$formattedTglAkhirSebelum,

            'sqlrajal'=>$sqlrajalnow,
            'pertumbuhan_ralan'=>$pertumbuhan_ralan,

            'sqlranap'=>$sqlranapnow,
            'pertumbuhan_ranap'=>$pertumbuhan_ranap,

            'sqligd'=>$sqligdnow,
            'pertumbuhan_igd'=>$pertumbuhan_igd,
            
            // 'sqlirm'=>$sqlirmnow,
            // 'pertumbuhan_irm'=>$pertumbuhan_irm,

            'sqllab'=>$sqllabnow,
            'pertumbuhan_lab'=>$pertumbuhan_lab,

            'sqlrad'=>$sqlradnow,
            'pertumbuhan_rad'=>$pertumbuhan_rad,

            // 'sqloperasi'=>$sqloperasinow,
            // 'pertumbuhan_operasi'=>$pertumbuhan_operasi,

            

        ]);
    }
}


