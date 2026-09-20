<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

/* =========================
   DETAIL PESANAN
========================= */
$detailPesanan = null;

if (isset($_GET['detail'])) {
    $id_pesanan = (int) $_GET['detail'];

   $stmt = mysqli_prepare(
    $conn,
    "
    SELECT 
        p.*,
        u.nama AS nama_pelanggan,
        u.email AS email_pelanggan,
        d.no_plat,
        d.jenis_kendaraan,
        du.nama AS nama_driver
    FROM pesanan p
    INNER JOIN user u 
        ON p.pelanggan_id = u.id
    LEFT JOIN driver d 
        ON p.driver_id = d.id
    LEFT JOIN user du 
        ON d.user_id = du.id
    WHERE p.id = ?
    "
);

    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);
    $resultDetail = mysqli_stmt_get_result($stmt);
    $detailPesanan = mysqli_fetch_assoc($resultDetail);
}

/* =========================
   DATA PESANAN
========================= */
$queryPesanan = mysqli_query(
    $conn,
    "
    SELECT 
        p.id AS id_pesanan,
        p.created_at AS tgl_pesanan,
        p.data_barang AS nama_barang,
        p.nama_penerima,
        p.total_biaya,
        p.status_pesanan,
        u.nama AS nama_pelanggan,
        d.no_plat,
        du.nama AS nama_driver
    FROM pesanan p
    INNER JOIN user u 
        ON p.pelanggan_id = u.id
    LEFT JOIN driver d 
        ON p.driver_id = d.id
    LEFT JOIN user du 
        ON d.user_id = du.id
    ORDER BY p.created_at DESC
    "
);

