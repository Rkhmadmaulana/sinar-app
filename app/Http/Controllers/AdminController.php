<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function account(Request $request)
    {       
            //proses add account
                $username = $request->input('username');
                $password = $request->input('password');
                $level = $request->input('level');
                $status_aktif = 'true';
                
                if ($username && $password && $level) {
                    $admin = DB::table('user_dashboard')
                    ->selectRaw("*")
                    ->whereRaw("id_user = AES_ENCRYPT('$username','nur')")
                    ->first();
                    if ($admin) {
                        // Jika autentikasi gagal, kembalikan ke halaman login dengan pesan error
                        return redirect()->route('account')->with('failed', 'Username Sudah Terdaftar');
                    }else{
                        $add = [
                            'id_user' => DB::raw("AES_ENCRYPT('$username', 'nur')"),
                            'password' => DB::raw("AES_ENCRYPT('$password', 'windi')"),
                            'level' => $level,
                            'status_aktif' => $status_aktif
                        ];
                        DB::table('user_dashboard')->insert($add);

                        $logData = [
                            'tanggal' => now(),
                            'aktifitas' => 'Menambah User | Username : '.$username.'',
                            'user' => 'Admin Utama'
                        ];
                        DB::table('log_simbha')->insert($logData);

                        return redirect()->route('account')->with('success', 'Proses Pendaftaran User Berhasil');
                    }
                }
            //proses add account

            //proses update
                $userId = $request->input('hak_akses_id');
                $status_aktif = $request->boolean('status_aktif') ? 'true' : 'false';
                $lap_ralan = $request->boolean('lap_ralan') ? 'true' : 'false';
                $lap_ranap = $request->boolean('lap_ranap') ? 'true' : 'false';
                $pegawai = $request->boolean('pegawai') ? 'true' : 'false';
                $kinerja = $request->boolean('kinerja') ? 'true' : 'false';
                $laporan_rm = $request->boolean('laporan_rm') ? 'true' : 'false';
                $riwayat_obat = $request->boolean('riwayat_obat') ? 'true' : 'false';
                $mapping_icdx = $request->boolean('mapping_icdx') ? 'true' : 'false';
                $perkiraan_biaya_ranap = $request->boolean('perkiraan_biaya_ranap') ? 'true' : 'false';
                $verifikator_perkiraan_biaya_ranap = $request->boolean('verifikator_perkiraan_biaya_ranap') ? 'true' : 'false';
                $administrator_laporan_rm = $request->boolean('administrator_laporan_rm') ? 'true' : 'false';
                $pasien_ralan = $request->boolean('pasien_ralan') ? 'true' : 'false';
                $pasien_ranap = $request->boolean('pasien_ranap') ? 'true' : 'false';
                $admin_mutu_pelayanan = $request->boolean('admin_mutu_pelayanan') ? 'true' : 'false';
                $mutu_pelayanan = $request->boolean('mutu_pelayanan') ? 'true' : 'false';
                $verifikator_peminjaman_rekam_medis = $request->boolean('verifikator_peminjaman_rekam_medis') ? 'true' : 'false';
                $peminjaman_rekam_medis = $request->boolean('peminjaman_rekam_medis') ? 'true' : 'false';
                $logbook_server = $request->boolean('logbook_server') ? 'true' : 'false';
                $fisioterapi = $request->boolean('fisioterapi') ? 'true' : 'false';
                $medis_ranap_kandungan = $request->boolean('medis_ranap_kandungan') ? 'true' : 'false';
                $medis_ranap_bayi = $request->boolean('medis_ranap_bayi') ? 'true' : 'false';
                $medis_hemodialisa = $request->boolean('medis_hemodialisa') ? 'true' : 'false';
                $medis_ralan_tht = $request->boolean('medis_ralan_tht') ? 'true' : 'false';
                $medis_ralan_psikiatri = $request->boolean('medis_ralan_psikiatri') ? 'true' : 'false';
                $medis_ralan_penyakit_dalam = $request->boolean('medis_ralan_penyakit_dalam') ? 'true' : 'false';
                $medis_ralan_mata = $request->boolean('medis_ralan_mata') ? 'true' : 'false';
                $medis_ralan_neurologi = $request->boolean('medis_ralan_neurologi') ? 'true' : 'false';
                $medis_ralan_orthopedi = $request->boolean('medis_ralan_orthopedi') ? 'true' : 'false';
                $medis_ralan_bedah = $request->boolean('medis_ralan_bedah') ? 'true' : 'false';
                
                if($userId){
                DB::table('user_dashboard')
                    ->where('id_user', DB::raw("AES_ENCRYPT('$userId', 'nur')"))
                    ->update([
                        'status_aktif' => $status_aktif,
                        'ralan' => $lap_ralan,
                        'ranap' => $lap_ranap,
                        'pegawai' => $pegawai,
                        'kinerja' => $kinerja,
                        'laporan_rm' => $laporan_rm,
                        'riwayat_obat' => $riwayat_obat,
                        'mapping_icdx' => $mapping_icdx,
                        'perkiraan_biaya_ranap' => $perkiraan_biaya_ranap,
                        'verifikator_perkiraan_biaya_ranap' => $verifikator_perkiraan_biaya_ranap,
                        'administrator_laporan_rm' => $administrator_laporan_rm,
                        'pasien_ralan' => $pasien_ralan,
                        'pasien_ranap' => $pasien_ranap,
                        'admin_mutu_pelayanan' => $admin_mutu_pelayanan,
                        'mutu_pelayanan' => $mutu_pelayanan,
                        'verifikator_peminjaman_rekam_medis' => $verifikator_peminjaman_rekam_medis,
                        'peminjaman_rekam_medis' => $peminjaman_rekam_medis,
                        'logbook_server' => $logbook_server,
                        'fisioterapi' => $fisioterapi,
                        'medis_ranap_kandungan' => $medis_ranap_kandungan,
                        'medis_ranap_bayi' => $medis_ranap_bayi,
                        'medis_hemodialisa' => $medis_hemodialisa,
                        'medis_ralan_tht' => $medis_ralan_tht,
                        'medis_ralan_psikiatri' => $medis_ralan_psikiatri,
                        'medis_ralan_penyakit_dalam' => $medis_ralan_penyakit_dalam,
                        'medis_ralan_mata' => $medis_ralan_mata,
                        'medis_ralan_neurologi' => $medis_ralan_neurologi,
                        'medis_ralan_orthopedi' => $medis_ralan_orthopedi,
                        'medis_ralan_bedah' => $medis_ralan_bedah,
                    ]);

                    $logData = [
                        'tanggal' => now(),
                        'aktifitas' => 'Update Hak Akses Username : '.$userId.'',
                        'user' => 'Admin Utama'
                    ];
                    DB::table('log_simbha')->insert($logData);

                return redirect()->route('account')->with('success', 'Hak Akses Berhasil Diperbaharui');
                }
            //proses update

            // pilihan_pegawai
                $pilihan_pegawai = DB::table('pegawai')
                ->select('pegawai.nik','pegawai.nama')
                ->orderBy('pegawai.nama', 'asc')
                ->get();
            // end pilihan_pegawai

            // list_user
                $list_user = DB::table('user_dashboard')
                ->join('pegawai', 'pegawai.nik', '=', DB::raw('AES_DECRYPT(user_dashboard.id_user, "nur")'))
                ->join('petugas', 'petugas.nip', '=', DB::raw('AES_DECRYPT(user_dashboard.id_user, "nur")'))
                ->join('jabatan', 'jabatan.kd_jbtn', '=', 'petugas.kd_jbtn')
                ->select('pegawai.nik as nik', 'pegawai.nama as nama','user_dashboard.level as level','user_dashboard.id_user as id_user','jabatan.nm_jbtn as jabatan')
                ->get();
            // end list_user

            //start return
                return view('admin.account',[
                    'pilihan_pegawai' => $pilihan_pegawai,
                    'list_user' => $list_user,
                ]);
            //end return  
    }
    
    public function copy_account(Request $request)
    {   

        $button = $request->input('button');//memanggil perintah berdasarkana value button

        // list_user
            $list_user = DB::table('user_dashboard')
            ->join('pegawai', 'pegawai.nik', '=', DB::raw('AES_DECRYPT(user_dashboard.id_user, "nur")'))
            ->join('petugas', 'petugas.nip', '=', DB::raw('AES_DECRYPT(user_dashboard.id_user, "nur")'))
            ->join('jabatan', 'jabatan.kd_jbtn', '=', 'petugas.kd_jbtn')
            ->select('pegawai.nik as nik', 'pegawai.nama as nama','user_dashboard.level as level','user_dashboard.id_user as id_user','jabatan.nm_jbtn as jabatan')
            ->get();
        // end list_user

        if ($button=="simpan"){
            $request->validate([
                'account_copy' => 'required',
                'account_paste' => 'required|array',
            ]);
            $account_master = $request->input('account_copy');
            // Ambil data pegawai master berdasarkan nik dari 'account_copy'
            $masterUser = DB::table('user_dashboard')
            ->where('id_user', DB::raw("AES_ENCRYPT('$account_master', 'nur')"))
            ->first();

            if (!$masterUser) {
                return redirect()->route('account')->with('failed', 'Akun master tidak ditemukan.');
            }

            // Iterasi melalui setiap pegawai yang dipilih untuk melakukan update
            foreach ($request->input('account_paste') as $nik) {
                DB::table('user_dashboard')
                    ->where('id_user', DB::raw("AES_ENCRYPT('$nik', 'nur')"))
                    ->update([
                        'level' => $masterUser->level,
                        'status_aktif' => $masterUser->status_aktif,
                        'ralan' => $masterUser->ralan,
                        'ranap' => $masterUser->ranap,
                        'pegawai' => $masterUser->pegawai,
                        'kinerja' => $masterUser->kinerja,
                        'laporan_rm' => $masterUser->laporan_rm,
                        'administrator_laporan_rm' => $masterUser->administrator_laporan_rm,
                        'riwayat_obat' => $masterUser->riwayat_obat,
                        'mapping_icdx' => $masterUser->mapping_icdx,
                        'perkiraan_biaya_ranap' => $masterUser->perkiraan_biaya_ranap,
                        'verifikator_perkiraan_biaya_ranap' => $masterUser->verifikator_perkiraan_biaya_ranap,
                        'pasien_ralan' => $masterUser->pasien_ralan,
                        'pasien_ranap' => $masterUser->pasien_ranap,
                        'mutu_pelayanan' => $masterUser->mutu_pelayanan,
                        'admin_mutu_pelayanan' => $masterUser->admin_mutu_pelayanan,
                        'verifikator_peminjaman_rekam_medis' => $masterUser->verifikator_peminjaman_rekam_medis,
                        'peminjaman_rekam_medis' => $masterUser->peminjaman_rekam_medis,
                        'logbook_server' => $masterUser->logbook_server,
                        'fisioterapi' => $masterUser->fisioterapi,
                        'medis_ranap_kandungan' => $masterUser->medis_ranap_kandungan,
                        'medis_ranap_bayi' => $masterUser->medis_ranap_bayi,
                        'medis_hemodialisa' => $masterUser->medis_hemodialisa,
                        'medis_ralan_tht' => $masterUser->medis_ralan_tht,
                        'medis_ralan_psikiatri' => $masterUser->medis_ralan_psikiatri,
                        'medis_ralan_penyakit_dalam' => $masterUser->medis_ralan_penyakit_dalam,
                        'medis_ralan_mata' => $masterUser->medis_ralan_mata,
                        'medis_ralan_neurologi' => $masterUser->medis_ralan_neurologi,
                        'medis_ralan_orthopedi' => $masterUser->medis_ralan_orthopedi,
                        'medis_ralan_bedah' => $masterUser->medis_ralan_bedah,

                    ]);

                    $logData = [
                        'tanggal' => now(),
                        'aktifitas' => 'Update Hak Akses Username : '.$nik.' Copy From '.$account_master,
                        'user' => 'Admin Utama'
                    ];
                    DB::table('log_simbha')->insert($logData);
            }


            // Redirect dengan pesan sukses
            return redirect()->route('account')->with('success', 'Copy Data berhasil.');
        }

        return view('admin.copy_account',[
            'list_user' => $list_user,
        ]);
    }

    public function hakacc(Request $request)
    { 
        $userId = $request->query('userId');
        $user = DB::table('user_dashboard')
            ->selectRaw("*")
            ->whereRaw("id_user = AES_ENCRYPT('$userId','nur')")
            ->first();
        
        return view('admin.hakacc', [
            // for id update
            'userId' => $userId,
            'user' => $user,
        ]);
    }

    

    public function deleteacc($userId)
    { 
        // Lakukan penghapusan data sesuai kriteria
        $deleted = DB::table('user_dashboard')
            ->whereRaw("id_user = AES_ENCRYPT('$userId','nur')")
            ->delete();
        
        if ($deleted) {
            // Jika penghapusan berhasil
            return redirect()->route('account')->with('success', 'Data berhasil dihapus.');
        } else {
            // Jika penghapusan gagal atau tidak ada data yang cocok
            return redirect()->route('account')->with('failed', 'Gagal menghapus data.');
        }
    }
    
}
