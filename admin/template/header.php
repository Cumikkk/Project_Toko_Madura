<?php use Config\Core\SystemInfo; ?>
<div class="main-header side-header sticky">
    <div class="main-container container-fluid">
        <div class="main-header-left">
            <a class="main-header-menu-icon" href="javascript:void(0);" id="mainSidebarToggle"><span></span></a>
            <div class="hor-logo">
                <a class="main-logo d-flex align-items-center gap-2 text-decoration-none" href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">
                    <div class="d-flex align-items-center justify-content-center shadow-sm" style="background: #7D0A0A; color: #ffffff; width: 36px; height: 36px; border-radius: 10px; font-size: 18px;">
                        <i class="fa-sharp fa-solid fa-shop"></i>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-extrabold text-uppercase" style="font-weight: 800; font-size: 14px; line-height: 1.1; letter-spacing: 0.5px; color: #1c273c;">TOKO MADURA</span>
                        <span class="fw-bold text-uppercase text-muted" style="font-weight: 700; font-size: 8.5px; letter-spacing: 0.8px;">ADMIN PANEL</span>
                    </div>
                </a>
            </div>
        </div>
        <div class="main-header-center">
            <div class="responsive-logo">
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                    <div class="d-flex align-items-center justify-content-center shadow-sm" style="background: #7D0A0A; color: #ffffff; width: 30px; height: 30px; border-radius: 8px; font-size: 15px;">
                        <i class="fa-sharp fa-solid fa-shop"></i>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-extrabold text-uppercase" style="font-weight: 800; font-size: 12px; line-height: 1.1; letter-spacing: 0.5px; color: #1c273c;">TOKO MADURA</span>
                        <span class="fw-bold text-uppercase text-muted" style="font-weight: 700; font-size: 8px; letter-spacing: 0.8px;">ADMIN PANEL</span>
                    </div>
                </a>
            </div>
        </div>
        <div class="main-header-right">
            <button class="navbar-toggler navresponsive-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4"
                aria-expanded="false" aria-label="Toggle navigation">
                <i class="fe fe-more-vertical header-icons navbar-toggler-icon"></i>
            </button><!-- Navresponsive closed -->
            <div
                class="navbar navbar-expand-lg  nav nav-item  navbar-nav-right responsive-navbar navbar-dark  ">
                <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                    <div class="d-flex order-lg-2 ms-auto">
                        <!-- Theme-Layout -->
                        <div class="dropdown d-flex main-header-theme">
                            <a class="nav-link icon layout-setting">
                                <span class="dark-layout">
                                    <i class="fe fe-sun header-icons"></i>
                                </span>
                                <span class="light-layout">
                                    <i class="fe fe-moon header-icons"></i>
                                </span>
                            </a>
                        </div>
                        <!-- Theme-Layout -->
                        <!-- Full screen -->
                        <div class="dropdown ">
                            <a class="nav-link icon full-screen-link">
                                <i class="fe fe-maximize fullscreen-button fullscreen header-icons"></i>
                                <i class="fe fe-minimize fullscreen-button exit-fullscreen header-icons"></i>
                            </a>
                        </div>
                        <!-- Full screen -->
                        <!-- Profile -->
                        <div class="dropdown main-profile-menu">
                            <a class="d-flex" href="javascript:void(0);">
                                <span class="main-img-user"><img alt="avatar" src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/favicon.ico"></span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="header-navheading">
                                    <h6 class="main-notification-title"><?= $user['ADM_NAME'] ?></h6>
                                    <p class="main-notification-text"><?= $user['ADMROLE_NAME'] ?? '' ?></p>
                                </div>
                                <a class="dropdown-item border-top" href="<?= SystemInfo::app('ADMIN_URL') ?>/password/view">
                                    <i class="fe fe-settings"></i> Password
                                </a>
                                <a class="dropdown-item" href="<?= SystemInfo::app('ADMIN_URL') ?>/logout">
                                    <i class="fe fe-power"></i> Sign Out
                                </a>
                            </div>
                        </div>
                        <!-- Profile -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>