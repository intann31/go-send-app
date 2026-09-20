<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Cek autentikasi admin
if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "admin") {
    header("Location: auth/login.php");
    exit;
}

$nama_user = $_SESSION['nama'] ?? 'Admin';
$inisial   = strtoupper(substr($nama_user, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GoSend Admin</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0b0f19; color: #ffffff; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; background: #0f172a; border-right: 1px solid #1e293b; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar-header { padding: 24px; border-bottom: 1px solid #1e293b; }
        .sidebar-header h2 { margin: 0; color: white; font-size: 18px; }
        .sidebar-header span { margin: 2px 0 0 0; color: #38bdf8; font-size: 11px; font-weight: bold; display: block; }
        
        .sidebar-menu { padding: 20px; display: flex; flex-direction: column; gap: 8px; overflow-y: auto; flex: 1; }
        .sidebar-menu .nav-item { padding: 12px 16px; color: #94a3b8; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu .nav-item:hover { background: #1e293b; color: #ffffff; }
        .sidebar-menu .nav-item.active { background: #2563eb; color: #ffffff; }
        
        .sidebar-bottom { padding: 20px; border-top: 1px solid #1e293b; }
        .sidebar-bottom .nav-item { color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 500; display: block; }

        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; padding: 30px; box-sizing: border-box; }
        
        .topbar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; background: #111827; border: 1px solid #1f2937; padding: 24px; border-radius: 12px; }
        .topbar h1 { margin: 0; font-size: 24px; color: #ffffff; }
        .topbar p { margin: 5px 0 0 0; color: #94a3b8; font-size: 13px; }
        
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; background-color: #2563eb; color: #ffffff; font-weight: bold; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .user-info strong { display: block; font-size: 14px; color: #ffffff; }
        .user-info small { font-size: 12px; color: #94a3b8; }

        /* Cards Grid */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .card { background: #111827; border: 1px solid #1f2937; padding: 20px; border-radius: 12px; }
        .card h3 { margin: 0 0 10px 0; color: #94a3b8; font-size: 14px; }
        .card .value { margin: 0; color: #ffffff; font-size: 24px; font-weight: bold; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div>
        <div class="sidebar-header">
            <h2>📦 GoSend</h2>
            <span>ADMIN PANEL</span>
        </div>

        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
            <a href="driver.php" class="nav-item">🛵 Kelola Driver</a>
            <a href="pesanan.php" class="nav-item">📦 Pesanan</a>
            <a href="pembayaran.php" class="nav-item">💳 Verifikasi Pembayaran</a>
            <a href="tracking.php" class="nav-item">📍 Tracking</a>
            <a href="tarif.php" class="nav-item">💰 Tarif Ongkir</a>
            <a href="area.php" class="nav-item">🗺️ Area Layanan</a>
            <a href="laporan.php" class="nav-item">📈 Laporan</a>
        </nav>
    </div>

    <div class="sidebar-bottom">
        <a href="auth/logout.php" class="nav-item">🚪 Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">

    <header class="topbar">
        <div>
            <h1>Dashboard Overview</h1>
            <p>Selamat datang kembali di panel administrasi GoSend.</p>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                <?= $inisial; ?>
            </div>
            <div>
                <strong><?= htmlspecialchars($nama_user); ?></strong>
                <small>Administrator</small>
            </div>
        </div>
    </header>

    <div class="cards-grid">
        <div class="card">
            <h3>Total Pesanan</h3>
            <p class="value">0</p>
        </div>
        <div class="card">
            <h3>Driver Aktif</h3>
            <p class="value">0</p>
        </div>
        <div class="card">
            <h3>Pendapatan</h3>
            <p class="value">Rp 0</p>
        </div>
    </div>

</main>

</body>
</html>