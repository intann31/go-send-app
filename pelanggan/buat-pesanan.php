<?php

session_start();

require_once __DIR__ . "/../config/database.php";
if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil tarif aktif dari database untuk perhitungan otomatis di Javascript
$tarif_query = mysqli_query($conn, 
    "SELECT * FROM tarif_ongkir ORDER BY tarif_per_km ASC LIMIT 1"
);
$tarif_data = mysqli_fetch_assoc($tarif_query);$tarif_per_km = $tarif_data ? $tarif_data["tarif_per_km"] : 5000;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - GoSend</title>

    <link rel="stylesheet" href="../css/buat-pesanan.css">
    <!-- Leaflet CSS untuk Peta -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            width: 100%;
            height: 320px;
            border-radius: 10px;
            margin-bottom: 15px;
            z-index: 1;
        }
        .search-box {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
        }
        .search-box input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        .search-box button {
            padding: 10px 18px;
            background: #047857;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .search-box button:hover {
            background: #065f46;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">🚚</div>
            <div>
                <strong>GoSend</strong>
                <small>Antar Jemput Barang</small>
            </div>
        </div>

        <nav class="sidebar-menu">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="cek-ongkir.php">💰 Cek Ongkir</a>
            <a href="buat-pesanan.php" class="active">📦 Buat Pesanan</a>
            <a href="pembayaran.php">💳 Pembayaran</a>
            <a href="tracking.php">📍 Tracking</a>
            <a href="riwayat.php">🕘 Riwayat Pengiriman</a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../auth/logout.php">🚪 Logout</a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1>Buat Pesanan</h1>
                <p>Cari lokasi di peta atau ketik alamat pengiriman dengan lengkap.</p>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION["nama"] ?? 'U', 0, 1)); ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($_SESSION["nama"] ?? 'Pengguna'); ?></strong>
                    <small>Pelanggan</small>
                </div>
            </div>
        </header>

        <!-- FORM PESANAN -->
        <section class="dashboard-section">
            <div class="glass-panel order-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">PESANAN BARU</span>
                        <h2>Informasi Pengiriman & Peta</h2>
                        <p>Ketik alamat atau geser pin pada peta untuk menentukan titik jemput dan tujuan.</p>
                    </div>
                    <div class="panel-icon">📦</div>
                </div>

                <!-- PENCARIAN & PETA INTERAKTIF -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Cari Lokasi di Peta (Titik Jemput)</label>
                    <div class="search-box">
                        <input type="text" id="cariLokasiInput" placeholder="Ketik nama tempat/jalan (Contoh: Alun-alun Bandung)...">
                        <button type="button" onclick="cariLokasiPeta()">Cari</button>
                    </div>
                    <div id="map"></div>
                </div>

                <form action="proses-pesanan.php" method="POST">
                    <!-- Hidden Input untuk Jarak & Total Biaya -->
                    <input type="hidden" name="jarak_km" id="jarak_km" value="0">
                    <input type="hidden" name="total_tarif" id="total_tarif" value="0">

                    <!-- ALAMAT (Bisa dari Peta atau Ketik Manual) -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="alamat_jemput">Alamat Jemput</label>
                            <textarea
                                name="alamat_jemput"
                                id="alamat_jemput"
                                rows="3"
                                required
                                placeholder="Ketik alamat atau geser pin di peta..."
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label for="alamat_tujuan">Alamat Tujuan</label>
                            <textarea
                                name="alamat_tujuan"
                                id="alamat_tujuan"
                                rows="3"
                                required
                                placeholder="Ketik alamat atau geser pin di peta..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- ESTIMASI JARAK & BIAYA -->
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: span 2;">
                            <div style="padding: 12px 15px; background: rgba(4, 120, 87, 0.1); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(4, 120, 87, 0.2);">
                                <div>
                                    <span style="font-size: 12px; color: #047857; font-weight: bold; display: block;">ESTIMASI JARAK</span>
                                    <strong id="infoJarakText" style="font-size: 16px;">0 KM</strong>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 12px; color: #047857; font-weight: bold; display: block;">TOTAL ESTIMASI BIAYA</span>
                                    <strong id="infoBiayaText" style="font-size: 18px; color: #047857;">Rp 0</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PENERIMA -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_penerima">Nama Penerima</label>
                            <input type="text" id="nama_penerima" name="nama_penerima" placeholder="Nama lengkap penerima" required>
                        </div>

                        <div class="form-group">
                            <label for="no_hp_penerima">No. HP Penerima</label>
                            <input type="text" id="no_hp_penerima" name="no_hp_penerima" placeholder="Contoh: 081234567890" required>
                        </div>
                    </div>

                    <!-- BARANG -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" id="nama_barang" name="nama_barang" placeholder="Contoh: Dokumen / Makanan" required>
                        </div>

                        <div class="form-group">
                            <label for="berat_barang">Berat Barang</label>
                            <div class="input-unit">
                                <input type="number" id="berat_barang" name="berat_barang" min="0.1" step="0.1" placeholder="Contoh: 2" required>
                                <span>KG</span>
                            </div>
                        </div>
                    </div>

                    <!-- BUTTON -->
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn-outline">Batal</a>
                        <button type="submit" class="btn-primary">Buat Pesanan →</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var tarifPerKm = <?= (float)$tarif_per_km; ?>;

        // Inisialisasi Peta (Default Bandung)
        var map = L.map('map').setView([-6.9175, 107.6191], 12);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Marker Titik Jemput & Titik Tujuan
        var markerJemput = L.marker([-6.9175, 107.6191], {draggable: true, title: "Lokasi Jemput"}).addTo(map)
            .bindPopup("<b>Titik Jemput</b><br>Geser pin ini untuk ubah lokasi.");

        var markerTujuan = L.marker([-6.9000, 107.6300], {draggable: true, title: "Lokasi Tujuan"}).addTo(map)
            .bindPopup("<b>Titik Tujuan</b><br>Geser pin ini untuk ubah lokasi.");

        var groupMarker = L.featureGroup([markerJemput, markerTujuan]);

        var sedangKetikJemput = false;
        var sedangKetikTujuan = false;

        // Klik pada peta untuk memindahkan marker Tujuan
        map.on('click', function(e) {
            markerTujuan.setLatLng(e.latlng);
            hitungDanUpdateUI(true);
        });

        // Event drag marker pada peta
        markerJemput.on('dragend', function() { hitungDanUpdateUI(true); });
        markerTujuan.on('dragend', function() { hitungDanUpdateUI(true); });

        // Reverse Geocoding (Koordinat -> Nama Tempat)
        function ambilNamaAlamat(lat, lng, elementId) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById(elementId).value = data.display_name;
                    }
                })
                .catch(err => console.error(err));
        }

        // Hitung Jarak (KM) dan Biaya (Rp)
        function hitungDanUpdateUI(updateTeksAlamat = false) {
            var posJemput = markerJemput.getLatLng();
            var posTujuan = markerTujuan.getLatLng();

            var jarakMeter = posJemput.distanceTo(posTujuan);
            var jarakKM = (jarakMeter / 1000).toFixed(1);
            var finalJarak = parseFloat(jarakKM) < 0.5 ? 0.5 : parseFloat(jarakKM);

            var tarif = Math.round(finalJarak * tarifPerKm);
            if (tarif < 10000) tarif = 10000;

            document.getElementById('jarak_km').value = finalJarak;
            document.getElementById('total_tarif').value = tarif;

            document.getElementById('infoJarakText').innerText = finalJarak + " KM";
            document.getElementById('infoBiayaText').innerText = "Rp " + tarif.toLocaleString('id-ID');

            if (updateTeksAlamat) {
                if (!sedangKetikJemput) ambilNamaAlamat(posJemput.lat, posJemput.lng, 'alamat_jemput');
                if (!sedangKetikTujuan) ambilNamaAlamat(posTujuan.lat, posTujuan.lng, 'alamat_tujuan');
            }

            groupMarker.clearLayers();
            groupMarker.addLayer(markerJemput);
            groupMarker.addLayer(markerTujuan);
            map.fitBounds(groupMarker.getBounds().pad(0.2));
        }

        // Forward Geocoding (Nama Tempat -> Koordinat Peta)
        function cariLokasiDariTeks(keyword, marker, isJemput) {
            if (!keyword.trim() || keyword.length < 3) return;

            fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&q=' + encodeURIComponent(keyword))
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);

                        marker.setLatLng([lat, lon]);

                        if (isJemput) sedangKetikJemput = true;
                        else sedangKetikTujuan = true;

                        hitungDanUpdateUI(false);

                        setTimeout(() => {
                            sedangKetikJemput = false;
                            sedangKetikTujuan = false;
                        }, 1000);
                    }
                })
                .catch(err => console.error(err));
        }

        // Debounce agar API Nominatim tidak spamming per huruf
        function debounce(func, timeout = 700) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => { func.apply(this, args); }, timeout);
            };
        }

        // Event listener pas ngetik di Textarea Jemput
        document.getElementById('alamat_jemput').addEventListener('input', debounce(function(e) {
            cariLokasiDariTeks(e.target.value, markerJemput, true);
        }));

        // Event listener pas ngetik di Textarea Tujuan
        document.getElementById('alamat_tujuan').addEventListener('input', debounce(function(e) {
            cariLokasiDariTeks(e.target.value, markerTujuan, false);
        }));

        // Pencarian dari input search paling atas
        function cariLokasiPeta() {
            var keyword = document.getElementById('cariLokasiInput').value;
            if (!keyword.trim()) return;

            fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&q=' + encodeURIComponent(keyword))
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);
                        markerJemput.setLatLng([lat, lon]);
                        document.getElementById('alamat_jemput').value = data[0].display_name;
                        hitungDanUpdateUI(false);
                    } else {
                        alert("Lokasi tidak ditemukan!");
                    }
                });
        }
    </script>
</body>
</html>