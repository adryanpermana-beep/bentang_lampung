<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class WilayahDesa extends Model
{
    use HasSpatial;

    // Menghubungkan ke tabel spasial postgre milikmu
    protected $table = 'master_data.m_wilayah_desa';
    
    protected $primaryKey = 'id';
    public $timestamps = false; // Set ke false jika tabel m_wilayah_desa bawaan tidak punya created_at/updated_at

    protected $fillable = [
        'kode', 
        'nama_desa', 
        'kecamatan', 
        'kabupaten', 
        'koordinat_batas'
    ];

    // Casting kolom geometri koordinat_batas agar dikenali sebagai objek spasial oleh Laravel
    protected $casts = [
        'koordinat_batas' => MultiPolygon::class,
    ];

    // Relasi ke tabel DataAtribut Prodeskel
    public function dataDesa()
    {
        return $this->hasMany(DataDesa::class, 'kode_desa', 'kode');
    }
}