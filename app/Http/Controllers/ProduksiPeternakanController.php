<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiPeternakanController extends Controller
{
    /**
     * Mengambil GeoJSON Peternakan per Desa/Kelurahan dengan Gradasi Warna Tema Peternakan
     */
    public function getGeojson($kategori = null, $kode_kecamatan = null)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        // 1. Tangkap kode_kecamatan dari berbagai parameter input
        $targetKode = null;

        if (!empty($kode_kecamatan)) {
            $targetKode = $kode_kecamatan;
        } elseif (!empty($kategori) && is_numeric(preg_replace('/[^0-9]/', '', $kategori))) {
            $targetKode = $kategori;
        } elseif (request()->has('kode_kecamatan')) {
            $targetKode = request('kode_kecamatan');
        } elseif (request()->has('kode')) {
            $targetKode = request('kode');
        }

        // 2. Bersihkan karakter non-angka
        $cleanKode = preg_replace('/[^0-9]/', '', $targetKode);

        if (empty($cleanKode)) {
            return response()->json([
                'type'     => 'FeatureCollection',
                'features' => []
            ], 200);
        }

        try {
            // 3. Subquery agregasi detail hasil ternak & populasi per desa
            // Kita petakan jenis_hasil_ternak ke nama_komoditas dan jumlah_populasi ke hasil_panen
            $subQueryDetail = "
                SELECT 
                    kode_desa::text as kode_desa,
                    json_agg(
                        json_build_object(
                            'kode_desa', kode_desa,
                            'nama_komoditas', COALESCE(jenis_hasil_ternak, '-'),
                            'jenis_hasil_ternak', COALESCE(jenis_hasil_ternak, '-'),
                            'luas_lahan', 0,
                            'hasil_panen', COALESCE(jumlah_populasi, 0),
                            'jumlah_populasi', COALESCE(jumlah_populasi, 0),
                            'nilai_produksi', COALESCE(nilai_produksi, 0)
                        )
                    ) as detail_items,
                    COALESCE(SUM(jumlah_populasi), 0) as sum_populasi,
                    COALESCE(SUM(nilai_produksi), 0) as sum_nilai,
                    COUNT(id) as total_jenis
                FROM produksi.peternakan
                GROUP BY kode_desa::text
            ";

            // 4. Query Utama PostGIS dengan Logika Gradasi Warna Dinamis Tema Peternakan
            $sqlFeature = "
                SELECT json_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(d.geometry)::json,
                    'properties', json_build_object(
                        'kode_desa', d.kode,
                        'nama_desa', d.nama,
                        'nama_kec', d.kecamatan,
                        'nama_kab', d.kabupaten,
                        'total_luas_lahan', 0,
                        'total_hasil_panen', COALESCE(dt.sum_populasi, 0),
                        'total_populasi', COALESCE(dt.sum_populasi, 0),
                        'total_nilai_produksi', COALESCE(dt.sum_nilai, 0),
                        'total_jenis_tanaman', COALESCE(dt.total_jenis, 0),
                        'data_produksi', COALESCE(dt.detail_items, '[]'::json),
                        
                        -- Logika Warna Dinamis Amber / Cokelat Peternakan
                        'theme_color', CASE 
                            WHEN COALESCE(dt.sum_populasi, 0) = 0 THEN '#CBD5E1'   -- Abu-abu Slate (Tanpa Data / Populasi 0)
                            WHEN dt.sum_populasi < 100 THEN '#FCD34D'              -- Kuning Emas (Rendah)
                            WHEN dt.sum_populasi < 500 THEN '#F59E0B'              -- Jingga Amber (Sedang)
                            WHEN dt.sum_populasi < 2000 THEN '#D97706'             -- Cokelat Oranye (Tinggi)
                            ELSE '#78350F'                                         -- Cokelat Tua Ternak (Sangat Tinggi)
                        END,
                        'border_color', '#451A03',
                        'hover_color', '#FBBF24'
                    )
                ) as feature
                FROM master_data.wilayah d
                LEFT JOIN ({$subQueryDetail}) dt ON d.kode::text = dt.kode_desa
                WHERE d.kode::text LIKE " . DB::getPdo()->quote($cleanKode . '%') . "
                  AND d.geometry IS NOT NULL
            ";

            // 5. Bungkus menjadi FeatureCollection
            $sqlCollection = "
                SELECT json_build_object(
                    'type', 'FeatureCollection',
                    'features', COALESCE(json_agg(f.feature), '[]'::json)
                ) as geojson
                FROM ({$sqlFeature}) as f
            ";

            $result = DB::select(DB::raw($sqlCollection));
            $geojsonString = isset($result[0]->geojson) ? $result[0]->geojson : '{"type":"FeatureCollection","features":[]}';

            return response($geojsonString, 200)->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan DB: ' . $e->getMessage()
            ], 500);
        }
    }
}