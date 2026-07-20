<div class="main-header side-header sticky">
    <div class="main-container container-fluid">
        <div class="main-header-left">
            <a class="main-header-menu-icon" href="javascript:void(0);" id="mainSidebarToggle"><span></span></a>
            <div class="hor-logo">
                <a class="main-logo" href="index.php">
                    <img src="/assets/img/brand/logo.png" width="75%" class="header-brand-img desktop-logo" alt="logo">
                    <img src="/assets/img/brand/logo-light.png" width="75%" class="header-brand-img desktop-logo-dark" alt="logo">
                </a>
            </div>
        </div>
        <div class="main-header-center">
            <div class="responsive-logo">
                <a href="/dashboard"><img src="/assets/img/logo-rrfx3.png" class="mobile-logo" alt="logo"></a>
                <a href="/dashboard"><img src="/assets/img/logo-rrfx-white1.png" class="mobile-logo-dark" alt="logo"></a>
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
                                <span class="main-img-user"><img alt="avatar" src="/assets/img/favicon/favicon.ico"></span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="header-navheading">
                                    <h6 class="main-notification-title"><?= $user['ADM_NAME'] ?></h6>
                                    <p class="main-notification-text"><?= $user['ADMROLE_NAME'] ?></p>
                                </div>
                                <a class="dropdown-item border-top" href="/password/view">
                                    <i class="fe fe-settings"></i> Password
                                </a>
                                <a class="dropdown-item" href="/logout">
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