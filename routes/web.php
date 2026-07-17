<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute default halaman utama Laravel
Route::get('/', function () {
    return view('welcome');
});

// Halaman utama WebGIS
Route::get('/peta', function () {
    return view('peta');
});

// === PERBAIKAN DI SINI ===
// Mengarahkan ke getBatasKabupaten, bukan getKabupaten
Route::get('/api/batas-kabupaten', 'API\GeoJsonController@getBatasKabupaten');

Route::get('/api/kabupaten', 'API\GeoJsonController@getKabupaten');
Route::get('/api/kecamatan/{kabupaten_code}', 'API\GeoJsonController@getKecamatan');
Route::get('/api/desa/{kecamatan_code}', 'API\GeoJsonController@getDesa');