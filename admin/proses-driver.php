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
| TAMBAH DRIVER
|--------------------------------------------------------------------------
*/

if ($aksi === "tambah") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama = trim($_POST['nama']);
    $no_hp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $no_plat = trim($_POST['no_plat']);
    $jenis_kendaraan = trim($_POST['jenis_kendaraan']);
    $status_driver = trim($_POST['status_driver']);
    $lokasi_terkini = trim($_POST['lokasi_terkini']);


    if (
        empty($username) ||
        empty($password) ||
        empty($nama) ||
        empty($no_hp) ||
        empty($email) ||
        empty($no_plat) ||
        empty($jenis_kendaraan)
    ) {
        die("Data driver belum lengkap.");
    }


    /* Cek username */
    $cek = mysqli_prepare(
        $conn,
        "SELECT id_user FROM user WHERE username = ?"
    );

    mysqli_stmt_bind_param(
        $cek,
        "s",
        $username
    );

    mysqli_stmt_execute($cek);

    $hasilCek = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasilCek) > 0) {
        die("Username sudah digunakan.");
    }


    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $role = "driver";


    /*
    | Gunakan transaction karena kita membuat
    | data di tabel user dan driver sekaligus.
    */

    mysqli_begin_transaction($conn);

    try {

        /* Insert user */

        $stmtUser = mysqli_prepare(
            $conn,
            "
            INSERT INTO user
            (
                username,
                password,
                nama,
                no_hp,
                email,
                role
            )
            VALUES (?, ?, ?, ?, ?, ?)
            "
        );

        mysqli_stmt_bind_param(
            $stmtUser,
            "ssssss",
            $username,
            $passwordHash,
            $nama,
            $no_hp,
            $email,
            $role
        );

        mysqli_stmt_execute($stmtUser);


        $id_user = mysqli_insert_id($conn);


        /* Insert driver */

        $stmtDriver = mysqli_prepare(
            $conn,
            "
            INSERT INTO driver
            (
                id_user,
                no_plat,
                jenis_kendaraan,
                status_driver,
                lokasi_terkini
            )
            VALUES (?, ?, ?, ?, ?)
            "
        );

        mysqli_stmt_bind_param(
            $stmtDriver,
            "issss",
            $id_user,
            $no_plat,
            $jenis_kendaraan,
            $status_driver,
            $lokasi_terkini
        );

        mysqli_stmt_execute($stmtDriver);


        mysqli_commit($conn);

        header("Location: driver.php?pesan=berhasil_tambah");
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        die("Gagal menambahkan driver: " . $e->getMessage());
    }
}


/*
|--------------------------------------------------------------------------
| EDIT DRIVER
|--------------------------------------------------------------------------
*/

if ($aksi === "edit") {

    $id_driver = (int) $_POST['id_driver'];
    $id_user = (int) $_POST['id_user'];

    $nama = trim($_POST['nama']);
    $no_hp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $no_plat = trim($_POST['no_plat']);
    $jenis_kendaraan = trim($_POST['jenis_kendaraan']);
    $status_driver = trim($_POST['status_driver']);
    $lokasi_terkini = trim($_POST['lokasi_terkini']);


    if (
        $id_driver <= 0 ||
        $id_user <= 0 ||
        empty($nama) ||
        empty($no_hp) ||
        empty($email) ||
        empty($no_plat) ||
        empty($jenis_kendaraan)
    ) {
        die("Data driver belum lengkap.");
    }


    mysqli_begin_transaction($conn);

    try {

        /* Update user */

        $stmtUser = mysqli_prepare(
            $conn,
            "
            UPDATE user
            SET
                nama = ?,
                no_hp = ?,
                email = ?
            WHERE id_user = ?
            "
        );

        mysqli_stmt_bind_param(
            $stmtUser,
            "sssi",
            $nama,
            $no_hp,
            $email,
            $id_user
        );

        mysqli_stmt_execute($stmtUser);


        /* Update driver */

        $stmtDriver = mysqli_prepare(
            $conn,
            "
            UPDATE driver
            SET
                no_plat = ?,
                jenis_kendaraan = ?,
                status_driver = ?,
                lokasi_terkini = ?
            WHERE id_driver = ?
            "
        );

        mysqli_stmt_bind_param(
            $stmtDriver,
            "ssssi",
            $no_plat,
            $jenis_kendaraan,
            $status_driver,
            $lokasi_terkini,
            $id_driver
        );

        mysqli_stmt_execute($stmtDriver);


        mysqli_commit($conn);

        header("Location: driver.php?pesan=berhasil_edit");
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        die("Gagal mengubah driver: " . $e->getMessage());
    }
}


die("Aksi tidak valid.");