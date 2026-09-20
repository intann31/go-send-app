<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// Cek autentikasi driver
if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "driver") {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION["driver_id"] ?? $_SESSION["id_user"];

// Contoh penarikan data dari database jika ada tabel pesanan
// Kamu bisa sesuaikan kueri SQL ini dengan nama tabel di database-mu
$riwayat_list = [];
$query = "SELECT * FROM pesanan WHERE driver_id = ? AND status = 'Selesai' ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $driver_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $riwayat_list[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Data dummy untuk tampilan jika database belum terisi
if (empty($riwayat_list)) {
    $riwayat_list = [
        [
            "layanan" => "Antar Pakaian Instan",
            "tujuan" => "Kebayoran Baru, Jaksel",
            "tanggal" => "12 Jan 2026",
            "pelanggan" => "Henny L.",
            "harga" => "22.000",
            "status" => "Selesai"
        ],
        [
            "layanan" => "Kirim Dokumen Pajak",
            "tujuan" => "Sudirman Plaza, Jakpus",
            "tanggal" => "12 Jan 2026",
            "pelanggan" => "Budi A.",
            "harga" => "18.000",
            "status" => "Selesai"
        ],
        [
            "layanan" => "Antar Paket Kue Lebaran",
            "tujuan" => "Cilandak Residence",
            "tanggal" => "11 Jan 2026",
            "pelanggan" => "Farhan M.",
            "harga" => "32.000",
            "status" => "Selesai"
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Delivery - GoSend</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            padding-bottom: 30px;
        }

        /* Top Header Navigation */
        .top-header {
            background-color: #ffffff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .btn-back {
            text-decoration: none;
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background-color: #f1f5f9;
        }

        .header-title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Content Container */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 16px;
        }

        /* Card Riwayat Item */
        .card-riwayat {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .riwayat-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .icon-check {
            width: 36px;
            height: 36px;
            background-color: #d1fae5;
            color: #059669;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .riwayat-info .title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .riwayat-info .sub-text {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        .riwayat-right {
            text-align: right;
            flex-shrink: 0;
        }

        .price {
            font-size: 14px;
            font-weight: 700;
            color: #059669;
            margin-bottom: 2px;
        }

        .status {
            font-size: 11px;
            font-weight: 600;
            color: #059669;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- Header Atas dengan Tombol Kembali -->
    <div class="top-header">
        <a href="dashboard.php" class="btn-back">❮</a>
        <h1 class="header-title">Riwayat Delivery</h1>
    </div>

    <!-- Container List Riwayat -->
    <div class="container">

        <?php if (!empty($riwayat_list)): ?>
            <?php foreach ($riwayat_list as $item): ?>
                <div class="card-riwayat">
                    <div class="riwayat-left">
                        <div class="icon-check">✓</div>
                        <div class="riwayat-info">
                            <div class="title"><?= htmlspecialchars($item["layanan"] ?? $item["nama_barang"] ?? "Antar Barang"); ?></div>
                            <div class="sub-text">
                                Tujuan: <?= htmlspecialchars($item["tujuan"] ?? $item["alamat_tujuan"] ?? "-"); ?>
                            </div>
                            <div class="sub-text">
                                <?= htmlspecialchars($item["tanggal"] ?? $item["created_at"] ?? ""); ?> • Pelanggan: <?= htmlspecialchars($item["pelanggan"] ?? $item["nama_pelanggan"] ?? "-"); ?>
                            </div>
                        </div>
                    </div>
                    <div class="riwayat-right">
                        <div class="price">+Rp <?= htmlspecialchars($item["harga"] ?? number_format($item["total_harga"] ?? 0, 0, ',', '.')); ?></div>
                        <div class="status"><?= htmlspecialchars($item["status"] ?? "Selesai"); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                Belum ada riwayat pengiriman yang selesai.
            </div>
        <?php endif; ?>

    </div>

</body>
</html>