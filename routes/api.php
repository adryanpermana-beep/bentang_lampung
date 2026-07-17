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
// ROUTE API DRILLDOWN PETA LAMPUNG (FORMAT KLASIK LARAVEL)
// =========================================================================

// 1. Ambil seluruh Kabupaten se-Lampung
Route::get('/wilayah/kabupaten', 'API\GeoJsonController@getKabupaten');

// 2. Ambil Kecamatan berdasarkan Kode Kabupaten
Route::get('/wilayah/kecamatan/{kabupaten_code}', 'API\GeoJsonController@getKecamatan');

// 3. Ambil Desa berdasarkan Kode Kecamatan
Route::get('/wilayah/desa/{kecamatan_code}', 'API\GeoJsonController@getDesa');