<?php

use App\Models\FileUpload;
use Config\Core\SystemInfo;
?>
<!-- preloader start -->
<div class="preloader d-none">
    <div class="loader">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>
<!-- preloader end -->

<?php
$topbarPage = $_GET['a'] ?? 'omzet';
$topbarTab  = $_GET['tab'] ?? '';
?>

<!-- header start (Fixed Maroon Top Navigation Bar) -->
<div class="header fixed-topbar-header py-2" style="background: linear-gradient(135deg, #7D0A0A 0%, #4A0404 100%) !important; background-color: #7D0A0A !important;">
    <div class="w-100 h-100">
        <div class="d-flex align-items-center justify-content-between h-100">
            
            <!-- 1. Left: Brand Logo & Title -->
            <div class="d-flex align-items-center">
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-extrabold fs-5 text-uppercase text-white mb-0" style="font-weight: 900; letter-spacing: 0.6px; line-height: 1.1;">TOKO MADURA</span>
                        <span class="fw-bold text-uppercase text-white-50" style="font-size: 9px; letter-spacing: 1px;"><?= strtoupper($user['role'] ?? 'outlet'); ?> PANEL</span>
                    </div>
                </a>
            </div>

            <!-- 2. Center: Fixed Top Navigation Bar (Role-Adaptive) -->
            <?php $role = strtolower($user['role'] ?? 'outlet'); ?>
            <div class="d-none d-lg-flex align-items-center gap-2 topbar-nav-pill-container shadow-sm">
                <?php if ($role === 'master') : ?>
                    <!-- Menu 1 Master: Data Investor -->
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/investor" class="topbar-nav-pill <?= ($topbarPage == 'investor' || $topbarPage == 'dashboard' || $topbarPage == 'outlet') ? 'active' : ''; ?>">
                        <i class="fa-light fa-users me-2"></i> Data Investor
                    </a>
                    <!-- Menu 2 Master: Komisi -->
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/komisi" class="topbar-nav-pill <?= ($topbarPage == 'komisi') ? 'active' : ''; ?>">
                        <i class="fa-light fa-award me-2"></i> Komisi
                    </a>
                <?php elseif ($role === 'investor') : ?>
                    <!-- Menu 1 Investor: Data Outlet -->
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/outlet" class="topbar-nav-pill <?= ($topbarPage == 'outlet') ? 'active' : ''; ?>">
                        <i class="fa-light fa-store me-2"></i> Data Outlet Toko
                    </a>
                    
                    <!-- Menu 2 Investor: Bagi Hasil & Omzet -->
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/bagi-hasil" class="topbar-nav-pill <?= ($topbarPage == 'bagi-hasil' || $topbarPage == 'omzet') ? 'active' : ''; ?>">
                        <i class="fa-light fa-vault me-2"></i> Rekap Bagi Hasil
                    </a>
                <?php else : ?>
                    <!-- Menu 1 Outlet: Input Omzet -->
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet" class="topbar-nav-pill <?= ($topbarPage == 'omzet' && empty($topbarTab)) ? 'active' : ''; ?>">
                        <i class="fa-light fa-money-bill-trend-up me-2"></i> Input Omzet
                    </a>
                    
                    <!-- Menu 2 Outlet: Riwayat & Potongan -->
                    <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet?tab=riwayat" class="topbar-nav-pill <?= ($topbarPage == 'omzet' && $topbarTab == 'riwayat') || $topbarPage == 'riwayat-omzet' || $topbarPage == 'bagi-hasil' ? 'active' : ''; ?>">
                        <i class="fa-light fa-clock-rotate-left me-2"></i> Riwayat & Potongan
                    </a>
                <?php endif; ?>
            </div>

            <!-- 3. Right: Utility Buttons & User Profile -->
            <div class="d-flex align-items-center gap-2">
                <!-- Calculator Dropdown Button -->
                <div class="header-btn-box">
                    <div class="dropdown">
                        <button class="header-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Kalkulator">
                            <i class="fa-light fa-calculator"></i>
                        </button>
                        <ul class="dropdown-menu calculator-dropdown shadow-lg">
                            <div class="dgb-calc-box">
                                <div>
                                    <input type="text" id="dgbCalcResult" placeholder="0" autocomplete="off" readonly>
                                </div>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="bg-danger">C</td>
                                            <td class="bg-secondary">CE</td>
                                            <td class="dgb-calc-oprator bg-primary">/</td>
                                            <td class="dgb-calc-oprator bg-primary">*</td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td>8</td>
                                            <td>9</td>
                                            <td class="dgb-calc-oprator bg-primary">-</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>5</td>
                                            <td>6</td>
                                            <td class="dgb-calc-oprator bg-primary">+</td>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>2</td>
                                            <td>3</td>
                                            <td rowspan="2" class="dgb-calc-sum bg-primary">=</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">0</td>
                                            <td>.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </ul>
                    </div>
                </div>

                <!-- Fullscreen Button -->
                <button class="header-btn fullscreen-btn d-none d-md-inline-flex" id="btnFullscreen" title="Layar Penuh"><i class="fa-light fa-expand"></i></button>

                <!-- Profile Avatar Dropdown -->
                <div class="header-btn-box profile-btn-box">
                    <button class="p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="custom-avatar-container shadow-sm" style="width: 38px; height: 38px;">
                            <img class="custom-avatar" style="width: 38px; height: 38px;" src="<?= App\Models\User::avatar($user['MBR_AVATAR']); ?>" alt="Avatar">
                        </div>
                    </button>
                    <ul class="dropdown-menu profile-dropdown-menu shadow-lg">
                        <li>
                            <div class="dropdown-txt text-center py-2">
                                <p class="fw-bold mb-0 text-body-emphasis"><?php echo htmlspecialchars($user['MBR_NAME'] ?? 'User'); ?></p>
                                <span class="badge bg-danger-subtle text-danger small text-uppercase rounded-pill px-2 py-1 mt-1"><?= strtoupper($user['role'] ?? 'outlet'); ?></span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-semibold" href="<?= SystemInfo::app('CLIENT_URL') ?>/personal-information"><span class="dropdown-icon me-2"><i class="fa-regular fa-circle-user"></i></span> Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-bold" href="<?= SystemInfo::app('CLIENT_URL') ?>/logout"><span class="dropdown-icon me-2 text-danger"><i class="fa-regular fa-arrow-right-from-bracket"></i></span> Logout</a></li>
                    </ul>
                </div>

                <!-- Mobile Menu Button (Hamburger Toggle for Topbar Menu) -->
                <button class="btn btn-light btn-sm text-danger d-lg-none rounded-pill px-3 py-1 fw-bold shadow-sm ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#mobileTopNav" aria-expanded="false">
                    <i class="fa-solid fa-bars me-1"></i> Menu
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Fixed Top Bar Collapse Dropdown (Role-Adaptive) -->
<div class="collapse d-lg-none mobile-topbar-dropdown" id="mobileTopNav">
    <div class="d-flex flex-column gap-1">
        <?php if ($role === 'master') : ?>
            <!-- Menu 1 Master: Data Investor -->
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/investor" class="mobile-topbar-link <?= ($topbarPage == 'investor' || $topbarPage == 'dashboard' || $topbarPage == 'outlet') ? 'active' : ''; ?>">
                <i class="fa-light fa-users me-2 fs-5"></i> Data Investor
            </a>
            <!-- Menu 2 Master: Komisi -->
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/komisi" class="mobile-topbar-link <?= ($topbarPage == 'komisi') ? 'active' : ''; ?>">
                <i class="fa-light fa-award me-2 fs-5"></i> Komisi
            </a>
        <?php elseif ($role === 'investor') : ?>
            <!-- Menu 1 Investor: Data Outlet -->
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/outlet" class="mobile-topbar-link <?= ($topbarPage == 'outlet') ? 'active' : ''; ?>">
                <i class="fa-light fa-store me-2 fs-5"></i> Data Outlet Toko
            </a>
            <!-- Menu 2 Investor: Rekap Bagi Hasil -->
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/bagi-hasil" class="mobile-topbar-link <?= ($topbarPage == 'bagi-hasil' || $topbarPage == 'omzet') ? 'active' : ''; ?>">
                <i class="fa-light fa-vault me-2 fs-5"></i> Rekap Bagi Hasil
            </a>
        <?php else : ?>
            <!-- Menu 1 Outlet: Input Omzet -->
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet" class="mobile-topbar-link <?= ($topbarPage == 'omzet' && empty($topbarTab)) ? 'active' : ''; ?>">
                <i class="fa-light fa-money-bill-trend-up me-2 fs-5"></i> Input Omzet
            </a>
            <!-- Menu 2 Outlet: Riwayat & Potongan -->
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/omzet?tab=riwayat" class="mobile-topbar-link <?= ($topbarPage == 'omzet' && $topbarTab == 'riwayat') || $topbarPage == 'riwayat-omzet' || $topbarPage == 'bagi-hasil' ? 'active' : ''; ?>">
                <i class="fa-light fa-clock-rotate-left me-2 fs-5"></i> Riwayat & Potongan
            </a>
        <?php endif; ?>
    </div>
