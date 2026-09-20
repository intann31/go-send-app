<?php
session_start();

// Panggil database.php dari folder config/
include '../config/database.php';

$pelanggan_id = $_SESSION['pelanggan_id'] ?? 1;
$pesanan_id = $_GET['pesanan_id'] ?? null;

if (!$pesanan_id) {
    header("Location: riwayat.php");
    exit();
}

$check_query = mysqli_prepare(
    $conn,
    "SELECT p.id, p.kode_pesanan, p.data_barang, r.id AS rating_id 
     FROM pesanan p 
     LEFT JOIN rating r ON p.id = r.pesanan_id 
     WHERE p.id = ? AND p.pelanggan_id = ?"
);
mysqli_stmt_bind_param($check_query, "ii", $pesanan_id, $pelanggan_id);
mysqli_stmt_execute($check_query);
$pesanan = mysqli_stmt_get_result($check_query)->fetch_assoc();

if (!$pesanan) {
    die("Pesanan tidak ditemukan.");
}

if (!empty($pesanan['rating_id'])) {
    echo "<script>alert('Anda sudah memberikan rating untuk pesanan ini!'); window.location='riwayat.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah_bintang = $_POST['jumlah_bintang'] ?? 5;
    $ulasan = trim($_POST['ulasan'] ?? '');

    $insert_query = mysqli_prepare(
        $conn,
        "INSERT INTO rating (pesanan_id, pelanggan_id, jumlah_bintang, ulasan) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($insert_query, "iiis", $pesanan_id, $pelanggan_id, $jumlah_bintang, $ulasan);

    if (mysqli_stmt_execute($insert_query)) {
        echo "<script>alert('Terima kasih atas penilaian Anda!'); window.location='riwayat.php';</script>";
        exit();
    } else {
        $error = "Gagal menyimpan rating. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Rating - GoSend</title>
    <!-- Panggil CSS dari folder css/ -->
    <link rel="stylesheet" href="../css/rating.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="title">Beri Penilaian</h2>
        <p class="subtitle">
            Pesanan #<?= htmlspecialchars($pesanan['kode_pesanan'] ?? $pesanan['id']) ?> (<?= htmlspecialchars($pesanan['data_barang']) ?>)
        </p>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="star-rating">
                <input type="radio" id="star5" name="jumlah_bintang" value="5" checked><label for="star5">★</label>
                <input type="radio" id="star4" name="jumlah_bintang" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="jumlah_bintang" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="jumlah_bintang" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="jumlah_bintang" value="1"><label for="star1">★</label>
            </div>

            <textarea name="ulasan" rows="4" placeholder="Tuliskan ulasan atau pengalaman pengiriman Anda..."></textarea>

            <button type="submit" class="btn-submit">Kirim Penilaian</button>
            <a href="riwayat.php" class="btn-back">Batal & Kembali</a>
        </form>
    </div>
</div>

</body>
</html>