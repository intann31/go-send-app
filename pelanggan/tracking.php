<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION["id_user"];

// Query mengambil data pesanan, driver, dan status tracking terbaru
$query = mysqli_prepare(
    $conn,
    "SELECT 
        p.*,
        u.nama AS nama_driver,
        d.no_plat,
        d.jenis_kendaraan,
        t.status_pengiriman,
        t.lokasi_sekarang AS lokasi_driver,
        t.updated_at AS waktu_update
    FROM pesanan p
    LEFT JOIN driver d ON p.driver_id = d.id
    LEFT JOIN user u ON d.user_id = u.id
    LEFT JOIN (
        SELECT t1.*
        FROM tracking t1
        INNER JOIN (
            SELECT pesanan_id, MAX(id) AS max_id
            FROM tracking
            GROUP BY pesanan_id
        ) t2 ON t1.id = t2.max_id
    ) t ON p.id = t.pesanan_id
    WHERE p.pelanggan_id = ?
    ORDER BY p.created_at DESC"
);
// Bind parameter id_user ke query prepared statement
mysqli_stmt_bind_param($query, "i", $id_user);

mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tracking - GoSend</title>

    <link
        rel="stylesheet"
        href="../css/tracking-p.css?v=6.0"
    >

</head>

<body>

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <div class="brand-icon">
                🚚
            </div>

            <div>
                <strong>GoSend</strong>

                <small>
                    Antar Jemput Barang
                </small>
            </div>

        </div>


        <nav class="sidebar-menu">

            <a href="dashboard.php">
                🏠 Dashboard
            </a>

            <a href="cek-ongkir.php">
                💰 Cek Ongkir
            </a>

            <a href="buat-pesanan.php">
                📦 Buat Pesanan
            </a>

            <a href="pembayaran.php">
                💳 Pembayaran
            </a>

            <a href="tracking.php" class="active">
                📍 Tracking
            </a>

            <a href="riwayat.php">
                🕘 Riwayat Pengiriman
            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../auth/logout.php">
                🚪 Logout
            </a>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="main-content">

        <header class="topbar">

            <div>

                <h1>
                    Tracking Pengiriman
                </h1>

                <p>
                    Pantau status perjalanan barang kamu.
                </p>

            </div>


            <div class="user-info">

                <div class="user-avatar">
                    <?= strtoupper(
                        substr($_SESSION["nama"] ?? "P", 0, 1)
                    ); ?>
                </div>

                <div>

                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION["nama"] ?? "Pelanggan"
                        ); ?>
                    </strong>

                    <small>
                        Pelanggan
                    </small>

                </div>

            </div>

        </header>


        <!-- TRACKING -->

        <section class="dashboard-section">

            <?php if (mysqli_num_rows($result) === 0): ?>

                <div class="glass-panel empty-state">

                    <div class="empty-icon">
                        📦
                    </div>

                    <h2>
                        Belum Ada Pesanan
                    </h2>

                    <p>
                        Kamu belum memiliki pesanan
                        yang dapat dilacak.
                    </p>

                    <a
                        href="buat-pesanan.php"
                        class="btn-primary"
                    >
                        Buat Pesanan
                    </a>

                </div>

            <?php endif; ?>


            <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>

                <div class="glass-panel tracking-card">

                    <!-- HEADER JUDUL -->
                    <div class="tracking-header-title">
                        <h2>Lacak Pengiriman (#<?= htmlspecialchars($pesanan["kode_pesanan"] ?? $pesanan["id"]); ?>)</h2>
                    </div>

                    <!-- PETA MAPS -->
                    <div class="map-container">
                        <iframe src="https://maps.google.com/maps?q=-6.200000,106.816666&hl=id&z=14&output=embed"></iframe>
                    </div>

                    <!-- INFO DRIVER -->
                    <div class="driver-card">
                        <div class="driver-profile">
                            <div class="driver-avatar">
                                <?= strtoupper(substr($pesanan["nama_driver"] ?? "D", 0, 1)); ?>
                            </div>
                            <div class="driver-details">
                                <h4><?= htmlspecialchars($pesanan["nama_driver"] ?? "Driver belum ditentukan"); ?></h4>
                                <p>
                                    GoSend Instant
                                    <?php if (!empty($pesanan["jenis_kendaraan"])): ?>
                                        • <?= htmlspecialchars($pesanan["jenis_kendaraan"]); ?> (<?= htmlspecialchars($pesanan["no_plat"]); ?>)
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <a href="tel:<?= htmlspecialchars($pesanan["no_hp_driver"] ?? "#"); ?>" class="btn-call">📞</a>
                    </div>

                    <!-- STATUS TIMELINE VERTIKAL -->
                    <div class="status-section">
                        <div class="status-title">Status Pengiriman</div>

                        <div class="timeline-vertical">

                            <!-- Step 1: Paket Dijemput -->
                            <div class="timeline-item-v active">
                                <div class="timeline-icon-v">✓</div>
                                <div class="timeline-content-v">
                                    <h5>Paket Dijemput</h5>
                                    <p>Kurir telah mengambil paket di lokasi asal (<?= htmlspecialchars($pesanan["alamat_jemput"]); ?>).</p>
                                </div>
                            </div>

                            <!-- Step 2: Dalam Perjalanan -->
                            <div class="timeline-item-v <?= in_array(strtolower($pesanan["status_pesanan"] ?? ""), ["proses", "dikirim", "selesai"]) ? "active" : ""; ?>">
                                <div class="timeline-icon-v">✓</div>
                                <div class="timeline-content-v">
                                    <h5>Dalam Perjalanan</h5>
                                    <p>Kurir sedang menuju alamat penerima (<?= htmlspecialchars($pesanan["nama_penerima"]); ?> - <?= htmlspecialchars($pesanan["alamat_tujuan"]); ?>).</p>
                                </div>
                            </div>

                            <!-- Step 3: Sampai Tujuan -->
                            <div class="timeline-item-v <?= strtolower($pesanan["status_pesanan"] ?? "") === "selesai" ? "active" : ""; ?>">
                                <div class="timeline-icon-v">✓</div>
                                <div class="timeline-content-v">
                                    <h5>Sampai Tujuan</h5>
                                    <p>Paket diserahkan kepada penerima.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            <?php endwhile; ?>

        </section>

    </main>

</body>

</html>