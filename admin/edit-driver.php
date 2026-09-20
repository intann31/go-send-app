<?php
include '../config/database.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data driver berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM driver WHERE id = $id");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (! $data) {
    echo "<script>alert('Data driver tidak ditemukan!'); window.location='driver.php';</script>";
    exit();
}

// Jika tombol update diklik
if (isset($_POST['submit'])) {
    $user_id         = mysqli_real_escape_string($conn, $_POST['user_id']);
    $no_plat         = mysqli_real_escape_string($conn, $_POST['no_plat']);
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $status_aktif    = mysqli_real_escape_string($conn, $_POST['status_aktif']);

    $update = mysqli_query($conn, "UPDATE driver SET user_id='$user_id', no_plat='$no_plat', jenis_kendaraan='$jenis_kendaraan', status_aktif='$status_aktif' WHERE id=$id");

    if ($update) {
        header("Location: driver.php");
        exit();
    } else {
        echo "Gagal mengupdate data: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver - GoSend Admin</title>
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
        .dashboard-card { background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 10px; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .form-actions { display: flex; gap: 12px; margin-top: 24px; }
        .btn-submit { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; padding: 12px 20px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
        .btn-cancel { background: #e2e8f0; color: #475569; padding: 12px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; }
    </style>
</head>
<body>

    <div class="dashboard-container">
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

        <main class="dashboard-main">
            <header class="dashboard-header">
                <div>
                    <h1>Edit Driver</h1>
                    <p>Ubah informasi data driver sistem.</p>
                </div>
            </header>

            <section class="dashboard-card">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>ID User (Angka)</label>
                        <input type="number" name="user_id" class="form-control" value="<?php echo htmlspecialchars($data['user_id']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Plat Nomor Kendaraan</label>
                        <input type="text" name="no_plat" class="form-control" value="<?php echo htmlspecialchars($data['no_plat']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <input type="text" name="jenis_kendaraan" class="form-control" value="<?php echo htmlspecialchars($data['jenis_kendaraan']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Status Aktif</label>
                        <select name="status_aktif" class="form-control" style="background: white;">
                            <option value="aktif" <?php if ($data['status_aktif'] == 'aktif') echo 'selected'; ?>>Aktif</option>
                            <option value="offline" <?php if ($data['status_aktif'] == 'offline') echo 'selected'; ?>>Offline</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="submit" class="btn-submit">Simpan Perubahan</button>
                        <a href="driver.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </section>
        </main>
    </div>

</body>
</html>