<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataDesa extends Model
{
    // Pastikan ada 'master_data.' di depan nama tabel
    protected $table = 'master_data.data_desa';
    
    protected $fillable = [
        'kode_desa', 
        'jumlah_penduduk', 
        'jumlah_kk', 
        'sejahtera', 
        'pra_sejahtera', 
        'miskin_ekstrem', 
        'tahun'
    ];
}