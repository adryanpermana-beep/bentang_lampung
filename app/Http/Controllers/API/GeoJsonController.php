<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeoJsonController extends Controller
{
    /**
     * Mengambil seluruh kabupaten/kota khusus Provinsi Lampung (Kode awalan '18')
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
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memproses data kabupaten: ' . $e->getMessage()], 500);
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
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memproses data kecamatan: ' . $e->getMessage()], 500);
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
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memproses data desa: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil data spasial desa sekaligus JOIN dengan data dari schema kesehatan
     * PERBAIKAN: Konstruksi GeoJSON manual agar tidak memicu error data pada agregasi JSONB
     */
    public function getKesehatan($kecamatan_code)
    {
        $query = "
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(w.geometry)::json,
                        'properties', json_build_object(
                            'kode_desa', w.kode,
                            'nama_desa', w.nama,
                            'data_kesehatan', COALESCE(
                                jsonb_agg(
                                    jsonb_build_object(
                                        'provinsi', k.provinsi,
                                        'kabupaten_kota', k.kabupaten_kota,
                                        'kecamatan', k.kecamatan,
                                        'status', k.status,
                                        'jenis_tenaga_medis', k.jenis_tenaga_medis,
                                        'jumlah_personil', k.jumlah_personil,
                                        'tanggal_data', k.tanggal_data
                                    )
                                ) FILTER (WHERE k.kode_desa IS NOT NULL), '[]'::jsonb
                            )
                        )
                    )
                ), '[]'::json)
            ) as geojson
            FROM master_data.wilayah w
            LEFT JOIN kesehatan.tenaga_medis k ON w.kode = k.kode_desa
            WHERE LENGTH(w.kode) = 10 
              AND SUBSTRING(w.kode FROM 1 FOR 6) = :kecamatan_code
            GROUP BY w.kode, w.nama, w.geometry;
        ";

        try {
            $result = DB::select($query, ['kecamatan_code' => $kecamatan_code]);
            
            if (empty($result) || !$result[0]->geojson) {
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal memproses data tematik kesehatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batas Kabupaten Terluar (Dissolve & Boundary)
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
            return response()->json(['error' => 'Gagal memproses garis batas luar: ' . $e->getMessage()], 500);
        }
    }
}