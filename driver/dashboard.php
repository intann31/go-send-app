<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['id_user'];

// 1. Ambil Data Driver & User
$q_driver = mysqli_query($conn, "SELECT d.*, u.nama, u.no_telepon, u.email 
                                  FROM driver d 
                                  JOIN user u ON d.user_id = u.id 
                                  WHERE d.user_id = '$user_id'");
$driver = mysqli_fetch_assoc($q_driver);
$driver_id = $driver['id'] ?? 0;

// 2. Orderan Aktif yang Sedang Dikerjakan Driver Ini
$q_aktif = mysqli_query($conn, "SELECT p.*, u.nama AS nama_pelanggan 
                                FROM pesanan p 
                                LEFT JOIN user u ON p.pelanggan_id = u.id 
                                WHERE p.driver_id = '$driver_id' AND p.status_pesanan != 'selesai' 
                                ORDER BY p.id DESC LIMIT 1");
$order_aktif = mysqli_fetch_assoc($q_aktif);

// 3. Tawaran Order Baru (Yang Belum Diambil Driver manapun)
$q_tawaran = mysqli_query($conn, "SELECT p.*, u.nama AS nama_pelanggan 
                                  FROM pesanan p 
                                  LEFT JOIN user u ON p.pelanggan_id = u.id 
                                  WHERE p.driver_id IS NULL 
                                  ORDER BY p.id DESC");

// 4. Hitung Statistik Riwayat Tugas Selesai
$q_selesai = mysqli_query($conn, "SELECT COUNT(*) as total_selesai, SUM(total_biaya) as total_pendapatan 
                                  FROM pesanan 
                                  WHERE driver_id = '$driver_id' AND status_pesanan = 'selesai'");
$stat = mysqli_fetch_assoc($q_selesai);

// 5. Data Riwayat Pengiriman Driver Ini
$q_riwayat = mysqli_query($conn, "SELECT p.*, u.nama AS nama_pelanggan 
                                  FROM pesanan p 
                                  LEFT JOIN user u ON p.pelanggan_id = u.id 
                                  WHERE p.driver_id = '$driver_id' AND p.status_pesanan = 'selesai' 
                                  ORDER BY p.id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Driver - GoSend</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .container { max-width: 600px; margin: 0 auto; }

        /* Header Driver */
        .profile-card {
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 16px;
        }
        .driver-info { display: flex; align-items: center; gap: 14px; }
        .avatar {
            width: 50px;
            height: 50px;
            background: #047857;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
        }
        .badge-aktif {
            background: #d1fae5;
            color: #047857;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-logout {
            background: #fee2e2;
            color: #dc2626;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Performa / Ringkasan */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #ffffff;
            padding: 14px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .stat-card small { color: #6b7280; font-size: 12px; }
        .stat-card h3 { margin: 4px 0 0 0; color: #047857; font-size: 18px; }

        /* General Card Style */
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .card-title {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 16px;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Card Orderan Aktif */
        .card-aktif {
            background: linear-gradient(135deg, #047857, #065f46);
            color: white;
        }
        .card-aktif h3 { color: #ffffff; }
        .badge-status {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* List Order Baru */
        .order-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-item:last-child { margin-bottom: 0; }
        .btn-terima {
            background: #047857;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
        }
        .btn-terima:hover { background: #065f46; }

        /* Tag Label Riwayat */
        .status-selesai {
            color: #047857;
            background: #d1fae5;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- HEADER PROFILE DRIVER -->
    <div class="profile-card">
        <div class="driver-info">
            <div class="avatar">
                <?= strtoupper(substr($_SESSION["nama"] ?? 'D', 0, 1)); ?>
            </div>
            <div>
                <small style="color: #6b7280;">Halo, Mitra Driver!</small>
                <strong style="display: block; font-size: 16px; color: #111827;">
                    <?= htmlspecialchars($_SESSION["nama"] ?? 'Driver'); ?>
                </strong>
                <small style="color: #047857; font-weight: 600;">
                    <?= htmlspecialchars($driver['jenis_kendaraan'] ?? 'Motor'); ?> • <?= htmlspecialchars($driver['no_plat'] ?? '-'); ?>
                </small>
            </div>
        </div>
        <div style="text-align: right;">
            <span class="badge-aktif">● Aktif</span>
            <div style="margin-top: 8px;">
                <a href="../auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>

    <!-- STATISTIK DRIVER -->
    <div class="stats-grid">
        <div class="stat-card">
            <small>Tugas Selesai</small>
            <h3><?= number_format($stat['total_selesai'] ?? 0); ?></h3>
        </div>
        <div class="stat-card">
            <small>Total Pendapatan</small>
            <h3>Rp <?= number_format($stat['total_pendapatan'] ?? 0, 0, ',', '.'); ?></h3>
        </div>
    </div>

    <!-- PENGIRIMAN SEDANG BERLANGSUNG -->
    <div class="card card-aktif">
        <div class="card-title">
            <h3 style="margin:0;">Pengiriman Berlangsung</h3>
            <?php if ($order_aktif): ?>
                <span class="badge-status"><?= htmlspecialchars($order_aktif['status_pesanan']); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($order_aktif): ?>
            <p style="margin: 4px 0;"><strong>Pelanggan:</strong> <?= htmlspecialchars($order_aktif['nama_pelanggan'] ?? 'Pelanggan'); ?></p>
            <p style="margin: 4px 0;"><strong>Jemput:</strong> <?= htmlspecialchars($order_aktif['alamat_jemput']); ?></p>
            <p style="margin: 4px 0;"><strong>Tujuan:</strong> <?= htmlspecialchars($order_aktif['alamat_tujuan']); ?></p>
            <p style="margin: 4px 0;"><strong>Penerima:</strong> <?= htmlspecialchars($order_aktif['nama_penerima']); ?> (<?= htmlspecialchars($order_aktif['no_telepon_penerima']); ?>)</p>
            
            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.2);">
                <a href="update-status.php?id=<?= $order_aktif['id']; ?>" style="display: inline-block; background: #ffffff; color: #047857; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 13px;">
                    Update Status / Selesaikan ➔
                </a>
            </div>
        <?php else: ?>
            <p style="margin:0; opacity: 0.9; font-size: 14px;">Tidak ada pengiriman yang sedang berlangsung.</p>
        <?php endif; ?>
    </div>

    <!-- TAWARAN ORDER BARU -->
    <div class="card">
        <div class="card-title">
            <h3>Tawaran Order Baru</h3>
        </div>

        <?php if (mysqli_num_rows($q_tawaran) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($q_tawaran)): ?>
                <div class="order-item">
                    <div style="padding-right: 10px;">
                        <strong style="font-size: 15px;">Pesanan #<?= $row['id']; ?> - <?= htmlspecialchars($row['nama_pelanggan'] ?? 'Pelanggan'); ?></strong>
                        <p style="margin: 4px 0; font-size: 13px; color: #4b5563;">
                            <strong>Jemput:</strong> <?= htmlspecialchars($row['alamat_jemput']); ?><br>
                            <strong>Tujuan:</strong> <?= htmlspecialchars($row['alamat_tujuan']); ?>
                        </p>
                        <span style="color: #047857; font-weight: bold; font-size: 14px;">
                            Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?>
                        </span>
                    </div>
                    <div>
                        <a href="terima-order.php?id=<?= $row['id']; ?>" class="btn-terima" onclick="return confirm('Yakin ingin mengambil orderan ini?')">Terima Order</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #6b7280; font-size: 14px; text-align: center; margin: 20px 0;">Belum ada orderan baru masuk saat ini.</p>
        <?php endif; ?>
    </div>

    <!-- RIWAYAT PENGIRIMAN -->
    <div class="card">
        <div class="card-title">
            <h3>Riwayat Pengiriman</h3>
        </div>

        <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
            <?php while ($r = mysqli_fetch_assoc($q_riwayat)): ?>
                <div style="border-bottom: 1px solid #f3f4f6; padding: 10px 0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 14px;">Pesanan #<?= $r['id']; ?> - <?= htmlspecialchars($r['nama_pelanggan'] ?? 'Pelanggan'); ?></strong>
                        <small style="display: block; color: #6b7280;"><?= htmlspecialchars($r['alamat_tujuan']); ?></small>
                    </div>
                    <div style="text-align: right;">
                        <span class="status-selesai">SELESAI</span>
                        <small style="display: block; font-weight: bold; margin-top: 2px;">Rp <?= number_format($r['total_biaya'], 0, ',', '.'); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #6b7280; font-size: 14px; text-align: center; margin: 10px 0;">Belum ada riwayat pesanan selesai.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>