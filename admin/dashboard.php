<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 1. Hitung ringkasan data
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role='pelanggan'"))['total'] ?? 0;
$total_driver    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role='driver'"))['total'] ?? 0;
$total_pesanan   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan"))['total'] ?? 0;
$menunggu_bayar  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan WHERE status_pesanan='menunggu_pembayaran'"))['total'] ?? 0;

// 2. Ambil 5 Pesanan terbaru
$query_terbaru = mysqli_query(
    $conn,
    "SELECT 
        p.id AS id_pesanan,
        p.data_barang AS nama_barang,
        p.created_at AS tgl_pesanan,
        p.total_biaya,
        p.status_pesanan,
        u.nama AS nama_pelanggan
    FROM pesanan p
    JOIN user u ON p.pelanggan_id = u.id
    ORDER BY p.id DESC
    LIMIT 5"
);

$nama_user = $_SESSION['nama'] ?? 'Admin';
$inisial   = strtoupper(substr($nama_user, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - GoSend</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0b0f19; color: #ffffff; display: flex; }
        .sidebar { width: 260px; height: 100vh; background: #0f172a; border-right: 1px solid #1e293b; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar-brand, .sidebar-logo { padding: 24px; border-bottom: 1px solid #1e293b; }
        .sidebar-logo h2 { margin: 0; color: white; font-size: 18px; }
        .sidebar-logo p { margin: 2px 0 0 0; color: #38bdf8; font-size: 11px; font-weight: bold; }
        
        .sidebar-menu { padding: 20px; display: flex; flex-direction: column; gap: 8px; overflow-y: auto; flex: 1; }
        .sidebar-menu a { padding: 12px 16px; color: #94a3b8; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu a:hover { background: #1e293b; color: #ffffff; }
        .sidebar-menu a.active { background: #2563eb; color: #ffffff; }
        
        .sidebar-bottom { padding: 20px; border-top: 1px solid #1e293b; }
        .sidebar-bottom a { color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 500; display: block; }
        
        .dashboard-main { margin-left: 260px; flex: 1; padding: 30px; box-sizing: border-box; }
        
        .dashboard-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; background: #111827; border: 1px solid #1f2937; padding: 24px; border-radius: 12px; }
        .dashboard-header h1 { margin: 0; font-size: 24px; color: #ffffff; }
        .dashboard-header p { margin: 5px 0 0 0; color: #94a3b8; font-size: 13px; }
        
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; background-color: #2563eb; color: #ffffff; font-weight: bold; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .user-info strong { display: block; font-size: 14px; color: #ffffff; }
        .user-info span { font-size: 12px; color: #94a3b8; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #111827; border: 1px solid #1f2937; padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { font-size: 28px; background: #1f2937; padding: 12px; border-radius: 10px; }
        .stat-info p { margin: 0; color: #94a3b8; font-size: 13px; }
        .stat-info h3 { margin: 4px 0 0 0; color: #ffffff; font-size: 20px; }

        /* Card & Table */
        .dashboard-card { background: #111827; border: 1px solid #1f2937; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { margin: 0; font-size: 18px; color: #ffffff; }
        .btn-link { color: #38bdf8; text-decoration: none; font-size: 13px; font-weight: 500; }
        .btn-link:hover { text-decoration: underline; }

        .table-wrapper { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .data-table th { background: #1f2937; color: #94a3b8; padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #374151; }
        .data-table td { padding: 14px 16px; border-bottom: 1px solid #1f2937; color: #e2e8f0; vertical-align: middle; }
        .data-table tr:hover td { background: #161e2e; }

        .status-badge { background: #1e3a8a; color: #93c5fd; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
        .empty-data { text-align: center; color: #94a3b8; padding: 30px !important; }
    </style>
</head>
<body>

<div class="dashboard-container" style="display: flex; width: 100%;">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-logo">
                <h2>📦 GoSend</h2>
                <p>ADMIN PANEL</p>
            </div>

            <nav class="sidebar-menu">
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="driver.php">🛵 Kelola Driver</a>
                <a href="pesanan.php">📦 Pesanan</a>
                <a href="pembayaran.php">💳 Pembayaran</a>
                <a href="tracking.php">📍 Tracking</a>
                <a href="tarif.php">💰 Tarif Ongkir</a>
                <a href="area.php">📍 Area Layanan</a>
                <a href="laporan.php">📈 Laporan</a>
            </nav>
        </div>
        <div class="sidebar-bottom">
            <a href="../auth/logout.php">🚪 Logout</a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="dashboard-main">

        <!-- HEADER -->
        <div class="dashboard-header">
            <div>
                <h1>Dashboard Admin</h1>
                <p>Kelola sistem antar jemput barang GoSend.</p>
            </div>
            <div class="user-profile">
                <div class="avatar"><?= $inisial; ?></div>
                <div class="user-info">
                    <strong><?= htmlspecialchars($nama_user); ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
        </div>

        <!-- CARDS RINGKASAN DATA (STATS GRID) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <p>Total Pelanggan</p>
                    <h3><?= $total_pelanggan; ?></h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🛵</div>
                <div class="stat-info">
                    <p>Total Driver</p>
                    <h3><?= $total_driver; ?></h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <p>Total Pesanan</p>
                    <h3><?= $total_pesanan; ?></h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💳</div>
                <div class="stat-info">
                    <p>Menunggu Pembayaran</p>
                    <h3><?= $menunggu_bayar; ?></h3>
                </div>
            </div>
        </div>

        <!-- TABEL AKTIVITAS TERBARU -->
        <section class="dashboard-card">
            <div class="card-header">
                <div>
                    <h2>Pesanan Terbaru</h2>
                </div>
                <a href="pesanan.php" class="btn-link">Lihat Semua &rarr;</a>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($query_terbaru) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_terbaru)): ?>
                            <tr>
                                <td><strong>#<?= $row['id_pesanan']; ?></strong></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tgl_pesanan'])); ?></td>
                                <td>Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="status-badge">
                                        <?= htmlspecialchars($row['status_pesanan']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-data">Belum ada pesanan terbaru.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

</div>

</body>
</html>