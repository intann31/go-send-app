<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['id_user'];
$id_pesanan = $_GET['id'] ?? null;

if ($id_pesanan) {
    // 1. Cari id dari tabel driver berdasarkan user_id yang sedang login
    $q_driver = mysqli_query($conn, "SELECT id FROM driver WHERE user_id = '$user_id'");
    $data_driver = mysqli_fetch_assoc($q_driver);

    if ($data_driver) {
        $driver_id = $data_driver['id'];

        // 2. Update pesanan: isi driver_id dan ubah status_pesanan jadi 'dijemput'
        $update = mysqli_query($conn, "UPDATE pesanan 
                                       SET driver_id = '$driver_id', 
                                           status_pesanan = 'dijemput' 
                                       WHERE id = '$id_pesanan' AND driver_id IS NULL");

        if ($update) {
            echo "<script>alert('Orderan berhasil diterima!'); window.location.href='dashboard.php';</script>";
            exit;
        }
    }
}

echo "<script>alert('Gagal mengambil orderan!'); window.location.href='dashboard.php';</script>";