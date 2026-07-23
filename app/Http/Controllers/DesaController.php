<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    // Fungsi untuk menampilkan halaman detail profil desa
    public function showProfile($kode_desa)
    {
        // Cari desa berdasarkan kode_desa
        $desa = Desa::where('kode_desa', $kode_desa)->firstOrFail();

        // Kirim data desa ke Blade view
        return view('profil-desa', compact('desa'));
    }

    // Fungsi untuk menyimpan/update data geografis (jika ada form edit admin)
    public function updateGeografis(Request $request, $id)
    {
        $request->validate([
            'luas_wilayah'  => 'nullable|numeric',
            'kode_pos'      => 'nullable|string|max:10',
            'latitude'      => 'nullable|string',
            'longitude'     => 'nullable|string',
            'batas_utara'   => 'nullable|string',
            'batas_selatan' => 'nullable|string',
            'batas_timur'   => 'nullable|string',
            'batas_barat'   => 'nullable|string',
        ]);

        $desa = Desa::findOrFail($id);
        $desa->update($request->all());

        return redirect()->back()->with('success', 'Data profil desa berhasil diperbarui!');
    }
}