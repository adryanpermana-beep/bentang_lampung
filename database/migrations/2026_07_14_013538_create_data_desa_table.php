<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataDesaTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('data_desa', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_desa', 20)->nullable();
            $table->string('kode_kec', 20)->nullable();
            $table->string('nama_desa');

            // --- TAMBAHAN ATRIBUT GEOGRAFIS & WILAYAH ---
            $table->decimal('luas_wilayah', 8, 2)->nullable();
            $table->string('batas_utara')->nullable();
            $table->string('batas_selatan')->nullable();
            $table->string('batas_timur')->nullable();
            $table->string('batas_barat')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('kode_pos', 10)->nullable();
            // ---------------------------------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('data_desa');
    }
}