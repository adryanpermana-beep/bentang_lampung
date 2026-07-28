<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WebGIS Tapal Desa - Provinsi Lampung</title>
<!-- CSS Leaflet & FontAwesome -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<!-- Chart.js CDN untuk Visualisasi Grafik Pop-up -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
/* CSS UNIFORM SEMUA MENU TEMATIK */
.menu-tematik-group {
    margin: 0 !important;
    padding: 0 !important;
    width: 100%;
}
.tematik-main-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s ease;
    user-select: none;
}
.tematik-main-item:hover {
    background-color: #f1f5f9;
}
.tematik-title-wrapper {
    display: flex;
    align-items: center;
}
.tematik-icon-box {
    width: 24px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    margin-right: 10px;
    font-size: 15px;
    color: #475569;
}
.tematik-label {
    font-size: 14px;
    font-weight: 500;
    color: #334155;
}
.tematik-arrow-icon {
    font-size: 12px;
    color: #94a3b8;
    transition: transform 0.3s ease;
}
.tematik-submenu-box {
    display: none;
    padding-left: 34px;
    margin: 6px 0;
    flex-direction: column;
    gap: 4px;
}
.tematik-sub-item {
    display: flex;
    align-items: center;
    padding: 7px 12px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    border-radius: 6px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    cursor: pointer;
}
.tematik-sub-item:hover {
    background-color: #eff6ff;
    color: #1e3a8a;
    border-color: #bfdbfe;
    transform: translateX(2px);
}
.tematik-sub-item i {
    width: 18px;
    text-align: center;
    margin-right: 8px;
    color: #2563eb;
    font-size: 12px;
}

