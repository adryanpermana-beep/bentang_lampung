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
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(geometry)::json,
                        'properties', json_build_object(
                            'kode_kab', kode,
                            'nama_kab', nama
                        )
                    )
                ), '[]'::json)
            ) as geojson
            FROM master_data.wilayah
            WHERE LENGTH(kode) = 4 
              AND kode LIKE '18%';
        ";

        try {
            $result = DB::select($query);
            if (empty($result) || !$result[0]->geojson) {
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal memproses data kabupaten: ' . $e->getMessage()
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
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(geometry)::json,
                        'properties', json_build_object(
                            'kode_kec', kode,
                            'nama_kec', nama
                        )
                    )
                ), '[]'::json)
            ) as geojson
            FROM master_data.wilayah
            WHERE LENGTH(kode) = 6 
              AND SUBSTRING(kode FROM 1 FOR 4) = :kabupaten_code;
        ";

        try {
            $result = DB::select($query, ['kabupaten_code' => $kabupaten_code]);
            if (empty($result) || !$result[0]->geojson) {
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal memproses data kecamatan: ' . $e->getMessage()
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
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(geometry)::json,
                        'properties', json_build_object(
                            'kode_desa', kode,
                            'nama_desa', nama
                        )
                    )
                ), '[]'::json)
            ) as geojson
            FROM master_data.wilayah
            WHERE LENGTH(kode) = 10 
              AND SUBSTRING(kode FROM 1 FOR 6) = :kecamatan_code;
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
                'message' => 'Gagal memproses data desa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil data spasial desa sekaligus JOIN dengan data Tenaga Medis (Kesehatan)
     */
    public function getKesehatan($kecamatan_code)
    {
        $query = "
            WITH data_medis_aggregated AS (
                SELECT 
                    kode_desa,
                    jsonb_agg(
                        jsonb_build_object(
                            'provinsi', provinsi,
                            'kabupaten_kota', kabupaten_kota,
                            'kecamatan', kecamatan,
                            'status', status,
                            'jenis_tenaga_medis', jenis_tenaga_medis,
                            'jumlah_personil', jumlah_personil,
                            'tanggal_data', tanggal_data
                        )
                    ) AS list_kesehatan
                FROM kesehatan.tenaga_medis
                GROUP BY kode_desa
            ),
            desa_features AS (
                SELECT 
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(w.geometry)::json,
                        'properties', json_build_object(
                            'kode_desa', w.kode,
                            'nama_desa', w.nama,
                            'data_kesehatan', COALESCE(m.list_kesehatan, '[]'::jsonb)
                        )
                    ) AS feature_object
                FROM master_data.wilayah w
                LEFT JOIN data_medis_aggregated m ON w.kode = m.kode_desa
                WHERE LENGTH(w.kode) = 10 
                  AND SUBSTRING(w.kode FROM 1 FOR 6) = :kecamatan_code
            )
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(feature_object), '[]'::json)
            ) as geojson
            FROM desa_features;
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
     * Mengambil data spasial desa sekaligus JOIN dengan data Sumber Air Bersih
     */
    public function getAirBersih($kecamatan_code)
    {
        $query = "
            WITH air_aggregated AS (
                SELECT 
                    kode_desa,
                    jsonb_agg(
                        jsonb_build_object(
                            'jenis_sumber_air', jenis_sumber_air,
                            'jumlah_unit', jumlah_unit,
                            'jumlah_pemakai', jumlah_pemakai,
                            'kondisi', kondisi,
                            'rasio_pemanfaatan', rasio_pemanfaatan,
                            'tanggal_data', tanggal_data
                        )
                    ) AS list_air
                FROM kesehatan.sumber_air_bersih
                GROUP BY kode_desa
            ),
            desa_features AS (
                SELECT 
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(w.geometry)::json,
                        'properties', json_build_object(
                            'kode_desa', w.kode,
                            'nama_desa', w.nama,
                            'data_air', COALESCE(m.list_air, '[]'::jsonb)
                        )
                    ) AS feature_object
                FROM master_data.wilayah w
                LEFT JOIN air_aggregated m ON w.kode = m.kode_desa
                WHERE LENGTH(w.kode) = 10 
                  AND SUBSTRING(w.kode FROM 1 FOR 6) = :kecamatan_code
            )
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(feature_object), '[]'::json)
            ) as geojson
            FROM desa_features;
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
                'message' => 'Gagal memproses data air bersih: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil data spasial desa sekaligus JOIN dengan data Sanitasi Lingkungan
     */
    public function getSanitasi($kecamatan_code)
    {
        $query = "
            WITH sanitasi_aggregated AS (
                SELECT 
                    kode_desa,
                    jsonb_agg(
                        jsonb_build_object(
                            'jamban_bersama', jamban_bersama,
                            'jamban_non_leher_angsa', jamban_non_leher_angsa,
                            'jamban_leher_angsa', jamban_leher_angsa,
                            'tanggal_data', tanggal_data
                        )
                    ) AS list_sanitasi
                FROM kesehatan.sanitasi
                GROUP BY kode_desa
            ),
            desa_features AS (
                SELECT 
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(w.geometry)::json,
                        'properties', json_build_object(
                            'kode_desa', w.kode,
                            'nama_desa', w.nama,
                            'data_sanitasi', COALESCE(m.list_sanitasi, '[]'::jsonb)
                        )
                    ) AS feature_object
                FROM master_data.wilayah w
                LEFT JOIN sanitasi_aggregated m ON w.kode = m.kode_desa
                WHERE LENGTH(w.kode) = 10 
                  AND SUBSTRING(w.kode FROM 1 FOR 6) = :kecamatan_code
            )
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(feature_object), '[]'::json)
            ) as geojson
            FROM desa_features;
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
                'message' => 'Gagal memproses data sanitasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batas Kabupaten Terluar (Dissolve & Boundary untuk Outline Utama Peta)
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
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(ST_Boundary(geom))::json,
                        'properties', json_build_object(
                            'kode_kab', kode_kab
                        )
                    )
                ), '[]'::json)
            ) as geojson
            FROM kabupaten_polygon;
        ";

        try {
            $result = DB::select($query);
            if (empty($result) || !$result[0]->geojson) {
                return response()->json(['type' => 'FeatureCollection', 'features' => []]);
            }
            return response()->json(json_decode($result[0]->geojson));
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal memproses garis batas luar: ' . $e->getMessage()
            ], 500);
        }
    }
}