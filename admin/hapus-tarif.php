<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "gosendd");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM tarif WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Tarif berhasil dihapus!'); window.location='tarif.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus tarif: " . mysqli_error($conn) . "'); window.location='tarif.php';</script>";
    }
} else {
    header("Location: tarif.php");
}
?>