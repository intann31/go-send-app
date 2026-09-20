<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

/* =========================
   DATA DRIVER & USER
========================= */
$queryDriver = mysqli_query(
    $conn,
    "
    SELECT 
        d.id AS id_driver,
        d.no_plat,
        d.jenis_kendaraan,
        d.status_aktif,
        u.nama AS nama_driver,
        u.email AS email_driver
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
    <title>Kelola Driver - GoSend Admin</title>
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
        .btn-small-edit { background: #2563eb; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; display: inline-block; margin-right: 6px; }
        .btn-small-edit:hover { background: #1d4ed8; }
        .btn-small-delete { background: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; display: inline-block; }
        .btn-small-delete:hover { background: #b91c1c; }

        .status-badge { background: #1e3a8a; color: #93c5fd; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
        .status-offline { background: #374151; color: #9ca3af; }
        .status-sibuk { background: #78350f; color: #fcd34d; }
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
            <a href="driver.php" class="active">🛵 Kelola Driver</a>
            <a href="pesanan.php">📦 Pesanan</a>
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
                <h1>Kelola Driver</h1>
                <p>Daftar seluruh driver aktif yang terdaftar di sistem.</p>
            </div>
        </header>

        <!-- TABEL DRIVER -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Data Driver GoSend</h2>
                    <p>Informasi akun dan kendaraan driver.</p>
                </div>
                <a href="tambah-driver.php" class="btn-primary">+ Tambah Driver</a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Driver</th>
                            <th>Plat Nomor</th>
                            <th>Jenis Kendaraan</th>
                            <th>Status Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    if ($queryDriver && mysqli_num_rows($queryDriver) > 0):
                        while ($row = mysqli_fetch_assoc($queryDriver)):
                            // Tentukan warna badge status
                            $badgeClass = "status-badge";
                            if (strtolower($row['status_aktif']) == 'offline') {
                                $badgeClass .= " status-offline";
                            } elseif (strtolower($row['status_aktif']) == 'sibuk') {
                                $badgeClass .= " status-sibuk";
                            }
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_driver']); ?></strong><br>
                                <small style="color: #94a3b8;"><?= htmlspecialchars($row['email_driver']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['no_plat']); ?></td>
                            <td><?= htmlspecialchars($row['jenis_kendaraan']); ?></td>
                            <td>
                                <span class="<?= $badgeClass; ?>"><?= htmlspecialchars($row['status_aktif']); ?></span>
                            </td>
                            <td>
                                <a href="edit-driver.php?id=<?= $row['id_driver']; ?>" class="btn-small-edit">Edit</a>
                                <a href="hapus-driver.php?id=<?= $row['id_driver']; ?>" class="btn-small-delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="empty-data">Belum ada data driver yang tersimpan.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>