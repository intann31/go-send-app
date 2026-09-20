<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

$editDriver = null;

if (isset($_GET['edit'])) {
    $id_driver = (int) $_GET['edit'];

    $stmt = mysqli_prepare($conn, "
        SELECT
            d.id_driver,
            d.id_user,
            d.no_plat,
            d.jenis_kendaraan,
            d.status_driver,
            d.lokasi_terkini,
            u.nama,
            u.username,
            u.no_hp,
            u.email
        FROM driver d
        INNER JOIN user u ON d.id_user = u.id_user
        WHERE d.id_driver = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $id_driver);
    mysqli_stmt_execute($stmt);

    $resultEdit = mysqli_stmt_get_result($stmt);
    $editDriver = mysqli_fetch_assoc($resultEdit);
}

/* Ambil semua driver */
$query = mysqli_query($conn, "
    SELECT
        d.id_driver,
        d.id_user,
        d.no_plat,
        d.jenis_kendaraan,
        d.status_driver,
        d.lokasi_terkini,
        u.nama,
        u.no_hp,
        u.email
    FROM driver d
    INNER JOIN user u ON d.id_user = u.id_user
    ORDER BY d.id_driver DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Driver - GoSend</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="sidebar-logo">
            <h2>GoSend</h2>
            <p>Admin Panel</p>
        </div>

        <nav class="sidebar-menu">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="driver.php" class="active">
                Kelola Driver
            </a>

            <a href="pesanan.php">
                Pesanan
            </a>

            <a href="pembayaran.php">
                Pembayaran
            </a>

            <a href="tracking.php">
                Tracking
            </a>

            <a href="tarif.php">
                Tarif Ongkir
            </a>

            <a href="area.php">
                Area Layanan
            </a>

            <a href="laporan.php">
                Laporan
            </a>

            <a href="../auth/logout.php">
                Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="dashboard-main">

        <div class="dashboard-header">

            <div>
                <h1>Kelola Driver</h1>
                <p>Kelola data driver dan kendaraan.</p>
            </div>

        </div>


        <!-- FORM TAMBAH / EDIT -->
        <section class="dashboard-card">

            <?php if ($editDriver): ?>

                <h2>Edit Driver</h2>

                <form action="proses-driver.php" method="POST">

                    <input
                        type="hidden"
                        name="aksi"
                        value="edit"
                    >

                    <input
                        type="hidden"
                        name="id_driver"
                        value="<?= $editDriver['id_driver']; ?>"
                    >

                    <input
                        type="hidden"
                        name="id_user"
                        value="<?= $editDriver['id_user']; ?>"
                    >


                    <div class="form-grid">

                        <div class="form-group">
                            <label>Nama Driver</label>

                            <input
                                type="text"
                                name="nama"
                                value="<?= htmlspecialchars($editDriver['nama']); ?>"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>No. HP</label>

                            <input
                                type="text"
                                name="no_hp"
                                value="<?= htmlspecialchars($editDriver['no_hp']); ?>"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Email</label>

                            <input
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($editDriver['email']); ?>"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>No. Plat</label>

                            <input
                                type="text"
                                name="no_plat"
                                value="<?= htmlspecialchars($editDriver['no_plat']); ?>"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Jenis Kendaraan</label>

                            <input
                                type="text"
                                name="jenis_kendaraan"
                                value="<?= htmlspecialchars($editDriver['jenis_kendaraan']); ?>"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Status Driver</label>

                            <select name="status_driver" required>

                                <option value="Aktif"
                                    <?= $editDriver['status_driver'] === 'Aktif' ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="Tidak Aktif"
                                    <?= $editDriver['status_driver'] === 'Tidak Aktif' ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>

                                <option value="Sedang Bertugas"
                                    <?= $editDriver['status_driver'] === 'Sedang Bertugas' ? 'selected' : ''; ?>>
                                    Sedang Bertugas
                                </option>

                            </select>
                        </div>


                        <div class="form-group form-full">
                            <label>Lokasi Terkini</label>

                            <input
                                type="text"
                                name="lokasi_terkini"
                                value="<?= htmlspecialchars($editDriver['lokasi_terkini']); ?>"
                                placeholder="Contoh: Bandung"
                            >
                        </div>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            Simpan Perubahan
                        </button>

                        <a
                            href="driver.php"
                            class="btn-secondary"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            <?php else: ?>

                <h2>Tambah Driver</h2>

                <form action="proses-driver.php" method="POST">

                    <input
                        type="hidden"
                        name="aksi"
                        value="tambah"
                    >

                    <div class="form-grid">

                        <div class="form-group">
                            <label>Username</label>

                            <input
                                type="text"
                                name="username"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Password</label>

                            <input
                                type="password"
                                name="password"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Nama Driver</label>

                            <input
                                type="text"
                                name="nama"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>No. HP</label>

                            <input
                                type="text"
                                name="no_hp"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Email</label>

                            <input
                                type="email"
                                name="email"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>No. Plat</label>

                            <input
                                type="text"
                                name="no_plat"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Jenis Kendaraan</label>

                            <input
                                type="text"
                                name="jenis_kendaraan"
                                placeholder="Contoh: Motor"
                                required
                            >
                        </div>


                        <div class="form-group">
                            <label>Status Driver</label>

                            <select name="status_driver" required>

                                <option value="Aktif">
                                    Aktif
                                </option>

                                <option value="Tidak Aktif">
                                    Tidak Aktif
                                </option>

                                <option value="Sedang Bertugas">
                                    Sedang Bertugas
                                </option>

                            </select>
                        </div>


                        <div class="form-group form-full">

                            <label>Lokasi Terkini</label>

                            <input
                                type="text"
                                name="lokasi_terkini"
                                placeholder="Contoh: Bandung"
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        + Tambah Driver
                    </button>

                </form>

            <?php endif; ?>

        </section>


        <!-- DATA DRIVER -->
        <section class="dashboard-card">

            <div class="card-header">

                <div>
                    <h2>Data Driver</h2>
                    <p>Daftar driver yang terdaftar.</p>
                </div>

            </div>


            <div class="table-wrapper">

                <table class="data-table">

                    <thead>

                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>No. HP</th>
                            <th>Kendaraan</th>
                            <th>No. Plat</th>
                            <th>Status</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php
                        $no = 1;

                        if (mysqli_num_rows($query) > 0):

                            while ($driver = mysqli_fetch_assoc($query)):
                        ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($driver['nama']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($driver['no_hp']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($driver['jenis_kendaraan']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($driver['no_plat']); ?>
                                </td>

                                <td>

                                    <span class="status-badge">
                                        <?= htmlspecialchars($driver['status_driver']); ?>
                                    </span>

                                </td>

                                <td>
                                    <?= htmlspecialchars($driver['lokasi_terkini'] ?: '-'); ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="driver.php?edit=<?= $driver['id_driver']; ?>"
                                            class="btn-small"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="hapus-driver.php?id=<?= $driver['id_driver']; ?>"
                                            class="btn-small btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus driver ini?');"
                                        >
                                            Hapus
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php

                            endwhile;

                        else:

                        ?>

                            <tr>

                                <td colspan="8" class="empty-data">
                                    Belum ada data driver.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>