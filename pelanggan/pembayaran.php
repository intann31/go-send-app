<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["id_user"]) || $_SESSION["role"] !== "pelanggan") {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION["id_user"];
$id_pesanan = isset($_GET["id_pesanan"]) ? (int) $_GET["id_pesanan"] : 0;

if ($id_pesanan <= 0) {
    header("Location: riwayat.php");
    exit;
}

/* Ambil data pesanan */
$stmt = mysqli_prepare($conn, "SELECT * FROM pesanan WHERE id = ? AND pelanggan_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$pesanan) {
    die("Pesanan tidak ditemukan.");
}

/* Cek pembayaran */
$stmt_bayar = mysqli_prepare($conn, "SELECT * FROM pembayaran WHERE pesanan_id = ? ORDER BY id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt_bayar, "i", $id_pesanan);
mysqli_stmt_execute($stmt_bayar);
$pembayaran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_bayar));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran - GoSend</title>
    <link rel="stylesheet" href="../css/pembayaran.css">
    <style>
        /* CSS TAMBAHAN UNTUK BOX DETAIL DI BAWAH OPSI */
        .payment-details-box {
            display: none;
            margin-top: -10px;
            margin-bottom: 12px;
            padding: 12px 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .info-account-card {
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 13px;
            color: #334155;
            margin-bottom: 10px;
        }

        .info-account-card strong {
            color: #0f172a;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="checkout-card">
        <header class="checkout-header">
            <a href="javascript:history.back()" class="back-btn">‹</a>
            <h2>Detail Pembayaran</h2>
        </header>

        <form action="proses-pembayaran.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_pesanan" value="<?= $id_pesanan; ?>">
            <input type="hidden" name="jumlah_bayar" value="<?= $pesanan["total_biaya"]; ?>">

            <section class="order-summary-box">
                <h3 class="section-title">Ringkasan Pesanan</h3>
                
                <div class="route-timeline">
                    <div class="route-item green-dot">
                        <small>Alamat Penjemputan</small>
                        <p><?= htmlspecialchars($pesanan["alamat_jemput"]); ?></p>
                    </div>
                    <div class="route-item red-dot">
                        <small>Alamat Tujuan</small>
                        <p><?= htmlspecialchars($pesanan["alamat_tujuan"]); ?></p>
                    </div>
                </div>

                <div class="item-info-row">
                    <div>
                        <span class="info-label">Jenis Barang</span>
                        <strong class="info-val"><?= htmlspecialchars($pesanan["data_barang"]); ?></strong>
                    </div>
                    <div class="text-right">
                        <span class="info-label">Penerima</span>
                        <strong class="info-val"><?= htmlspecialchars($pesanan["nama_penerima"]); ?> (<?= htmlspecialchars($pesanan["no_telepon_penerima"]); ?>)</strong>
                    </div>
                </div>
            </section>

            <section class="payment-method-section">
                <h3 class="section-title">Pilih Metode Pembayaran</h3>

                <label class="payment-option">
                    <input type="radio" name="metode_pembayaran" value="GoPay" id="radio_gopay" checked onclick="toggleDetail('gopay')">
                    <div class="option-content">
                        <div class="option-left">
                            <span class="icon">💳</span>
                            <span class="option-name">GoPay (E-Wallet)</span>
                        </div>
                        <span class="radio-custom"></span>
                    </div>
                </label>
                <div id="detail_gopay" class="payment-details-box">
                    <div class="info-account-card">
                        📱 Nomor GoPay / E-Wallet:<br>
                        <strong>0812-3456-7890</strong> (a.n PT GoSend Express)
                    </div>
                    <div class="upload-box" style="margin: 0;">
                        <label>Upload Bukti Transfer GoPay</label>
                        <input type="file" name="bukti_transfer" class="input-bukti" accept=".jpg,.jpeg,.png">
                    </div>
                </div>

                <label class="payment-option">
                    <input type="radio" name="metode_pembayaran" value="Transfer Bank" id="radio_va" onclick="toggleDetail('va')">
                    <div class="option-content">
                        <div class="option-left">
                            <span class="icon">🏦</span>
                            <span class="option-name">Transfer Virtual Account</span>
                        </div>
                        <span class="radio-custom"></span>
                    </div>
                </label>
                <div id="detail_va" class="payment-details-box">
                    <div class="info-account-card">
                        🏛️ Nomor Virtual Account (BCA / Mandiri):<br>
                        <strong>88012 3456 7890 12</strong> (a.n PT GoSend Express)
                    </div>
                    <div class="upload-box" style="margin: 0;">
                        <label>Upload Bukti Transfer Virtual Account</label>
                        <input type="file" name="bukti_transfer" class="input-bukti" accept=".jpg,.jpeg,.png">
                    </div>
                </div>

                <label class="payment-option">
                    <input type="radio" name="metode_pembayaran" value="COD" id="radio_cod" onclick="toggleDetail('cod')">
                    <div class="option-content">
                        <div class="option-left">
                            <span class="icon">💵</span>
                            <span class="option-name">Cash on Delivery (COD)</span>
                        </div>
                        <span class="radio-custom"></span>
                    </div>
                </label>
                <div id="detail_cod" class="payment-details-box">
                    <p style="margin: 0; font-size: 13px; color: #64748b;">
                        Bayar tunai langsung kepada driver saat barang telah diterima.
                    </p>
                </div>

            </section>

            <div class="bottom-checkout-bar">
                <div class="total-price-box">
                    <span>Total Biaya</span>
                    <strong>Rp <?= number_format($pesanan["total_biaya"], 0, ",", "."); ?></strong>
                </div>
                <button type="submit" class="btn-confirm">Konfirmasi & Bayar</button>
            </div>
        </form>
    </div>

    <script>
        function toggleDetail(type) {
            // Sembunyikan semua box detail lebih dulu
            document.querySelectorAll('.payment-details-box').forEach(box => box.style.display = 'none');
            
            // Matikan required & disabled pada semua file input
            document.querySelectorAll('.input-bukti').forEach(input => {
                input.removeAttribute('required');
                input.disabled = true;
            });

            // Tampilkan detail yang diklik radio button-nya
            if (type === 'gopay') {
                const box = document.getElementById('detail_gopay');
                box.style.display = 'block';
                const fileInput = box.querySelector('.input-bukti');
                fileInput.disabled = false;
                fileInput.setAttribute('required', 'required');
            } else if (type === 'va') {
                const box = document.getElementById('detail_va');
                box.style.display = 'block';
                const fileInput = box.querySelector('.input-bukti');
                fileInput.disabled = false;
                fileInput.setAttribute('required', 'required');
            } else if (type === 'cod') {
                document.getElementById('detail_cod').style.display = 'block';
            }
        }

        // Buka pilihan pertama (GoPay) secara default saat pertama masuk
        document.addEventListener('DOMContentLoaded', function() {
            toggleDetail('gopay');
        });
    </script>

</body>
</html>