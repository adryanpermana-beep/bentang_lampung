<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Wilayah;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\ProduksiApotikHidupController;
use App\Http\Controllers\ProduksiBahanGalianController; // <-- 1. Impor controller baru

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

// === HALAMAN PROFIL DESA (DINAMIS SELURUH KABUPATEN/KOTA SE-LAMPUNG) ===
Route::get('/profil-desa/{kode_desa}', function ($kode_desa) {
    // 1. Set Nilai Default Fallback
    $nama_kab      = 'Provinsi Lampung';
    $nama_kec      = 'Kecamatan Wilayah';
    $nama_desa     = 'Nama Desa Tidak Ditemukan';
    
    // Fallback Nilai Geografis
    $luas_wilayah  = null;
    $kode_pos      = null;
    $latitude      = null;
    $longitude     = null;

    try {
        // 2. Ambil data dari tabel master_data.wilayah menggunakan Model \App\Wilayah
        $wilayah = Wilayah::where('kode', $kode_desa)->first();

        // Jika tidak ketemu pakai Model, query langsung ke DB
        if (!$wilayah) {
            $wilayah = DB::table('master_data.wilayah')->where('kode', $kode_desa)->first();
        }

        // 3. Jika data ditemukan, set nama dan atribut geografis secara dinamis
        if ($wilayah) {
            if (!empty($wilayah->kabupaten))    $nama_kab      = $wilayah->kabupaten;
            if (!empty($wilayah->kecamatan))    $nama_kec      = $wilayah->kecamatan;
            if (!empty($wilayah->nama))         $nama_desa     = $wilayah->nama;
            
            // Mengambil Atribut Geografis
            if (isset($wilayah->luas_wilayah))  $luas_wilayah  = $wilayah->luas_wilayah;
            if (isset($wilayah->kode_pos))      $kode_pos      = $wilayah->kode_pos;
            if (isset($wilayah->latitude))      $latitude      = $wilayah->latitude;
            if (isset($wilayah->longitude))     $longitude     = $wilayah->longitude;

            // === REVERSE GEOCODING OTOMATIS (MENGGUNAKAN CURL) ===
            if (empty($kode_pos) && !empty($latitude) && !empty($longitude)) {
                try {
                    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&addressdetails=1";
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'WebGIS-Lampung-App/1.0');
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    
                    $response = curl_exec($ch);
                    curl_close($ch);

                    if ($response) {
                        $dataApi = json_decode($response, true);

                        if (isset($dataApi['address']['postcode'])) {
                            $kode_pos = $dataApi['address']['postcode'];

                            // Simpan otomatis ke PostgreSQL
                            DB::table('master_data.wilayah')
                                ->where('kode', $kode_desa)
                                ->update(['kode_pos' => $kode_pos]);
                        }
                    }
                } catch (\Exception $apiErr) {
                    // Abaikan jika terjadi kendala koneksi API
                }
            }

            // === FALLBACK KODE POS DEFAULT ===
            if (empty($kode_pos)) {
                $prefix_kab = substr($kode_desa, 0, 4);
                
                $map_kodepos = [
                    '1801' => '35351', // Lampung Selatan
                    '1802' => '34161', // Lampung Tengah
                    '1803' => '34511', // Lampung Utara
                    '1804' => '34811', // Lampung Barat
                    '1805' => '34611', // Tulang Bawang
                    '1806' => '35384', // Tanggamus
                    '1807' => '34194', // Lampung Timur
                    '1808' => '34764', // Way Kanan
                    '1809' => '35371', // Pesawaran
                    '1810' => '35373', // Pringsewu
                    '1811' => '34698', // Mesuji
                    '1812' => '34691', // Tulang Bawang Barat
                    '1813' => '34874', // Pesisir Barat
                    '1871' => '35111', // Bandar Lampung
                    '1872' => '34111', // Metro
                ];

                if (isset($map_kodepos[$prefix_kab])) {
                    $kode_pos = $map_kodepos[$prefix_kab];
                } else {
                    $kode_pos = '34000';
                }

                // Simpan fallback ke DB
                DB::table('master_data.wilayah')
                    ->where('kode', $kode_desa)
                    ->update(['kode_pos' => $kode_pos]);
            }
        }
    } catch (\Exception $e) {
        // Biarkan menggunakan nilai default
    }

    // 4. Deteksi Logo Kabupaten Dinamis
    $kode_kab = substr($kode_desa, 0, 4);
    $path_png = 'images/logo/' . $kode_kab . '.png';
    $path_jpg = 'images/logo/' . $kode_kab . '.jpg';

    if (file_exists(public_path($path_png))) {
        $logo_kab = asset($path_png);
    } elseif (file_exists(public_path($path_jpg))) {
        $logo_kab = asset($path_jpg);
    } else {
        $logo_kab = asset('images/logo-way-kanan.png');
    }

    // 5. Kirimkan seluruh data ke profil-desa.blade.php
    return view('profil-desa', [
        'kode_desa'    => $kode_desa,
        'nama_kab'     => $nama_kab,
        'nama_kec'     => $nama_kec,
        'nama_desa'    => $nama_desa,
        'logo_kab'     => $logo_kab,
        'luas_wilayah' => $luas_wilayah,
        'kode_pos'     => $kode_pos,
        'latitude'     => $latitude,
        'longitude'    => $longitude,
    ]);
});

