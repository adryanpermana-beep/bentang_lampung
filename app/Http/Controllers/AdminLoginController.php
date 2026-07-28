<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    // 1. Menampilkan Halaman Login
    public function showForm()
    {
        // Jika sudah login, alihkan ke peta admin
        if (Auth::check()) {
            return redirect('/admin/peta');
        }
        return view('auth_admin_baru'); 
    }

    // 2. Memproses Data Login
    public function prosesLogin(Request $request)
    {
        // Validasi input email dan password
        $this->validate($request, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            session(['admin_logged_in' => true]);
            
            return redirect('/admin/peta')->with('success', 'Selamat datang Admin!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah, silakan periksa kembali.',
        ])->withInput($request->only('email'));
    }

    // 3. Menampilkan Halaman Peta Khusus Admin
    public function adminPeta()
    {
        // Cek autentikasi admin
        if (!Auth::check()) {
            return redirect('/halaman-login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return view('admin-peta');
    }

    // 4. Memproses Logout (Final Fix untuk CSRF Token)
    public function prosesLogout(Request $request)
    {
        // Logout autentikasi guard Laravel
        Auth::logout();

        // Hapus session kustom
        $request->session()->forget('admin_logged_in');
        $request->session()->forget('admin_nama');

        // Hapus & buat ulang token CSRF baru
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/halaman-login')->with('success', 'Anda telah berhasil keluar.');
    }
}