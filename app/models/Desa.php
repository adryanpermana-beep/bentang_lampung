<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    // Arahkan ke nama tabel yang benar di database
    protected $table = 'data_desa';

    protected $fillable = [
        'kode_desa',
        'kode_kec',
        'nama_desa',
        'luas_wilayah',
        'batas_utara',
        'batas_selatan',
        'batas_timur',
        'batas_barat',
        'latitude',
        'longitude',
        'kode_pos',
    ];
}