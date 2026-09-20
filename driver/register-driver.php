<?php
session_start();

if (file_exists('../config/database.php')) {
    include '../config/database.php';
} else {
    include 'config/database.php';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap    = trim($_POST['nama_lengkap'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $no_telepon      = trim($_POST['no_telepon'] ?? '');
    $no_plat         = trim($_POST['no_plat'] ?? '');
    $jenis_kendaraan = trim($_POST['jenis_kendaraan'] ?? 'Motor');
    $password        = $_POST['password'] ?? '';
    $konfirmasi      = $_POST['konfirmasi_password'] ?? '';

    if (!empty($nama_lengkap) && !empty($username) && !empty($email) && !empty($no_telepon) && !empty($no_plat) && !empty($password)) {
        if ($password !== $konfirmasi) {
            $error = "Konfirmasi kata sandi tidak cocok!";
        } else {
            // Cek ketersediaan username / email
            $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ?");
            mysqli_stmt_bind_param($check, "ss", $username, $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error = "Username atau email sudah terdaftar!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = "driver";

                // 1. Simpan ke tabel user
                $query_user = mysqli_prepare($conn, "INSERT INTO user (username, password, nama_lengkap, no_telepon, email, role) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($query_user, "ssssss", $username, $hashed_password, $nama_lengkap, $no_telepon, $email, $role);

                if (mysqli_stmt_execute($query_user)) {
                    $user_id = mysqli_insert_id($conn);

                    // 2. Simpan ke tabel driver
                    $query_driver = mysqli_prepare($conn, "INSERT INTO driver (user_id, no_plat, jenis_kendaraan, status_aktif, email) VALUES (?, ?, ?, 'offline', ?)");
                    mysqli_stmt_bind_param($query_driver, "isss", $user_id, $no_plat, $jenis_kendaraan, $email);

                    if (mysqli_stmt_execute($query_driver)) {
                        // 3. Set Session & Langsung ke Dashboard Driver
                        $_SESSION['driver_id'] = $user_id;
                        $_SESSION['nama_driver'] = $nama_lengkap;
                        $_SESSION['role'] = 'driver';

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Gagal menyimpan data driver.";
                    }
                    mysqli_stmt_close($query_driver);
                } else {
                    $error = "Gagal mendaftar akun, coba lagi nanti.";
                }
                mysqli_stmt_close($query_user);
            }
            mysqli_stmt_close($check);
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
    <title>GoSend Driver - Pendaftaran Mitra</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px 28px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e5e7eb;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: #10b981;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }

        .logo-icon svg {
            width: 26px;
            height: 26px;
            stroke: #ffffff;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            text-align: center;
        }

        .badge-driver {
            background: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 14px;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-top: 4px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .input-box input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-box input:focus {
            border-color: #10b981;
        }

        .btn-submit {
            width: 100%;
            height: 44px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #059669;
        }

        .register-text {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-top: 16px;
        }

        .register-text a {
            color: #10b981;
            font-weight: 600;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a5.97 5.97 0 00-.942 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
        </svg>
    </div>

    <div class="brand-title">
        Daftar <span class="badge-driver">DRIVER</span>
    </div>
    <p class="subtitle">Bergabung menjadi Mitra Pengantar GoSend</p>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <div class="input-box">
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama sesuai KTP" required>
            </div>
        </div>

        <div class="form-group">
            <label>Username</label>
            <div class="input-box">
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>
        </div>

        <div class="form-group">
            <label>Email Driver</label>
            <div class="input-box">
                <input type="email" name="email" placeholder="mitra.driver@gosend.com" required>
            </div>
        </div>

        <div class="form-group">
            <label>Nomor WhatsApp / HP</label>
            <div class="input-box">
                <input type="text" name="no_telepon" placeholder="08xxxxxxxxxx" required>
            </div>
        </div>

        <div class="form-group">
            <label>Plat Nomor Kendaraan</label>
            <div class="input-box">
                <input type="text" name="no_plat" placeholder="B 1234 ABC" required>
            </div>
        </div>

        <div class="form-group">
            <label>Jenis Kendaraan</label>
            <div class="input-box">
                <input type="text" name="jenis_kendaraan" placeholder="Contoh: Motor / Mobil" required>
            </div>
        </div>

        <div class="form-group">
            <label>Kata Sandi</label>
            <div class="input-box">
                <input type="password" name="password" placeholder="••••••••••••" required>
            </div>
        </div>

        <div class="form-group">
            <label>Konfirmasi Kata Sandi</label>
            <div class="input-box">
                <input type="password" name="konfirmasi_password" placeholder="••••••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-submit">Daftar Sekarang</button>
    </form>

    <div class="register-text">
        Sudah punya akun driver? <a href="login-driver.php">Masuk di sini</a>
    </div>
</div>

</body>
</html>