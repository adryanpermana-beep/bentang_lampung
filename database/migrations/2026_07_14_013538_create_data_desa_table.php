<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Jalankan query mentah untuk memastikan tabel dibuat di skema master_data
        DB::statement('
            CREATE TABLE master_data.data_desa (
                id BIGSERIAL PRIMARY KEY,
                kode_desa VARCHAR(13) NOT NULL,
                jumlah_penduduk INT DEFAULT 0,
                jumlah_kk INT DEFAULT 0,
                sejahtera INT DEFAULT 0,
                pra_sejahtera INT DEFAULT 0,
                miskin_ekstrem INT DEFAULT 0,
                tahun INT NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                CONSTRAINT fk_data_desa_wilayah FOREIGN KEY (kode_desa) 
                    REFERENCES master_data.m_wilayah_desa(kode) ON DELETE CASCADE
            )
        ');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS master_data.data_desa');
    }
};