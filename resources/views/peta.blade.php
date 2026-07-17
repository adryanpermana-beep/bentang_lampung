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
        .menu-item { padding: 12px 20px; display: flex; align-items: center; gap: 12px; color: #555; font-size: 14px; cursor: pointer; transition: 0.2s; text-decoration: none; }
        .menu-item i { width: 20px; text-align: center; color: #666; }
        .menu-item:hover { background: #f4f7f6; color: #028090; }
        
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
        
        <button class="login-btn">
            <i class="fa-regular fa-user"></i> Login
        </button>
        
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
        
        <div class="menu-heading">Data Profile</div>
        <ul class="menu-list">
            <a href="javascript:void(0)" class="menu-item"><i class="fa-solid fa-users"></i> Demografi Wilayah</a>
            <a href="javascript:void(0)" class="menu-item"><i class="fa-solid fa-chart-line"></i> Potensi Daerah</a>
        </ul>
        
        <div class="menu-heading">Data Tematik</div>
        <ul class="menu-list">
            <a href="javascript:void(0)" class="menu-item"><i class="fa-solid fa-building-flag"></i> Sarana Pemerintahan</a>
            <a href="javascript:void(0)" class="menu-item" onclick="loadTematikKesehatan(event)"><i class="fa-solid fa-notes-medical"></i> Kesehatan</a>
            <a href="javascript:void(0)" class="menu-item"><i class="fa-solid fa-graduation-cap"></i> Pendidikan</a>
            <a href="javascript:void(0)" class="menu-item"><i class="fa-solid fa-mosque"></i> Keagamaan</a>
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

        // BASEMAP SATELIT
        const satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps'
        }).addTo(map);

        const openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

        // Layer Group khusus untuk garis batas luar kabupaten agar selalu tampil paling atas
        const batasKabupatenGroup = L.layerGroup().addTo(map);
        const tematikKesehatanGroup = L.layerGroup().addTo(map);

        L.control.layers(
            { "Satelit": satelliteLayer, "Peta Jalan": openStreetMap }, 
            { "Garis Batas Kabupaten": batasKabupatenGroup, "Layer Tematik Kesehatan": tematikKesehatanGroup }, 
            { position: 'topright' }
        ).addTo(map);

        // State Navigasi & Penyimpanan Kode Aktif
        let geojsonLayer;
        let currentLevel = 'provinsi'; // 'provinsi', 'kabupaten', 'kecamatan'
        
        let activeKabCode = "";
        let activeKecCode = "";

        // Skema Warna Wilayah Terang Estetik
        const colors = ["#e5c158", "#00a896", "#3388ff", "#9b59b6", "#e74c3c", "#1abc9c", "#e67e22", "#2ecc71"];
        function getColor(str) {
            let hash = 0;
            if (!str) return colors[0];
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        // Stylesheet Profesional Spasial
        const styleKabupatenDefault = function(feature) {
            return {
                color: "#2C3E50",          
                weight: 2,                 
                fillColor: getColor(feature.properties.nama_kab),
                fillOpacity: 0.15          
            };
        };

        const styleKecamatanDefault = function(feature) {
            return {
                color: "#E74C3C",          
                weight: 1.5,
                dashArray: "3, 3",         
                fillColor: getColor(feature.properties.nama_kec),
                fillOpacity: 0.12
            };
        };

        // ==========================================
        // SISTEM LOAD OVERLAY GARIS BATAS KABUPATEN
        // ==========================================
        function loadBatasKabupaten() {
            const batasStyle = {
                color: "#2C3E50",       
                weight: 3.5,            
                opacity: 0.95,          
                dashArray: "8, 6",      
                interactive: false      
            };

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

        // ==========================================
        // LEVEL 1: PROVINSI (Menampilkan Kabupaten)
        // ==========================================
        function initMapProvinsi() {
            currentLevel = 'provinsi';
            document.getElementById('back-btn').style.display = 'none';
            resetDropdowns(1);
            tematikKesehatanGroup.clearLayers();

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
                                    const activeLayer = e.target;
                                    activeLayer.setStyle({
                                        weight: 4,
                                        color: "#1ABC9C", 
                                        fillOpacity: 0.35
                                    });
                                    activeLayer.bringToFront();
                                },
                                mouseout: function (e) {
                                    geojsonLayer.resetStyle(e.target);
                                },
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

        // ==========================================
        // LEVEL 2: KABUPATEN (Menampilkan Kecamatan)
        // ==========================================
        function renderLevelKabupaten(kabCode) {
            currentLevel = 'kabupaten';
            document.getElementById('back-btn').style.display = 'flex';
            resetDropdowns(2);
            tematikKesehatanGroup.clearLayers();

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
                                mouseover: function (e) {
                                    e.target.setStyle({
                                        weight: 3,
                                        color: "#E67E22", 
                                        fillOpacity: 0.3
                                    });
                                },
                                mouseout: function (e) {
                                    geojsonLayer.resetStyle(e.target);
                                },
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

        // ==========================================
        // LEVEL 3: KECAMATAN (Menampilkan Detail Desa Standar)
        // ==========================================
        function renderLevelKecamatan(kecCode) {
            currentLevel = 'kecamatan';
            document.getElementById('back-btn').style.display = 'flex';
            tematikKesehatanGroup.clearLayers();

            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/desa/${kecCode}`)
                .then(res => res.json())
                .then(data => {
                    geojsonLayer = L.geoJSON(data, {
                        style: {
                            color: "#3388ff",
                            weight: 1.5,
                            fillColor: "#3388ff",
                            fillOpacity: 0.1
                        },
                        onEachFeature: function (feature, layer) {
                            const desaName = feature.properties.nama_desa; 
                            const desaCode = feature.properties.kode_desa;

                            layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });
                            
                            layer.on('click', function () {
                                const popupContent = `
                                    <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-width: 200px; padding: 4px;">
                                        <h4 style="margin: 0 0 2px 0; color: #028090; font-size: 14px; text-align: center;">${desaName}</h4>
                                        <p style="margin: 0; font-size: 10px; color: #7f8c8d; text-align: center;">Kode: ${desaCode}</p>
                                        <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                                        <p style="font-size:11px; text-align:center; color:#555;">Gunakan menu <b>Data Tematik -> Kesehatan</b> di sebelah kiri untuk melihat persebaran jumlah tenaga medis.</p>
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
        // MENU DATA TEMATIK: KESEHATAN DESA
        // ==========================================
        function loadTematikKesehatan(e) {
            if(e) e.preventDefault();
            
            const kecamatanCode = document.getElementById('select-kecamatan').value;
            if(!kecamatanCode) {
                alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
                return;
            }

            tematikKesehatanGroup.clearLayers();
            if (geojsonLayer) map.removeLayer(geojsonLayer);

            fetch(`/api/wilayah/kesehatan/${kecamatanCode}`)
                .then(res => res.json())
                .then(geojsonData => {
                    const healthLayer = L.geoJSON(geojsonData, {
                        style: function(feature) {
                            const hasData = feature.properties.data_kesehatan && feature.properties.data_kesehatan.length > 0;
                            return {
                                fillColor: hasData ? '#22c55e' : '#cbd5e1', 
                                weight: 1.5,
                                opacity: 1,
                                color: '#ffffff',
                                fillOpacity: 0.65
                            };
                        },
                        onEachFeature: function(feature, layer) {
                            const desaName = feature.properties.nama_desa;
                            const desaCode = feature.properties.kode_desa;
                            const listMedis = feature.properties.data_kesehatan;

                            layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });
                            
                            let kontenMedis = "";
                            if (listMedis && listMedis.length > 0) {
                                kontenMedis = `
                                    <table style="width:100%; font-size:11px; margin-top:8px; border-collapse: collapse;">
                                        <tr style="background:#f4f7f6; text-align:left;">
                                            <th style="padding:5px; border-bottom:1px solid #ddd; color:#444;">Tenaga Medis</th>
                                            <th style="padding:5px; border-bottom:1px solid #ddd; text-align:center; color:#444;">Jumlah</th>
                                        </tr>`;
                                listMedis.forEach(item => {
                                    kontenMedis += `
                                        <tr>
                                            <td style="padding:5px; border-bottom:1px solid #eee; color:#555;">
                                                ${item.jenis_tenaga_medis} <span style="font-size:9px; color:#888;">(${item.status})</span>
                                            </td>
                                            <td style="padding:5px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#028090;">
                                                ${item.jumlah_personil}
                                            </td>
                                        </tr>`;
                                });
                                kontenMedis += `</table>`;
                            } else {
                                kontenMedis = `
                                    <div style="background: #fdfefe; border: 1px dashed #ddd; border-radius: 6px; padding: 10px; margin-top: 8px; text-align: center;">
                                        <i class="fa-solid fa-triangle-exclamation" style="color: #e67e22; font-size: 14px; margin-bottom: 4px;"></i>
                                        <p style="font-size:11px; color:#7f8c8d; margin: 0;">Data tenaga kesehatan belum tersedia.</p>
                                    </div>`;
                            }

                            const popupContent = `
                                <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-width: 250px; padding: 4px;">
                                    <h4 style="margin: 0 0 2px 0; color: #028090; font-size: 14px; text-align: center;">${desaName}</h4>
                                    <p style="margin: 0; font-size: 10px; color: #7f8c8d; text-align: center;">Kode: ${desaCode}</p>
                                    <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                                    
                                    <div style="display: flex; align-items: center; gap: 6px; color:#2c3e50; font-size:11px; font-weight: bold;">
                                        <i class="fa-solid fa-notes-medical" style="color:#e74c3c;"></i>
                                        <span>Fasilitas & Tenaga Medis</span>
                                    </div>
                                    ${kontenMedis}
                                </div>`;
                                
                            layer.bindPopup(popupContent);
                        }
                    });
                    
                    tematikKesehatanGroup.addLayer(healthLayer);
                    const bounds = healthLayer.getBounds();
                    if(bounds.isValid()) map.fitBounds(bounds);
                })
                .catch(error => {
                    console.error('Error fetching data kesehatan tematik:', error);
                    alert('Gagal memuat visualisasi sebaran data kesehatan.');
                });
        }

        // ==========================================
        // SINKRONISASI DROPDOWN DARI DATA API
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
                
                // Cari target di layer standar maupun layer kesehatan aktif
                if (map.hasLayer(geojsonLayer)) {
                    layerTarget = geojsonLayer.getLayers().find(layer => layer.feature.properties.kode_desa === value);
                } else {
                    tematikKesehatanGroup.eachLayer(function(group) {
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