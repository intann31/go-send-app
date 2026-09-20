<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Cek Session User
if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

$id_user           = $_SESSION["id_user"];
$id_pesanan        = (int) ($_POST["id_pesanan"] ?? 0);
$metode_pembayaran = $_POST["metode_pembayaran"] ?? "";
$jumlah_bayar      = (float) ($_POST["jumlah_bayar"] ?? 0);

// Helper function untuk redirect error kembali ke form pembayaran
function redirectError($id_pesanan, $msg) {
    header("Location: pembayaran.php?id_pesanan=" . $id_pesanan . "&error=" . urlencode($msg));
    exit;
}

// 1. Validasi Kelengkapan Data Input
if ($id_pesanan <= 0 || empty($metode_pembayaran) || $jumlah_bayar <= 0) {
    redirectError($id_pesanan, "Data pembayaran tidak lengkap.");
}

// 2. Pastikan Pesanan Memang Milik Pelanggan Ini
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, total_biaya 
     FROM pesanan 
     WHERE id = ? 
     AND pelanggan_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result);

if (!$pesanan) {
    redirectError($id_pesanan, "Pesanan tidak ditemukan.");
}

// 3. Validasi Nominal Pembayaran
if ($jumlah_bayar < $pesanan["total_biaya"]) {
    redirectError($id_pesanan, "Jumlah pembayaran kurang dari total biaya.");
}

// 4. Pengecekan Logika File Bukti Transfer
$nama_baru = NULL;
$is_cod    = (strtoupper($metode_pembayaran) === 'COD' || stripos($metode_pembayaran, 'cash') !== false);

// Ambil file yang ter-upload (mengatasi masalah penimpaan input file)
$file_uploaded = isset($_FILES["bukti_transfer"]) && $_FILES["bukti_transfer"]["error"] === UPLOAD_ERR_OK;

if (!$is_cod) {
    if (!$file_uploaded) {
        redirectError($id_pesanan, "Bukti pembayaran wajib diupload untuk metode ini.");
    }

    $file        = $_FILES["bukti_transfer"];
    $nama_file   = $file["name"];
    $tmp_file    = $file["tmp_name"];
    $ukuran_file = $file["size"];

    // Cek Ekstensi File
    $extension         = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $allowed_extension = ["jpg", "jpeg", "png"];

    if (!in_array($extension, $allowed_extension, true)) {
        redirectError($id_pesanan, "Format file harus JPG, JPEG, atau PNG.");
    }

    // Batas Ukuran File 2 MB
    if ($ukuran_file > 2 * 1024 * 1024) {
        redirectError($id_pesanan, "Ukuran file maksimal 2 MB.");
    }

    // Folder Upload
    $upload_dir = __DIR__ . "/../assets/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Penamaan File Baru
    $nama_baru = "bukti_" . $id_pesanan . "_" . time() . "." . $extension;
    $path_file = $upload_dir . $nama_baru;

    if (!move_uploaded_file($tmp_file, $path_file)) {
        redirectError($id_pesanan, "Gagal mengunggah bukti pembayaran ke server.");
    }
}

// 5. Simpan / Update Data ke Tabel Pembayaran
$status_pembayaran = $is_cod ? "Pending COD" : "Menunggu Verifikasi";

// Cek apakah sudah ada record pembayaran untuk pesanan ini
$stmt_cek = mysqli_prepare($conn, "SELECT id FROM pembayaran WHERE pesanan_id = ?");
mysqli_stmt_bind_param($stmt_cek, "i", $id_pesanan);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($res_cek) > 0) {
    // Jika sudah ada, lakukan UPDATE
    $stmt_bayar = mysqli_prepare(
        $conn,
        "UPDATE pembayaran 
         SET tgl_pembayaran = NOW(), 
             metode_pembayaran = ?, 
             bukti_pembayaran = ?, 
             status_pembayaran = ? 
         WHERE pesanan_id = ?"
    );
    mysqli_stmt_bind_param(
        $stmt_bayar,
        "sssi",
        $metode_pembayaran,
        $nama_baru,
        $status_pembayaran,
        $id_pesanan
    );
} else {
    // Jika belum ada, lakukan INSERT
    $stmt_bayar = mysqli_prepare(
        $conn,
        "INSERT INTO pembayaran (pesanan_id, tgl_pembayaran, metode_pembayaran, bukti_pembayaran, status_pembayaran)
         VALUES (?, NOW(), ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt_bayar,
        "isss",
        $id_pesanan,
        $metode_pembayaran,
        $nama_baru,
        $status_pembayaran
    );
}

if (!mysqli_stmt_execute($stmt_bayar)) {
    redirectError($id_pesanan, "Gagal menyimpan pembayaran ke database.");
}

// 6. Update Status di Tabel Pesanan
$status_pesanan = $is_cod ? "Menunggu Penjemputan" : "Menunggu Verifikasi Pembayaran";

$stmt_update = mysqli_prepare(
    $conn,
    "UPDATE pesanan 
     SET status_pesanan = ? 
     WHERE id = ? 
     AND pelanggan_id = ?"
);
mysqli_stmt_bind_param($stmt_update, "sii", $status_pesanan, $id_pesanan, $id_user);
mysqli_stmt_execute($stmt_update);

// Direct Ke Detail Pembayaran
header("Location: detail-pembayaran.php?id=" . $id_pesanan . "&pesan=pembayaran_berhasil");
exit;
?>