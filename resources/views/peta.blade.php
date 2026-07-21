<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGIS Tapal Desa - Provinsi Lampung</title>
    
    <!-- CSS Leaflet & FontAwesome -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR STYLE */
        #sidebar { width: 320px; height: 100vh; background: #ffffff; box-shadow: 2px 0 15px rgba(0,0,0,0.1); z-index: 2000; display: flex; flex-direction: column; overflow-y: auto; }
        
        .brand-section { 
            padding: 25px 15px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-bottom: 1px solid #f0f0f0; 
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px; 
            width: 100%;
        }
        .logo-left { height: 55px; object-fit: contain; }
        .logo-right { height: 55px; object-fit: contain; }
        
        .login-btn { margin: 15px 20px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; background: none; cursor: pointer; text-align: left; color: #666; font-weight: 500; display: flex; align-items: center; gap: 10px; transition: 0.2s; }
        .login-btn:hover { background: #f8f9fa; }
        
        .filter-section { padding: 0 20px 20px 20px; display: flex; flex-direction: column; gap: 12px; border-bottom: 1px solid #f0f0f0; }
        .filter-select { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; background-color: #ffffff; color: #333; font-size: 14px; outline: none; cursor: pointer; }
        .filter-select:disabled { background-color: #f5f5f5; color: #aaa; cursor: not-allowed; }
        
        .menu-heading { padding: 15px 20px 5px 20px; font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        .menu-list { list-style: none; padding: 0; margin: 0; }
        .menu-item { padding: 12px 20px; display: flex; align-items: center; gap: 12px; color: #555; font-size: 14px; cursor: pointer; transition: 0.2s; text-decoration: none; position: relative; }
        .menu-item i:first-child { width: 20px; text-align: center; color: #666; }
        .menu-item:hover { background: #f4f7f6; color: #028090; }
        
        /* Gaya Ikon Panah Dropdown */
        .menu-item .fa-chevron-right {
            margin-left: auto;
            font-size: 11px;
            color: #aaa;
            transition: transform 0.3s ease;
        }
        .rotate-arrow { transform: rotate(90deg); }

        /* STYLING SUBMENU (ACCORDION EFFECT) */
        .submenu-list {
            list-style: none;
            padding-left: 15px;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            background: #fcfdfe;
            transition: max-height 0.3s ease-out;
            border-left: 3px solid #f0f0f0;
            margin-left: 25px;
        }
        .submenu-open { max-height: 300px; }
        .submenu-item { padding: 10px 15px; display: flex; align-items: center; gap: 10px; color: #666; font-size: 13px; cursor: pointer; text-decoration: none; transition: 0.2s; }
        .submenu-item i { width: 18px; text-align: center; font-size: 12px; color: #888; }
        .submenu-item:hover { color: #028090; background: #f4f7f6; }
        
        /* WADAH PETA */
        #map { flex: 1; height: 100vh; position: relative; }
        
        #back-btn { position: absolute; top: 20px; left: 20px; z-index: 1000; background: #ffffff; color: #333; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 10px rgba(0,0,0,0.15); display: none; align-items: center; gap: 8px; }
        #back-btn:hover { background: #f4f4f4; }
        
        /* Custom label styling */
        .map-label {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #333;
            border-radius: 4px;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 12px;
            color: #2c3e50;
            box-shadow: 0 1px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <!-- SIDEBAR KIRI -->
    <div id="sidebar">
        <div class="brand-section">
            <div class="logo-container">
                <img src="/remove.png" alt="Logo Tapal Desa" class="logo-left">
                <img src="/remove2.png" alt="Logo Lampung" class="logo-right">
            </div>
        </div>
        
        <!-- Area Kontainer Login Admin -->
        <div class="login-container" style="margin-left: 24px; margin-right: 24px; margin-bottom: 16px;">
            @guest
                <a href="/halaman-login" style="text-decoration: none; display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; color: #0f172a; background-color: #f0f9ff; font-family: system-ui, -apple-system, sans-serif; font-size: 15px; font-weight: 500; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s; box-sizing: border-box;">
                    <i class="fas fa-user-lock" style="color: #1e40af; font-size: 16px;"></i>
                    <span style="color: #1e40af; font-weight: 600;">Login Admin</span>
                </a>
            @endguest

            @auth
                <div style="background-color: #f0f9ff; border: 1px solid #bee3f8; border-radius: 10px; padding: 16px; font-family: system-ui, -apple-system, sans-serif; font-size: 14px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); box-sizing: border-box;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #1e40af; font-weight: 600; margin-bottom: 6px;">
                        <i class="fas fa-user-shield" style="font-size: 16px;"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 12px; color: #1e40af; font-weight: 500; opacity: 0.8;">Status: Administrator</p>
                    
                    <form action="/keluar-admin" method="POST" style="margin: 0;">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button type="submit" style="width: 100%; background: #dc2626; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.2s;">
                            <i class="fas fa-power-off" style="font-size: 12px;"></i> Keluar
                        </button>
                    </form>
                </div>
            @endauth
        </div>
        
        <div class="filter-section">
            <select id="select-kabupaten" class="filter-select" onchange="filterFromDropdown('kabupaten', this.value)">
                <option value="">Pilih Kota/Kabupaten</option>
            </select>
            <select id="select-kecamatan" class="filter-select" onchange="filterFromDropdown('kecamatan', this.value)" disabled>
                <option value="">Pilih Kecamatan</option>
            </select>
            <select id="select-desa" class="filter-select" onchange="filterFromDropdown('desa', this.value)" disabled>
                <option value="">Pilih Desa/Kelurahan</option>
            </select>
        </div>
        
        <!-- FITUR DATA TEMATIK -->
        <div class="menu-heading">Data Tematik</div>
        <ul class="menu-list">
            
            <!-- Menu Utama Kesehatan -->
            <a href="javascript:void(0)" class="menu-item" onclick="toggleKesehatanMenu(this)">
                <i class="fa-solid fa-notes-medical"></i> Kesehatan
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <ul id="submenu-kesehatan" class="submenu-list">
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikKesehatan(event)">
                    <i class="fa-solid fa-user-doctor"></i> Tenaga Medis
                </a>
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikUmum(event, 'air-bersih')">
                    <i class="fa-solid fa-droplet"></i> Sumber Air Bersih
                </a>
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikUmum(event, 'sanitasi')">
                    <i class="fa-solid fa-faucet-drip"></i> Sanitasi Lingkungan
                </a>
            </ul>

            <!-- Menu Utama Kependudukan -->
            <a href="javascript:void(0)" class="menu-item" onclick="toggleKependudukanMenu(this)">
                <i class="fa-solid fa-id-card"></i> Kependudukan
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <ul id="submenu-kependudukan" class="submenu-list">
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikKependudukan(event, 'penduduk-kk')">
                    <i class="fa-solid fa-users"></i> Penduduk dan KK
                </a>
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikKependudukan(event, 'kesejahteraan')">
                    <i class="fa-solid fa-hand-holding-heart"></i> Kesejahteraan
                </a>
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikKependudukan(event, 'mata-pencaharian')">
                    <i class="fa-solid fa-briefcase"></i> Mata Pencaharian
                </a>
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikKependudukan(event, 'tenaga-kerja')">
                    <i class="fa-solid fa-user-gear"></i> Tenaga Kerja
                </a>
            </ul>
            
            <!-- Menu Utama Pendidikan -->
            <a href="javascript:void(0)" class="menu-item" onclick="togglePendidikanMenu(this)">
                <i class="fa-solid fa-graduation-cap"></i> Pendidikan
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <ul id="submenu-pendidikan" class="submenu-list">
                <a href="javascript:void(0)" class="submenu-item" onclick="loadTematikKependudukan(event, 'tingkat-pendidikan')">
                    <i class="fa-solid fa-graduation-cap"></i> Tingkat Pendidikan
                </a>
            </ul>

            <!-- === FITUR LAPANGAN PEKERJAAN (100% SEJAJAR PRESISI) === -->
<style>
    /* Menghilangkan margin/padding pembungkus grup agar persis menu biasa */
    .menu-group-pekerjaan {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
    }
    
    /* Memaksa ukuran area ikon agar sama persis dengan menu lainnya */
    .menu-item .icon-wrapper-pekerjaan {
        width: 24px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin-right: 10px;
    }
</style>

<div class="menu-group-pekerjaan">
    <!-- Menu Utama -->
    <div class="menu-item" onclick="toggleSubPekerjaan(this)" style="cursor: pointer;">
        <div class="menu-title" style="display: flex; align-items: center;">
            <span class="icon-wrapper-pekerjaan">
                <i class="fa-solid fa-briefcase"></i>
            </span>
            <span>Lapangan Pekerjaan</span>
        </div>
        <i class="fa-solid fa-chevron-right arrow-icon-pekerjaan" style="transition: transform 0.3s ease;"></i>
    </div>

    <!-- Sub Menu (Tampil Saat Diklik) -->
    <div id="submenu-pekerjaan" style="display: none; padding-left: 34px; margin: 6px 0;">
        <a href="https://id.jobstreet.com/id/jobs/in-Lampung" target="_blank" style="display: flex; align-items: center; padding: 8px 12px; color: #475569; text-decoration: none; font-size: 13.5px; font-weight: 500; border-radius: 6px; background-color: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.2s ease;">
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 12px; margin-right: 8px; color: #1e3a8a;"></i>
            <span>Lihat Lowongan</span>
        </a>
    </div>
</div>

<script>
    function toggleSubPekerjaan(element) {
        var subMenu = document.getElementById('submenu-pekerjaan');
        var icon = element.querySelector('.arrow-icon-pekerjaan');
        
        if (subMenu.style.display === "none" || subMenu.style.display === "") {
            subMenu.style.display = "block";
            icon.style.transform = "rotate(90deg)";
        } else {
            subMenu.style.display = "none";
            icon.style.transform = "rotate(0deg)";
        }
    }
</script>


            <a href="javascript:void(0)" class="menu-item"><i class="fa-solid fa-industry"></i> Produksi</a>
        </ul>
    </div>
    
    <!-- PETA KANAN -->
    <div id="map">
        <button id="back-btn" onclick="goBack()"><i class="fa-solid fa-arrow-left"></i> Kembali</button>
    </div>

    <!-- JS Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        const map = L.map('map', { zoomControl: false }).setView([-4.85, 105.0], 9);
        L.control.zoom({ position: 'topright' }).addTo(map);

        // BASEMAP SATELIT & OSM
        const satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps'
        }).addTo(map);

        const openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

        // Layer Groups
        const batasKabupatenGroup = L.layerGroup().addTo(map);
        const tematikKesehatanGroup = L.layerGroup().addTo(map);
        const tematikUmumGroup = L.layerGroup().addTo(map); 
        const tematikKependudukanGroup = L.layerGroup().addTo(map);

        L.control.layers(
            { "Satelit": satelliteLayer, "Peta Jalan": openStreetMap }, 
            { 
                "Garis Batas Kabupaten": batasKabupatenGroup, 
                "Layer Tematik Kesehatan": tematikKesehatanGroup,
                "Layer Tematik Lainnya": tematikUmumGroup,
                "Layer Kependudukan": tematikKependudukanGroup
            }, 
            { position: 'topright' }
        ).addTo(map);

        // State Navigasi
        let geojsonLayer;
        let currentLevel = 'provinsi'; 
        let activeKabCode = "";
        let activeKecCode = "";

        // Skema Warna Wilayah
        const colors = ["#e5c158", "#00a896", "#3388ff", "#9b59b6", "#e74c3c", "#1abc9c", "#e67e22", "#2ecc71"];
        function getColor(str) {
            let hash = 0;
            if (!str) return colors[0];
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        const styleKabupatenDefault = function(feature) {
            return { color: "#2C3E50", weight: 2, fillColor: getColor(feature.properties.nama_kab), fillOpacity: 0.15 };
        };

        const styleKecamatanDefault = function(feature) {
            return { color: "#E74C3C", weight: 1.5, dashArray: "3, 3", fillColor: getColor(feature.properties.nama_kec), fillOpacity: 0.12 };
        };

        function loadBatasKabupaten() {
            const batasStyle = { color: "#2C3E50", weight: 3.5, opacity: 0.95, dashArray: "8, 6", interactive: false };
            fetch('/api/batas-kabupaten')
                .then(res => res.json())
                .then(data => {
                    batasKabupatenGroup.clearLayers();
                    const geojsonBatas = L.geoJSON(data, { style: batasStyle });
                    batasKabupatenGroup.addLayer(geojsonBatas);
                })
                .catch(err => console.error("Gagal memuat batas kabupaten luar:", err));
        }

        loadBatasKabupaten();
        initMapProvinsi();

        function initMapProvinsi() {
            currentLevel = 'provinsi';
            document.getElementById('back-btn').style.display = 'none';
            resetDropdowns(1);
            tematikKesehatanGroup.clearLayers();
            tematikUmumGroup.clearLayers();
            tematikKependudukanGroup.clearLayers();

            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch('/api/wilayah/kabupaten')
                .then(res => res.json())
                .then(data => {
                    geojsonLayer = L.geoJSON(data, {
                        style: styleKabupatenDefault,
                        onEachFeature: function (feature, layer) {
                            const kabName = feature.properties.nama_kab; 
                            const kabCode = feature.properties.kode_kab; 
                            layer.bindTooltip(`<b>${kabName}</b>`, { sticky: true });
                            
                            layer.on({
                                mouseover: function (e) {
                                    e.target.setStyle({ weight: 4, color: "#1ABC9C", fillOpacity: 0.35 });
                                    e.target.bringToFront();
                                },
                                mouseout: function (e) { geojsonLayer.resetStyle(e.target); },
                                click: function () {
                                    activeKabCode = kabCode;
                                    document.getElementById('select-kabupaten').value = kabCode;
                                    renderLevelKabupaten(kabCode);
                                }
                            });
                        }
                    }).addTo(map);
                    populateKabupatenDropdown(data);
                })
                .catch(err => console.error("Error loading kabupaten:", err));
        }

        function renderLevelKabupaten(kabCode) {
            currentLevel = 'kabupaten';
            document.getElementById('back-btn').style.display = 'flex';
            resetDropdowns(2);
            tematikKesehatanGroup.clearLayers();
            tematikUmumGroup.clearLayers();
            tematikKependudukanGroup.clearLayers();

            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/kecamatan/${kabCode}`)
                .then(res => res.json())
                .then(data => {
                    geojsonLayer = L.geoJSON(data, {
                        style: styleKecamatanDefault,
                        onEachFeature: function (feature, layer) {
                            const kecName = feature.properties.nama_kec; 
                            const kecCode = feature.properties.kode_kec; 
                            layer.bindTooltip(`<b>Kecamatan ${kecName}</b>`, { sticky: true });
                            
                            layer.on({
                                mouseover: function (e) { e.target.setStyle({ weight: 3, color: "#E67E22", fillOpacity: 0.3 }); },
                                mouseout: function (e) { geojsonLayer.resetStyle(e.target); },
                                click: function () {
                                    activeKecCode = kecCode;
                                    document.getElementById('select-kecamatan').value = kecCode;
                                    renderLevelKecamatan(kecCode);
                                }
                            });
                        }
                    }).addTo(map);

                    const bounds = geojsonLayer.getBounds();
                    if(bounds.isValid()) map.fitBounds(bounds);
                    populateKecamatanDropdown(data);
                })
                .catch(err => console.error("Error loading kecamatan:", err));
        }

        function renderLevelKecamatan(kecCode) {
            currentLevel = 'kecamatan';
            document.getElementById('back-btn').style.display = 'flex';
            tematikKesehatanGroup.clearLayers();
            tematikUmumGroup.clearLayers();
            tematikKependudukanGroup.clearLayers();

            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/desa/${kecCode}`)
                .then(res => res.json())
                .then(data => {
                    geojsonLayer = L.geoJSON(data, {
                        style: { color: "#3388ff", weight: 1.5, fillColor: "#3388ff", fillOpacity: 0.1 },
                        onEachFeature: function (feature, layer) {
                            const desaName = feature.properties.nama_desa; 
                            const desaCode = feature.properties.kode_desa;
                            layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });
                            
                            layer.on('click', function () {
                                const popupContent = `
                                    <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-width: 210px; padding: 4px;">
                                        <h4 style="margin: 0 0 2px 0; color: #1e3a8a; font-size: 15px; text-align: center; font-weight: 700;">${desaName}</h4>
                                        <p style="margin: 0; font-size: 11px; color: #7f8c8d; text-align: center;">Kode: ${desaCode}</p>
                                        <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                                        
                                        <p style="font-size: 11px; text-align: center; color: #555; margin-bottom: 12px; line-height: 1.4;">
                                            Gunakan menu <b>Data Tematik</b> di sebelah kiri untuk melihat detail infografis desa ini.
                                        </p>

                                        <!-- TOMBOL PROFIL DESA -->
                                        <button onclick="bukaProfilDesa('${desaCode}', '${desaName}')" 
                                            style="width: 100%; background: #1e3a8a; color: white; border: none; padding: 8px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: background 0.2s;">
                                            <i class="fa-solid fa-id-card"></i> Profil Desa
                                        </button>
                                    </div>`;
                                layer.bindPopup(popupContent).openPopup();
                            });
                        }
                    }).addTo(map);

                    const bounds = geojsonLayer.getBounds();
                    if(bounds.isValid()) map.fitBounds(bounds);
                    populateDesaDropdown(data);
                })
                .catch(err => console.error("Error loading desa:", err));
        }

        // ==========================================
        // FUNGSI AKSI TOMBOL PROFIL DESA
        // ==========================================
        function bukaProfilDesa(kodeDesa, namaDesa) {
            window.location.href = `/profil-desa/${kodeDesa}`;
        }

        // ==========================================
        // SUBMENU KESEHATAN 1: TENAGA MEDIS
        // ==========================================
        function loadTematikKesehatan(e) {
            if(e) e.preventDefault();
            const kecamatanCode = document.getElementById('select-kecamatan').value;
            if(!kecamatanCode) {
                alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
                return;
            }

            tematikKesehatanGroup.clearLayers();
            tematikUmumGroup.clearLayers();
            tematikKependudukanGroup.clearLayers();
            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/kesehatan/${kecamatanCode}`)
                .then(res => res.json())
                .then(geojsonData => {
                    const healthLayer = L.geoJSON(geojsonData, {
                        style: function(feature) {
                            const hasData = feature.properties.data_kesehatan && feature.properties.data_kesehatan.length > 0;
                            return { fillColor: hasData ? '#22c55e' : '#cbd5e1', weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.65 };
                        },
                        onEachFeature: function(feature, layer) {
                            const desaName = feature.properties.nama_desa;
                            const desaCode = feature.properties.kode_desa;
                            const listMedis = feature.properties.data_kesehatan;

                            layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });
                            
                            let kontenMedis = "";
                            if (listMedis && listMedis.length > 0) {
                                kontenMedis = `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;"><tr style="background:#f4f7f6; text-align:left;"><th style="padding:5px; border-bottom:1px solid #ddd; color:#444;">Tenaga Medis</th><th style="padding:5px; border-bottom:1px solid #ddd; text-align:center; color:#444;">Jumlah</th></tr>`;
                                listMedis.forEach(item => {
                                    kontenMedis += `<tr><td style="padding:5px; border-bottom:1px solid #eee; color:#555;">${item.jenis_tenaga_medis} <span style="font-size:9px; color:#888;">(${item.status})</span></td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#028090;">${item.jumlah_personil}</td></tr>`;
                                });
                                kontenMedis += `</table>`;
                            } else {
                                kontenMedis = `<div style="background: #fdfefe; border: 1px dashed #ddd; border-radius: 6px; padding: 10px; margin-top: 8px; text-align: center;"><i class="fa-solid fa-triangle-exclamation" style="color: #e67e22; font-size: 14px; margin-bottom: 4px;"></i><p style="font-size:11px; color:#7f8c8d; margin: 0;">Data tenaga kesehatan belum tersedia.</p></div>`;
                            }

                            const popupContent = `
                                <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-width: 250px; padding: 4px;">
                                    <h4 style="margin: 0 0 2px 0; color: #028090; font-size: 14px; text-align: center;">${desaName}</h4>
                                    <p style="margin: 0; font-size: 10px; color: #7f8c8d; text-align: center;">Kode: ${desaCode}</p>
                                    <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                                    <div style="display: flex; align-items: center; gap: 6px; color:#2c3e50; font-size:11px; font-weight: bold;"><i class="fa-solid fa-user-doctor" style="color:#e74c3c;"></i><span>Fasilitas & Tenaga Medis</span></div>
                                    ${kontenMedis}
                                </div>`;
                            layer.bindPopup(popupContent);
                        }
                    });
                    
                    tematikKesehatanGroup.addLayer(healthLayer);
                    const bounds = healthLayer.getBounds();
                    if(bounds.isValid()) map.fitBounds(bounds);
                })
                .catch(error => { console.error('Error:', error); alert('Gagal memuat data kesehatan.'); });
        }

        // ==========================================
        // SUBMENU KESEHATAN 2 & 3: AIR BERSIH & SANITASI
        // ==========================================
        function loadTematikUmum(e, tipe) {
            if(e) e.preventDefault();
            const kecamatanCode = document.getElementById('select-kecamatan').value;
            if(!kecamatanCode) {
                alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
                return;
            }

            tematikKesehatanGroup.clearLayers();
            tematikUmumGroup.clearLayers();
            tematikKependudukanGroup.clearLayers();
            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/${tipe}/${kecamatanCode}`)
                .then(res => res.json())
                .then(geojsonData => {
                    const dynamicLayer = L.geoJSON(geojsonData, {
                        style: function(feature) {
                            let hasData = false;
                            if (tipe === 'air-bersih') {
                                hasData = feature.properties.data_air && feature.properties.data_air.length > 0;
                            } else if (tipe === 'sanitasi') {
                                hasData = feature.properties.data_sanitasi && feature.properties.data_sanitasi.length > 0;
                            }
                            
                            let colorTheme = tipe === 'air-bersih' ? '#028090' : '#8e44ad';
                            return { fillColor: hasData ? colorTheme : '#cbd5e1', weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.65 };
                        },
                        onEachFeature: function(feature, layer) {
                            const desaName = feature.properties.nama_desa;
                            const desaCode = feature.properties.kode_desa;
                            layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });

                            let detailHtml = "";
                            
                            if (tipe === 'air-bersih') {
                                const listAir = feature.properties.data_air || [];
                                detailHtml += `<div style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:bold; color:#028090;"><i class="fa-solid fa-droplet"></i><span>Sumber Air Bersih</span></div>`;
                                if (listAir.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse:collapse;"><tr style="background:#f4f7f6;"><th style="padding:5px; border-bottom:1px solid #ddd; text-align:left;">Jenis</th><th style="padding:5px; border-bottom:1px solid #ddd; text-align:center;">Unit</th><th style="padding:5px; border-bottom:1px solid #ddd; text-align:center;">Kondisi</th></tr>`;
                                    listAir.forEach(item => {
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">${item.jenis_sumber_air}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold;">${item.jumlah_unit}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;"><span style="font-size:10px; padding:2px 6px; background:#e8f8f5; border-radius:4px; color:#117a65;">${item.kondisi}</span></td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                } else {
                                    detailHtml += `<div style="background:#fdfefe; border:1px dashed #ddd; border-radius:6px; padding:10px; margin-top:8px; text-align:center; font-size:11px; color:#7f8c8d;">Data air bersih belum tersedia.</div>`;
                                }
                            } 
                            else if (tipe === 'sanitasi') {
                                const listSanitasi = feature.properties.data_sanitasi || [];
                                detailHtml += `<div style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:bold; color:#8e44ad;"><i class="fa-solid fa-faucet-drip"></i><span>Sanitasi Lingkungan</span></div>`;
                                if (listSanitasi.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse:collapse;"><tr style="background:#f4f7f6;"><th style="padding:5px; border-bottom:1px solid #ddd; text-align:left;">Fasilitas Sanitasi</th><th style="padding:5px; border-bottom:1px solid #ddd; text-align:center;">Jumlah</th></tr>`;
                                    listSanitasi.forEach(item => {
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Jamban Leher Angsa</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#8e44ad;">${item.jamban_leher_angsa || 0}</td></tr>`;
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Jamban Non-Leher Angsa</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#888;">${item.jamban_non_leher_angsa || 0}</td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                } else {
                                    detailHtml += `<div style="background:#fdfefe; border:1px dashed #ddd; border-radius:6px; padding:10px; margin-top:8px; text-align:center; font-size:11px; color:#7f8c8d;">Data sanitasi belum tersedia.</div>`;
                                }
                            }

                            const popupContent = `
                                <div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-width:250px; padding:4px;">
                                    <h4 style="margin:0 0 2px 0; color:#028090; font-size:14px; text-align:center;">${desaName}</h4>
                                    <p style="margin:0; font-size:10px; color:#7f8c8d; text-align:center;">Kode: ${desaCode}</p>
                                    <hr style="border:0; border-top:1px solid #eee; margin:8px 0;">
                                    ${detailHtml}
                                </div>`;
                            layer.bindPopup(popupContent);
                        }
                    });

                    tematikUmumGroup.addLayer(dynamicLayer);
                    const bounds = dynamicLayer.getBounds();
                    if(bounds.isValid()) map.fitBounds(bounds);
                })
                .catch(error => { console.error('Error:', error); alert('Gagal memuat data tematik wilayah.'); });
        }

        // ==========================================
        // SUBMENU KEPENDUDUKAN
        // ==========================================
        function loadTematikKependudukan(e, tipe) {
            if(e) e.preventDefault();
            const kecamatanCode = document.getElementById('select-kecamatan').value;
            if(!kecamatanCode) {
                alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
                return;
            }

            tematikKesehatanGroup.clearLayers();
            tematikUmumGroup.clearLayers();
            tematikKependudukanGroup.clearLayers();
            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/kependudukan/${tipe}/${kecamatanCode}`)
                .then(res => res.json())
                .then(geojsonData => {
                    const dynamicKependudukanLayer = L.geoJSON(geojsonData, {
                        style: function(feature) {
                            const hasData = feature.properties.data_kependudukan && feature.properties.data_kependudukan.length > 0;
                            return { fillColor: hasData ? '#1e3a8a' : '#cbd5e1', weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.65 };
                        },
                        onEachFeature: function(feature, layer) {
                            const desaName = feature.properties.nama_desa;
                            const desaCode = feature.properties.kode_desa;
                            const dataList = feature.properties.data_kependudukan || [];

                            layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });
                            
                            let detailHtml = "";
                            let iconTitle = "fa-users";
                            let titleLabel = "Data Kependudukan";

                            if (tipe === 'penduduk-kk') {
                                iconTitle = "fa-users";
                                titleLabel = "Penduduk dan KK";
                                if (dataList.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;">`;
                                    dataList.forEach(item => {
                                        const jmlLakiLaki = item.laki_laki || item.laki || item.pria || item.l || item.L || 0;
                                        const jmlPerempuan = item.perempuan || item.wanita || item.p || item.P || 0;
                                        
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Total Penduduk</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#1e3a8a;">${item.total_penduduk || 0} Jiwa</td></tr>`;
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Laki-laki</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${jmlLakiLaki}</td></tr>`;
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Perempuan</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${jmlPerempuan}</td></tr>`;
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Jumlah KK</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.jumlah_kk || item.total_kk || 0}</td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                }
                            }
                            else if (tipe === 'kesejahteraan') {
                                iconTitle = "fa-hand-holding-heart";
                                titleLabel = "Kesejahteraan";
                                if (dataList.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;">`;
                                    dataList.forEach(item => {
                                        const ks1 = parseInt(item.ks_1 || 0, 10);
                                        const ks2 = parseInt(item.ks_2 || 0, 10);
                                        const ks3 = parseInt(item.ks_3 || 0, 10);
                                        const ks3Plus = parseInt(item.ks_3_plus || 0, 10);
                                        
                                        const totalSejahtera = item.keluarga_sejahtera || (ks1 + ks2 + ks3 + ks3Plus) || 0;
                                        const totalPrasejahtera = item.keluarga_prasejahtera || item.pra_ks || 0;

                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Keluarga Sejahtera I - III Plus</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#22c55e;">${totalSejahtera} KK</td></tr>`;
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">Keluarga Prasejahtera</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#ef4444;">${totalPrasejahtera} KK</td></tr>`;
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee; font-style:italic;">Total Terdata</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold;">${item.total_kk || 0} KK</td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                }
                            }
                            else if (tipe === 'mata-pencaharian') {
                                iconTitle = "fa-briefcase";
                                titleLabel = "Mata Pencaharian Utama";
                                if (dataList.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;"><tr style="background:#f4f7f6;"><th style="padding:5px; text-align:left;">Sektor Pekerjaan</th><th style="padding:5px; text-align:center;">L</th><th style="padding:5px; text-align:center;">P</th><th style="padding:5px; text-align:center;">Total</th></tr>`;
                                    dataList.forEach(item => {
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee; font-weight:500;">${item.sektor_pekerjaan}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.pria || 0}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.wanita || 0}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#1e3a8a;">${item.total || 0}</td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                }
                            }
                            else if (tipe === 'tenaga-kerja') {
                                iconTitle = "fa-user-gear";
                                titleLabel = "Kelompok Usia & Ketenagakerjaan";
                                if (dataList.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;"><tr style="background:#f4f7f6;"><th style="padding:5px; text-align:left;">Deskripsi Kategori</th><th style="padding:5px; text-align:center;">L</th><th style="padding:5px; text-align:center;">P</th><th style="padding:5px; text-align:center;">Total</th></tr>`;
                                    dataList.forEach(item => {
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">${item.kategori_kerja}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.pria || 0}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.wanita || 0}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold;">${item.total || 0}</td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                }
                            }
                            else if (tipe === 'tingkat-pendidikan') {
                                iconTitle = "fa-graduation-cap";
                                titleLabel = "Jenjang Pendidikan Terakhir";
                                if (dataList.length > 0) {
                                    detailHtml += `<table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;"><tr style="background:#f4f7f6;"><th style="padding:5px; text-align:left;">Status Jenjang</th><th style="padding:5px; text-align:center;">L</th><th style="padding:5px; text-align:center;">P</th><th style="padding:5px; text-align:center;">Total</th></tr>`;
                                    
                                    dataList.forEach(item => {
                                        const namaJenjang = item.jenjang || item.jenjang_pendidikan || item.kategori || item.status_jenjang || "Tidak Diketahui";
                                        detailHtml += `<tr><td style="padding:5px; border-bottom:1px solid #eee;">${namaJenjang}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.pria || 0}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center;">${item.wanita || 0}</td><td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#028090;">${item.total || 0}</td></tr>`;
                                    });
                                    detailHtml += `</table>`;
                                }
                            }

                            if (detailHtml === "") {
                                detailHtml = `<div style="background: #fdfefe; border: 1px dashed #ddd; border-radius: 6px; padding: 10px; margin-top: 8px; text-align: center;"><i class="fa-solid fa-triangle-exclamation" style="color: #e67e22; font-size: 14px; margin-bottom: 4px;"></i><p style="font-size:11px; color:#7f8c8d; margin: 0;">Data kategori ini belum tersedia.</p></div>`;
                            }

                            const popupContent = `
                                <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-width: 290px; padding: 4px;">
                                    <h4 style="margin: 0 0 2px 0; color: #1e3a8a; font-size: 14px; text-align: center;">${desaName}</h4>
                                    <p style="margin: 0; font-size: 10px; color: #7f8c8d; text-align: center;">Kode: ${desaCode}</p>
                                    <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                                    <div style="display: flex; align-items: center; gap: 6px; color:#2c3e50; font-size:11px; font-weight: bold;"><i class="fa-solid ${iconTitle}" style="color:#1e3a8a;"></i><span>${titleLabel}</span></div>
                                    ${detailHtml}
                                </div>`;
                            layer.bindPopup(popupContent);
                        }
                    });

                    tematikKependudukanGroup.addLayer(dynamicKependudukanLayer);
                    const bounds = dynamicKependudukanLayer.getBounds();
                    if(bounds.isValid()) map.fitBounds(bounds);
                })
                .catch(error => { console.error('Error:', error); alert('Gagal memuat detail data kependudukan.'); });
        }

        // CONTROL ACCORDION TOGGLE
        function toggleKesehatanMenu(element) {
            const submenu = document.getElementById('submenu-kesehatan');
            const arrowIcon = element.querySelector('.fa-chevron-right');
            if (submenu) submenu.classList.toggle('submenu-open');
            if (arrowIcon) arrowIcon.classList.toggle('rotate-arrow');
        }

        function toggleKependudukanMenu(element) {
            const submenu = document.getElementById('submenu-kependudukan');
            const arrowIcon = element.querySelector('.fa-chevron-right');
            if (submenu) submenu.classList.toggle('submenu-open');
            if (arrowIcon) arrowIcon.classList.toggle('rotate-arrow');
        }

        function togglePendidikanMenu(element) {
            const submenu = document.getElementById('submenu-pendidikan');
            const arrowIcon = element.querySelector('.fa-chevron-right');
            if (submenu) submenu.classList.toggle('submenu-open');
            if (arrowIcon) arrowIcon.classList.toggle('rotate-arrow');
        }

        // ==========================================
        // SINKRONISASI DROPDOWN WILAYAH
        // ==========================================
        function populateKabupatenDropdown(data) {
            const select = document.getElementById('select-kabupaten');
            select.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
            data.features.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.properties.kode_kab;   
                opt.innerHTML = f.properties.nama_kab; 
                select.appendChild(opt);
            });
        }

        function populateKecamatanDropdown(data) {
            const select = document.getElementById('select-kecamatan');
            select.innerHTML = '<option value="">Pilih Kecamatan</option>';
            select.disabled = false;
            data.features.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.properties.kode_kec;   
                opt.innerHTML = f.properties.nama_kec; 
                select.appendChild(opt);
            });
        }

        function populateDesaDropdown(data) {
            const select = document.getElementById('select-desa');
            select.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            select.disabled = false;
            data.features.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.properties.kode_desa;   
                opt.innerHTML = f.properties.nama_desa; 
                select.appendChild(opt);
            });
        }

        function filterFromDropdown(level, value) {
            if (!value) {
                if(level === 'kabupaten') goBack();
                return;
            }
            if (level === 'kabupaten') {
                activeKabCode = value;
                renderLevelKabupaten(value);
            } else if (level === 'kecamatan') {
                activeKecCode = value;
                renderLevelKecamatan(value);
            } else if (level === 'desa') {
                let layerTarget = null;
                if (map.hasLayer(geojsonLayer)) {
                    layerTarget = geojsonLayer.getLayers().find(layer => layer.feature.properties.kode_desa === value);
                } else if (map.hasLayer(tematikKesehatanGroup)) {
                    tematikKesehatanGroup.eachLayer(function(group) {
                        layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
                    });
                } else if (map.hasLayer(tematikUmumGroup)) {
                    tematikUmumGroup.eachLayer(function(group) {
                        layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
                    });
                } else if (map.hasLayer(tematikKependudukanGroup)) {
                    tematikKependudukanGroup.eachLayer(function(group) {
                        layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
                    });
                }

                if (layerTarget) {
                    map.fitBounds(layerTarget.getBounds());
                    layerTarget.fire('click');
                }
            }
        }

        function resetDropdowns(level) {
            if (level <= 1) {
                document.getElementById('select-kabupaten').value = "";
                document.getElementById('select-kecamatan').innerHTML = '<option value="">Pilih Kecamatan</option>';
                document.getElementById('select-kecamatan').disabled = true;
            }
            if (level <= 2) {
                document.getElementById('select-desa').innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                document.getElementById('select-desa').disabled = true;
            }
        }

        function goBack() {
            if (currentLevel === 'kecamatan') {
                renderLevelKabupaten(activeKabCode);
                document.getElementById('select-kabupaten').value = activeKabCode;
            } else if (currentLevel === 'kabupaten') {
                initMapProvinsi();
                map.setView([-4.85, 105.0], 9);
            }
        }
    </script>
</body>
</html>