<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiHasilTangkapanController extends Controller
{
    /**
     * Mengambil GeoJSON Hasil Tangkapan per Desa/Kelurahan dengan Gradasi Warna Biru Bahari
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
            // 3. Subquery agregasi detail komoditas hasil tangkapan per desa
            $subQueryDetail = "
                SELECT 
                    kode_desa::text as kode_desa,
                    json_agg(
                        json_build_object(
                            'kode_desa', kode_desa,
                            'nama_komoditas', nama_komoditas,
                            'luas_lahan', 0,
                            'hasil_panen', COALESCE(hasil_panen, 0)
                        )
                    ) as detail_items,
                    0 as sum_luas,
                    COALESCE(SUM(hasil_panen), 0) as sum_produksi,
                    COUNT(id) as total_jenis
                FROM produksi.hasil_tangkapan
                GROUP BY kode_desa::text
            ";

            // 4. Query Utama PostGIS dengan Logika Gradasi Warna Dinamis Tema Biru Bahari
            $sqlFeature = "
                SELECT json_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(d.geometry)::json,
                    'properties', json_build_object(
                        'kode_desa', d.kode,
                        'nama_desa', d.nama,
                        'nama_kec', d.kecamatan,
                        'nama_kab', d.kabupaten,
                        'total_luas_lahan', COALESCE(dt.sum_luas, 0),
                        'total_hasil_panen', COALESCE(dt.sum_produksi, 0),
                        'total_jenis_tanaman', COALESCE(dt.total_jenis, 0),
                        'data_produksi', COALESCE(dt.detail_items, '[]'::json),
                        
                        -- Logika Warna Dinamis Biru Bahari
                        'theme_color', CASE 
                            WHEN COALESCE(dt.sum_produksi, 0) = 0 THEN '#CBD5E1'   -- Abu-abu Slate (Tanpa Data / Produksi 0)
                            WHEN dt.sum_produksi < 10 THEN '#38BDF8'               -- Biru Cerah (Rendah)
                            WHEN dt.sum_produksi < 100 THEN '#0284C7'              -- Biru Laut (Sedang)
                            WHEN dt.sum_produksi < 1000 THEN '#1E40AF'             -- Biru Tua (Tinggi)
                            ELSE '#0F172A'                                         -- Biru Navy Gelap (Sangat Tinggi)
                        END,
                        'border_color', '#0F172A',
                        'hover_color', '#38BDF8'
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