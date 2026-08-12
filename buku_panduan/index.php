<?php
$role = $_GET['role'] ?? 'master';
$title = "Buku Panduan Sistem - " . ucfirst($role);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="background-elements">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <aside class="sidebar">
        <div class="logo-area">
            <i class="fa-solid fa-book-open"></i>
            <h2>Panduan <span>Sistem</span></h2>
        </div>
        <nav class="nav-menu">
            <p class="nav-label">PILIH ROLE</p>
            <a href="?role=master" class="<?= $role === 'master' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-tie"></i> Master (Client)
            </a>
            <a href="?role=admin" class="<?= $role === 'admin' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-shield"></i> Admin (Panel)
            </a>
        </nav>
    </aside>

    <main class="content-area">
        <header class="top-header">
            <div class="welcome-text">
                <h1>Dokumentasi Penggunaan <?= ucfirst($role) ?></h1>
                <p>Panduan lengkap langkah demi langkah pengoperasian sistem.</p>
            </div>
        </header>

        <div class="doc-container">
            <?php if ($role === 'master'): ?>
                <!-- MASTER DOCUMENTATION -->
                <section class="doc-section glass-card" id="master-login">
                    <div class="section-badge"><i class="fa-solid fa-1"></i></div>
                    <div class="section-content">
                        <h3>1. Login sebagai Master</h3>
                        <p>Silakan buka portal client melalui URL <code>http://client-tokomadura.test</code>. Masukkan kredensial Master Anda.</p>
                        <ul class="guidelines">
                            <li><strong>Username:</strong> master</li>
                            <li><strong>Password:</strong> 123</li>
                        </ul>
                        <div class="image-wrapper">
                            <img src="assets/master/01-login.png" alt="Halaman Login Master" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Login';">
                        </div>
                    </div>
                </section>

                <section class="doc-section glass-card" id="master-dashboard">
                    <div class="section-badge"><i class="fa-solid fa-2"></i></div>
                    <div class="section-content">
                        <h3>2. Dashboard & Monitoring Outlet</h3>
                        <p>Setelah login, Anda akan diarahkan ke halaman Dashboard/Omzet dimana Anda bisa memantau pergerakan omzet toko.</p>
                        <div class="image-wrapper">
                            <img src="assets/master/02-dashboard.png" alt="Halaman Dashboard Master" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Dashboard';">
                        </div>
                    </div>
                </section>
                
                <section class="doc-section glass-card" id="master-investor">
                    <div class="section-badge"><i class="fa-solid fa-3"></i></div>
                    <div class="section-content">
                        <h3>3. Manajemen Data Investor</h3>
                        <p>Klik menu <strong>Data Investor</strong> di navigasi atas untuk melihat dan memantau semua outlet yang aktif, menunggak (expired), atau ditolak.</p>
                        <div class="image-wrapper">
                            <img src="assets/master/03-investor.png" alt="Halaman Data Investor Master" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Investor';">
                        </div>
                    </div>
                </section>
                
                <section class="doc-section glass-card" id="master-komisi">
                    <div class="section-badge"><i class="fa-solid fa-4"></i></div>
                    <div class="section-content">
                        <h3>4. Laporan Rekap Komisi</h3>
                        <p>Klik menu <strong>Komisi</strong> di navigasi atas untuk melihat seluruh transferan komisi bersih yang disetor oleh Admin dari bagi hasil seluruh cabang.</p>
                        <div class="image-wrapper">
                            <img src="assets/master/04-komisi.png" alt="Halaman Komisi Master" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Komisi';">
                        </div>
                    </div>
                </section>

            <?php elseif ($role === 'admin'): ?>
                <!-- ADMIN DOCUMENTATION -->
                <section class="doc-section glass-card" id="admin-login">
                    <div class="section-badge"><i class="fa-solid fa-1"></i></div>
                    <div class="section-content">
                        <h3>1. Login ke Panel Admin</h3>
                        <p>Buka <code>http://admin-tokomadura.test</code> untuk masuk ke portal Manajemen Backend.</p>
                        <div class="image-wrapper">
                            <img src="assets/admin/01-login.png" alt="Halaman Login Admin" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Login';">
                        </div>
                    </div>
                </section>

                <section class="doc-section glass-card" id="admin-dashboard">
                    <div class="section-badge"><i class="fa-solid fa-2"></i></div>
                    <div class="section-content">
                        <h3>2. Dashboard Utama Admin</h3>
                        <p>Halaman utama yang menampilkan ringkasan performa seluruh toko, antrean approval, dan grafik pendapatan bulanan.</p>
                        <div class="image-wrapper">
                            <img src="assets/admin/02-dashboard.png" alt="Dashboard Admin" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Dashboard';">
                        </div>
                    </div>
                </section>

                <section class="doc-section glass-card" id="admin-approval">
                    <div class="section-badge"><i class="fa-solid fa-3"></i></div>
                    <div class="section-content">
                        <h3>3. Persetujuan (ACC) Outlet Baru</h3>
                        <p>Di modul Data Outlet, Admin memverifikasi bukti pembayaran investor. Admin bisa menerima atau menolak (beserta alasan penolakan).</p>
                        <div class="image-wrapper">
                            <img src="assets/admin/03-acc-outlet.png" alt="ACC Outlet" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+ACC+Outlet';">
                        </div>
                    </div>
                </section>

                <section class="doc-section glass-card" id="admin-komisi">
                    <div class="section-badge"><i class="fa-solid fa-4"></i></div>
                    <div class="section-content">
                        <h3>4. Kirim Komisi ke Master</h3>
                        <p>Pada modul Data Komisi, Admin akan mentransfer dana hasil bersih bulanan kepada Master dan mengunggah bukti transfer.</p>
                        <div class="image-wrapper">
                            <img src="assets/admin/04-kirim-komisi.png" alt="Kirim Komisi" onerror="this.src='https://placehold.co/800x400/1a1a1a/444444?text=Menunggu+Screenshot+Kirim+Komisi';">
                        </div>
                    </div>
                </section>

            <?php endif; ?>
        </div>
    </main>

</body>
</html>
