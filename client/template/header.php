<?php

use App\Models\FileUpload;
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

<!-- header start -->
<div class="header">
    <div class="row g-0 align-items-center">
        <div class="col-xxl-6 col-xl-5 col-4 d-flex align-items-center gap-20">
            <div class="main-logo d-lg-block d-none">
                <div class="logo-big">
                    <a href="/dashboard">
                        <img src="/assets/images/logo-full-first-state-futures.svg" class="w-100 light-theme" alt="Logo">
                        <img src="/assets/images/logo-full-first-state-futures-light.svg" class="w-100 dark-theme" alt="Logo">
                    </a>
                </div>
                <div class="logo-small">
                    <a href="/dashboard">
                        <img src="/assets/images/favicon/ms-icon-144x144.png" alt="Logo">
                    </a>
                </div>
            </div>
            <div class="nav-close-btn">
                <button id="navClose"><i class="fa-light fa-bars-sort"></i></button>
            </div>
        </div>
        <div class="col-4 d-lg-none">
            <div class="mobile-logo">
                <a href="/dashboard">
                    <img src="/assets/images/logo-full-first-state-futures.svg" class="w-100 light-theme" alt="Logo">
                    <img src="/assets/images/logo-full-first-state-futures-light.svg" class="w-100 dark-theme" alt="Logo">
                </a>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-7 col-lg-8 col-4">
            <div class="header-right-btns d-flex justify-content-end align-items-center">
                <div class="header-collapse-group">
                    <div class="header-right-btns d-flex justify-content-end align-items-center p-0">
                        <div class="header-right-btns d-flex justify-content-end align-items-center p-0">
                            <div class="header-btn-box">
                                <div class="dropdown">
                                    <button class="header-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <i class="fa-light fa-calculator"></i>
                                    </button>
                                    <ul class="dropdown-menu calculator-dropdown">
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
                            <button class="header-btn fullscreen-btn" id="btnFullscreen"><i class="fa-light fa-expand"></i></button>
                        </div>
                    </div>
                </div>
                <button class="header-btn header-collapse-group-btn d-lg-none"><i class="fa-light fa-ellipsis-vertical"></i></button>
                <button class="header-btn theme-settings-btn d-lg-none"><i class="fa-light fa-gear"></i></button>
                <div class="header-btn-box profile-btn-box">
                    <button class="" data-bs-toggle="dropdown" aria-expanded="false" style="border: 0px; background: transparent;">
                        <div class="custom-avatar-container" style="width: 40px; height: 40px;">
                            <img class="custom-avatar" style="width: 40px; height: 40px;" src="<?= App\Models\User::avatar($user['MBR_AVATAR']); ?>" alt="image">
                        </div>
                    </button>
                    <ul class="dropdown-menu profile-dropdown-menu">
                        <li>
                            <div class="dropdown-txt text-center">
                                <p class="mb-0"><?php echo $user['MBR_NAME']; ?></p>
                                <!-- <span class="d-block">Web Developer</span> -->
                                <div class="d-flex justify-content-center">
                                    <div class="form-check pt-3">
                                        <input class="form-check-input" type="checkbox" id="seeProfileAsSidebar">
                                        <label class="form-check-label" for="seeProfileAsSidebar">See as sidebar</label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="/personal-information"><span class="dropdown-icon"><i class="fa-regular fa-circle-user"></i></span> Profile</a></li>
                        <li><a class="dropdown-item" href="/help-center"><span class="dropdown-icon"><i class="fa-regular fa-circle-question"></i></span> Help</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><span class="dropdown-icon"><i class="fa-regular fa-arrow-right-from-bracket"></i></span> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
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
                    <a class="dropdown-item" href="/personal-information"><span class="dropdown-icon"><i class="fa-regular fa-circle-user"></i></span> Profile</a>
                </li>
                <li>
                    <a class="dropdown-item" href="/help-center"><span class="dropdown-icon"><i class="fa-regular fa-circle-question"></i></span> Help</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="bottom-panel">
        <div class="button-group">
            <a href="/personal-information"><i class="fa-light fa-gear"></i><span>Settings</span></a>
            <a href="/logout"><i class="fa-light fa-power-off"></i><span>Logout</span></a>
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