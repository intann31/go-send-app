<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}


$id_pembayaran = (int) ($_GET["id"] ?? 0);

$status = $_GET["status"] ?? "";


if (
    $id_pembayaran <= 0 ||
    !in_array(
        $status,
        ["Disetujui", "Ditolak"],
        true
    )
) {

    die("Data verifikasi tidak valid.");

}


/* Ambil pembayaran */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id_pesanan
     FROM pembayaran
     WHERE id_pembayaran = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_pembayaran
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$pembayaran = mysqli_fetch_assoc($result);


if (!$pembayaran) {
    die("Data pembayaran tidak ditemukan.");
}


$id_pesanan = $pembayaran["id_pesanan"];


/* Update pembayaran */

$stmt_update = mysqli_prepare(
    $conn,
    "UPDATE pembayaran
     SET status_pembayaran = ?
     WHERE id_pembayaran = ?"
);

mysqli_stmt_bind_param(
    $stmt_update,
    "si",
    $status,
    $id_pembayaran
);

mysqli_stmt_execute($stmt_update);


/* Update pesanan */

if ($status === "Disetujui") {

    $status_pesanan =
        "Pembayaran Disetujui";

} else {

    $status_pesanan =
        "Pembayaran Ditolak";

}


$stmt_pesanan = mysqli_prepare(
    $conn,
    "UPDATE pesanan
     SET status_pesanan = ?
     WHERE id_pesanan = ?"
);

mysqli_stmt_bind_param(
    $stmt_pesanan,
    "si",
    $status_pesanan,
    $id_pesanan
);

mysqli_stmt_execute($stmt_pesanan);


/* Kembali */

header(
    "Location: pembayaran.php"
);

exit;