/* Custom Leaflet Popup Styling */
.leaflet-popup-content-wrapper {
    padding: 0;
    border-radius: 10px;
    overflow: hidden;
}
.leaflet-popup-content {
    margin: 0 !important;
    width: 310px !important;
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
        <!-- Tampilan Saat Belum Login (Publik) -->
        <a href="{{ route('login') }}" style="text-decoration: none; display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; color: #0f172a; background-color: #f0f9ff; font-family: system-ui, -apple-system, sans-serif; font-size: 15px; font-weight: 500; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s; box-sizing: border-box;">
            <i class="fas fa-user-lock" style="color: #1e40af; font-size: 16px;"></i>
            <span style="color: #1e40af; font-weight: 600;">Login Admin</span>
        </a>
    @endguest

    @auth
        <!-- Tampilan Box Profil Admin & Tombol Keluar Saat Sudah Login -->
        <div style="background-color: #f0f9ff; border: 1px solid #bee3f8; border-radius: 10px; padding: 14px; font-family: system-ui, -apple-system, sans-serif; font-size: 14px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); box-sizing: border-box;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                <div style="display: flex; align-items: center; gap: 8px; color: #1e40af; font-weight: 700;">
                    <i class="fas fa-user-shield" style="font-size: 16px;"></i>
                    <span>{{ Auth::check() && Auth::user()->name ? Auth::user()->name : session('admin_nama', 'Admin Bentang Lampung') }}</span>
                </div>
                <span style="background-color: #dbeafe; color: #1e40af; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">Admin</span>
            </div>
            <p style="margin: 0 0 10px 0; font-size: 11px; color: #1e40af; font-weight: 500; opacity: 0.85;">
                Status: Administrator Peta
            </p>

            <!-- Form Logout Admin -->
           <form action="/keluar-admin" method="POST" style="margin: 0;">
    {{ csrf_field() }}
    <button type="submit" style="width: 100%; background: #dc2626; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.1);" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
        <i class="fas fa-power-off" style="font-size: 12px;"></i> Keluar
    </button>
</form>
        </div>
    @endauth
</div>

<!-- Section Filter Dropdown -->
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
<!-- 1. KESEHATAN -->
<li class="menu-tematik-group">
<div class="tematik-main-item" onclick="toggleTematikSubmenu('kesehatan', this)">
<div class="tematik-title-wrapper">
<span class="tematik-icon-box"><i class="fa-solid fa-notes-medical"></i></span>
<span class="tematik-label">Kesehatan</span>
</div>
<i class="fa-solid fa-chevron-right tematik-arrow-icon"></i>
</div>
<div id="submenu-kesehatan" class="tematik-submenu-box">
<div class="tematik-sub-item" onclick="loadTematikKesehatan(event)">
<i class="fa-solid fa-user-doctor"></i>
<span>Tenaga Medis</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikUmum(event, 'air-bersih')">
<i class="fa-solid fa-droplet"></i>
<span>Sumber Air Bersih</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikUmum(event, 'sanitasi')">
<i class="fa-solid fa-faucet-drip"></i>
<span>Sanitasi Lingkungan</span>
</div>
</div>
</li>
<!-- 2. KEPENDUDUKAN -->
<li class="menu-tematik-group">
<div class="tematik-main-item" onclick="toggleTematikSubmenu('kependudukan', this)">
<div class="tematik-title-wrapper">
<span class="tematik-icon-box"><i class="fa-solid fa-id-card"></i></span>
<span class="tematik-label">Kependudukan</span>
</div>
<i class="fa-solid fa-chevron-right tematik-arrow-icon"></i>
</div>
<div id="submenu-kependudukan" class="tematik-submenu-box">
<div class="tematik-sub-item" onclick="loadTematikKependudukan(event, 'penduduk-kk')">
<i class="fa-solid fa-users"></i>
<span>Penduduk dan KK</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikKependudukan(event, 'kesejahteraan')">
<i class="fa-solid fa-hand-holding-heart"></i>
<span>Kesejahteraan</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikKependudukan(event, 'mata-pencaharian')">
<i class="fa-solid fa-briefcase"></i>
<span>Mata Pencaharian</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikKependudukan(event, 'tenaga-kerja')">
<i class="fa-solid fa-user-gear"></i>
<span>Tenaga Kerja</span>
</div>
</div>
</li>
<!-- 3. PENDIDIKAN -->
<li class="menu-tematik-group">
<div class="tematik-main-item" onclick="toggleTematikSubmenu('pendidikan', this)">
<div class="tematik-title-wrapper">
<span class="tematik-icon-box"><i class="fa-solid fa-graduation-cap"></i></span>
<span class="tematik-label">Pendidikan</span>
</div>
<i class="fa-solid fa-chevron-right tematik-arrow-icon"></i>
</div>
<div id="submenu-pendidikan" class="tematik-submenu-box">
<div class="tematik-sub-item" onclick="loadTematikKependudukan(event, 'tingkat-pendidikan')">
<i class="fa-solid fa-school"></i>
<span>Tingkat Pendidikan</span>
</div>
</div>
</li>
<!-- 4. PRODUKSI -->
<li class="menu-tematik-group">
<div class="tematik-main-item" onclick="toggleTematikSubmenu('produksi', this)">
<div class="tematik-title-wrapper">
<span class="tematik-icon-box"><i class="fa-solid fa-industry"></i></span>
<span class="tematik-label">Produksi</span>
</div>
<i class="fa-solid fa-chevron-right tematik-arrow-icon"></i>
</div>
<div id="submenu-produksi" class="tematik-submenu-box">
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'apotik-hidup')">
<i class="fa-solid fa-mortar-pestle"></i>
<span>Apotik Hidup</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'bahan-galian')">
<i class="fa-solid fa-cubes"></i>
<span>Bahan Galian</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'buah-buahan')">
<i class="fa-solid fa-apple-whole"></i>
<span>Buah Buahan</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'budi-daya-air-tawar')">
<i class="fa-solid fa-water"></i>
<span>Budi Daya Air Tawar</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'hasil-hutan')">
<i class="fa-solid fa-tree"></i>
<span>Hasil Hutan</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'hasil-tangkapan')">
<i class="fa-solid fa-fish"></i>
<span>Hasil Tangkapan</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'perkebunan')">
<i class="fa-solid fa-seedling"></i>
<span>Perkebunan</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'peternakan')">
<i class="fa-solid fa-cow"></i>
<span>Peternakan</span>
</div>
<div class="tematik-sub-item" onclick="loadTematikProduksi(event, 'tanaman-pangan')">
<i class="fa-solid fa-wheat-awn"></i>
<span>Tanaman Pangan</span>
</div>
</div>
</li>
<!-- 5. LAPANGAN PEKERJAAN -->
<li class="menu-tematik-group">
<div class="tematik-main-item" onclick="toggleTematikSubmenu('pekerjaan', this)">
<div class="tematik-title-wrapper">
<span class="tematik-icon-box"><i class="fa-solid fa-briefcase"></i></span>
<span class="tematik-label">Lapangan Pekerjaan</span>
</div>
<i class="fa-solid fa-chevron-right tematik-arrow-icon"></i>
</div>
<div id="submenu-pekerjaan" class="tematik-submenu-box">
<div class="tematik-sub-item" onclick="bukaModalLowongan('https://sigajahkerja.disnaker.lampungprov.go.id/lowongan')" style="cursor: pointer;">
<i class="fa-solid fa-window-maximize"></i>
<span>Lihat Lowongan</span>
</div>
</div>
</li>
</ul>
</div>
<!-- PETA KANAN -->
<div id="map">
<button id="back-btn" onclick="goBack()"><i class="fa-solid fa-arrow-left"></i> Kembali</button>
</div>

<!-- JS Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// INISIALISASI MAP
const map = L.map('map', { zoomControl: false }).setView([-4.85, 105.0], 9);
L.control.zoom({position: 'topright' }).addTo(map);

// BASEMAP SATELIT & OSM
const satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
    attribution: '&copy; Google Maps'
}).addTo(map);
const openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

