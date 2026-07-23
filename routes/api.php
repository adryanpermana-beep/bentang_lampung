<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =========================================================================
// ROUTE API DRILLDOWN PETA LAMPUNG
// =========================================================================

// 1. Ambil seluruh Kabupaten se-Lampung
Route::get('/wilayah/kabupaten', 'API\GeoJsonController@getKabupaten');

// 2. Ambil Kecamatan berdasarkan Kode Kabupaten
Route::get('/wilayah/kecamatan/{kabupaten_code}', 'API\GeoJsonController@getKecamatan');

// 3. Ambil Desa berdasarkan Kode Kecamatan
Route::get('/wilayah/desa/{kecamatan_code}', 'API\GeoJsonController@getDesa');

// 4. Ambil Tematik Data Kesehatan Desa (Tenaga Medis) berdasarkan Kode Kecamatan
Route::get('/wilayah/kesehatan/{kecamatan_code}', 'API\GeoJsonController@getKesehatan');

// 5. Ambil Tematik Data Sumber Air Bersih berdasarkan Kode Kecamatan
Route::get('/wilayah/air-bersih/{kecamatan_code}', 'API\GeoJsonController@getAirBersih');

// 6. Ambil Tematik Data Sanitasi berdasarkan Kode Kecamatan
Route::get('/wilayah/sanitasi/{kecamatan_code}', 'API\GeoJsonController@getSanitasi');

// 7. Garis batas luar terluar kabupaten
Route::get('/batas-kabupaten', 'API\GeoJsonController@getBatasKabupaten');