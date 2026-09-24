<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_pesanan = $_GET['id'] ?? null;
$user_id = $_SESSION['id_user'];

// Ambil ID Driver
$q_driver = mysqli_query($conn, "SELECT id FROM driver WHERE user_id = '$user_id'");
$driver = mysqli_fetch_assoc($q_driver);
$driver_id = $driver['id'] ?? 0;

// Ambil data pesanan
$query = mysqli_query($conn, "SELECT p.*, u.nama AS nama_pelanggan 
                              FROM pesanan p 
                              LEFT JOIN user u ON p.pelanggan_id = u.id 
                              WHERE p.id = '$id_pesanan' AND p.driver_id = '$driver_id'");
$pesanan = mysqli_fetch_assoc($query);

if (!$pesanan) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Update status pesanan saat tombol ditekan
if (isset($_POST['update_status'])) {
    $status_baru = $_POST['status_pesanan'];
    
    $update = mysqli_query($conn, "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id = '$id_pesanan'");
    
    if ($update) {
        echo "<script>alert('Status berhasil diperbarui!'); window.location.href='dashboard.php';</script>";
        exit;
    } else {
        $error = "Gagal memperbarui status!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Pesanan - GoSend</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .container { max-width: 500px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .btn-submit { background: #047857; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; }
        .btn-submit:hover { background: #065f46; }
        .btn-back { display: block; text-align: center; margin-top: 12px; color: #6b7280; text-decoration: none; font-size: 14px; }
        select { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="margin-top: 0; color: #111827;">Update Status Pengiriman</h2>
    
    <div class="info-box">
        <p style="margin: 4px 0;"><strong>ID Pesanan:</strong> #<?= $pesanan['id']; ?></p>
        <p style="margin: 4px 0;"><strong>Pelanggan:</strong> <?= htmlspecialchars($pesanan['nama_pelanggan'] ?? 'Pelanggan'); ?></p>
        <p style="margin: 4px 0;"><strong>Alamat Tujuan:</strong> <?= htmlspecialchars($pesanan['alamat_tujuan']); ?></p>
        <p style="margin: 4px 0;"><strong>Penerima:</strong> <?= htmlspecialchars($pesanan['nama_penerima']); ?> (<?= htmlspecialchars($pesanan['no_telepon_penerima']); ?>)</p>
    </div>

    <form method="POST">
        <label for="status_pesanan"><strong>Pilih Status Terkini:</strong></label>
        <select name="status_pesanan" id="status_pesanan">
            <option value="dijemput" <?= $pesanan['status_pesanan'] == 'dijemput' ? 'selected' : ''; ?>>Paket Dijemput</option>
            <option value="dalam_perjalanan" <?= $pesanan['status_pesanan'] == 'dalam_perjalanan' ? 'selected' : ''; ?>>Dalam Perjalanan</option>
            <option value="selesai" <?= $pesanan['status_pesanan'] == 'selesai' ? 'selected' : ''; ?>>Selesai / Sampai Tujuan</option>
        </select>

        <button type="submit" name="update_status" class="btn-submit">Simpan Perubahan Status</button>
    </form>

    <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
</div>

</body>
</html>