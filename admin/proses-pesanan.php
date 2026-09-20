<?php

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";


$aksi = $_POST['aksi'] ?? "";


/*
|--------------------------------------------------------------------------
| TENTUKAN DRIVER
|--------------------------------------------------------------------------
*/

if ($aksi === "tentukan_driver") {

    $id_pesanan = (int) ($_POST['id_pesanan'] ?? 0);
    $id_driver = (int) ($_POST['id_driver'] ?? 0);


    if ($id_pesanan <= 0 || $id_driver <= 0) {
        die("Data pesanan atau driver tidak valid.");
    }


    /*
    | Pastikan driver benar-benar ada
    */

   $cekDriver = mysqli_prepare(
    $conn,
    "
    SELECT id 
    FROM driver 
    WHERE id = ?
    "
);
    mysqli_stmt_bind_param(
        $cekDriver,
        "i",
        $id_driver
    );

    mysqli_stmt_execute($cekDriver);

    $resultDriver = mysqli_stmt_get_result(
        $cekDriver
    );


    if (mysqli_num_rows($resultDriver) === 0) {
        die("Driver tidak ditemukan.");
    }


    /*
    | Update driver pada pesanan
    */
$stmt = mysqli_prepare(
    $conn,
    "
    UPDATE pesanan 
    SET driver_id = ?, status_pesanan = 'dipproses' 
    WHERE id = ?
    "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $id_driver,
        $id_pesanan
    );


    mysqli_stmt_execute($stmt);


    /*
    | Buat data tracking awal
    */

   $driverInfo = mysqli_prepare(
    $conn,
    "
    SELECT no_plat, jenis_kendaraan 
    FROM driver 
    WHERE id = ?
    "
);


    mysqli_stmt_bind_param(
        $driverInfo,
        "i",
        $id_driver
    );


    mysqli_stmt_execute($driverInfo);

    $resultInfo = mysqli_stmt_get_result(
        $driverInfo
    );

    $driverData = mysqli_fetch_assoc(
        $resultInfo
    );


    $lokasi_driver =
        $driverData['lokasi_terkini'] ?? "";


    $status_pengiriman = "Driver Ditentukan";


    /*
    | Masukkan tracking
    */
$tracking = mysqli_prepare(
    $conn,
    "
    INSERT INTO tracking 
    (
        pesanan_id,
        status_pengiriman,
        lokasi_sekarang,
        keterangan
    )
    VALUES (?, ?, ?, ?)
    "
);
    mysqli_stmt_bind_param(
        $tracking,
        "iiss",
        $id_pesanan,
        $id_driver,
        $status_pengiriman,
        $lokasi_driver
    );


    mysqli_stmt_execute($tracking);


    header(
        "Location: pesanan.php?detail="
        . $id_pesanan
        . "&pesan=driver_berhasil"
    );

    exit;
}


die("Aksi tidak valid.");