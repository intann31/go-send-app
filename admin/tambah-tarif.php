<?php
session_start();

// Koneksi database langsung (Host: localhost, User: root, Pass: kosong, DB: gosend)
$conn = mysqli_connect("localhost", "root", "", "gosendd");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Proses simpan data jika tombol submit ditekan
if (isset($_POST['submit'])) {
    $jenis_kendaraan = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $tarif_dasar     = mysqli_real_escape_string($conn, $_POST['tarif_dasar']);
    $tarif_km        = mysqli_real_escape_string($conn, $_POST['tarif_km']);

    // Query untuk memasukkan data ke database
    $query = "INSERT INTO tarif (jenis_kendaraan, tarif_dasar, tarif_km) VALUES ('$jenis_kendaraan', '$tarif_dasar', '$tarif_km')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: tarif.php");
        exit;
    } else {
        $error = "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tarif Ongkir - GoSend Admin</title>
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
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #cbd5e1;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #0b0f19;
            border: 1px solid #374151;
            border-radius: 8px;
            color: #ffffff;
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
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-submit:hover {
            background: #1d4ed8;
        }
        .btn-back {
            background: #374151;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            margin-left: 10px;
            display: inline-block;
        }
        .btn-back:hover {
            background: #4b5563;
        }
        .alert-error {
            background: #7f1d1d;
            border: 1px solid #991b1b;
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
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
        <!-- TOPBAR -->
        <header class="topbar">
            <div>
                <h1>Tambah Tarif Ongkir</h1>
                <p>Masukkan jenis kendaraan serta rincian tarif dasar dan kilometer.</p>
            </div>
        </header>

        <!-- FORM TAMBAH TARIF -->
        <section class="dashboard-section">
            <div class="form-card">
                <?php if (isset($error)) : ?>
                    <div class="alert-error"><?= $error; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <input type="text" name="jenis_kendaraan" placeholder="Contoh: Motor (Instant) atau Mobil" required>
                    </div>
                    <div class="form-group">
                        <label>Tarif Dasar (0-1 km)</label>
                        <input type="number" name="tarif_dasar" placeholder="Contoh: 10000" required>
                    </div>
                    <div class="form-group">
                        <label>Tarif Per Kilometer Berikutnya</label>
                        <input type="number" name="tarif_km" placeholder="Contoh: 3000" required>
                    </div>
                    <div style="margin-top: 25px;">
                        <button type="submit" name="submit" class="btn-submit">Simpan Tarif</button>
                        <a href="tarif.php" class="btn-back">Kembali</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

</body>
</html>