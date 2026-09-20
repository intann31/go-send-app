<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

/* Ambil semua pembayaran */
$query = mysqli_query(
    $conn,
    "SELECT 
        pb.id AS id_pembayaran,
        pb.pesanan_id AS id_pesanan,
        pb.tgl_pembayaran,
        pb.metode_pembayaran,
        pb.bukti_pembayaran AS bukti_transfer,
        pb.status_pembayaran,
        u.nama,
        p.data_barang AS nama_barang,
        p.total_biaya AS jumlah_bayar,
        p.total_biaya
    FROM pembayaran pb
    JOIN pesanan p ON pb.pesanan_id = p.id
    JOIN user u ON p.pelanggan_id = u.id
    ORDER BY pb.id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - GoSend</title>
    <link rel="stylesheet" href="../css/pembayaran-admin.css">
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
        <a href="pembayaran.php" class="nav-item active">💳 Verifikasi Pembayaran</a>
        <a href="tracking.php" class="nav-item">📍 Tracking</a>
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
            <h1>Verifikasi Pembayaran</h1>
            <p>Periksa dan verifikasi pembayaran pelanggan.</p>
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
                    <span class="panel-label">DATA PEMBAYARAN</span>
                    <h2>Daftar Pembayaran</h2>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Pesanan</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td>#<?= $data["id_pembayaran"]; ?></td>
                                <td><?= htmlspecialchars($data["nama"]); ?></td>
                                <td>
                                    #<?= $data["id_pesanan"]; ?>
                                    <br>
                                    <small><?= htmlspecialchars($data["nama_barang"]); ?></small>
                                </td>
                                <td><?= date("d/m/Y H:i", strtotime($data["tgl_pembayaran"])); ?></td>
                                <td>Rp <?= number_format($data["jumlah_bayar"], 0, ",", "."); ?></td>
                                <td>
                                    <?php if (!empty($data["bukti_transfer"])): ?>
                                        <a href="../assets/uploads/<?= htmlspecialchars($data["bukti_transfer"]); ?>" target="_blank" class="btn-small">
                                            Lihat Bukti
                                        </a>
                                    <?php else: ?>
                                        Tidak ada
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="table-status"><?= htmlspecialchars($data["status_pembayaran"]); ?></span>
                                </td>
                                <td>
                                    <?php if ($data["status_pembayaran"] === "Menunggu Verifikasi"): ?>
                                        <div class="action-buttons">
                                            <a href="proses-verifikasi.php?id=<?= $data["id_pembayaran"]; ?>&status=Disetujui" class="btn-approve" onclick="return confirm('Setujui pembayaran ini?')">
                                                ✓ Setujui
                                            </a>
                                            <a href="proses-verifikasi.php?id=<?= $data["id_pembayaran"]; ?>&status=Ditolak" class="btn-reject" onclick="return confirm('Tolak pembayaran ini?')">
                                                ✕ Tolak
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="done-text">Sudah Diverifikasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-table">Belum ada data pembayaran.</td>
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