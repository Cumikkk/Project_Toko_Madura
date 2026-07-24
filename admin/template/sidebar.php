<?php
    use Config\Core\SystemInfo;
    $login_page = $page;
    $page_sub = $_GET['b'] ?? '';
?>
<div class="sticky">
    <div class="main-menu main-sidebar main-sidebar-sticky side-menu">
        <div class="main-sidebar-header main-container-1 active">
            <div class="sidemenu-logo" style="overflow: hidden; height: 64px; max-height: 64px; padding: 0 15px; display: flex; align-items: center; justify-content: flex-start; background: #0f141f;">
                <a class="main-logo" href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard" style="display: flex; align-items: center; justify-content: flex-start; width: 100%; height: 100%;">
                    <img src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/toko_madura_white_text_dark_bg.jpg" class="header-brand-img desktop-logo" style="max-height: 46px; height: 46px; width: auto; max-width: 100%; object-fit: contain; object-position: left center;" alt="Toko Madura Logo">
                    <img src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/toko_madura_white_text_dark_bg.jpg" class="header-brand-img icon-logo" style="max-height: 36px; height: 36px; width: auto; object-fit: contain;" alt="Toko Madura Icon">
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
                                    <?php if(strcasecmp($permission['code'], "view") == 0 && ($module['visible'] == -1 || $module['visible'] == 1) && ($permission['status'] == -1 || $permission['status'] == 1)) : ?>
                                        <li class="nav-item <?= (strcasecmp($module['module'], $login_page) == 0)? "active" : ""; ?>">
                                            <a class="nav-link" href="<?= SystemInfo::app('ADMIN_URL') . $permission['link'] ?>">
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
                            <?php
                            $isGroupActive = false;
                            if (strcasecmp($group['group'], $login_page) == 0) {
                                $isGroupActive = true;
                            } else {
                                foreach ($group['modules'] as $mod) {
                                    if (strcasecmp($mod['module'], $login_page) == 0) {
                                        $isGroupActive = true;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <li class="nav-item <?= $isGroupActive ? "active show" : ""; ?>">
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
                                            <?php if(strcasecmp($permission['code'], "view") == 0 && ($module['visible'] == -1 || $module['visible'] == 1) && ($permission['status'] == -1 || $permission['status'] == 1)) : ?>
                                                <li class="nav-sub-item <?= (strcasecmp($module['module'], $login_page) == 0 || strcasecmp($module['module'], $page_sub) == 0)? "active" : ""; ?>">
                                                    <a class="nav-sub-link" href="<?= SystemInfo::app('ADMIN_URL') . $permission['link'] ?>"><?= $module['alias'] ?></a>
                                                </li>
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