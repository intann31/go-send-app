<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
    $username     = trim($_POST["username"] ?? "");
    $email        = trim($_POST["email"] ?? "");
    $no_telepon   = trim($_POST["no_telepon"] ?? "");
    $password     = $_POST["password"] ?? "";

    if (!empty($nama_lengkap) && !empty($username) && !empty($email) && !empty($no_telepon) && !empty($password)) {
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "admin";

            $query = mysqli_prepare($conn, "INSERT INTO user (username, password, nama_lengkap, no_telepon, email, role) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($query, "ssssss", $username, $hashed_password, $nama_lengkap, $no_telepon, $email, $role);

            if (mysqli_stmt_execute($query)) {
                $_SESSION["login"]   = true;
                $_SESSION["id_user"] = mysqli_insert_id($conn);
                $_SESSION["nama"]    = $nama_lengkap;
                $_SESSION["role"]    = "admin";

                header("Location: ../admin/dashboard.php");
                exit;
            } else {
                $error = "Gagal mendaftar akun admin.";
            }
            mysqli_stmt_close($query);
        }
        mysqli_stmt_close($check);
    } else {
        $error = "Semua bidang wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - GoSend</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h1>Daftar Akun Admin</h1>
        <p class="subtitle">Buat akun pengelola sistem GoSend</p>

        <?php if ($error !== ""): ?>
            <div class="alert error" style="background: #fef2f2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 13px;">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="no_telepon" placeholder="Masukkan nomor telepon" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-auth">Daftar Admin</button>
        </form>

        <p class="auth-footer" style="margin-top: 15px;">
            Sudah punya akun? <a href="login.php" style="color: #047857; font-weight: bold;">Login</a>
        </p>
    </div>
</div>
</body>
</html>