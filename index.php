<?php
require_once __DIR__ . "/config/database.php";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoSend - Antar Jemput Barang</title>

    <!-- Font Modern Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <!-- Style Utama -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Background Dynamic Glow Effect -->
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>

    <!-- NAVBAR -->
    <header class="navbar">
        <a href="index.php" class="brand">
            <div class="brand-icon">🚚</div>
            <div>
                <span>GoSend</span>
                <small>Antar Jemput Barang</small>
            </div>
        </a>

        <nav>
            <a href="#layanan">Layanan</a>
            <a href="#cara-kerja">Cara Kerja</a>
            <a href="#tentang">Tentang</a>
            <a href="auth/login.php" class="btn-login">Login</a>
            <a href="auth/register.php" class="btn-daftar">Daftar</a>
        </nav>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-content" data-aos="fade-right" data-aos-duration="1000">
            <div class="badge">✦ Solusi Pengiriman Mudah & Cepat</div>
            <h1>Kirim Barang <span class="gradient-text">Lebih Mudah.</span></h1>
            <p>Sistem antar jemput barang modern yang membantu kamu mengirim barang dengan cepat, aman, dan pantau status secara realtime.</p>

            <div class="hero-buttons">
                <a href="auth/register.php" class="btn-primary glow-btn">Mulai Kirim Barang →</a>
                <a href="#cara-kerja" class="btn-outline">Lihat Cara Kerja</a>
            </div>

            <div class="hero-info">
                <div class="info-item">
                    <strong>100%</strong>
                    <span>Aman Terpercaya</span>
                </div>
                <div class="info-divider"></div>
                <div class="info-item">
                    <strong>Fast</strong>
                    <span>Express Delivery</span>
                </div>
            </div>
        </div>

        <div class="hero-visual" data-aos="fade-left" data-aos-duration="1000">
            <div class="glass-card 3d-card">
                <div class="card-top">
                    <div>
                        <small>Pengiriman Hari Ini</small>
                        <h3>Dalam Perjalanan</h3>
                    </div>
                    <span class="status-dot pulse">●</span>
                </div>

                <div class="route">
                    <div class="route-item">
                        <div class="route-icon">📍</div>
                        <div>
                            <small>Lokasi Jemput</small>
                            <strong>Bandung</strong>
                        </div>
                    </div>

                    <div class="route-line">
                        <div class="moving-truck">🚚</div>
                    </div>

                    <div class="route-item">
                        <div class="route-icon destination">📦</div>
                        <div>
                            <small>Tujuan</small>
                            <strong>Jakarta</strong>
                        </div>
                    </div>
                </div>

                <div class="delivery-progress">
                    <div class="progress-header">
                        <span>Status Pengiriman</span>
                        <strong>75%</strong>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LAYANAN -->
    <section class="section" id="layanan">
        <div class="section-heading" data-aos="fade-up">
            <div class="section-label">LAYANAN KAMI</div>
            <h2>Semua kebutuhan pengiriman <span class="gradient-text">dalam satu sistem.</span></h2>
            <p>Dari cek ongkir sampai tracking, semuanya dibuat sangat praktis.</p>
        </div>

        <div class="service-grid">
            <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                <span class="service-number">01</span>
                <div class="service-icon">💰</div>
                <h3>Cek Ongkir</h3>
                <p>Cek estimasi biaya pengiriman transparan berdasarkan jarak dan tarif real-time.</p>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                <span class="service-number">02</span>
                <div class="service-icon">📦</div>
                <h3>Buat Pesanan</h3>
                <p>Masukkan informasi barang, lokasi penjemputan, serta alamat tujuan dengan mudah.</p>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                <span class="service-number">03</span>
                <div class="service-icon">🚚</div>
                <h3>Antar Barang</h3>
                <p>Driver profesional siap menjemput dan mengantarkan barang hingga sampai tujuan.</p>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-delay="400">
                <span class="service-number">04</span>
                <div class="service-icon">📍</div>
                <h3>Tracking Realtime</h3>
                <p>Pantau posisi dan status barang kamu kapan saja dari mana saja.</p>
            </div>
        </div>
    </section>

    <!-- CARA KERJA -->
    <section class="process-section" id="cara-kerja">
        <div class="process-content" data-aos="fade-up">
            <div class="section-label">CARA KERJA</div>
            <h2>Kirim barang dalam <span class="gradient-text">beberapa langkah.</span></h2>
        </div>

        <div class="steps">
            <div class="step" data-aos="zoom-in" data-aos-delay="100">
                <div class="step-number">01</div>
                <h3>Buat Pesanan</h3>
                <p>Masukkan alamat jemput, tujuan, dan detail paket.</p>
            </div>

            <div class="step" data-aos="zoom-in" data-aos-delay="200">
                <div class="step-number">02</div>
                <h3>Pembayaran</h3>
                <p>Lakukan pembayaran instan dan upload bukti transfer.</p>
            </div>

            <div class="step" data-aos="zoom-in" data-aos-delay="300">
                <div class="step-number">03</div>
                <h3>Driver Ditentukan</h3>
                <p>Sistem otomatis/admin mengarahkan driver terdekat.</p>
            </div>

            <div class="step" data-aos="zoom-in" data-aos-delay="400">
                <div class="step-number">04</div>
                <h3>Barang Dikirim</h3>
                <p>Barang diantar dan pantau hingga sampai lokasi.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta" id="tentang" data-aos="flip-up">
        <div class="cta-bg-glow"></div>
        <div>
            <h2>Mulai kirim barang dengan lebih mudah.</h2>
            <p>Buat akun sekarang dan rasakan kemudahan layanan pengiriman masa kini.</p>
        </div>
        <a href="auth/register.php" class="btn-primary glow-btn">Daftar Sekarang →</a>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-brand">
            <div class="brand-icon">🚚</div>
            <div>
                <strong>GoSend</strong>
                <p>Sistem Antar Jemput Barang Modern</p>
            </div>
        </div>
        <p class="copyright">© 2026 GoSend. All rights reserved.</p>
    </footer>

    <!-- AOS Script JS -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            duration: 800
        });

        // Smooth Scroll Fix
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>