</div>
<!-- header end -->

<!-- profile right sidebar start -->
<div class="profile-right-sidebar">
    <button class="right-bar-close"><i class="fa-light fa-angle-right"></i></button>
    <div class="top-panel">
        <div class="profile-content scrollable">
            <ul>
                <li>
                    <div class="dropdown-txt text-center">
                        <p class="mb-0"><?= $user['MBR_NAME']; ?></p>
                        <!-- <span class="d-block">Web Developer</span> -->
                        <div class="d-flex justify-content-center">
                            <div class="form-check pt-3">
                                <input class="form-check-input" type="checkbox" id="seeProfileAsDropdown">
                                <label class="form-check-label" for="seeProfileAsDropdown">See as dropdown</label>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= SystemInfo::app('CLIENT_URL') ?>/personal-information"><span class="dropdown-icon"><i class="fa-regular fa-circle-user"></i></span> Profile</a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= SystemInfo::app('CLIENT_URL') ?>/help-center"><span class="dropdown-icon"><i class="fa-regular fa-circle-question"></i></span> Help</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="bottom-panel">
        <div class="button-group">
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/personal-information"><i class="fa-light fa-gear"></i><span>Settings</span></a>
            <a href="<?= SystemInfo::app('CLIENT_URL') ?>/logout"><i class="fa-light fa-power-off"></i><span>Logout</span></a>
        </div>
    </div>