// LAYER GROUPS
const batasKabupatenGroup = L.layerGroup().addTo(map);
const tematikKesehatanGroup = L.layerGroup().addTo(map);
const tematikUmumGroup = L.layerGroup().addTo(map);
const tematikKependudukanGroup = L.layerGroup().addTo(map);
const tematikProduksiGroup = L.layerGroup().addTo(map);

L.control.layers(
    {"Satelit": satelliteLayer, "Peta Jalan": openStreetMap },
    {
        "Garis Batas Kabupaten": batasKabupatenGroup,
        "Layer Tematik Kesehatan": tematikKesehatanGroup,
        "Layer Tematik Lainnya": tematikUmumGroup,
        "Layer Kependudukan": tematikKependudukanGroup,
        "Layer Produksi": tematikProduksiGroup
    },
    {position: 'topright' }
).addTo(map);

// STATE NAVIGASI
let geojsonLayer;
let currentLevel = 'provinsi';
let activeKabCode = "";
let activeKecCode = "";

// SKEMA WARNA WILAYAH
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

// FUNGSI LOAD BATAS KABUPATEN
function loadBatasKabupaten() {
    const batasStyle = { color: "#2C3E50", weight: 3.5, opacity: 0.95, dashArray: "8, 6", interactive: false };
    fetch('/api/batas-kabupaten')
    .then(res => res.json())
    .then(data => {
        batasKabupatenGroup.clearLayers();
        const geojsonBatas = L.geoJSON(data, {style: batasStyle });
        batasKabupatenGroup.addLayer(geojsonBatas);
    })
    .catch(err => console.error("Gagal memuat batas kabupaten luar:", err));
}

// FUNGSI INITIALISASI MAP PROVINSI
function initMapProvinsi() {
    currentLevel = 'provinsi';
    document.getElementById('back-btn').style.display = 'none';
    resetDropdowns(0);
    document.getElementById('select-kabupaten').value = "";
    clearAllTematikLayers();
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
                        e.target.setStyle({ weight: 4, color: "#fa070f", fillOpacity: 0.35 });
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
        const bounds = geojsonLayer.getBounds();
        if(bounds.isValid()) map.fitBounds(bounds);
        populateKabupatenDropdown(data);
    })
    .catch(err => console.error("Error loading kabupaten:", err));
}

