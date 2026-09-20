<?php
include '../config/database.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Cek apakah driver masih digunakan di tabel pesanan (opsional, sesuaikan nama kolom foreign key-nya)
    $stmtPesanan = mysqli_prepare($conn, "SELECT COUNT(*) AS jumlah FROM pesanan WHERE driver_id = ?");
    if ($stmtPesanan) {
        mysqli_stmt_bind_param($stmtPesanan, "i", $id);
        mysqli_stmt_execute($stmtPesanan);
        $result = mysqli_stmt_get_result($stmtPesanan);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['jumlah'] > 0) {
            echo "<script>alert('Driver tidak dapat dihapus karena masih memiliki riwayat pesanan!'); window.location='driver.php';</script>";
            exit();
        }
    }

    // Eksekusi hapus data driver
    $query = mysqli_query($conn, "DELETE FROM driver WHERE id = $id");

    if ($query) {
        echo "<script>alert('Data driver berhasil dihapus!'); window.location='driver.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal menghapus data: " . mysqli_error($conn) . "'); window.location='driver.php';</script>";
        exit();
    }
} else {
    header("Location: driver.php");
    exit();
}
?>