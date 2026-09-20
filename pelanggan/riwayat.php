<?php
session_start();

// Panggil database.php dari folder config/
include '../config/database.php';

// PERBAIKAN 1: Gunakan $_SESSION['id_user'] agar sesuai dengan session login
if (!isset($_SESSION["id_user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pelanggan_id = $_SESSION['id_user'];

$query = mysqli_prepare(
    $conn,
    "SELECT 
        p.id AS id_pesanan,
        p.kode_pesanan,
        p.created_at AS tgl_pesanan,
        p.data_barang AS nama_barang,
        p.alamat_jemput,
        p.alamat_tujuan,
        p.nama_penerima,
        p.total_biaya,
        p.status_pesanan,
        r.jumlah_bintang,
        r.ulasan
    FROM pesanan p
    LEFT JOIN rating r ON p.id = r.pesanan_id
    WHERE p.pelanggan_id = ?
    ORDER BY p.id DESC"
);

mysqli_stmt_bind_param($query, "i", $pelanggan_id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - GoSend</title>
    <!-- Panggil CSS dari folder css/ -->
    <link rel="stylesheet" href="../css/riwayat.css">
</head>
<body>

<div class="container">
    <h1 class="page-title">Riwayat Pesanan</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="kode-pesanan">#<?= htmlspecialchars($row['kode_pesanan'] ?? $row['id_pesanan']) ?></div>
                        <div class="tgl-pesanan">
                            <?= !empty($row['tgl_pesanan']) ? date('d M Y, H:i', strtotime($row['tgl_pesanan'])) : date('d M Y, H:i') ?>
                        </div>
                    </div>
                    <?php 
                        $status = strtolower($row['status_pesanan'] ?? '');
                        $badgeClass = 'badge-proses';
                        if ($status == 'selesai') $badgeClass = 'badge-selesai';
                        if ($status == 'dibatalkan' || $status == 'batal') $badgeClass = 'badge-batal';
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status_pesanan'] ?? 'Diproses') ?></span>
                </div>

                <div class="nama-barang"><?= htmlspecialchars($row['nama_barang'] ?? '-') ?></div>
                
                <div class="detail-rute">
                    <div>📍 <b>Jemput:</b> <?= htmlspecialchars($row['alamat_jemput'] ?? '-') ?></div>
                    <div>🎯 <b>Tujuan:</b> <?= htmlspecialchars($row['alamat_tujuan'] ?? '-') ?> (Penerima: <?= htmlspecialchars($row['nama_penerima'] ?? '-') ?>)</div>
                </div>

                <div class="card-footer">
                    <div>
                        <div style="font-size: 11px; color: #9ca3af;">Total Biaya</div>
                        <div class="total-biaya">Rp <?= number_format($row['total_biaya'] ?? 0, 0, ',', '.') ?></div>
                    </div>
                    
                    <div class="btn-group">
                        <a href="tracking.php?id=<?= $row['id_pesanan'] ?>" class="btn btn-tracking">Tracking</a>
                        
                        <?php if (strtolower($row['status_pesanan'] ?? '') == 'selesai'): ?>
                            <?php if (empty($row['jumlah_bintang'])): ?>
                                <a href="rating.php?pesanan_id=<?= $row['id_pesanan'] ?>" class="btn btn-rating">Beri Rating</a>
                            <?php else: ?>
                                <div class="rated-box">★ <?= $row['jumlah_bintang'] ?>/5</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>Belum ada riwayat pesanan.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>