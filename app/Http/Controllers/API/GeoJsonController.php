<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Wilayah; // Mengimpor model Wilayah dari namespace App

class GeoJsonController extends Controller
{
    /**
     * Mengambil seluruh kabupaten/kota khusus Provinsi Lampung (Kode awalan '18')
     * langsung dari database PostGIS dengan format GeoJSON yang presisi.
     */
    public function getKabupaten()
    {
        $query = "
            WITH kabupaten_polygon AS (
                SELECT 
                    kode AS kode_kab,
                    nama AS nama_kab, 
                    geometry AS geom
                FROM master_data.wilayah
                -- Panjang kode 4 digit adalah level Kabupaten/Kota
                -- Awalan '18' membatasi hanya wilayah di Provinsi Lampung
                WHERE LENGTH(kode) = 4 
                  AND kode LIKE '18%' 
            )
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(ST_AsGeoJSON(t.*)::json)
            ) as geojson
            FROM (
                SELECT kode_kab, nama_kab, geom AS geometry FROM kabupaten_polygon
            ) AS t;
        ";

        try {
            $result = DB::select($query);
            
            if (empty($result) || !$result[0]->geojson) {
                return response()->json([
                    'type' => 'FeatureCollection',
                    'features' => []
                ]);
            }

            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses data spasial kabupaten: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil seluruh kecamatan berdasarkan kode kabupaten induknya
     */
    public function getKecamatan($kabupaten_code)
    {
        $query = "
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(ST_AsGeoJSON(t.*)::json)
            ) as geojson
            FROM (
                SELECT 
                    kode AS kode_kec,
                    nama AS nama_kec,
                    geometry 
                FROM master_data.wilayah
                WHERE LENGTH(kode) = 6 
                  AND SUBSTRING(kode FROM 1 FOR 4) = :kabupaten_code
            ) AS t;
        ";

        try {
            $result = DB::select($query, ['kabupaten_code' => $kabupaten_code]);
            
            if (empty($result) || !$result[0]->geojson) {
                return response()->json([
                    'type' => 'FeatureCollection',
                    'features' => []
                ]);
            }

            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses data kecamatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil seluruh desa/kelurahan berdasarkan kode kecamatan induknya
     */
    public function getDesa($kecamatan_code)
    {
        $query = "
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(ST_AsGeoJSON(t.*)::json)
            ) as geojson
            FROM (
                SELECT 
                    kode AS kode_desa,
                    nama AS nama_desa,
                    geometry 
                FROM master_data.wilayah
                WHERE LENGTH(kode) = 10 
                  AND SUBSTRING(kode FROM 1 FOR 6) = :kecamatan_code
            ) AS t;
        ";

        try {
            $result = DB::select($query, ['kecamatan_code' => $kecamatan_code]);
            
            if (empty($result) || !$result[0]->geojson) {
                return response()->json([
                    'type' => 'FeatureCollection',
                    'features' => []
                ]);
            }

            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses data desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batas Kabupaten Terluar (Dissolve & Boundary) untuk garis overlay merah/hitam tebal di peta
     */
    public function getBatasKabupaten()
    {
        $query = "
            WITH kabupaten_polygon AS (
                SELECT 
                    SUBSTRING(kode FROM 1 FOR 4) AS kode_kab,
                    ST_Union(geometry) AS geom
                FROM master_data.wilayah
                WHERE LENGTH(kode) = 10
                  AND kode LIKE '18%'
                GROUP BY SUBSTRING(kode FROM 1 FOR 4)
            )
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(ST_AsGeoJSON(t.*)::json)
            ) as geojson
            FROM (
                SELECT kode_kab, ST_Boundary(geom) AS geometry FROM kabupaten_polygon
            ) AS t;
        ";

        try {
            $result = DB::select($query);
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses garis batas luar: ' . $e->getMessage()
            ], 500);
        }
    }
}