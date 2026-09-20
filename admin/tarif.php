<?php
session_start();
// Pastikan koneksi database benar (ubah jadi gosendd)
$conn = mysqli_connect("localhost", "root", "", "gosendd");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data tarif dari database
$query = "SELECT * FROM tarif";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tarif Ongkir - GoSend Admin</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #0b0f19;
            color: #ffffff;
            display: flex;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background: #0f172a;
            border-right: 1px solid #1e293b;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand {
            padding: 24px;
            border-bottom: 1px solid #1e293b;
        }
        .sidebar-menu {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
        }
        .sidebar-menu a {
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
        }
        .sidebar-menu a:hover {
            background: #1e293b;
            color: #ffffff;
        }
        .sidebar-menu a.active {
            background: #2563eb;
            color: #ffffff;
        }
        .sidebar-bottom {
            padding: 20px;
            border-top: 1px solid #1e293b;
        }
        .sidebar-bottom a {
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
            box-sizing: border-box;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        .topbar h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
        }
        .topbar p {
            margin: 5px 0 0 0;
            color: #94a3b8;
            font-size: 13px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
        }
        .tracking-card {
            background: #111827;
            border: 1px solid #1f2937;
            padding: 24px;
            border-radius: 12px;
        }
        .data-table th {
            color: #38bdf8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #1f2937;
        }
        .data-table td {
            border-bottom: 1px solid #1f2937;
            color: #e2e8f0;
            font-size: 14px;
        }
        .data-table tr:hover td {
            background: #1a2234;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <span style="font-size: 20px;">📦</span>
            <strong style="color: white; font-size: 18px; margin-left: 8px;">GoSend</strong>
            <small style="color: #38bdf8; display: block; font-size: 11px; font-weight: bold; margin-top: 2px;">ADMIN PANEL</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="driver.php">🛵 Kelola Driver</a>
            <a href="pesanan.php">📦 Pesanan</a>
            <a href="pembayaran.php">💳 Verifikasi Pembayaran</a>
            <a href="tracking.php">📍 Tracking</a>
            <a href="tarif.php" class="active">💰 Tarif Ongkir</a>
            <a href="area.php">📍 Area Layanan</a>
            <a href="laporan.php">📈 Laporan</a>
        </div>
        <div class="sidebar-bottom">
            <a href="../auth/logout.php" style="color: #ef4444;">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h1>Kelola Tarif Ongkir</h1>
                <p>Atur harga dasar dan tarif per kilometer pengiriman barang.</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <div>
                    <strong style="color: #ffffff; display: block; font-size: 14px;">Administrator</strong>
                </div>
            </div>
        </header>

        <section class="dashboard-section">
            <div class="tracking-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 18px; color: #ffffff;">Daftar Tarif Berdasarkan Kendaraan</h2>
                    <a href="tambah-tarif.php" class="btn-link" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500;">+ Tambah Tarif Baru</a>
                </div>

                <div style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr>
                                <th style="padding: 12px;">No</th>
                                <th style="padding: 12px;">Jenis Kendaraan</th>
                                <th style="padding: 12px;">Tarif Dasar (0-1 km)</th>
                                <th style="padding: 12px;">Tarif Per Kilometer berikutnya</th>
                                <th style="padding: 12px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) : 
                            ?>
                            <tr>
                                <td style="padding: 12px;"><?= $no++; ?></td>
                                <td style="padding: 12px; font-weight: 600; color: #ffffff;"><?= htmlspecialchars($row['jenis_kendaraan']); ?></td>
                                <td style="padding: 12px;">Rp <?= number_format($row['tarif_dasar'], 0, ',', '.'); ?></td>
                                <td style="padding: 12px;">Rp <?= number_format($row['tarif_km'], 0, ',', '.'); ?> / km</td>
                                <td style="padding: 12px;">
                                    <a href="edit-tarif.php?id=<?= $row['id']; ?>" style="color: #38bdf8; text-decoration: none; margin-right: 12px; font-weight: 500;">Edit</a>
                                    <a href="hapus-tarif.php?id=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus tarif ini?');" style="color: #ef4444; text-decoration: none; font-weight: 500;">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if (mysqli_num_rows($result) == 0) : ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;">Belum ada data tarif tersedia.</td>
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