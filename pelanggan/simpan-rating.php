<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "gosendd");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$user_id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pesanan_id = $_POST['pesanan_id'] ?? null;
    $driver_id  = $_POST['driver_id'] ?? null;
    $rating     = $_POST['rating'] ?? null;
    $ulasan     = $_POST['ulasan'] ?? '';

    if ($pesanan_id && $rating) {
        $stmt = $conn->prepare("UPDATE pesanan SET rating = ?, ulasan = ? WHERE id = ?");
        $stmt->bind_param("isi", $rating, $ulasan, $pesanan_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Terima kasih! Rating berhasil dikirim.'); window.location.href='riwayat.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan rating.'); window.location.href='riwayat.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
    }
} else {
    header("Location: riwayat.php");
    exit();
}
?>