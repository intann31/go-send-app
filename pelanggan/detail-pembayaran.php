<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Cek Session User
if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION["id_user"];
$id_pesanan = (int) ($_GET["id"] ?? 0);

if ($id_pesanan <= 0) {
    header("Location: riwayat.php");
    exit;
}

// Query data pesanan dan detail pembayaran
$stmt = mysqli_prepare(
    $conn,
    "SELECT p.*, bayar.metode_pembayaran, bayar.bukti_pembayaran, bayar.status_pembayaran, bayar.tgl_pembayaran 
     FROM pesanan p
     LEFT JOIN pembayaran bayar ON p.id = bayar.pesanan_id
     WHERE p.id = ? AND p.pelanggan_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Data pembayaran tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail & Bukti Pembayaran</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Sesuaikan dengan CSS kamu -->
    <style>
        .container { max-width: 600px; margin: 30px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #fff; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .bukti-img { max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ccc; margin-top: 10px; }
        .btn-riwayat { display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <!-- Notifikasi Pembayaran Berhasil -->
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'pembayaran_berhasil'): ?>
            <div class="alert-success">
                <strong>Pembayaran Berhasil Dikirim!</strong><br>
                Bukti pembayaran Anda telah kami terima dan sedang dalam proses verifikasi.
            </div>
        <?php endif; ?>

        <h2>Detail Pembayaran #<?= htmlspecialchars($data['id']) ?></h2>
        <hr>

        <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars(strtoupper($data['metode_pembayaran'] ?? '-')) ?></p>
        <p><strong>Total Biaya:</strong> Rp <?= number_format($data['total_biaya'] ?? 0, 0, ',', '.') ?></p>
        <p><strong>Tanggal Bayar:</strong> <?= htmlspecialchars($data['tgl_pembayaran'] ?? '-') ?></p>
        <p><strong>Status Pembayaran:</strong> 
            <span style="color: orange; font-weight: bold;"><?= htmlspecialchars($data['status_pembayaran'] ?? 'Pending') ?></span>
        </p>

        <!-- Bukti Pembayaran -->
        <p><strong>Bukti Transfer / Pembayaran:</strong></p>
        <?php if (!empty($data['bukti_pembayaran'])): ?>
            <img src="../assets/uploads/<?= htmlspecialchars($data['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" class="bukti-img">
        <?php else: ?>
            <p><em>Metode Cash/COD (Tidak ada bukti upload file).</em></p>
        <?php endif; ?>

        <br>
        <!-- Tombol Lanjut ke Riwayat -->
        <a href="riwayat.php" class="btn-riwayat">Lihat Riwayat Pesanan &rarr;</a>
    </div>

</body>
</html>