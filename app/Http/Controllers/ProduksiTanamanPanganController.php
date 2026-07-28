<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiTanamanPanganController extends Controller
{
    /**
     * Mengambil GeoJSON Tanaman Pangan per Desa/Kelurahan dengan Gradasi Warna Tema Hijau
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
            // 3. Subquery agregasi detail tanaman pangan per desa
            $subQueryDetail = "
                SELECT 
                    kode_desa::text as kode_desa,
                    json_agg(
                        json_build_object(
                            'kode_desa', kode_desa,
                            'nama_komoditas', COALESCE(jenis_tanaman, '-'),
                            'jenis_tanaman', COALESCE(jenis_tanaman, '-'),
                            'luas_lahan', COALESCE(luas_lahan, 0),
                            'hasil_panen', COALESCE(hasil_panen, 0),
                            'nilai_produksi', COALESCE(nilai_produksi, 0)
                        )
                    ) as detail_items,
                    COALESCE(SUM(luas_lahan), 0) as sum_luas,
                    COALESCE(SUM(hasil_panen), 0) as sum_hasil,
                    COALESCE(SUM(nilai_produksi), 0) as sum_nilai,
                    COUNT(id) as total_jenis
                FROM produksi.tanaman_pangan
                GROUP BY kode_desa::text
            ";

            // 4. Query Utama PostGIS dengan Gradasi Warna Hijau Pertanian (Padi / Tanaman Pangan)
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
                        'total_hasil_panen', COALESCE(dt.sum_hasil, 0),
                        'total_nilai_produksi', COALESCE(dt.sum_nilai, 0),
                        'total_jenis_tanaman', COALESCE(dt.total_jenis, 0),
                        'data_produksi', COALESCE(dt.detail_items, '[]'::json),
                        
                        -- Gradasi Warna Tema Hijau Tanaman Pangan
                        'theme_color', CASE 
                            WHEN COALESCE(dt.sum_hasil, 0) = 0 THEN '#CBD5E1'   -- Abu-abu Slate (Tanpa Data / Hasil 0)
                            WHEN dt.sum_hasil < 50 THEN '#A7F3D0'              -- Hijau Muda Pastel (Rendah)
                            WHEN dt.sum_hasil < 200 THEN '#34D399'             -- Hijau Mint (Sedang)
                            WHEN dt.sum_hasil < 1000 THEN '#059669'            -- Hijau Zamrud (Tinggi)
                            ELSE '#064E3B'                                     -- Hijau Tua Pekat (Sangat Tinggi)
                        END,
                        'border_color', '#022C22',
                        'hover_color', '#6EE7B7'
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