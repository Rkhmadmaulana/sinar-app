<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
    //
    public function index(){
        return view('auth.login');
    }

    public function login_proses(Request $request)
    {
        // Validasi input dari form login
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        $username = $request->input('username');
        $password = $request->input('password');

        // Proses autentikasi pengguna
        $admin = DB::table('admin')
            ->select('*')
            ->whereRaw("usere = AES_ENCRYPT(?, 'nur')", [$username])
            ->whereRaw("passworde = AES_ENCRYPT(?, 'windi')", [$password])
            ->first();

        $user = DB::table('user')
            ->select('id_user')
            ->whereRaw("id_user = AES_ENCRYPT(?, 'nur')", [$username])
            ->whereRaw("password = AES_ENCRYPT(?, 'windi')", [$password])
            ->first();   

        if (!$user && !$admin ) {
            // Jika autentikasi gagal, kembalikan ke halaman login dengan pesan error
            return redirect()->route('login')->with('failed', 'Salah Username atau Password.');
        } else if($admin) {
            // Autentikasi berhasil
            // Simpan informasi pengguna ke dalam session
            session(['authenticated' => true]);
            session(['id_user' => $admin->usere]);
            session(['nik' => 'Admin Utama']);
            session(['level' => "admin"]);

            // Tambahkan entri log ke dalam tabel log
            $logData = [
                'tanggal' => now(),
                'aktifitas' => 'Login',
                'user' => 'Admin Utama'
            ];

            // DB::table('log_simbha')->insert($logData);
            // Redirect ke halaman dashboard atau halaman yang ditentukan setelah login berhasil
            return redirect()->route('dashboard');
        } else if($user) {
            // Autentikasi berhasil
            // Simpan informasi pengguna ke dalam session
            session(['authenticated' => true]);
            session(['id_user' => $user->id_user]);//encrypt
            session(['nik' => $username]);//no encrypt
            session(['level' =>  'pegawai']);

            // Tambahkan entri log ke dalam tabel log
            $logData = [
                'tanggal' => now(),
                'aktifitas' => 'Login',
                'user' => $username
            ];

            DB::table('log_simbha')->insert($logData);
            // Redirect ke halaman dashboard atau halaman yang ditentukan setelah login berhasil
            return redirect()->route('dashboard');
        }
    }

    public function logout(Request $request)
    {
        // Hapus session jika level adalah 'GUEST'
        session()->forget(['authenticated', 'id_user', 'level']);
        
        // Redirect ke halaman login dengan header untuk mencegah caching
        return redirect()->route('login')->with('success', 'Berhasil Logout.')
                                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
                                        ->header('Pragma', 'no-cache')
                                        ->header('Expires', '0');
    }
    public function profil(){
        return view('teamprofil');
    }
}
