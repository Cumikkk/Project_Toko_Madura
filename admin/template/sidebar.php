<?php
    use Config\Core\SystemInfo;
    $login_page = $page;
    $page_sub = $_GET['b'] ?? '';
?>
<div class="sticky">
    <div class="main-menu main-sidebar main-sidebar-sticky side-menu">
        <div class="main-sidebar-header main-container-1 active">
            <div class="sidemenu-logo" style="overflow: hidden; height: 64px; max-height: 64px; padding: 0 15px; display: flex; align-items: center; justify-content: flex-start; background: #0f141f;">
                <a class="main-logo" href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard" style="display: flex; align-items: center; gap: 12px; text-decoration: none; width: 100%; height: 100%;">
                    <div class="brand-icon" style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(6px); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 19px; color: #FFFFFF; border: 1px solid rgba(255, 255, 255, 0.25); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
                        <i class="fa-sharp fa-solid fa-shop"></i>
                    </div>
                    <div class="brand-text-container" style="display: flex; flex-direction: column;">
                        <span class="brand-title" style="color: #FFFFFF; font-weight: 800; font-size: 15px; letter-spacing: 0.8px; line-height: 1.1;">TOKO MADURA</span>
                        <span class="brand-subtitle" style="color: rgba(255, 255, 255, 0.75); font-weight: 700; font-size: 9px; letter-spacing: 1.2px;">ADMIN PANEL</span>
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