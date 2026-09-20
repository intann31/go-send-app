<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION["id_user"];

// Ambil data dari form buat-pesanan.php
$jarak_km = $_POST["jarak_km"] ?? "";
$total_biaya = $_POST["total_tarif"] ?? "";

$alamat_jemput = $_POST["alamat_jemput"] ?? "";
$alamat_tujuan = $_POST["alamat_tujuan"] ?? "";

$nama_penerima = $_POST["nama_penerima"] ?? "";
$no_hp_penerima = $_POST["no_hp_penerima"] ?? "";

$nama_barang = $_POST["nama_barang"] ?? "";
$berat_barang = $_POST["berat_barang"] ?? "";

// Validasi kelengkapan data
if (
    empty($jarak_km) ||
    empty($total_biaya) ||
    empty($alamat_jemput) ||
    empty($alamat_tujuan) ||
    empty($nama_penerima) ||
    empty($no_hp_penerima) ||
    empty($nama_barang) ||
    empty($berat_barang)
) {
    die("Data pesanan belum lengkap.");
}

/* Status awal pesanan */
$status_pesanan = "menunggu_pembayaran";

/* Generate Kode Pesanan Unik & Format Data Barang */
$kode_pesanan = "ORD-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -4));
$data_barang = $nama_barang . " (" . $berat_barang . " kg)";

/* Simpan pesanan ke database */
$query = mysqli_prepare(
    $conn,
    "INSERT INTO pesanan
    (
        kode_pesanan,
        pelanggan_id,
        driver_id,
        alamat_jemput,
        alamat_tujuan,
        nama_penerima,
        no_telepon_penerima,
        data_barang,
        total_biaya,
        status_pesanan
    )
    VALUES
    (
        ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?
    )"
);

mysqli_stmt_bind_param(
    $query,
    "sisssssds",
    $kode_pesanan,
    $id_user,
    $alamat_jemput,
    $alamat_tujuan,
    $nama_penerima,
    $no_hp_penerima,
    $data_barang,
    $total_biaya,
    $status_pesanan
);

if (mysqli_stmt_execute($query)) {
    $id_pesanan = mysqli_insert_id($conn);
    header("Location: pembayaran.php?id_pesanan=" . $id_pesanan);
    exit;
} else {
    die("Pesanan gagal dibuat: " . mysqli_error($conn));
}