</div>
<!-- profile right sidebar end -->

<div class="right-sidebar-btn d-lg-block d-none">
    <button class="header-btn theme-settings-btn"><i class="fa-light fa-gear"></i></button>
</div>

<!-- right sidebar start -->
<div class="right-sidebar">
    <button class="right-bar-close"><i class="fa-light fa-angle-right"></i></button>
    <div class="sidebar-title">
        <h3>Layout Settings</h3>
    </div>
    <div class="sidebar-body scrollable">
        <div class="right-sidebar-group">
            <span class="sidebar-subtitle">Nav Position <span><i class="fa-light fa-angle-up"></i></span></span>
            <div class="settings-row">
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded active" id="verticalMenu">
                        <div class="pb-2 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="border border-primary mb-1">
                                <div class="px-2 pt-1 bg-nav mb-1"></div>
                                <div class="px-2 pt-1 bg-nav mb-1"></div>
                            </div>
                            <div class="border border-primary">
                                <div class="px-2 pt-1 bg-nav mb-1"></div>
                                <div class="px-2 pt-1 bg-nav mb-1"></div>
                            </div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Vertical</span>
                    </div>
                </div>
                <div class="settings-col d-lg-block d-none">
                    <div class="dashboard-icon d-flex h-100 gap-1 border rounded" id="horizontalMenu">
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="p-1 bg-menu border-bottom">
                                    <div class="rounded-circle p-1 bg-nav w-max-content"></div>
                                </div>
                                <div class="p-1 bg-menu d-flex gap-1 mb-1">
                                    <div class="w-max-content px-2 pt-1 rounded bg-nav"></div>
                                    <div class="w-max-content px-2 pt-1 rounded bg-nav"></div>
                                    <div class="w-max-content px-2 pt-1 rounded bg-nav"></div>
                                    <div class="w-max-content px-2 pt-1 rounded bg-nav"></div>
                                </div>
                            </div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Horizontal</span>
                    </div>
                </div>
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded" id="twoColumnMenu">
                        <div class="p-1 bg-menu"></div>
                        <div class="pb-4 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Two column</span>
                    </div>
                </div>
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded" id="flushMenu">
                        <div class="pb-4 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Flush</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-sidebar-group">
            <span class="sidebar-subtitle">Theme Color <?= $user['MBR_THEME'] ?> <span><i class="fa-light fa-angle-up"></i></span></span>
            <div class="settings-row">
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded bg-body-secondary light-theme-btn <?= ($user['MBR_THEME'] == 0) ? 'active' : ''; ?>" id="lightTheme">
                        <div class="pb-4 px-1 pt-1 bg-dark-subtle">
                            <div class="px-2 py-1 rounded-pill bg-primary mb-2"></div>
                            <div class="px-2 pt-1 bg-primary mb-1"></div>
                            <div class="px-2 pt-1 bg-primary mb-1"></div>
                            <div class="px-2 pt-1 bg-primary mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-dark-subtle"></div>
                            <div class="px-2 py-1 bg-dark-subtle"></div>
                        </div>
                        <span class="part-txt">Light Theme</span>
                    </div>
                </div>
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded bg-dark <?= ($user['MBR_THEME'] == 1) ? 'active' : ''; ?>" id="darkTheme">
                        <div class="pb-4 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Dark Theme</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-sidebar-group" id="navBarSizeGroup">
            <span class="sidebar-subtitle">Navbar Size <span><i class="fa-light fa-angle-up"></i></span></span>
            <div class="settings-row">
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded active" id="sidebarDefault">
                        <div class="pb-4 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Default</span>
                    </div>
                </div>
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded" id="sidebarSmall">
                        <div class="pb-4 pt-1 bg-menu">
                            <div class="p-1 rounded-pill bg-nav mb-2"></div>
                            <div class="ps-1 pt-1 bg-nav mb-1"></div>
                            <div class="ps-1 pt-1 bg-nav mb-1"></div>
                            <div class="ps-1 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Small icon</span>
                    </div>
                </div>
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded" id="sidebarHover">
                        <div class="pb-4 pt-1 bg-menu">
                            <div class="p-1 rounded-pill bg-nav mb-2"></div>
                            <div class="ps-1 pt-1 bg-nav mb-1"></div>
                            <div class="ps-1 pt-1 bg-nav mb-1"></div>
                            <div class="ps-1 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Expand on hover</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-sidebar-group">
            <span class="sidebar-subtitle">Main preloader <span><i class="fa-light fa-angle-up"></i></span></span>
            <div class="settings-row">
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded" id="enableLoader">
                        <div class="pb-4 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <div class="preloader-small">
                            <div class="loader">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <span class="part-txt">Enable</span>
                    </div>
                </div>
                <div class="settings-col">
                    <div class="dashboard-icon d-flex gap-1 border rounded active" id="disableLoader">
                        <div class="pb-4 px-1 pt-1 bg-menu">
                            <div class="px-2 py-1 rounded-pill bg-nav mb-2"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                            <div class="px-2 pt-1 bg-nav mb-1"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-between">
                            <div class="px-2 py-1 bg-menu"></div>
                            <div class="px-2 py-1 bg-menu"></div>
                        </div>
                        <span class="part-txt">Disable</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- right sidebar end -->