<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama     = trim($_POST["nama"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $email    = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    if (empty($nama) || empty($username) || empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        // Cek apakah username sudah dipakai
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "admin";

            // Simpan ke database
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO user (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sssss", $nama, $username, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Akun Admin berhasil dibuat! Silakan login.";
            } else {
                $error = "Gagal mendaftarkan admin: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - GoSend</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card-register {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            color: #047857;
            margin-bottom: 6px;
            text-align: center;
        }

        .subtitle {
            font-size: 13px;
            color: #64748b;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #10b981;
        }

        .btn-submit {
            width: 100%;
            background-color: #047857;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background-color: #065f46;
        }

        .alert {
            padding: 10px;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 16px;
            text-align: center;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #047857;
        }

        .link-login {
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #64748b;
        }

        .link-login a {
            color: #047857;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="card-register">
        <div class="title">GoSend Admin</div>
        <div class="subtitle">Registrasi Akun Administrator</div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama admin" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email admin" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-submit">Daftar Admin</button>
        </form>

        <div class="link-login">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>

</body>
</html>