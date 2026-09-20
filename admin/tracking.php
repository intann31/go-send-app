<?php
session_start();

require_once __DIR__ . "/../config/database.php";

// Cek autentikasi admin
if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data tracking beserta detail pesanan, pelanggan, dan driver
$query_tracking = mysqli_query(
    $conn,
    "SELECT
        p.id AS id_pesanan,
        u.nama AS nama_pelanggan,
        p.alamat_jemput,
        p.alamat_tujuan,
        p.status_pesanan,
        du.nama AS nama_driver,
        d.no_plat
    FROM pesanan p
    LEFT JOIN user u ON p.pelanggan_id = u.id
    LEFT JOIN driver d ON p.driver_id = d.id
    LEFT JOIN user du ON d.user_id = du.id
    ORDER BY p.id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Pengiriman - GoSend Admin</title>
    <link rel="stylesheet" href="../css/pembayaran-admin.css">
    <style>
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-proses { background-color: #cce5ff; color: #004085; }
        .status-dikirim { background-color: #d4edda; color: #155724; }
        .status-selesai { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>GoSend</h2>
        <span>ADMIN PANEL</span>
    </div>

    <nav class="sidebar-menu">
        <a href="dashboard.php" class="nav-item">🏠 Dashboard</a>
        <a href="driver.php" class="nav-item">🚚 Kelola Driver</a>
        <a href="pesanan.php" class="nav-item">📦 Pesanan</a>
        <a href="pembayaran.php" class="nav-item">💳 Verifikasi Pembayaran</a>
        <a href="tracking.php" class="nav-item active">📍 Tracking</a>
        <a href="tarif.php" class="nav-item">💰 Tarif Ongkir</a>
        <a href="area.php" class="nav-item">🗺️ Area Layanan</a>
        <a href="laporan.php" class="nav-item">📊 Laporan</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="../auth/logout.php" class="nav-item">🚪 Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">

    <header class="topbar">
        <div>
            <h1>Tracking Pengiriman</h1>
            <p>Pantau status dan lokasi pengiriman barang pelanggan secara real-time.</p>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION["nama"] ?? 'A', 0, 1)); ?>
            </div>
            <div>
                <strong><?= htmlspecialchars($_SESSION["nama"] ?? 'Admin'); ?></strong>
                <small>Administrator</small>
            </div>
        </div>
    </header>

    <section class="dashboard-section">
        <div class="glass-panel">
            <div class="panel-header">
                <div>
                    <span class="panel-label">MONITORING PENGIRIMAN</span>
                    <h2>Daftar Lacak Pesanan</h2>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Lokasi Jemput</th>
                            <th>Lokasi Tujuan</th>
                            <th>Driver</th>
                            <th>Status Pengiriman</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($query_tracking && mysqli_num_rows($query_tracking) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_tracking)): ?>
                            <tr>
                                <td>#<?= $row["id_pesanan"]; ?></td>
                                <td><?= htmlspecialchars($row["nama_pelanggan"] ?? 'Pelanggan'); ?></td>
                                <td><?= htmlspecialchars($row["alamat_jemput"] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($row["alamat_tujuan"] ?? '-'); ?></td>
                                <td>
                                    <?php if (!empty($row["nama_driver"])): ?>
                                        <strong><?= htmlspecialchars($row["nama_driver"]); ?></strong><br>
                                        <small>(<?= htmlspecialchars($row["no_plat"] ?? '-'); ?>)</small>
                                    <?php else: ?>
                                        <span style="color: #888;">Belum Ditentukan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status = $row["status_pesanan"] ?? 'Menunggu';
                                    $badge_class = 'status-pending';
                                    if ($status === 'Diproses') $badge_class = 'status-proses';
                                    elseif ($status === 'In Transit' || $status === 'Dikirim') $badge_class = 'status-dikirim';
                                    elseif ($status === 'Selesai') $badge_class = 'status-selesai';
                                    ?>
                                    <span class="badge-status <?= $badge_class; ?>">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-table">Belum ada data pengiriman untuk dilacak.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>

</body>
</html>