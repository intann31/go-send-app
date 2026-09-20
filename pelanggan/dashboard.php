<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

$nama = $_SESSION["nama"] ?? "Pelanggan";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - GoSend</title>

    <!-- Pastikan lokasi file CSS ini benar -->
    <link rel="stylesheet" href="../css/d.css">
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="#" class="brand">
        <div class="brand-icon">🚚</div>
        <div>
            <span>GoSend</span>
            <small>Sistem Antar Jemput</small>
        </div>
    </a>

    <nav>
        <a href="dashboard.php" style="color: #079455;">Dashboard</a>
        <a href="cek-ongkir.php">Cek Ongkir</a>
        <a href="buat-pesanan.php">Buat Pesanan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="tracking.php">Tracking</a>
    </nav>

    <div style="display: flex; align-items: center; gap: 15px;">
        <span style="font-size: 14px; font-weight: 600; color: #245c40;">👤 <?= htmlspecialchars($nama) ?></span>
        <a href="../auth/logout.php" class="btn-login" style="color: #d92d20; border-color: #fda29b;">Logout</a>
    </div>
</div>


<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <span class="badge">
            🚚 Sistem Antar Jemput Barang
        </span>

        <h1>
            Halo, <?= htmlspecialchars($nama) ?>! 👋
            <span>Mau kirim apa hari ini?</span>
        </h1>

        <p>
            Kami siap membantu pengirimanmu dengan mudah, cepat, dan aman sampai tujuan.
        </p>

        <div class="hero-buttons">
            <a href="buat-pesanan.php" class="btn-primary">
                🚚 Buat Pesanan
            </a>
            <a href="cek-ongkir.php" class="btn-outline">
                💰 Cek Ongkir
            </a>
        </div>
    </div>

    <!-- HERO VISUAL (Mencocokkan CSS Glass Card) -->
    <div class="hero-visual">
        <div class="glass-card">
            <div class="card-top">
                <div>
                    <small>Status Pengiriman</small>
                    <h3>Pesanan Aktif</h3>
                </div>
                <span class="status-dot">● Live</span>
            </div>

            <div class="route">
                <div class="route-item">
                    <div class="route-icon">📦</div>
                    <div>
                        <small>Lokasi Penjemputan</small>
                        <strong>Bandung</strong>
                    </div>
                </div>
                <div class="route-line"></div>
                <div class="route-item">
                    <div class="route-icon destination">📍</div>
                    <div>
                        <small>Tujuan</small>
                        <strong>Jakarta</strong>
                    </div>
                </div>
            </div>

            <div class="delivery-progress">
                <div class="progress-header">
                    <span>Proses Pengiriman</span>
                    <strong>75%</strong>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
        </div>

        <div class="floating-card card-price">
            <div class="floating-icon">💰</div>
            <div>
                <small>Harga Hemat</small>
                <strong>Mulai Rp 10.000</strong>
            </div>
        </div>

        <div class="floating-card card-track">
            <div class="floating-icon">⚡</div>
            <div>
                <small>Pengiriman</small>
                <strong>Super Cepat</strong>
            </div>
        </div>
    </div>
</section>


<!-- LAYANAN -->
<section class="section">
    <div class="section-heading">
        <div class="section-label">LAYANAN</div>
        <h2>Layanan <span>Pengiriman</span></h2>
        <p>Semua kebutuhan pengirimanmu dalam satu tempat.</p>
    </div>

    <div class="service-grid">
        <div class="service-card">
            <span class="service-number">01</span>
            <div class="service-icon">💰</div>
            <h3>Cek Ongkir</h3>
            <p>Hitung perkiraan biaya pengiriman dengan mudah sebelum memesan.</p>
            <a href="cek-ongkir.php" class="btn-primary" style="margin-top: 15px; padding: 8px 14px; font-size: 12px;">Cek Sekarang →</a>
        </div>

        <div class="service-card">
            <span class="service-number">02</span>
            <div class="service-icon">📦</div>
            <h3>Buat Pesanan</h3>
            <p>Kirim barang ke alamat tujuan dengan cepat dan terpercaya.</p>
            <a href="buat-pesanan.php" class="btn-primary" style="margin-top: 15px; padding: 8px 14px; font-size: 12px;">Buat Pesanan →</a>
        </div>

        <div class="service-card">
            <span class="service-number">03</span>
            <div class="service-icon">📍</div>
            <h3>Tracking</h3>
            <p>Pantau status dan lokasi real-time pengiriman barangmu.</p>
            <a href="tracking.php" class="btn-primary" style="margin-top: 15px; padding: 8px 14px; font-size: 12px;">Lihat Tracking →</a>
        </div>

        <div class="service-card">
            <span class="service-number">04</span>
            <div class="service-icon">🧾</div>
            <h3>Riwayat</h3>
            <p>Lihat daftar pengiriman yang pernah kamu lakukan sebelumnya.</p>
            <a href="riwayat.php" class="btn-primary" style="margin-top: 15px; padding: 8px 14px; font-size: 12px;">Lihat Riwayat →</a>
        </div>
    </div>
</section>


<!-- PROSES / KEUNGGULAN -->
<section class="process-section">
    <div class="process-content">
        <h2>Mengapa Pilih <span>GoSend?</span></h2>
        <p>Nikmati kemudahan layanan kurir modern langsung dari genggamanmu.</p>
    </div>

    <div class="steps">
        <div class="step">
            <div class="step-number">STEP 01</div>
            <h3>Kemudahan Akses</h3>
            <p>Buat pesanan dan pantau pengiriman kapan saja langsung dari sistem.</p>
        </div>

        <div class="step">
            <div class="step-number">STEP 02</div>
            <h3>Tracking Real-time</h3>
            <p>Kamu dapat melihat status dan posisi pengiriman barang secara transparan.</p>
        </div>

        <div class="step">
            <div class="step-number">STEP 03</div>
            <h3>Harga Terjangkau</h3>
            <p>Tarif transparan tanpa biaya tersembunyi dengan estimasi tepat.</p>
        </div>

        <div class="step">
            <div class="step-number">STEP 04</div>
            <h3>Berikan Rating</h3>
            <p>Berikan penilaian dan ulasan setelah pengiriman selesai dilakukan.</p>
        </div>
    </div>
</section>


<!-- FOOTER -->
<footer>
    <div class="footer-brand">
        <div class="brand-icon">🚚</div>
        <div>
            <strong>GoSend</strong>
            <p>Sistem Antar Jemput Barang</p>
        </div>
    </div>
    <div class="copyright">
        © 2026 GoSend. All rights reserved.
    </div>
</footer>

</body>
</html>