// RENDER LEVEL KABUPATEN
function renderLevelKabupaten(kabCode) {
    currentLevel = 'kabupaten';
    document.getElementById('back-btn').style.display = 'flex';
    resetDropdowns(2);
    clearAllTematikLayers();
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
                    mouseover: function (e) { e.target.setStyle({ weight: 3, color: "#e2fa08", fillOpacity: 0.3 }); },
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

// RENDER LEVEL KECAMATAN
function renderLevelKecamatan(kecCode) {
    currentLevel = 'kecamatan';
    document.getElementById('back-btn').style.display = 'flex';
    clearAllTematikLayers();
    if (geojsonLayer) map.removeLayer(geojsonLayer);
    
    fetch(`/api/wilayah/desa/${kecCode}`)
    .then(res => res.json())
    .then(data => {
        geojsonLayer = L.geoJSON(data, {
            style: { color: "#44ff33", weight: 1.5, fillColor: "#33ff58", fillOpacity: 0.1},
            onEachFeature: function (feature, layer) {
                const desaName = feature.properties.nama_desa;
                const desaCode = feature.properties.kode_desa;
                layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });
                layer.on('click', function () {
                    const popupContent = `
                    <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 10px;">
                        <h4 style="margin: 0 0 2px 0; color: #1e3a8a; font-size: 15px; text-align: center; font-weight: 700;">${desaName}</h4>
                        <p style="margin: 0; font-size: 11px; color: #7f8c8d; text-align: center;">Kode: ${desaCode}</p>
                        <hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;">
                        <p style="font-size: 11px; text-align: center; color: #555; margin-bottom: 12px; line-height: 1.4;">
                            Gunakan menu <b>Data Tematik</b> di sebelah kiri untuk melihat detail infografis desa ini.
                        </p>
                        <button onclick="bukaProfilDesa('${desaCode}', '${desaName}')" style="width: 100%; background: #1e3a8a; color: white; border: none; padding: 8px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: background 0.2s;">
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

// FUNGSI BERSIHKAN LAYER TEMATIK
function clearAllTematikLayers() {
    tematikKesehatanGroup.clearLayers();
    tematikUmumGroup.clearLayers();
    tematikKependudukanGroup.clearLayers();
    tematikProduksiGroup.clearLayers();
}

// FUNGSI TOGGLE ACCORDION SUBMENU TEMATIK
function toggleTematikSubmenu(menuName, element) {
    var subMenu = document.getElementById('submenu-' + menuName);
    var arrowIcon = element.querySelector('.tematik-arrow-icon');
    if (subMenu.style.display === "none" || subMenu.style.display === "") {
        subMenu.style.display = "flex";
        arrowIcon.style.transform = "rotate(90deg)";
    } else {
        subMenu.style.display = "none";
        arrowIcon.style.transform = "rotate(0deg)";
    }
    if (typeof map !== 'undefined') {
        setTimeout(() => { map.invalidateSize(); }, 200);
    }
}

// FUNGSI HELPER: MEMBUAT RENDER CHART DI DALAM POPUP LEAFLET
function renderPopupChart(canvasId, chartType, labels, dataValues, chartLabel, bgColors) {
    setTimeout(function() {
        const ctx = document.getElementById(canvasId);
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartLabel,
                        data: dataValues,
                        backgroundColor: bgColors || ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#1d4ed8'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: chartType === 'pie' || chartType === 'doughnut', position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                        tooltip: { callbacks: { label: function(c) { return ' ' + c.label + ': ' + c.raw.toLocaleString('id-ID'); } } }
                    },
                    scales: chartType === 'bar' ? { y: { beginAtZero: true, ticks: { font: { size: 9 } } }, x: { ticks: { font: { size: 9 } } } } : {}
                }
            });
        }
    }, 250);
}

// FUNGSI POPUP BUILDER UNTUK DATA TEMATIK BERSAMA CHART & ATRIBUT
function buildPopupWithChart(title, code, totalText, canvasId, tableHtml, buttonCallback) {
    return `
    <div style="font-family: 'Segoe UI', Tahoma, sans-serif; padding: 12px; background: #ffffff;">
        <h4 style="margin: 0 0 2px 0; color: #1e3a8a; font-size: 14px; text-align: center; font-weight: 700;">${title}</h4>
        <p style="margin: 0; font-size: 10px; color: #64748b; text-align: center;">Kode: ${code}</p>
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 6px; margin: 8px 0; text-align: center; font-size: 11px; font-weight: 600; color: #1e40af;">
            ${totalText}
        </div>
        <div style="position: relative; height: 160px; width: 100%; margin-bottom: 8px;">
            <canvas id="${canvasId}"></canvas>
        </div>
        <div style="max-height: 120px; overflow-y: auto; margin-bottom: 8px; border-top: 1px solid #f1f5f9;">
            ${tableHtml}
        </div>
        <button onclick="${buttonCallback}" style="width: 100%; background: #1e3a8a; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
            <i class="fa-solid fa-id-card"></i> Profil Desa
        </button>
    </div>`;
}

// SUBMENU KESEHATAN 1: TENAGA MEDIS (DILENGKAPI CHART)
function loadTematikKesehatan(e) {
    if(e) e.preventDefault();
    const kecamatanCode = document.getElementById('select-kecamatan').value;
    if(!kecamatanCode) {
        alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
        return;
    }
    clearAllTematikLayers();
    if (geojsonLayer) map.removeLayer(geojsonLayer);

    fetch(`/api/wilayah/kesehatan/${kecamatanCode}`)
    .then(res => res.json())
    .then(geojsonData => {
        const healthLayer = L.geoJSON(geojsonData, {
            style: function(feature) {
                const hasData = feature.properties.data_kesehatan && feature.properties.data_kesehatan.length > 0;
                return {fillColor: hasData? '#22c55e': '#cbd5e1', weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.65 };
            },
            onEachFeature: function(feature, layer) {
                const desaName = feature.properties.nama_desa;
                const desaCode = feature.properties.kode_desa;
                const listMedis = feature.properties.data_kesehatan || [];
                layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });

                if (listMedis.length > 0) {
                    const canvasId = 'chart-medis-' + desaCode;
                    const labels = listMedis.map(i => i.jenis_tenaga_medis);
                    const values = listMedis.map(i => parseInt(i.jumlah_personil || 0));
                    const totalPersonil = values.reduce((a, b) => a + b, 0);

                    let tableHtml = `<table style="width:100%; font-size:10px; border-collapse: collapse; margin-top: 4px;">
                    <tr style="background:#f1f5f9;"><th style="padding:4px; text-align:left;">Tenaga Medis</th><th style="padding:4px; text-align:center;">Jumlah</th></tr>`;
                    listMedis.forEach(item => {
                        tableHtml += `<tr><td style="padding:4px; border-bottom:1px solid #eee;">${item.jenis_tenaga_medis}</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#028090;">${item.jumlah_personil} Personil</td></tr>`;
                    });
                    tableHtml += `</table>`;

                    const popupContent = buildPopupWithChart(
                        desaName, desaCode, 
                        `Total Tenaga Medis: ${totalPersonil} Personil`, 
                        canvasId, tableHtml, 
                        `bukaProfilDesa('${desaCode}', '${desaName}')`
                    );
                    layer.bindPopup(popupContent);
                    layer.on('popupopen', () => {
                        renderPopupChart(canvasId, 'bar', labels, values, 'Jumlah Personil', ['#028090', '#00a896', '#059669', '#10b981', '#34d399']);
                    });
                } else {
                    layer.bindPopup(`<div style="padding:10px; font-size:11px; text-align:center;">Data tenaga kesehatan belum tersedia untuk <b>${desaName}</b></div>`);
                }
            }
        });
        tematikKesehatanGroup.addLayer(healthLayer);
        const bounds = healthLayer.getBounds();
        if(bounds.isValid()) map.fitBounds(bounds);
    })
    .catch(error => { console.error('Error:', error); alert('Gagal memuat data kesehatan.'); });
}

