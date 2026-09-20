<?php
session_start();

if (file_exists('../config/database.php')) {
    include '../config/database.php';
} else {
    include 'config/database.php';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $query = mysqli_prepare($conn, "SELECT id, nama_driver, password FROM driver WHERE email = ?");
        mysqli_stmt_bind_param($query, "s", $email);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);

        if ($driver = mysqli_fetch_assoc($result)) {
            // Jika password di-hash gunakan password_verify($password, $driver['password'])
            if ($password === $driver['password'] || password_verify($password, $driver['password'])) {
                $_SESSION['driver_id'] = $driver['id'];
                $_SESSION['nama_driver'] = $driver['nama_driver'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Kata sandi salah!";
            }
        } else {
            $error = "Email driver tidak terdaftar!";
        }
    } else {
        $error = "Silakan isi semua bidang!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoSend Driver - Login</title>
    <link rel="stylesheet" href="../css/login-driver.css">
</head>
<body>

<div class="login-card">
    <div class="logo-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25v11.25m0-11.25H8.25m8.25 0l1.875 3.75M8.25 7.5H4.875c-.621 0-1.125.504-1.125 1.125v5.625c0 .621.504 1.125 1.125 1.125h3.375" />
        </svg>
    </div>

    <div class="brand-title">
        GoSend <span class="badge-driver">DRIVER</span>
    </div>
    <p class="subtitle">Aplikasi Mitra Pengantar Barang Instan</p>

    <?php if (!empty($error)): ?>
        <p style="color: #ef4444; font-size: 13px; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Driver</label>
            <div class="input-box">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                <input type="email" name="email" placeholder="mitra.driver@gosend.com" required>
            </div>
        </div>

        <div class="form-group">
            <label>Kata Sandi</label>
            <div class="input-box">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                <input type="password" name="password" placeholder="••••••••••••" required>
            </div>
        </div>

        <a href="#" class="forgot-link">Lupa Kata Sandi?</a>

        <button type="submit" class="btn-submit">Masuk ke Akun Driver</button>
    </form>

    <div class="register-text">
        Ingin menjadi mitra? <a href="#">Daftar Sekarang</a>
    </div>
</div>

</body>
</html>