/* =========================
   DATA DRIVER
========================= */
$queryDriver = mysqli_query(
    $conn,
    "
    SELECT 
        d.id AS id_driver,
        d.no_plat,
        d.jenis_kendaraan,
        u.nama
    FROM driver d
    INNER JOIN user u 
        ON d.user_id = u.id
    ORDER BY u.nama ASC
    "
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - GoSend Admin</title>
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

        .card { background: #111827; border: 1px solid #1f2937; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { margin: 0; font-size: 18px; color: #ffffff; }
        .card-header p { margin: 4px 0 0 0; color: #94a3b8; font-size: 13px; }

        /* Table Styling */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { background: #1f2937; color: #94a3b8; padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #374151; }
        td { padding: 14px 16px; border-bottom: 1px solid #1f2937; color: #e2e8f0; vertical-align: middle; }
        tr:hover td { background: #161e2e; }

        /* Buttons & Badges */
        .btn-primary { background: #2563eb; color: white; padding: 10px 16px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #374151; color: white; padding: 8px 14px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; font-size: 13px; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-small { background: #2563eb; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; display: inline-block; }
        .btn-small:hover { background: #1d4ed8; }

        .status-badge { background: #1e3a8a; color: #93c5fd; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
        
        /* Detail Grid */
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .detail-item { background: #1f2937; padding: 14px; border-radius: 8px; }
        .detail-item span { display: block; color: #94a3b8; font-size: 12px; margin-bottom: 4px; }
        .detail-item strong { color: #ffffff; font-size: 14px; }
        .detail-full { grid-column: 1 / -1; }
        
        /* Assignment Section */
        .driver-assignment { border-top: 1px solid #1f2937; padding-top: 20px; }
        .driver-assignment h3 { margin: 0 0 12px 0; font-size: 16px; color: #ffffff; }
        .assignment-form { display: flex; gap: 12px; align-items: center; }
        .assignment-form select { flex: 1; background: #1f2937; border: 1px solid #374151; color: white; padding: 10px; border-radius: 8px; font-size: 14px; }
        .empty-data { text-align: center; color: #94a3b8; padding: 30px !important; }
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
            <a href="pesanan.php" class="active">📦 Pesanan</a>
            <a href="pembayaran.php">💳 Verifikasi Pembayaran</a>
            <a href="tracking.php">📍 Tracking</a>
            <a href="tarif.php">💰 Tarif Ongkir</a>
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
                <h1>Kelola Pesanan</h1>
                <p>Melihat pesanan masuk dan menentukan driver pengiriman.</p>
            </div>
        </header>

        <!-- DETAIL PESANAN -->
        <?php if ($detailPesanan): ?>
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Detail Pesanan #<?= $detailPesanan['id']; ?></h2>
                    <p>Informasi lengkap transaksi pelanggan.</p>
                </div>
                <a href="pesanan.php" class="btn-secondary">Tutup Detail</a>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <span>ID Pesanan</span>
                    <strong>#<?= $detailPesanan['id']; ?></strong>
                </div>
                <div class="detail-item">
                    <span>Tanggal Pesanan</span>
                    <strong><?= date('d-m-Y H:i', strtotime($detailPesanan['created_at'])); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Pelanggan</span>
                    <strong><?= htmlspecialchars($detailPesanan['nama_pelanggan']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Email Pelanggan</span>
                    <strong><?= htmlspecialchars($detailPesanan['email_pelanggan']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Nama Barang</span>
                    <strong><?= htmlspecialchars($detailPesanan['data_barang']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Nama Penerima</span>
                    <strong><?= htmlspecialchars($detailPesanan['nama_penerima']); ?></strong>
                </div>
                <div class="detail-item detail-full">
                    <span>Alamat Jemput</span>
                    <strong><?= htmlspecialchars($detailPesanan['alamat_jemput']); ?></strong>
                </div>
                <div class="detail-item detail-full">
                    <span>Alamat Tujuan</span>
                    <strong><?= htmlspecialchars($detailPesanan['alamat_tujuan']); ?></strong>
                </div>
                <div class="detail-item">
    <span>Jarak Pengiriman</span>
    <strong><?= htmlspecialchars($detailPesanan['jarak_km'] ?? '0'); ?> km</strong>
</div>
                <div class="detail-item">
    <span>Area Layanan</span>
    <strong><?= htmlspecialchars($detailPesanan['nama_area'] ?? 'Area Umum'); ?></strong>
</div>
                <div class="detail-item">
                    <span>Total Biaya</span>
                    <strong style="color: #38bdf8;">Rp <?= number_format($detailPesanan['total_biaya'], 0, ',', '.'); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Status Pesanan</span>
                    <strong><span class="status-badge"><?= htmlspecialchars($detailPesanan['status_pesanan']); ?></span></strong>
                </div>
                <div class="detail-item">
                    <span>Driver</span>
                    <strong>
                        <?php if ($detailPesanan['nama_driver']): ?>
                            <?= htmlspecialchars($detailPesanan['nama_driver']); ?> - <?= htmlspecialchars($detailPesanan['no_plat']); ?>
                        <?php else: ?>
                            <span style="color: #f59e0b;">Belum ditentukan</span>
                        <?php endif; ?>
                    </strong>
                </div>
            </div>

            <!-- TENTUKAN DRIVER -->
            <div class="driver-assignment">
                <h3>Tentukan Driver</h3>
                <form action="proses-pesanan.php" method="POST">
                    <input type="hidden" name="id_pesanan" value="<?= $detailPesanan['id']; ?>">
                    <input type="hidden" name="aksi" value="tentukan_driver">

                    <div class="assignment-form">
                        <select name="id_driver" required>
                            <option value="">-- Pilih Driver --</option>
                            <?php while ($driver = mysqli_fetch_assoc($queryDriver)): ?>
                                <option value="<?= $driver['id_driver']; ?>" <?= (isset($detailPesanan['driver_id']) && $detailPesanan['driver_id'] == $driver['id_driver']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($driver['nama']); ?> - <?= htmlspecialchars($driver['no_plat']); ?> (<?= htmlspecialchars($driver['jenis_kendaraan']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn-primary">Simpan Driver</button>
                    </div>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <!-- TABEL PESANAN -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Daftar Pesanan</h2>
                    <p>Semua pesanan yang masuk dari pelanggan.</p>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Penerima</th>
                            <th>Total</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($queryPesanan) > 0):
                        while ($pesanan = mysqli_fetch_assoc($queryPesanan)):
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong>#<?= $pesanan['id_pesanan']; ?></strong></td>
                            <td><?= htmlspecialchars($pesanan['nama_pelanggan']); ?></td>
                            <td><?= htmlspecialchars($pesanan['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($pesanan['nama_penerima']); ?></td>
                            <td>Rp <?= number_format($pesanan['total_biaya'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($pesanan['nama_driver']): ?>
                                    <?= htmlspecialchars($pesanan['nama_driver']); ?><br>
                                    <small style="color: #94a3b8;"><?= htmlspecialchars($pesanan['no_plat']); ?></small>
                                <?php else: ?>
                                    <span style="color: #f59e0b;">Belum ditentukan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge"><?= htmlspecialchars($pesanan['status_pesanan']); ?></span>
                            </td>
                            <td>
                                <a href="pesanan.php?detail=<?= $pesanan['id_pesanan']; ?>" class="btn-small">Detail</a>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="9" class="empty-data">Belum ada data pesanan.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>