// SUBMENU KESEHATAN 2 & 3: AIR BERSIH & SANITASI (DILENGKAPI CHART)
function loadTematikUmum(e, tipe) {
    if(e) e.preventDefault();
    const kecamatanCode = document.getElementById('select-kecamatan').value;
    if(!kecamatanCode) {
        alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
        return;
    }
    clearAllTematikLayers();
    if (geojsonLayer) map.removeLayer(geojsonLayer);

    fetch(`/api/wilayah/${tipe}/${kecamatanCode}`)
    .then(res => res.json())
    .then(geojsonData => {
        const dynamicLayer = L.geoJSON(geojsonData, {
            style: function(feature) {
                let hasData = (tipe === 'air-bersih') ? (feature.properties.data_air && feature.properties.data_air.length > 0) : (feature.properties.data_sanitasi && feature.properties.data_sanitasi.length > 0);
                let colorTheme = tipe === 'air-bersih' ? '#028090' : '#8e44ad';
                return { fillColor: hasData ? colorTheme : '#cbd5e1', weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.65 };
            },
            onEachFeature: function(feature, layer) {
                const desaName = feature.properties.nama_desa;
                const desaCode = feature.properties.kode_desa;
                layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });

                if (tipe === 'air-bersih') {
                    const listAir = feature.properties.data_air || [];
                    if (listAir.length > 0) {
                        const canvasId = 'chart-air-' + desaCode;
                        const labels = listAir.map(i => i.jenis_sumber_air);
                        const values = listAir.map(i => parseInt(i.jumlah_unit || 0));
                        const totalUnit = values.reduce((a, b) => a + b, 0);

                        let tableHtml = `<table style="width:100%; font-size:10px; border-collapse: collapse; margin-top:4px;">
                        <tr style="background:#f4f7f6;"><th style="padding:4px; text-align:left;">Sumber Air</th><th style="padding:4px; text-align:center;">Unit</th><th style="padding:4px; text-align:center;">Kondisi</th></tr>`;
                        listAir.forEach(item => {
                            tableHtml += `<tr><td style="padding:4px; border-bottom:1px solid #eee;">${item.jenis_sumber_air}</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center; font-weight:bold;">${item.jumlah_unit}</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center;">${item.kondisi}</td></tr>`;
                        });
                        tableHtml += `</table>`;

                        const popupContent = buildPopupWithChart(
                            desaName, desaCode, 
                            `Total Sumber Air: ${totalUnit} Unit`, 
                            canvasId, tableHtml, 
                            `bukaProfilDesa('${desaCode}', '${desaName}')`
                        );
                        layer.bindPopup(popupContent);
                        layer.on('popupopen', () => {
                            renderPopupChart(canvasId, 'pie', labels, values, 'Jumlah Unit', ['#028090', '#3b82f6', '#06b6d4', '#0ea5e9', '#38bdf8']);
                        });
                    } else {
                        layer.bindPopup(`<div style="padding:10px; font-size:11px; text-align:center;">Data air bersih belum tersedia.</div>`);
                    }
                } else if (tipe === 'sanitasi') {
                    const listSanitasi = feature.properties.data_sanitasi || [];
                    if (listSanitasi.length > 0) {
                        const canvasId = 'chart-sanitasi-' + desaCode;
                        const item = listSanitasi[0] || {};
                        const labels = ['Jamban Leher Angsa', 'Jamban Non-Leher Angsa'];
                        const values = [parseInt(item.jamban_leher_angsa || 0), parseInt(item.jamban_non_leher_angsa || 0)];
                        const totalSanitasi = values[0] + values[1];

                        let tableHtml = `<table style="width:100%; font-size:10px; border-collapse: collapse; margin-top:4px;">
                        <tr style="background:#f4f7f6;"><th style="padding:4px; text-align:left;">Fasilitas</th><th style="padding:4px; text-align:center;">Jumlah</th></tr>
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Jamban Leher Angsa</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#8e44ad;">${values[0]} Unit</td></tr>
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Jamban Non-Leher Angsa</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center; font-weight:bold; color:#666;">${values[1]} Unit</td></tr>
                        </table>`;

                        const popupContent = buildPopupWithChart(
                            desaName, desaCode, 
                            `Total Sanitasi: ${totalSanitasi} Unit`, 
                            canvasId, tableHtml, 
                            `bukaProfilDesa('${desaCode}', '${desaName}')`
                        );
                        layer.bindPopup(popupContent);
                        layer.on('popupopen', () => {
                            renderPopupChart(canvasId, 'doughnut', labels, values, 'Fasilitas Sanitasi', ['#8e44ad', '#a855f7']);
                        });
                    } else {
                        layer.bindPopup(`<div style="padding:10px; font-size:11px; text-align:center;">Data sanitasi belum tersedia.</div>`);
                    }
                }
            }
        });
        tematikUmumGroup.addLayer(dynamicLayer);
        const bounds = dynamicLayer.getBounds();
        if(bounds.isValid()) map.fitBounds(bounds);
    })
    .catch(error => { console.error('Error:', error); alert('Gagal memuat data tematik wilayah.'); });
}

