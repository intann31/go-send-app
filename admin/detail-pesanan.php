<?php
include '../config/database.php';

// Ambil ID pesanan dari URL
$id_pesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data pesanan lengkap dengan JOIN ke tabel user, driver, dan tarif
$stmt = mysqli_prepare(
    $conn,
    "SELECT 
        p.*,
        u.nama AS nama_pelanggan,
        u.no_hp AS hp_pelanggan,
        d.no_plat,
        d.jenis_kendaraan,
        du.nama AS nama_driver,
        t.tarif AS harga_tarif
    FROM pesanan p 
    LEFT JOIN user u ON p.user_id = u.id 
    LEFT JOIN driver d ON p.driver_id = d.id 
    LEFT JOIN user du ON d.user_id = du.id 
    LEFT JOIN tarif t ON p.tarif_id = t.id 
    WHERE p.id = ?"
);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);
    $resultDetail = mysqli_stmt_get_result($stmt);
    $detailPesanan = mysqli_fetch_assoc($resultDetail);
}

// Jika data pesanan tidak ditemukan
if (!$detailPesanan) {
    echo "<script>alert('Data pesanan tidak ditemukan!'); window.location='pesanan.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $detailPesanan['id']; ?> - GoSend Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .dashboard-container { display: flex; width: 100%; min-height: 100vh; }
        .sidebar { width: 260px; background-color: #0b1120; padding: 24px 16px; display: flex; flex-direction: column; flex-shrink: 0; position: fixed; height: 100vh; left: 0; top: 0; }
        .sidebar-logo { margin-bottom: 28px; padding-left: 12px; }
        .sidebar-logo h2 { color: #ffffff; font-size: 22px; font-weight: 700; }
        .sidebar-logo p { color: #38bdf8; font-size: 11px; letter-spacing: 1px; margin-top: 2px; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 8px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 18px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 12px; transition: all 0.2s ease-in-out; }
        .sidebar-menu a:hover { color: #ffffff; background-color: rgba(255, 255, 255, 0.05); }
        .sidebar-menu a.active { color: #ffffff; background: linear-gradient(135deg, #2563eb, #3b82f6); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); font-weight: 600; }
        .sidebar-bottom { margin-top: auto; }
        .sidebar-bottom a { color: #ef4444; }
        .dashboard-main { margin-left: 260px; flex: 1; padding: 32px; overflow-y: auto; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; background: #ffffff; padding: 20px 24px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02); }
        .dashboard-header h1 { font-size: 24px; color: #0f172a; font-weight: 700; }
        .dashboard-header p { color: #64748b; font-size: 14px; margin-top: 4px; }
        .dashboard-card { background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); margin-bottom: 24px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { font-size: 18px; color: #0f172a; }
        .status-badge { background-color: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        
        /* Style Kotak Detail Pesanan */
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-top: 15px; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 15px; }
        .detail-item label { display: block; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .detail-item span { font-size: 15px; color: #0f172a; font-weight: 500; }
        .btn-back { display: inline-block; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 15px; transition: background 0.2s; }
        .btn-back:hover { background: #475569; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>GoSend</h2>
                <p>ADMIN PANEL</p>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="driver.php">Kelola Driver</a>
                <a href="pesanan.php" class="active">Pesanan</a>
                <a href="pembayaran.php">Pembayaran</a>
                <a href="tracking.php">Tracking</a>
                <a href="tarif.php">Tarif Ongkir</a>
                <a href="area.php">Area Layanan</a>
                <a href="laporan.php">Laporan</a>
            </div>
            <div class="sidebar-bottom sidebar-menu">
                <a href="../auth/logout.php">Logout</a>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="dashboard-main">
            <header class="dashboard-header">
                <div>
                    <h1>Detail Pesanan</h1>
                    <p>Informasi lengkap transaksi pesanan pengiriman barang.</p>
                </div>
            </header>

            <section class="dashboard-card">
                <a href="pesanan.php" class="btn-back">&larr; Kembali ke Daftar Pesanan</a>
                
                <div class="card-header" style="margin-top: 10px;">
                    <h2>Informasi Pesanan #<?php echo $detailPesanan['id']; ?></h2>
                    <span class="status-badge"><?php echo ucfirst($detailPesanan['status_pesanan'] ?? 'Proses'); ?></span>
                </div>

                <div class="detail-box">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Nama Pelanggan</label>
                            <span><?php echo htmlspecialchars($detailPesanan['nama_pelanggan'] ?? 'Tidak diketahui'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Nomor HP Pelanggan</label>
                            <span><?php echo htmlspecialchars($detailPesanan['hp_pelanggan'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Alamat Pengirim / Jemput</label>
                            <span><?php echo htmlspecialchars($detailPesanan['alamat_jemput'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Alamat Penerima</label>
                            <span><?php echo htmlspecialchars($detailPesanan['alamat_tujuan'] ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Driver Bertugas</label>
                            <span><?php echo htmlspecialchars($detailPesanan['nama_driver'] ?? 'Belum ada driver'); ?> (Plat: <?php echo htmlspecialchars($detailPesanan['no_plat'] ?? '-'); ?>)</span>
                        </div>
                        <div class="detail-item">
                            <label>Total Biaya / Tarif</label>
                            <span style="color: #16a34a; font-weight: 700;">Rp <?php echo number_format($detailPesanan['harga_tarif'] ?? 0, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

</body>
</html>