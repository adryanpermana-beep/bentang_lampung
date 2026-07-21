<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Desa - @if(!empty($nama_desa) && !is_numeric($nama_desa)){{ $nama_desa }}@else{{ 'Detail Desa' }}@endif</title>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 40px 20px; 
            background: #f8fafc; 
            color: #334155;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
        .btn-back { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            padding: 10px 18px; 
            background: #1e3a8a; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px; 
            transition: background 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-back:hover { 
            background: #1e40af; 
        }
        .card { 
            background: white; 
            padding: 28px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
            border: 1px solid #e2e8f0;
        }
        .card-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-title h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 20px;
        }
        .kab-logo {
            height: 100px;
            width: auto;
            object-fit: contain;
        }
        .info-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 14px;
            border: 1px solid #f1f5f9;
        }
        .info-item strong {
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-value {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
        }
        .badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .empty-value {
            color: #94a3b8;
            font-style: italic;
        }

        /* STYLING UNTUK ATRIBUT GEOGRAFIS */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #028090;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 20px 0 8px 0;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Tombol Kembali -->
        <a href="javascript:history.back()" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Peta
        </a>

        <!-- Card Profil Desa -->
        <div class="card">
            <div class="card-header">
                <div class="header-title">
                    <i class="fa-solid fa-id-card fa-2xl" style="color: #1e3a8a;"></i>
                    <h2>Detail Profil Desa</h2>
                </div>
                
                <!-- LOGO KABUPATEN -->
                <img src="@if(!empty($logo_kab)){{ $logo_kab }}@else{{ asset('images/logo-way-kanan.png') }}@endif" 
                     onerror="this.onerror=null; this.src='{{ asset('images/logo-way-kanan.png') }}';"
                     alt="Logo Kabupaten" 
                     class="kab-logo">
            </div>

            <div class="info-group">
                <!-- NAMA KABUPATEN -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-building-columns" style="color: #64748b;"></i> Kabupaten / Kota:</strong>
                    <span class="info-value">
                        @if(!empty($nama_kab)){{ $nama_kab }}@else Kabupaten Way Kanan @endif
                    </span>
                </div>

                <!-- NAMA KECAMATAN -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-map-location-dot" style="color: #64748b;"></i> Kecamatan:</strong>
                    <span class="info-value">
                        @if(!empty($nama_kec))
                            Kec. {{ $nama_kec }}
                        @else
                            <span class="empty-value">-</span>
                        @endif
                    </span>
                </div>

                <!-- NAMA KELURAHAN / DESA -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-house-chimney" style="color: #64748b;"></i> Kelurahan / Desa:</strong>
                    <span class="info-value" style="color: #1e3a8a; font-size: 15px;">
                        @if(!empty($nama_desa) && !str_contains($nama_desa, $kode_desa))
                            {{ $nama_desa }}
                        @else
                            <span class="empty-value">Nama desa tidak ditemukan</span>
                        @endif
                    </span>
                </div>

                <!-- KODE WILAYAH DESA -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-barcode" style="color: #64748b;"></i> Kode Wilayah Desa:</strong>
                    <span class="badge">
                        @if(!empty($kode_desa)){{ $kode_desa }}@else - @endif
                    </span>
                </div>

                <!-- ======================================================= -->
                <!-- ATRIBUT TAMBAHAN: INFORMASI WILAYAH & GEOGRAFIS        -->
                <!-- ======================================================= -->
                <div class="section-title">
                    <i class="fa-solid fa-earth-asia"></i> Informasi Wilayah & Geografis
                </div>

                <!-- LUAS WILAYAH -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-vector-square" style="color: #10b981;"></i> Luas Wilayah:</strong>
                    <span class="info-value">
                        @if(!empty($luas_wilayah))
                            {{ number_format($luas_wilayah, 2, ',', '.') }} km²
                        @else
                            <span class="empty-value">-</span>
                        @endif
                    </span>
                </div>

                <!-- KODE POS -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-envelope" style="color: #f59e0b;"></i> Kode Pos:</strong>
                    <span class="info-value">
                        @if(!empty($kode_pos))
                            <span class="badge" style="background:#fef3c7; color:#b45309;">{{ $kode_pos }}</span>
                        @else
                            <span class="empty-value">-</span>
                        @endif
                    </span>
                </div>

                <!-- TITIK PUSAT (LATITUDE & LONGITUDE) -->
                <div class="info-item">
                    <strong><i class="fa-solid fa-location-crosshairs" style="color: #ef4444;"></i> Titik Pusat (Lat, Long):</strong>
                    <span class="info-value">
                        @if(!empty($latitude) && !empty($longitude))
                            <a href="https://maps.google.com/?q={{ $latitude }},{{ $longitude }}" target="_blank" style="color:#028090; text-decoration:none;">
                                {{ $latitude }}, {{ $longitude }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
                            </a>
                        @else
                            <span class="empty-value">-</span>
                        @endif
                    </span>
                </div>

            </div>
        </div>
    </div>

</body>
</html>