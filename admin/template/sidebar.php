<?php
    $login_page = $page;
?>
<div class="sticky">
    <div class="main-menu main-sidebar main-sidebar-sticky side-menu">
        <div class="main-sidebar-header main-container-1 active">
            <div class="sidemenu-logo">
                <a class="main-logo" href="#">
                    <img src="/assets/img/logo-black4.png" class="header-brand-img desktop-logo" alt="logo">
                    <img src="/assets/img/favicon/favicon.ico" class="header-brand-img icon-logo" alt="logo">
                    <!-- <img src="/assets/img/brand/logo.png" class="header-brand-img desktop-logo theme-logo" alt="logo">
                    <img src="/assets/img/brand/logo.png" class="header-brand-img icon-logo theme-logo" alt="logo"> -->
                </a>
            </div>
            <div class="main-sidebar-body main-body-1">
                <div class="slide-left disabled" id="slide-left"><i class="fe fe-chevron-left"></i></div>
                <ul class="menu-nav nav">
                    <li class="nav-header"><span class="nav-label">Dashboard</span></li>
                    <?php foreach($getAuthrorizedPermissions as $group) : ?>
                        <?php if($group['type'] == "single") : ?>
                            <?php foreach($group['modules'] as $module) : ?>
                                <?php foreach($module['permission'] as $permission) : ?>
                                    <?php if($permission['code'] == "view" && $module['visible'] == -1 && $permission['status']) : ?>
                                        <li class="nav-item <?= ($module['module'] == $login_page)? "active" : ""; ?>">
                                            <a class="nav-link" href="<?= $permission['link'] ?>">
                                                <span class="shape1"></span>
                                                <span class="shape2"></span>
                                                <i class="<?= $group['icon'] ?>"></i>
                                                <span class="sidemenu-label"><?= $module['alias'] ?></span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>

                        <?php elseif($group['type'] == "dropdown") : ?>
                            <li class="nav-item">
                                <a class="nav-link with-sub" href="javascript:void(0);">
                                    <span class="shape1"></span>
                                    <span class="shape2"></span>
                                    <i class="<?= $group['icon'] ?>"></i>
                                    <span class="sidemenu-label"><?= ucwords(str_replace("-", " ", $group['group'])); ?></span>
                                    <i class="angle fe fe-chevron-right"></i>
                                </a>
                                <ul class="nav-sub">
                                    <?php foreach($group['modules'] as $module) : ?>
                                        <?php foreach($module['permission'] as $permission) : ?>
                                            <?php if($permission['code'] == "view" && $module['visible'] == -1 && $permission['status']) : ?>
                                                <li class="nav-sub-item"><a class="nav-sub-link" href="<?= $permission['link'] ?>"><?= $module['alias'] ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>

                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="slide-right" id="slide-right"><i class="fe fe-chevron-right"></i></div>
            </div>
        </div>
    </div>
</div>