<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiApotikHidup extends Model
{
    use HasFactory;

    // Nama schema dan tabel di PostgreSQL
    protected $table = 'produksi.apotik_hidup';

    protected $fillable = [
        'provinsi',
        'nama_kab',
        'nama_kec',
        'status_desa',
        'kode_desa',
        'nama_desa',
        'tanggal_data',
        'nama_tanaman',
        'luas_lahan',
        'hasil_panen',
    ];
}