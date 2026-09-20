<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Ongkir - GoSend</title>
    <link rel="stylesheet" href="../css/ongkir.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            width: 100%;
            height: 280px;
            border-radius: 10px;
            margin-bottom: 15px;
            z-index: 1;
        }
        .search-box {
            margin-bottom: 10px;
            display: flex;
            gap: 8px;
        }
        .search-box input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        .search-box button {
            padding: 8px 15px;
            background: #047857;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="app-container">

        <div class="app-header">
            <a href="dashboard.php" class="btn-back" title="Kembali ke Dashboard">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <h2>Cek Ongkir</h2>
        </div>

        <main class="main-content-full">

            <div class="card-panel">

                <div class="panel-header">
                    <div>
                        <span class="panel-label">ESTIMASI ONGKIR</span>
                        <h3>Hitung biaya pengiriman</h3>
                        <p>Cari nama tempat atau geser pin pada peta untuk menentukan titik jemput & tujuan.</p>
                    </div>
                </div>

                <div class="search-box">
                    <input type="text" id="inputCariJemput" placeholder="Ketik lokasi jemput (Contoh: Alun-alun Bandung)...">
                    <button type="button" onclick="cariLokasi('jemput')">Cari</button>
                </div>

                <div id="map"></div>

                <form id="formOngkir">

                    <div class="form-grid" style="grid-template-columns: 1fr;">

                        <div class="form-group">
                            <label>Jarak Pengiriman</label>
                            <div class="input-unit">
                                <input
                                    type="number"
                                    id="jarak"
                                    min="1"
                                    step="0.1"
                                    placeholder="Contoh: 10"
                                    required
                                >
                                <span>KM</span>
                            </div>
                        </div>

                    </div>

                    <div class="tarif-info">
                        <div>
                            <span>Tarif per KM</span>
                            <strong id="tarifPerKm">Rp 0</strong>
                        </div>

                        <div>
                            <span>Tarif Minimal</span>
                            <strong id="tarifMinimal">Rp 0</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        Hitung Ongkir
                    </button>

                </form>

                <div id="hasilOngkir" class="hasil-ongkir">
                    <span>ESTIMASI BIAYA</span>
                    <h2 id="totalOngkir">Rp 0</h2>
                    <p>Estimasi biaya berdasarkan jarak pengiriman.</p>
                </div>

            </div>

        </main>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../js/ongkir.js"></script>
    <script>
        // Inisialisasi Peta (Titik tengah default di Bandung)
        var map = L.map('map').setView([-6.9175, 107.6191], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Marker Titik Jemput (Bisa digeser)
        var markerJemput = L.marker([-6.9175, 107.6191], {draggable: true, title: "Titik Jemput"}).addTo(map)
            .bindPopup("Lokasi Jemput").openPopup();

        // Marker Titik Tujuan (Bisa digeser)
        var markerTujuan = L.marker([-6.9000, 107.6300], {draggable: true, title: "Titik Tujuan"}).addTo(map)
            .bindPopup("Lokasi Tujuan");

        // Fungsi otomatis menghitung jarak saat marker digeser
        function hitungJarakPeta() {
            var posJemput = markerJemput.getLatLng();
            var posTujuan = markerTujuan.getLatLng();

            var jarakMeter = posJemput.distanceTo(posTujuan);
            var jarakKM = (jarakMeter / 1000).toFixed(1);

            var inputJarak = document.getElementById('jarak');
            if (inputJarak) {
                inputJarak.value = jarakKM < 0.5 ? 1 : jarakKM; 
                inputJarak.dispatchEvent(new Event('input'));
                inputJarak.dispatchEvent(new Event('change'));
            }
        }

        markerJemput.on('dragend', hitungJarakPeta);
        markerTujuan.on('dragend', hitungJarakPeta);

        // Fungsi Pencarian Lokasi (Geocoding via Nominatim OpenStreetMap)
        function cariLokasi(tipe) {
            var keyword = document.getElementById('inputCariJemput').value;
            if (!keyword) return;

            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(keyword))
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);
                        
                        var newLatLng = new L.LatLng(lat, lon);
                        markerJemput.setLatLng(newLatLng);
                        map.setView(newLatLng, 14);
                        
                        hitungJarakPeta();
                    } else {
                        alert("Lokasi tidak ditemukan!");
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Hitung jarak awal saat halaman dimuat
        hitungJarakPeta();
    </script>

</body>

</html>