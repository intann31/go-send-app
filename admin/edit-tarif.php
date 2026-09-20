<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "gosendd");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek apakah ada parameter ID di URL
if (!isset($_GET['id'])) {
    header("Location: tarif.php");
    exit;
}

$id = $_GET['id'];

// Ambil data tarif berdasarkan ID
$query = "SELECT * FROM tarif WHERE id = $id";
$result = mysqli_query($conn, $query);
$tarif = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$tarif) {
    echo "<script>alert('Data tarif tidak ditemukan!'); window.location='tarif.php';</script>";
    exit;
}

// Proses update data jika tombol submit ditekan
if (isset($_POST['update'])) {
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $tarif_dasar     = mysqli_real_escape_string($conn, $_POST['tarif_dasar']);
    $tarif_km        = mysqli_real_escape_string($conn, $_POST['tarif_km']);

    $update_query = "UPDATE tarif SET jenis_kendaraan = '$jenis_kendaraan', tarif_dasar = '$tarif_dasar', tarif_km = '$tarif_km' WHERE id = $id";
    
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Tarif berhasil diperbarui!'); window.location='tarif.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui tarif: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tarif Ongkir - GoSend Admin</title>
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
        .form-card {
            background: #111827;
            border: 1px solid #1f2937;
            padding: 24px;
            border-radius: 12px;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-size: 14px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        .btn-submit {
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background: #1d4ed8;
        }
        .btn-back {
            background: #374151;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }
        .btn-back:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
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

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1>Edit Tarif Ongkir</h1>
                <p>Ubah informasi harga dasar dan tarif per kilometer.</p>
            </div>
        </header>

        <section>
            <div class="form-card">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <input type="text" name="jenis_kendaraan" value="<?= htmlspecialchars($tarif['jenis_kendaraan']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tarif Dasar (0-1 km)</label>
                        <input type="number" name="tarif_dasar" value="<?= htmlspecialchars($tarif['tarif_dasar']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tarif Per Kilometer Berikutnya</label>
                        <input type="number" name="tarif_km" value="<?= htmlspecialchars($tarif['tarif_km']); ?>" required>
                    </div>
                    <button type="submit" name="update" class="btn-submit">Simpan Perubahan</button>
                    <a href="tarif.php" class="btn-back">Batal</a>
                </form>
            </div>
        </section>
    </main>

</body>
</html>