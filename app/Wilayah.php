<?php

namespace App; // Menggunakan namespace App secara langsung

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    // Mengarahkan ke schema master_data dan tabel wilayah sesuai pgAdmin Anda
    protected $table = 'master_data.wilayah';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode', 'nama', 'kabupaten', 'kecamatan', 'tipologi', 
        'luas_ha', 'ketinggian_dpl', 'status', 'latitude', 'longitude', 'geometry'
    ];
}