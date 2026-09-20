<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "gosendd");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - GoSend Admin</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0b0f19; color: #ffffff; display: flex; }
        .sidebar { width: 260px; height: 100vh; background: #0f172a; border-right: 1px solid #1e293b; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar-brand { padding: 24px; border-bottom: 1px solid #1e293b; }
        .sidebar-menu { padding: 20px; display: flex; flex-direction: column; gap: 8px; overflow-y: auto; }
        .sidebar-menu a { padding: 12px 16px; color: #94a3b8; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 500; }
        .sidebar-menu a:hover { background: #1e293b; color: #ffffff; }
        .sidebar-menu a.active { background: #2563eb; color: #ffffff; }
        .sidebar-bottom { padding: 20px; border-top: 1px solid #1e293b; }
        .sidebar-bottom a { text-decoration: none; font-size: 14px; font-weight: 500; }
        .main-content { margin-left: 260px; flex: 1; padding: 30px; box-sizing: border-box; }
        .topbar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .topbar h1 { margin: 0; font-size: 24px; color: #ffffff; }
        .topbar p { margin: 5px 0 0 0; color: #94a3b8; font-size: 13px; }
        .tracking-card { background: #111827; border: 1px solid #1f2937; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
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
            <a href="tarif.php">💰 Tarif Ongkir</a>
            <a href="area.php">📍 Area Layanan</a>
            <a href="laporan.php" class="active">📈 Laporan</a>
        </div>
        <div class="sidebar-bottom">
            <a href="../auth/logout.php" style="color: #ef4444;">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h1>Laporan Transaksi</h1>
                <p>Rekapitulasi data pengiriman dan keuangan GoSend.</p>
            </div>
        </header>

        <section>
            <!-- Filter & Aksi Laporan -->
            <div class="tracking-card">
                <h2 style="margin-top: 0; font-size: 18px; color: #ffffff; margin-bottom: 15px;">Filter Rekapitulasi</h2>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px;">🖨️ Cetak / Print Laporan</button>
                </div>
            </div>

            <!-- Tabel Data Laporan Transaksi -->
            <div class="tracking-card">
                <h2 style="margin-top: 0; font-size: 18px; color: #ffffff; margin-bottom: 15px;">Riwayat Transaksi Masuk</h2>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                        <thead>
                            <tr style="border-bottom: 1px solid #1f2937; color: #94a3b8;">
                                <th style="padding: 12px;">No</th>
                                <th style="padding: 12px;">ID Pesanan</th>
                                <th style="padding: 12px;">Pengirim</th>
                                <th style="padding: 12px;">Penerima</th>
                                <th style="padding: 12px;">Total Biaya</th>
                                <th style="padding: 12px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Mengambil data dari tabel pesanan (pastikan nama tabel sesuai database Anda)
                            $query = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC");
                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    echo '<tr style="border-bottom: 1px solid #1f2937;">';
                                    echo '<td style="padding: 12px;">' . $no++ . '</td>';
                                    echo '<td style="padding: 12px; font-weight: 500;">#' . htmlspecialchars($row['id']) . '</td>';
                                    echo '<td style="padding: 12px;">' . htmlspecialchars($row['nama_pengirim'] ?? '-') . '</td>';
                                    echo '<td style="padding: 12px;">' . htmlspecialchars($row['nama_penerima'] ?? '-') . '</td>';
                                    echo '<td style="padding: 12px; color: #34d399; font-weight: bold;">Rp ' . number_format($row['total_biaya'] ?? 0, 0, ',', '.') . '</td>';
                                    echo '<td style="padding: 12px;"><span style="background: #1e293b; padding: 4px 8px; border-radius: 6px; font-size: 12px; color: #38bdf8;">' . htmlspecialchars($row['status'] ?? 'Selesai') . '</span></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #94a3b8;">Belum ada data laporan transaksi yang tercatat.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>