// SUBMENU KEPENDUDUKAN (DILENGKAPI CHART)
function loadTematikKependudukan(e, tipe) {
    if(e) e.preventDefault();
    const kecamatanCode = document.getElementById('select-kecamatan').value;
    if(!kecamatanCode) {
        alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter atas!');
        return;
    }
    clearAllTematikLayers();
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

                if (dataList.length > 0) {
                    const canvasId = 'chart-kependudukan-' + desaCode;
                    let labels = [], values = [], chartType = 'bar', totalSummary = "", bgColors = [];
                    let tableHtml = `<table style="width:100%; font-size:10px; border-collapse: collapse; margin-top:4px;">`;

                    if (tipe === 'penduduk-kk') {
                        const item = dataList[0];
                        const jmlLaki = parseInt(item.laki_laki || item.laki || item.pria || 0);
                        const jmlPerempuan = parseInt(item.perempuan || item.wanita || 0);
                        const totalPenduduk = parseInt(item.total_penduduk || (jmlLaki + jmlPerempuan));
                        const totalKK = parseInt(item.jumlah_kk || item.total_kk || 0);

                        labels = ['Laki-laki', 'Perempuan'];
                        values = [jmlLaki, jmlPerempuan];
                        chartType = 'pie';
                        bgColors = ['#2563eb', '#ec4899'];
                        totalSummary = `Total Penduduk: ${totalPenduduk.toLocaleString('id-ID')} Jiwa (${totalKK} KK)`;

                        tableHtml += `
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Total Penduduk</td><td style="padding:4px; border-bottom:1px solid #eee; font-weight:bold; text-align:center;">${totalPenduduk} Jiwa</td></tr>
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Laki-laki</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center;">${jmlLaki}</td></tr>
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Perempuan</td><td style="padding:4px; border-bottom:1px solid #eee; text-align:center;">${jmlPerempuan}</td></tr>
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Jumlah KK</td><td style="padding:4px; border-bottom:1px solid #eee; font-weight:bold; text-align:center;">${totalKK} KK</td></tr>`;

                    } else if (tipe === 'kesejahteraan') {
                        const item = dataList[0];
                        const totalSejahtera = parseInt(item.keluarga_sejahtera || 0);
                        const totalPrasejahtera = parseInt(item.keluarga_prasejahtera || item.pra_ks || 0);
                        const totalKK = parseInt(item.total_kk || (totalSejahtera + totalPrasejahtera));

                        labels = ['Keluarga Sejahtera', 'Prasejahtera'];
                        values = [totalSejahtera, totalPrasejahtera];
                        chartType = 'doughnut';
                        bgColors = ['#22c55e', '#ef4444'];
                        totalSummary = `Total Terdata: ${totalKK.toLocaleString('id-ID')} KK`;

                        tableHtml += `
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Sejahtera</td><td style="padding:4px; border-bottom:1px solid #eee; font-weight:bold; color:#22c55e; text-align:center;">${totalSejahtera} KK</td></tr>
                        <tr><td style="padding:4px; border-bottom:1px solid #eee;">Prasejahtera</td><td style="padding:4px; border-bottom:1px solid #eee; font-weight:bold; color:#ef4444; text-align:center;">${totalPrasejahtera} KK</td></tr>`;

                    } else if (tipe === 'mata-pencaharian' || tipe === 'tenaga-kerja' || tipe === 'tingkat-pendidikan') {
                        labels = dataList.map(i => i.sektor_pekerjaan || i.kategori_kerja || i.jenjang || i.jenjang_pendidikan || 'Lainnya');
                        values = dataList.map(i => parseInt(i.total || 0));
                        const grandTotal = values.reduce((a, b) => a + b, 0);

                        chartType = 'bar';
                        bgColors = ['#1e3a8a', '#3b82f6', '#028090', '#10b981', '#f59e0b', '#8e44ad'];
                        totalSummary = `Total Terdata: ${grandTotal.toLocaleString('id-ID')} Orang`;

                        tableHtml += `<tr style="background:#f4f7f6;"><th style="padding:4px; text-align:left;">Kategori</th><th style="padding:4px; text-align:center;">Total</th></tr>`;
                        dataList.forEach(i => {
                            const name = i.sektor_pekerjaan || i.kategori_kerja || i.jenjang || i.jenjang_pendidikan || 'Lainnya';
                            tableHtml += `<tr><td style="padding:4px; border-bottom:1px solid #eee;">${name}</td><td style="padding:4px; border-bottom:1px solid #eee; font-weight:bold; text-align:center;">${i.total || 0}</td></tr>`;
                        });
                    }
                    tableHtml += `</table>`;

                    const popupContent = buildPopupWithChart(
                        desaName, desaCode, 
                        totalSummary, 
                        canvasId, tableHtml, 
                        `bukaProfilDesa('${desaCode}', '${desaName}')`
                    );
                    layer.bindPopup(popupContent);
                    layer.on('popupopen', () => {
                        renderPopupChart(canvasId, chartType, labels, values, 'Jumlah', bgColors);
                    });
                } else {
                    layer.bindPopup(`<div style="padding:10px; font-size:11px; text-align:center;">Data kependudukan belum tersedia.</div>`);
                }
            }
        });
        tematikKependudukanGroup.addLayer(dynamicKependudukanLayer);
        const bounds = dynamicKependudukanLayer.getBounds();
        if(bounds.isValid()) map.fitBounds(bounds);
    })
    .catch(error => { console.error('Error:', error); alert('Gagal memuat data kependudukan.'); });
}

