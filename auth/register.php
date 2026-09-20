<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$error = $_SESSION['error'] ?? "";
$success = $_SESSION['success'] ?? "";

// Hapus pesan sesudah dibaca agar tidak terus muncul saat di-refresh
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
    $username     = trim($_POST["username"] ?? "");
    $email        = trim($_POST["email"] ?? "");
    $password     = $_POST["password"] ?? "";
    $no_telepon   = trim($_POST["no_telepon"] ?? "");

    // Simpan data ke session sementara agar jika error, inputan tidak hilang
    $_SESSION['old'] = [
        'nama_lengkap' => $nama_lengkap,
        'username'     => $username,
        'email'        => $email,
        'no_telepon'   => $no_telepon
    ];

    if (
        $nama_lengkap === "" ||
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $no_telepon === ""
    ) {
        $_SESSION['error'] = "Semua data wajib diisi.";
        header("Location: register.php");
        exit;
    }

    // Cek ketersediaan username / email
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM user WHERE username = ? OR email = ?"
    );
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Username atau email sudah digunakan.";
        mysqli_stmt_close($stmt);
        header("Location: register.php");
        exit;
    }
    mysqli_stmt_close($stmt);

    // Hash password & Insert user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role          = "pelanggan";

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO user (nama_lengkap, username, email, password, no_telepon, role) VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $nama_lengkap,
        $username,
        $email,
        $password_hash,
        $no_telepon,
        $role
    );

    if (mysqli_stmt_execute($stmt)) {
        unset($_SESSION['old']); // Hapus inputan lama jika berhasil
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
    } else {
        $_SESSION['error'] = "Registrasi gagal: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    
    // Redirect ke register.php untuk menghindari ERR_CACHE_MISS
    header("Location: register.php");
    exit;
}

// Ambil inputan lama jika ada error
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pelanggan - GoSend</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>

<body>

<div class="auth-container">
    <div class="auth-card">

        <h1>Daftar Akun</h1>
        <p class="auth-subtitle">Buat akun pelanggan GoSend</p>

        <?php if ($error !== ""): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert success">
                <?= htmlspecialchars($success) ?>
            </div>
            <p class="auth-footer">
                <a href="login.php">Login Sekarang</a>
            </p>
        <?php else: ?>

            <form method="POST" action="register.php">

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input
                        type="text"
                        name="nama_lengkap"
                        placeholder="Masukkan nama lengkap"
                        value="<?= htmlspecialchars($old['nama_lengkap'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input
                        type="text"
                        name="username"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="Masukkan email"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>No. Telepon</label>
                    <input
                        type="text"
                        name="no_telepon"
                        placeholder="Masukkan nomor telepon"
                        value="<?= htmlspecialchars($old['no_telepon'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <button type="submit" class="btn">Daftar</button>

            </form>

            <p class="auth-footer">
                Sudah punya akun? <a href="login.php">Login</a>
            </p>

        <?php endif; ?>

    </div>
</div>

</body>
</html>