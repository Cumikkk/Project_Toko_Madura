<?php
    use Config\Core\SystemInfo;
    $login_page = $page;
    $page_sub = $_GET['b'] ?? '';
?>
<div class="sticky">
    <div class="main-menu main-sidebar main-sidebar-sticky side-menu">
        <div class="main-sidebar-header main-container-1 active">
            <div class="sidemenu-logo">
                <a class="brand-link" href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">
                    <div class="brand-icon">
                        <i class="fa-sharp fa-solid fa-shop"></i>
                    </div>
                    <div class="brand-text-container">
                        <span class="brand-title">TOKO MADURA</span>
                        <span class="brand-subtitle">ADMIN PANEL</span>
                    </div>
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