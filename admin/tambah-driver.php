<?php
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Driver - GoSend Admin</title>
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
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .user-profile .avatar { width: 40px; height: 40px; background-color: #2563eb; color: #ffffff; font-weight: bold; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .user-info strong { display: block; font-size: 14px; color: #0f172a; }
        .user-info span { font-size: 12px; color: #64748b; }
        .dashboard-card { background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { font-size: 18px; color: #0f172a; }
        .btn-add { background: #22c55e; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        th { color: #64748b; font-weight: 600; background-color: #f8fafc; }
        td { color: #334155; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-aktif { background-color: #dcfce7; color: #15803d; }
        .status-offline { background-color: #fee2e2; color: #b91c1c; }
        .action-edit { color: #2563eb; text-decoration: none; font-weight: 600; margin-right: 12px; }
        .action-delete { color: #ef4444; text-decoration: none; font-weight: 600; }
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
                <a href="driver.php" class="active">Kelola Driver</a>
                <a href="pesanan.php">Pesanan</a>
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
                    <h1>Kelola Driver</h1>
                    <p>Daftar seluruh driver aktif yang terdaftar di sistem.</p>
                </div>
                <div class="user-profile">
                    <div class="avatar">A</div>
                    <div class="user-info">
                        <strong>Administrator</strong>
                        <span>Admin</span>
                    </div>
                </div>
            </header>

            <section class="dashboard-card">
                <div class="card-header">
                    <h2>Data Driver GoSend</h2>
                    <a href="tambah-driver.php" class="btn-add">+ Tambah Driver</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID User</th>
                            <th>Email</th>
                            <th>Plat Nomor</th>
                            <th>Jenis Kendaraan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM driver");
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['no_plat']; ?></td>
                                <td><?php echo $row['jenis_kendaraan']; ?></td>
                                <td>
                                    <span class="status-badge status-aktif">
                                        <?php echo ucfirst($row['status_aktif']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit-driver.php?id=<?php echo $row['id']; ?>" class="action-edit">Edit</a>
                                    <a href="hapus-driver.php?id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Yakin ingin memadamkan data ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="7" style="text-align: center; color: #64748b;">Belum ada data driver yang dimasukkan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

</body>
</html>