// SUBMENU PRODUKSI & APOTIK HIDUP (DILENGKAPI CHART)
function loadTematikProduksi(e, kategori) {
    if (e) e.preventDefault();
    const kecamatanCode = document.getElementById('select-kecamatan').value;
    if (!kecamatanCode) {
        alert('Silakan pilih Wilayah Kabupaten dan Kecamatan terlebih dahulu di dropdown filter!');
        return;
    }
    clearAllTematikLayers();
    if (geojsonLayer) map.removeLayer(geojsonLayer);

    fetch(`/api/tematik/produksi/${kategori}/${kecamatanCode}`)
    .then(res => res.json())
    .then(geojsonData => {
        if (!geojsonData || !geojsonData.features || geojsonData.features.length === 0) {
            alert(`Data produksi ${kategori.replace('-', ' ')} belum tersedia untuk wilayah ini.`);
            return;
        }

        const produksiLayer = L.geoJSON(geojsonData, {
            style: function(feature) {
                const props = feature.properties || {};
                const listData = props.data_apotik_hidup || props.list_tanaman || props.data_produksi || props.list_komoditas || [];
                const hasData = listData.length > 0;
                let themeColor = kategori === 'apotik-hidup' ? '#10b981' : '#f59e0b';
                return { fillColor: hasData ? themeColor : '#cbd5e1', weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.75 };
            },
            onEachFeature: function(feature, layer) {
                const props = feature.properties || {};
                const desaName = props.nama_desa || 'Desa Tanpa Nama';
                const desaCode = props.kode_desa || '-';
                layer.bindTooltip(desaName, { permanent: true, direction: "center", className: "map-label" });

                const listProduksi = props.data_apotik_hidup || props.list_tanaman || props.data_produksi || props.list_komoditas || [];

                if (listProduksi.length > 0) {
                    const canvasId = 'chart-produksi-' + desaCode;
                    const labels = listProduksi.map(i => i.nama_tanaman || i.nama_komoditas || 'Komoditas');
                    const valuesHasil = listProduksi.map(i => parseFloat(i.hasil_panen || i.hasil || 0));
                    const totalPanen = valuesHasil.reduce((a, b) => a + b, 0);

                    let tableHtml = `<table style="width:100%; font-size:10px; border-collapse: collapse; margin-top:4px;">
                    <thead><tr style="background:#fef3c7; color:#92400e; text-align:left;">
                        <th style="padding:4px;">Komoditas</th>
                        <th style="padding:4px; text-align:center;">Luas (Ha)</th>
                        <th style="padding:4px; text-align:right;">Hasil (Ton)</th>
                    </tr></thead><tbody>`;

                    listProduksi.forEach(item => {
                        tableHtml += `<tr>
                            <td style="padding:4px; border-bottom:1px solid #fffbeb;">${item.nama_tanaman || item.nama_komoditas || '-'}</td>
                            <td style="padding:4px; border-bottom:1px solid #fffbeb; text-align:center;">${item.luas_lahan || item.luas || 0}</td>
                            <td style="padding:4px; border-bottom:1px solid #fffbeb; text-align:right; font-weight:bold; color:#d97706;">${item.hasil_panen || item.hasil || 0}</td>
                        </tr>`;
                    });
                    tableHtml += '</tbody></table>';

                    const popupContent = buildPopupWithChart(
                        desaName, desaCode, 
                        `Total Hasil Panen: ${totalPanen.toLocaleString('id-ID')} Ton/Thn`, 
                        canvasId, tableHtml, 
                        `bukaProfilDesa('${desaCode}', '${desaName}')`
                    );

                    layer.bindPopup(popupContent);
                    layer.on('popupopen', () => {
                        renderPopupChart(canvasId, 'bar', labels, valuesHasil, 'Hasil Panen (Ton)', ['#f59e0b', '#d97706', '#b45309', '#78350f', '#fcd34d']);
                    });
                } else {
                    layer.bindPopup(`<div style="padding:10px; font-size:11px; text-align:center;">Data komoditas ${kategori} belum tersedia.</div>`);
                }
            }
        });

        tematikProduksiGroup.addLayer(produksiLayer);
        const bounds = produksiLayer.getBounds();
        if (bounds.isValid()) map.fitBounds(bounds);
    })
    .catch(err => {
        console.error(`Gagal memuat API produksi ${kategori}:`, err);
        alert(`Terjadi kesalahan koneksi saat mengambil data ${kategori}.`);
    });
}

