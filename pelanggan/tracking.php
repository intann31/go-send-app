<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_pesanan = $_GET['id'] ?? null;

// Query yang lebih handal (Mengecek nama dari tabel user)
if ($id_pesanan) {
    $query = mysqli_query($conn, "SELECT p.*, 
                                         COALESCE(u.nama, 'Driver GoSend') AS nama_driver, 
                                         COALESCE(u.no_telepon, '') AS hp_driver,
                                         d.no_plat,
                                         d.jenis_kendaraan
                                  FROM pesanan p 
                                  LEFT JOIN driver d ON p.driver_id = d.id 
                                  LEFT JOIN user u ON d.user_id = u.id
                                  WHERE p.id = '$id_pesanan' AND p.pelanggan_id = '$id_user'");
} else {
    $query = mysqli_query($conn, "SELECT p.*, 
                                         COALESCE(u.nama, 'Driver GoSend') AS nama_driver, 
                                         COALESCE(u.no_telepon, '') AS hp_driver,
                                         d.no_plat,
                                         d.jenis_kendaraan
                                  FROM pesanan p 
                                  LEFT JOIN driver d ON p.driver_id = d.id 
                                  LEFT JOIN user u ON d.user_id = u.id
                                  WHERE p.pelanggan_id = '$id_user' 
                                  ORDER BY p.id DESC LIMIT 1");
}

$pesanan = mysqli_fetch_assoc($query);

// Cek apakah driver_id sudah terisi (berarti orderan sudah diambil)
$sudah_ada_driver = !empty($pesanan['driver_id']);

// Ambil Status Pesanan dari Database
$status = strtolower($pesanan['status_pesanan'] ?? 'menunggu_pembayaran');

// Logika Aktif Stepper Status
$is_dijemput   = in_array($status, ['dijemput', 'dalam_perjalanan', 'selesai']);
$is_perjalanan = in_array($status, ['dalam_perjalanan', 'selesai']);
$is_selesai    = ($status == 'selesai');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Pengiriman - GoSend</title>
    <link rel="stylesheet" href="../css/buat-pesanan.css">
    <style>
        .tracking-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .driver-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .driver-profile {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .driver-avatar {
            width: 48px;
            height: 48px;
            background: #047857;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }
        .phone-btn {
            width: 40px;
            height: 40px;
            background: #e6f4ea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #047857;
            text-decoration: none;
            font-size: 18px;
        }

        /* Timeline / Stepper Status */
        .timeline {
            position: relative;
            padding-left: 30px;
            list-style: none;
            margin: 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 5px;
            bottom: 5px;
            left: 9px;
            width: 2px;
            background: #e5e7eb;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 24px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-icon {
            position: absolute;
            left: -30px;
            top: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #d1d5db;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
        .timeline-item.active .timeline-icon {
            background: #047857;
        }
        .timeline-content h4 {
            margin: 0;
            font-size: 15px;
            color: #9ca3af;
        }
        .timeline-item.active .timeline-content h4 {
            color: #111827;
            font-weight: 600;
        }
        .timeline-content p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #9ca3af;
        }
        .timeline-item.active .timeline-content p {
            color: #4b5563;
        }
    </style>
</head>
<body>

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
            <a href="buat-pesanan.php">📦 Buat Pesanan</a>
            <a href="pembayaran.php">💳 Pembayaran</a>
            <a href="tracking.php" class="active">📍 Tracking</a>
            <a href="riwayat.php">🕘 Riwayat Pengiriman</a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../auth/logout.php">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h1>Tracking Pengiriman</h1>
                <p>Pantau status pengiriman paket dan informasi driver.</p>
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

        <section class="dashboard-section">
            <?php if ($pesanan): ?>
                <div class="driver-box">
                    <div class="driver-profile">
                        <div class="driver-avatar">
                            <?= $sudah_ada_driver ? strtoupper(substr($pesanan['nama_driver'], 0, 1)) : '🚚'; ?>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 16px; color: #111827;">
                                <?= $sudah_ada_driver ? htmlspecialchars($pesanan['nama_driver']) : 'Mencari Driver...'; ?>
                            </strong>
                            <small style="color: #6b7280;">
                                <?php if ($sudah_ada_driver): ?>
                                    <?= htmlspecialchars($pesanan['jenis_kendaraan'] ?? 'Motor'); ?> • <?= htmlspecialchars($pesanan['no_plat'] ?? 'B 1234 ABC'); ?>
                                <?php else: ?>
                                    Pesanan Anda sedang menunggu konfirmasi driver
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <?php if ($sudah_ada_driver && !empty($pesanan['hp_driver'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $pesanan['hp_driver']); ?>" target="_blank" class="phone-btn" title="Hubungi Driver Via WA">💬</a>
                    <?php endif; ?>
                </div>

                <div class="tracking-card">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; color: #1f2937;">Status Pengiriman</h3>
                    
                    <ul class="timeline">
                        <li class="timeline-item <?= $is_dijemput ? 'active' : ''; ?>">
                            <div class="timeline-icon">✓</div>
                            <div class="timeline-content">
                                <h4>Paket Dijemput</h4>
                                <p>Kurir mengambil paket di alamat asal: <strong><?= htmlspecialchars($pesanan['alamat_jemput']); ?></strong></p>
                            </div>
                        </li>

                        <li class="timeline-item <?= $is_perjalanan ? 'active' : ''; ?>">
                            <div class="timeline-icon">✓</div>
                            <div class="timeline-content">
                                <h4>Dalam Perjalanan</h4>
                                <p>Paket sedang diantar ke penerima: <strong><?= htmlspecialchars($pesanan['nama_penerima'] ?? 'Penerima'); ?></strong> (<?= htmlspecialchars($pesanan['alamat_tujuan']); ?>)</p>
                            </div>
                        </li>

                        <li class="timeline-item <?= $is_selesai ? 'active' : ''; ?>">
                            <div class="timeline-icon">✓</div>
                            <div class="timeline-content">
                                <h4>Sampai Tujuan</h4>
                                <p>Paket telah diterima dengan baik di alamat tujuan.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="tracking-card" style="text-align: center; padding: 40px;">
                    <p style="color: #6b7280; font-size: 15px;">Belum ada pesanan aktif untuk dilacak.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>