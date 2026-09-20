<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Cek autentikasi driver
if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "driver") {
}

$nama_driver = $_SESSION["nama_driver"] ?? $_SESSION["nama"] ?? "Driver";
$inisial     = strtoupper(substr($nama_driver, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Driver - GoSend</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f1f5f9;
            color: #1e293b;
            padding-bottom: 90px;
        }

        .top-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar-circle {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        .user-details .sub {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .user-details .name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge-active {
            background-color: #d1fae5;
            color: #047857;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .btn-logout {
            background-color: #fee2e2;
            color: #ef4444;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background-color: #fca5a5;
            color: #b91c1c;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 16px;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-performa {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .card-performa h3 {
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .performa-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr 1fr;
            text-align: center;
            align-items: center;
        }

        .performa-item {
            padding: 0 8px;
        }

        .performa-item:not(:last-child) {
            border-right: 1px solid #e2e8f0;
        }

        .performa-item .label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .performa-item .val {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .performa-item .val-green {
            color: #10b981;
        }

        .card-ongoing {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-ongoing:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(16, 185, 129, 0.35);
        }

        .card-ongoing .badge-ongoing {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .card-ongoing .title-ongoing {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .card-ongoing .desc-ongoing {
            font-size: 12px;
            opacity: 0.95;
        }

        .card-ongoing .tag-aktif {
            position: absolute;
            top: 16px;
            right: 16px;
            background-color: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(5px);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .card-order-baru {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.2s;
        }

        .card-order-baru:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            transform: translateY(-2px);
        }

        .order-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .icon-box {
            width: 46px;
            height: 46px;
            background-color: #eff6ff;
            color: #3b82f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .order-info .order-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .order-info .order-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            color: #0f172a;
        }

        .item-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .item-title {
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .item-desc {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .btn-action {
            width: 100%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn-action:hover {
            opacity: 0.95;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.03);
            z-index: 10;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 500;
            gap: 4px;
            cursor: pointer;
            border: none;
            background: none;
            transition: color 0.2s;
        }

        .nav-item.active {
            color: #10b981;
            font-weight: 700;
        }

        .nav-icon {
            font-size: 18px;
            transition: transform 0.2s;
        }

        .nav-item.active .nav-icon {
            transform: scale(1.1);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: flex-end;
            justify-content: center;
        }

        .modal.open {
            display: flex;
        }

        .modal-body {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
            border-radius: 20px 20px 0 0;
            padding: 24px;
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.1);
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .btn-close {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fade-out {
            animation: fadeOutAnim 0.4s ease forwards;
        }

        @keyframes fadeOutAnim {
            0% { opacity: 1; transform: scale(1); max-height: 200px; margin-bottom: 14px; }
            50% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 0; transform: scale(0.9); max-height: 0; padding: 0; margin-bottom: 0; border: none; overflow: hidden; }
        }

        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="top-header">
        <div class="user-profile">
            <div class="avatar-circle"><?= $inisial; ?></div>
            <div class="user-details">
                <div class="sub">Halo, Mitra Driver!</div>
                <div class="name"><?= htmlspecialchars($nama_driver); ?></div>
            </div>
        </div>

        <div class="header-actions">
            <div class="status-badge-active">
                <span class="status-dot"></span>
                Aktif
            </div>
            <a href="../auth/logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div id="tab-dashboard" class="tab-content active">
            <div class="card-performa">
                <h3>Performa Hari Ini</h3>
                <div class="performa-grid">
                    <div class="performa-item">
                        <div class="label">Tugas Selesai</div>
                        <div class="val" id="val-tugas">12</div>
                    </div>
                    <div class="performa-item">
                        <div class="label">Pendapatan</div>
                        <div class="val val-green" id="val-pendapatan">Rp 184.000</div>
                    </div>
                    <div class="performa-item">
                        <div class="label">Rating</div>
                        <div class="val">★ 4.9</div>
                    </div>
                </div>
            </div>

            <div class="card-ongoing" id="banner-ongoing" onclick="openModal('modalDetail')">
                <div class="tag-aktif">AKTIF</div>
                <div class="badge-ongoing">PENGIRIMAN SEDANG BERLANGSUNG</div>
                <div class="title-ongoing">Antar Barang ke Sudirman</div>
                <div class="desc-ongoing">Estimasi tiba dalam 18 menit. Klik untuk detail.</div>
            </div>

            <div class="card-order-baru" onclick="switchTab('tugas')">
                <div class="order-left">
                    <div class="icon-box">📥</div>
                    <div class="order-info">
                        <div class="order-title">Lihat Order Baru</div>
                        <div class="order-desc" id="desc-jumlah-order">Ada 2 tawaran pengiriman di sekitarmu</div>
                    </div>
                </div>
                <div style="color: #cbd5e1; font-weight: bold;">❯</div>
            </div>
        </div>

        <div id="tab-tugas" class="tab-content">
            <div class="section-title">Daftar Order Baru</div>

            <div id="list-order-container">
                <div class="item-card" id="order-8821" data-harga="25000">
                    <div class="item-header">
                        <span>#ORD-8821</span>
                        <span style="color: #10b981; font-weight: bold;">Rp 25.000</span>
                    </div>
                    <div class="item-title">Kirim Dokumen ke Jl. Asia Afrika</div>
                    <div class="item-desc">Penjemputan: Jl. Sunda No. 12 • Jarak: 3,2 km</div>
                    <button class="btn-action" onclick="terimaTugas('order-8821', 'Kirim Dokumen ke Jl. Asia Afrika')">Terima Tugas Ini</button>
                </div>

                <div class="item-card" id="order-8824" data-harga="35000">
                    <div class="item-header">
                        <span>#ORD-8824</span>
                        <span style="color: #10b981; font-weight: bold;">Rp 35.000</span>
                    </div>
                    <div class="item-title">Kirim Paket Makanan ke Dago</div>
                    <div class="item-desc">Penjemputan: Resto Buah Batu • Jarak: 5,1 km</div>
                    <button class="btn-action" onclick="terimaTugas('order-8824', 'Kirim Paket Makanan ke Dago')">Terima Tugas Ini</button>
                </div>
            </div>
        </div>

        <div id="tab-riwayat" class="tab-content">
            <div class="section-title">Riwayat Pengiriman Hari Ini</div>

            <div id="list-riwayat-container">
                <div class="item-card">
                    <div class="item-header">
                        <span>18 Sep 2026 • 14:20</span>
                        <span style="color: #10b981; font-weight: bold;">Selesai</span>
                    </div>
                    <div class="item-title">Antar Barang ke Gatot Subroto</div>
                    <div class="item-desc">Pendapatan: Rp 18.000</div>
                </div>

                <div class="item-card">
                    <div class="item-header">
                        <span>18 Sep 2026 • 11:05</span>
                        <span style="color: #10b981; font-weight: bold;">Selesai</span>
                    </div>
                    <div class="item-title">Antar Dokumen ke Pasir Kaliki</div>
                    <div class="item-desc">Pendapatan: Rp 22.000</div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalDetail" class="modal">
        <div class="modal-body">
            <div class="modal-header">
                <h3 style="font-size: 16px;">Detail Pengiriman Aktif</h3>
                <button class="btn-close" onclick="closeModal('modalDetail')">✕</button>
            </div>
            <div style="font-size: 14px; line-height: 1.6; margin-bottom: 20px; color: #475569;">
                <p><strong>Pengirim:</strong> Toko Serba Ada (08123456789)</p>
                <p><strong>Penerima:</strong> Pak Budi - Jl. Sudirman No. 45</p>
                <p><strong>Jenis Barang:</strong> Paket Electronic / Laptop</p>
                <p><strong>Catatan:</strong> Hati-hati barang pecah belah.</p>
            </div>
            <button class="btn-action" onclick="selesaikanPengiriman()">Tandai Selesai Diantar</button>
        </div>
    </div>

    <div class="bottom-nav">
        <button class="nav-item active" id="btn-dashboard" onclick="switchTab('dashboard')">
            <span class="nav-icon">🏠</span>
            Dashboard
        </button>
        <button class="nav-item" id="btn-tugas" onclick="switchTab('tugas')">
            <span class="nav-icon">📦</span>
            Tugas
        </button>
        <button class="nav-item" id="btn-riwayat" onclick="switchTab('riwayat')">
            <span class="nav-icon">🕒</span>
            Riwayat
        </button>
    </div>

    <script>
        let jumlahTugasSelesai = 12;
        let totalPendapatan = 184000;
        let activeOrderTitle = "Antar Barang ke Sudirman";
        let activeOrderPendapatan = 20000;

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('btn-' + tabName).classList.add('active');
        }

        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        function terimaTugas(cardId, namaTugas) {
            const card = document.getElementById(cardId);
            const harga = parseInt(card.getAttribute('data-harga')) || 20000;
            
            card.classList.add('fade-out');
            setTimeout(() => {
                card.remove();
                cekSisaTugas();
            }, 400);

            activeOrderTitle = namaTugas;
            activeOrderPendapatan = harga;
            
            const banner = document.getElementById('banner-ongoing');
            banner.style.display = 'block';
            banner.querySelector('.title-ongoing').innerText = namaTugas;
            banner.querySelector('.desc-ongoing').innerText = "Estimasi tiba dalam 15 menit. Klik untuk detail.";

            alert('Order berhasil diambil! Cek di halaman utama (Dashboard).');
        }

        function selesaikanPengiriman() {
            closeModal('modalDetail');

            jumlahTugasSelesai += 1;
            totalPendapatan += activeOrderPendapatan;

            document.getElementById('val-tugas').innerText = jumlahTugasSelesai;
            document.getElementById('val-pendapatan').innerText = "Rp " + totalPendapatan.toLocaleString('id-ID');

            const waktuSekarang = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + " • " + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            const riwayatContainer = document.getElementById('list-riwayat-container');
            const newRiwayatHTML = `
                <div class="item-card" style="animation: fadeIn 0.4s ease;">
                    <div class="item-header">
                        <span>${waktuSekarang}</span>
                        <span style="color: #10b981; font-weight: bold;">Selesai</span>
                    </div>
                    <div class="item-title">${activeOrderTitle}</div>
                    <div class="item-desc">Pendapatan: Rp ${activeOrderPendapatan.toLocaleString('id-ID')}</div>
                </div>
            `;
            riwayatContainer.insertAdjacentHTML('afterbegin', newRiwayatHTML);

            const banner = document.getElementById('banner-ongoing');
            banner.style.display = 'none';

            alert('Pengiriman berhasil diselesaikan dan masuk ke riwayat!');
        }

        function cekSisaTugas() {
            const container = document.getElementById('list-order-container');
            const sisaCard = container.querySelectorAll('.item-card');
            const descOrder = document.getElementById('desc-jumlah-order');
            
            if (sisaCard.length === 0) {
                container.innerHTML = '<div class="empty-state">Belum ada tawaran order baru di sekitarmu.</div>';
                descOrder.innerText = "Tidak ada order baru";
            } else {
                descOrder.innerText = `Ada ${sisaCard.length} tawaran pengiriman di sekitarmu`;
            }
        }
    </script>
</body>
</html>