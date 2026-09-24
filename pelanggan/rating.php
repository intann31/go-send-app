<?php
session_start();

// Koneksi database langsung
$conn = mysqli_connect("localhost", "root", "", "gosendd");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek sesi login pelanggan
$user_id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit();
}

$pesanan_id = $_GET['pesanan_id'] ?? $_POST['pesanan_id'] ?? null;
if (!$pesanan_id) {
    echo "<script>alert('ID Pesanan tidak valid!'); window.location.href='riwayat.php';</script>";
    exit();
}

// Proses jika tombol kirim rating ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_rating'])) {
    $jumlah_bintang = $_POST['jumlah_bintang'] ?? null;
    $ulasan         = $_POST['ulasan'] ?? '';

    if ($jumlah_bintang) {
        // Cek apakah pesanan ini sudah pernah diberi rating sebelumnya
        $cek_rating = $conn->prepare("SELECT id FROM rating WHERE pesanan_id = ?");
        $cek_rating->bind_param("i", $pesanan_id);
        $cek_rating->execute();
        $cek_rating->store_result();

        if ($cek_rating->num_rows > 0) {
            // Jika sudah ada, update datanya
            $stmt = $conn->prepare("UPDATE rating SET jumlah_bintang = ?, ulasan = ? WHERE pesanan_id = ?");
            $stmt->bind_param("isi", $jumlah_bintang, $ulasan, $pesanan_id);
        } else {
            // Jika belum ada, insert data baru ke tabel rating
            $stmt = $conn->prepare("INSERT INTO rating (pesanan_id, pelanggan_id, jumlah_bintang, ulasan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $pesanan_id, $user_id, $jumlah_bintang, $ulasan);
        }
        
        if ($stmt->execute()) {
            echo "<script>alert('Terima kasih! Rating dan ulasan berhasil dikirim.'); window.location.href='riwayat.php';</script>";
            exit();
        } else {
            $error = "Gagal menyimpan rating ke database.";
        }
        $stmt->close();
    } else {
        $error = "Silakan pilih rating bintang terlebih dahulu!";
    }
}

// Ambil data pesanan dan driver
$stmt = $conn->prepare("
    SELECT p.*, d.nama_driver 
    FROM pesanan p 
    LEFT JOIN driver d ON p.driver_id = d.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $pesanan_id);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();

if (!$pesanan) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location.href='riwayat.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Rating - GoSend</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f0fdf4; 
            margin: 0; 
            padding: 20px; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card { 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 16px; 
            width: 100%;
            max-width: 450px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); 
            box-sizing: border-box;
        }
        h2 { 
            margin-top: 0; 
            color: #065f46; 
            font-size: 22px; 
            text-align: center;
            margin-bottom: 8px;
        }
        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #374151;
        }
        .info-box strong {
            color: #111827;
        }
        label { 
            font-weight: 600; 
            color: #374151; 
            font-size: 14px; 
            display: block; 
            margin-bottom: 6px; 
        }
        select, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #d1d5db; 
            border-radius: 10px; 
            font-size: 14px; 
            margin-bottom: 16px; 
            box-sizing: border-box; 
            background: #fff;
        }
        select:focus, textarea:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .btn { 
            background: #10b981; 
            color: white; 
            border: none; 
            padding: 12px; 
            width: 100%; 
            border-radius: 10px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 15px; 
            transition: background 0.2s;
        }
        .btn:hover { 
            background: #059669; 
        }
        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            color: #6b7280; 
            text-decoration: none; 
            font-size: 14px; 
        }
        .back-link:hover {
            color: #374151;
            text-decoration: underline;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Beri Rating Driver</h2>
    <div class="subtitle">Bagikan pengalaman pengiriman Anda bersama GoSend</div>

    <div class="info-box">
        Pesanan: <strong>#<?= htmlspecialchars($pesanan['id']); ?></strong><br>
        Driver: <strong><?= htmlspecialchars($pesanan['nama_driver'] ?? 'Driver GoSend'); ?></strong>
    </div>

    <?php if (!empty($error)) : ?>
        <div class="alert-error"><?= $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="pesanan_id" value="<?= htmlspecialchars($pesanan['id']); ?>">

        <label for="jumlah_bintang">Pilih Rating:</label>
        <select name="jumlah_bintang" id="jumlah_bintang" required>
            <option value="" disabled selected>-- Pilih Bintang --</option>
            <option value="5">⭐⭐⭐⭐⭐ (5/5 - Sangat Puas)</option>
            <option value="4">⭐⭐⭐⭐ (4/5 - Puas)</option>
            <option value="3">⭐⭐⭐ (3/5 - Cukup)</option>
            <option value="2">⭐⭐ (2/5 - Kurang)</option>
            <option value="1">⭐ (1/5 - Sangat Kurang)</option>
        </select>

        <label for="ulasan">Ulasan / Komentar (Opsional):</label>
        <textarea name="ulasan" id="ulasan" rows="4" placeholder="Tuliskan ulasan untuk pelayanan driver..."></textarea>

        <button type="submit" name="kirim_rating" class="btn">Kirim Rating</button>
    </form>

    <a href="riwayat.php" class="back-link">← Kembali ke Riwayat Pesanan</a>
</div>

</body>
</html>