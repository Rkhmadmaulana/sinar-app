<?php

namespace App\Http\Middleware\hak_akses_app\peminjaman_rekam_medis;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthenticateVerifikatorpeminjamanrekammedisCustom
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('authenticated')) {
            return redirect()->route('login')->with('failed', 'Anda Harus Login');
        }
        // Ambil nilai user_id dari session
        $id_user = session()->get('id_user');
        $level = session()->get('level');
        // Cek Seasson
        $admin = DB::table('admin')
        ->where('usere', $id_user)
        ->first();
        //
        $user = DB::table('user_dashboard')
        ->where('id_user', $id_user)
        ->where('level', $level)
        ->where('verifikator_peminjaman_rekam_medis', 'true')
        ->where('status_aktif', 'true')
        ->first();

        if (!$admin && !$user){
        // Hapus session jika level adalah 'GUEST'
        session()->forget(['authenticated', 'id_user', 'level']);
        
        // Redirect ke halaman login dengan header untuk mencegah caching
        return redirect()->route('login')->with('failed', 'Oopss Anda Tidak Memiliki Akses')
                                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
                                        ->header('Pragma', 'no-cache')
                                        ->header('Expires', '0');
        }
        return $next($request);
    }
}
