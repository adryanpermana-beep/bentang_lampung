<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\GeoJsonController; 

class KependudukanController extends Controller
{
    /**
     * Membantu menggabungkan GeoJSON dari GeoJsonController dengan Data Kependudukan Tabel Terkait
     */
    private function dapatkanGeoJsonDenganKependudukan($kode_kecamatan, $nama_tabel)
    {
        try {
            // 1. Panggil fungsi internal dari GeoJsonController secara aman
            $geoJsonController = new GeoJsonController();
            $responseSpasial = $geoJsonController->getDesa($kode_kecamatan);
            
            // Ubah response spasial menjadi array PHP biasa
            $geoJsonData = json_decode($responseSpasial->getContent(), true);

            // Jika data spasial kosong atau bukan format GeoJSON yang valid
            if (!isset($geoJsonData['features'])) {
                return response()->json([
                    'type' => 'FeatureCollection',
                    'features' => []
                ]);
            }

            // 2. Ambil data kependudukan dari schema kependudukan berdasarkan kode kecamatan
            $dataKependudukan = DB::table($nama_tabel)
                ->where('kode_desa', 'LIKE', $kode_kecamatan . '%')
                ->get()
                ->groupBy('kode_desa');

            // 3. Sisipkan data kependudukan ke dalam properti 'data_kependudukan' di setiap poligon desa
            foreach ($geoJsonData['features'] as &$feature) {
                // Menggunakan isset() tradisional agar kompatibel dengan PHP versi lama
                $kodeDesa = null;
                if (isset($feature['properties']['kode_desa'])) {
                    $kodeDesa = $feature['properties']['kode_desa'];
                } elseif (isset($feature['properties']['KODE_DESA'])) {
                    $kodeDesa = $feature['properties']['KODE_DESA'];
                }
                
                $feature['properties']['data_kependudukan'] = isset($dataKependudukan[$kodeDesa]) 
                    ? $dataKependudukan[$kodeDesa] 
                    : [];
            }

            return response()->json($geoJsonData);

        } catch (\Exception $e) {
            // Menangkap error logis jika ada kendala sistem backend
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Mengambil data Penduduk dan KK + GeoJSON Desanya
     */
    public function getPendudukKk($kode_wilayah)
    {
        return $this->dapatkanGeoJsonDenganKependudukan($kode_wilayah, 'kependudukan.penduduk_kk');
    }

    /**
     * Mengambil data Kesejahteraan + GeoJSON Desanya
     */
    public function getKesejahteraan($kode_wilayah)
    {
        return $this->dapatkanGeoJsonDenganKependudukan($kode_wilayah, 'kependudukan.kesejahteraan');
    }

    /**
     * Mengambil data Mata Pencaharian + GeoJSON Desanya
     */
    public function getMataPencaharian($kode_wilayah)
    {
        return $this->dapatkanGeoJsonDenganKependudukan($kode_wilayah, 'kependudukan.mata_pencaharian');
    }

    /**
     * Mengambil data Tenaga Kerja + GeoJSON Desanya
     */
    public function getTenagaKerja($kode_wilayah)
    {
        return $this->dapatkanGeoJsonDenganKependudukan($kode_wilayah, 'kependudukan.tenaga_kerja');
    }

    /**
     * Mengambil data Tingkat Pendidikan + GeoJSON Desanya
     */
   public function getTingkatPendidikan($kode_wilayah)
    {
        // Ganti 'nama_kolom_asli_jenjang_di_db' dengan nama kolom asli di tabel Anda (misal: 'status', 'tingkat', atau 'pendidikan')
        return $this->dapatkanGeoJsonDenganKependudukan($kode_wilayah, 'kependudukan.tingkat_pendidikan');
    }
}