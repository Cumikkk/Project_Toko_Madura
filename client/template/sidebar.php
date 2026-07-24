<?php
use Config\Core\SystemInfo;

$role = strtolower($user['role'] ?? '');
$currentPage = $_GET['a'] ?? 'dashboard';
?>

<!-- Mobile Sidebar Overlay Backdrop -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Sidebar Start -->
<div class="main-sidebar tm-maroon-sidebar" id="mainSidebar">
    <!-- Brand / Logo Header (Matching Design Reference) -->
    <div class="sidebar-brand">
        <a href="<?= SystemInfo::app('CLIENT_URL') ?>/dashboard" class="brand-link">
            <div class="brand-icon">
                <i class="fa-sharp fa-solid fa-shop"></i>
            </div>
            <div class="brand-text-container">
                <span class="brand-title">TOKO MADURA</span>
                <span class="brand-subtitle"><?= strtoupper($role) ?> PANEL</span>
            </div>
        </a>
        <button type="button" class="sidebar-close-btn d-lg-none" id="mobileSidebarClose" aria-label="Close Sidebar">
            <i class="fa-light fa-xmark"></i>
        </button>
    </div>

    <!-- User Profile Badge in Sidebar -->
    <div class="sidebar-user-card">
        <div class="user-avatar-wrap">
            <img class="user-avatar" src="<?= App\Models\User::avatar($user['MBR_AVATAR'] ?? ''); ?>" alt="Avatar">
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['MBR_NAME'] ?? 'Pengguna'); ?></div>
            <div class="user-role-badge">
                <i class="fa-solid fa-circle-user me-1" style="font-size: 9px;"></i>
                <?= ucfirst($role); ?>
            </div>
        </div>
    </div>

    <!-- Main Navigation Menu -->
    <div class="main-menu">
        <ul class="sidebar-menu scrollable">
            <!-- DASHBOARD MENU -->
            <li class="sidebar-item">
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/dashboard" class="sidebar-link <?= ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="fa-light fa-grid-2"></i></span> 
                    <span class="sidebar-txt">Dashboard</span>
                </a>
            </li>

            <?php if ($role === 'investor') : ?>
                <!-- INVESTOR SPECIFIC MENU -->
                <li class="sidebar-section-title">Menu Investor</li>
                
                <!-- 1. Data Outlet -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/outlet" class="sidebar-link <?= ($currentPage == 'outlet') ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-store"></i></span> 
                        <span class="sidebar-txt">Data Outlet</span>
                    </a>
                </li>

                <!-- 2. Bagi Hasil -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/bagi-hasil" class="sidebar-link <?= ($currentPage == 'bagi-hasil') ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-hand-holding-dollar"></i></span> 
                        <span class="sidebar-txt">Bagi Hasil</span>
                    </a>
                </li>

                <!-- 3. Laporan -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/laporan" class="sidebar-link <?= ($currentPage == 'laporan') ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-file-invoice-dollar"></i></span> 
                        <span class="sidebar-txt">Laporan</span>
                    </a>
                </li>

            <?php elseif ($role === 'outlet') : ?>
                <!-- OUTLET SPECIFIC MENU -->
                <li class="sidebar-section-title">Menu Outlet</li>

                <!-- 1. Input Omzet Harian -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet" class="sidebar-link <?= ($currentPage == 'omzet' && empty($_GET['tab'])) ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-money-bill-trend-up"></i></span> 
                        <span class="sidebar-txt">Input Omzet</span>
                    </a>
                </li>

                <!-- 2. Riwayat Potongan -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet?tab=riwayat" class="sidebar-link <?= ($currentPage == 'omzet' && ($_GET['tab'] ?? '') == 'riwayat') || $currentPage == 'riwayat-omzet' ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-clock-rotate-left"></i></span> 
                        <span class="sidebar-txt">Riwayat Potongan</span>
                    </a>
                </li>

                <!-- 3. Bagi Hasil -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/bagi-hasil" class="sidebar-link <?= ($currentPage == 'bagi-hasil') ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-hand-holding-dollar"></i></span> 
                        <span class="sidebar-txt">Bagi Hasil</span>
                    </a>
                </li>

                <!-- 4. Laporan Omzet -->
                <li class="sidebar-item">
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/laporan" class="sidebar-link <?= ($currentPage == 'laporan') ? 'active' : ''; ?>">
                        <span class="nav-icon"><i class="fa-light fa-file-invoice-dollar"></i></span> 
                        <span class="sidebar-txt">Laporan</span>
                    </a>
                </li>

            <?php endif; ?>

            <!-- GENERAL SYSTEM MENU -->
            <li class="sidebar-section-title">Pengaturan</li>
            
            <li class="sidebar-item">
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/personal-information" class="sidebar-link <?= ($currentPage == 'personal-information' || $currentPage == 'profile') ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="fa-light fa-gear"></i></span> 
                    <span class="sidebar-txt">Pengaturan</span>
                </a>
            </li>

            <li class="sidebar-item mt-3">
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/logout" class="sidebar-link sidebar-logout-btn">
                    <span class="nav-icon"><i class="fa-light fa-arrow-right-from-bracket"></i></span> 
                    <span class="sidebar-txt">Log Out</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- Main Sidebar End -->