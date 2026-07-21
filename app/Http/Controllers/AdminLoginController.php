<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    // 1. Menampilkan Halaman Login
    public function showForm()
    {
        return view('auth_admin_baru'); 
    }

    // 2. Memproses Data Login
    public function prosesLogin(Request $request)
    {
        // Perbaikan Validasi untuk Laravel versi lama
        $this->validate($request, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Mengambil data inputan email & password
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/peta')->with('success', 'Selamat datang Admin!');
        }

        // Kembali jika gagal dengan input email sebelumnya tetap terisi
        return back()->withErrors([
            'email' => 'Email atau password salah, silakan periksa kembali.',
        ])->withInput($request->only('email'));
    }

    // 3. Memproses Logout
    public function prosesLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/peta');
    }
}