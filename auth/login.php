<?php
session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";
$username_input = "";
$selected_role  = $_POST["role"] ?? "pelanggan";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username     = trim($_POST["username"] ?? "");
    $password     = $_POST["password"] ?? "";
    $role_pilihan = strtolower(trim($_POST["role"] ?? "pelanggan"));
    
    $username_input = $username;
    $selected_role  = $role_pilihan;

    if ($username === "" || $password === "") {
        $error = "Username/Email dan password wajib diisi.";
    } else {

        // Cari user di tabel user berdasarkan username/email dan role
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password, nama_lengkap, no_telepon, email, role 
             FROM user 
             WHERE (username = ? OR email = ?) AND LOWER(role) = ?"
        );

        mysqli_stmt_bind_param($stmt, "sss", $username, $username, $role_pilihan);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {

            // Verifikasi Password
            if (password_verify($password, $user["password"]) || $password === $user["password"]) {

                session_regenerate_id(true);

                // Set Session
                $_SESSION["login"]      = true;
                $_SESSION["id_user"]    = $user["id"];
                $_SESSION["username"]   = $user["username"];
                $_SESSION["nama"]       = $user["nama_lengkap"];
                $_SESSION["no_telepon"] = $user["no_telepon"];
                $_SESSION["email"]      = $user["email"];
                $_SESSION["role"]       = strtolower($user["role"]);

                // Direct Halaman Berdasarkan Role
                if ($_SESSION["role"] === "driver") {
                    
                    $stmt_driver = mysqli_prepare($conn, "SELECT id FROM driver WHERE user_id = ?");
                    if ($stmt_driver) {
                        mysqli_stmt_bind_param($stmt_driver, "i", $user["id"]);
                        mysqli_stmt_execute($stmt_driver);
                        $res_driver = mysqli_stmt_get_result($stmt_driver);

                        if ($driver_data = mysqli_fetch_assoc($res_driver)) {
                            $_SESSION["driver_id"]   = $driver_data["id"];
                            $_SESSION["nama_driver"] = $user["nama_lengkap"];
                        } else {
                            $_SESSION["driver_id"]   = $user["id"];
                            $_SESSION["nama_driver"] = $user["nama_lengkap"];
                        }
                        mysqli_stmt_close($stmt_driver);
                    } else {
                        $_SESSION["driver_id"]   = $user["id"];
                        $_SESSION["nama_driver"] = $user["nama_lengkap"];
                    }

                    header("Location: ../driver/dashboard.php");
                    exit();

                } elseif ($_SESSION["role"] === "admin") {
                    
                    header("Location: ../admin/dashboard.php");
                    exit();

                } else {
                    
                    header("Location: ../pelanggan/dashboard.php");
                    exit();

                }

            } else {
                $error = "Password yang Anda masukkan salah!";
            }

        } else {
            $error = "Akun tidak ditemukan atau role yang dipilih tidak sesuai!";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GoSend</title>
    <link rel="stylesheet" href="../css/auth.css">
    <style>
        .role-selector {
            display: flex;
            background: #e5e7eb;
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .role-btn {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s ease;
            user-select: none;
        }

        .role-btn.active {
            background: #047857;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="logo">🚚</div>

        <h1>GoSend</h1>
        <p class="subtitle">Silakan pilih jenis akun Anda</p>

        <?php if ($error !== ""): ?>
            <div class="alert error" style="background: #fef2f2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 13px;">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="role" id="selectedRoleInput" value="<?= htmlspecialchars($selected_role); ?>">

            <div class="role-selector">
                <div class="role-btn <?= ($selected_role === 'pelanggan') ? 'active' : ''; ?>" onclick="selectTab('pelanggan', this)">Pelanggan</div>
                <div class="role-btn <?= ($selected_role === 'driver') ? 'active' : ''; ?>" onclick="selectTab('driver', this)">Driver</div>
                <div class="role-btn <?= ($selected_role === 'admin') ? 'active' : ''; ?>" onclick="selectTab('admin', this)">Admin</div>
            </div>

            <div class="form-group">
                <label for="username">Username / Email</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username atau email"
                    value="<?= htmlspecialchars($username_input); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn-auth" id="submitBtn">
                Masuk sebagai <?= ucfirst($selected_role); ?>
            </button>
        </form>

        <p class="auth-footer" style="margin-top: 15px;">
            Belum punya akun?
            <a href="register-pelanggan.php" id="registerLink" style="color: #047857; font-weight: bold;">Daftar Sekarang</a>
        </p>

        <p class="back-home" style="margin-top: 10px; text-align: center;">
            <a href="../index.php">← Kembali ke Beranda</a>
        </p>
    </div>
</div>

<script>
    function selectTab(role, element) {
        const buttons = document.querySelectorAll('.role-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');

        document.getElementById('selectedRoleInput').value = role;

        const roleCapitalized = role.charAt(0).toUpperCase() + role.slice(1);
        document.getElementById('submitBtn').innerText = 'Masuk sebagai ' + roleCapitalized;

        const registerLink = document.getElementById('registerLink');
        if (role === 'pelanggan') {
            registerLink.href = 'register-pelanggan.php';
        } else if (role === 'driver') {
            registerLink.href = 'register-driver.php';
        } else if (role === 'admin') {
            registerLink.href = 'register-admin.php';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const currentRole = document.getElementById('selectedRoleInput').value;
        const registerLink = document.getElementById('registerLink');
        if (currentRole === 'driver') {
            registerLink.href = 'register-driver.php';
        } else if (currentRole === 'admin') {
            registerLink.href = 'register-admin.php';
        }
    });
</script>

</body>
</html>