// === API SPASIAL & WILAYAH ===
Route::get('/api/batas-kabupaten', 'API\GeoJsonController@getBatasKabupaten');
Route::get('/api/kabupaten', 'API\GeoJsonController@getKabupaten');
Route::get('/api/kecamatan/{kabupaten_code}', 'API\GeoJsonController@getKecamatan');
Route::get('/api/desa/{kecamatan_code}', 'API\GeoJsonController@getDesa');

// === API TEMATIK KEPENDUDUKAN ===
Route::get('/api/wilayah/kependudukan/penduduk-kk/{kode_kecamatan}', 'API\KependudukanController@getPendudukKk');
Route::get('/api/wilayah/kependudukan/kesejahteraan/{kode_kecamatan}', 'API\KependudukanController@getKesejahteraan');
Route::get('/api/wilayah/kependudukan/mata-pencaharian/{kode_kecamatan}', 'API\KependudukanController@getMataPencaharian');
Route::get('/api/wilayah/kependudukan/tenaga-kerja/{kode_kecamatan}', 'API\KependudukanController@getTenagaKerja');
Route::get('/api/wilayah/kependudukan/tingkat-pendidikan/{kode_kecamatan}', 'API\KependudukanController@getTingkatPendidikan');

// === API TEMATIK PRODUKSI ===
// Special Route untuk Apotik Hidup (Menuju ProduksiApotikHidupController)
Route::get('/api/tematik/produksi/apotik-hidup/{kode_kecamatan}', 'ProduksiApotikHidupController@getGeojson');

// Special Route untuk Bahan Galian (Menuju ProduksiBahanGalianController) <-- 2. Tambah Rute Ini
Route::get('/api/tematik/produksi/bahan-galian/{kode_kecamatan}', 'ProduksiBahanGalianController@getGeojson');

// Route Generik untuk Kategori Produksi Lainnya (Pertanian, Perikanan, Peternakan, dll)
Route::get('/api/tematik/produksi/{kategori}/{kode_kecamatan}', 'ProduksiController@getProduksiData');

// === FITUR LOGIN ADMIN ===
Route::group(['middleware' => ['web']], function () {
    Route::get('/halaman-login', 'AdminLoginController@showForm');
    Route::post('/masuk-admin', 'AdminLoginController@prosesLogin');
    Route::post('/keluar-admin', 'AdminLoginController@prosesLogout');
});