// FUNGSI NAVIGASI KEMBALI
function goBack() {
    if (currentLevel === 'kecamatan') {
        renderLevelKabupaten(activeKabCode);
        document.getElementById('select-kabupaten').value = activeKabCode;
    } else if (currentLevel === 'kabupaten') {
        initMapProvinsi();
        map.setView([-4.85, 105.0], 9);
    }
}

// FUNGSI POPULATE DROPDOWN & FILTER
function populateKabupatenDropdown(data) {
    const select = document.getElementById('select-kabupaten');
    select.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
    if (data && data.features) {
        data.features.forEach(f => {
            select.innerHTML += `<option value="${f.properties.kode_kab}">${f.properties.nama_kab}</option>`;
        });
    }
}

function populateKecamatanDropdown(data) {
    const select = document.getElementById('select-kecamatan');
    select.disabled = false;
    select.innerHTML = '<option value="">Pilih Kecamatan</option>';
    if (data && data.features) {
        data.features.forEach(f => {
            select.innerHTML += `<option value="${f.properties.kode_kec}">${f.properties.nama_kec}</option>`;
        });
    }
}

function populateDesaDropdown(data) {
    const select = document.getElementById('select-desa');
    select.disabled = false;
    select.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
    if (data && data.features) {
        data.features.forEach(f => {
            select.innerHTML += `<option value="${f.properties.kode_desa}">${f.properties.nama_desa}</option>`;
        });
    }
}

function resetDropdowns(level) {
    if (level <= 1) {
        document.getElementById('select-kabupaten').value = "";
        const kec = document.getElementById('select-kecamatan');
        kec.innerHTML = '<option value="">Pilih Kecamatan</option>';
        kec.disabled = true;
    }
    if (level <= 2) {
        const desa = document.getElementById('select-desa');
        desa.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
        desa.disabled = true;
    }
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
                if (group.getLayers) layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
            });
        } else if (map.hasLayer(tematikUmumGroup)) {
            tematikUmumGroup.eachLayer(function(group) {
                if (group.getLayers) layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
            });
        } else if (map.hasLayer(tematikKependudukanGroup)) {
            tematikKependudukanGroup.eachLayer(function(group) {
                if (group.getLayers) layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
            });
        } else if (map.hasLayer(tematikProduksiGroup)) {
            tematikProduksiGroup.eachLayer(function(group) {
                if (group.getLayers) layerTarget = group.getLayers().find(layer => layer.feature.properties.kode_desa === value);
            });
        }

        if (layerTarget) {
            map.fitBounds(layerTarget.getBounds());
            layerTarget.fire('click');
        }
    }
}

// AKSI TOMBOL PROFIL DESA
function bukaProfilDesa(kodeDesa, namaDesa) {
    window.location.href = `/profil-desa/${kodeDesa}`;
}

// RUN AT STARTUP
loadBatasKabupaten();
initMapProvinsi();
</script>

<!-- MODAL LOWONGAN KERJA -->
<div id="modal-lowongan" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.65); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
<div style="position: relative; width: 90%; height: 88%; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); display: flex; flex-direction: column;">
<div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; background-color: #1e3a8a; color: white;">
<div style="display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 15px;">
<i class="fa-solid fa-briefcase"></i>
<span>Portal Lowongan Kerja SiGajah Kerja</span>
</div>
<button onclick="tutupModalLowongan()" style="background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
&times;
</button>
</div>
<div style="flex: 1; width: 100%; height: 100%; position: relative; background: #f8fafc;">
<iframe id="iframe-lowongan" src="" style="width: 100%; height: 100%; border: none;"></iframe>
</div>
</div>
</div>

<script>
function bukaModalLowongan(url) {
    var modal = document.getElementById('modal-lowongan');
    var iframe = document.getElementById('iframe-lowongan');
    if (modal && iframe) {
        iframe.src = url;
        modal.style.display = 'flex';
    }
}

function tutupModalLowongan() {
    var modal = document.getElementById('modal-lowongan');
    var iframe = document.getElementById('iframe-lowongan');
    if (modal && iframe) {
        modal.style.display = 'none';
        iframe.src = "";
        if (typeof map !== 'undefined' && map !== null) {
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }
    }
}
</script>
</body>
</html>