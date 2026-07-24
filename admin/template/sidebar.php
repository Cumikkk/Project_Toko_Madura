<?php
    use Config\Core\SystemInfo;
    $login_page = $page;
    $page_sub = $_GET['b'] ?? '';
?>
<div class="sticky">
    <div class="main-menu main-sidebar main-sidebar-sticky side-menu">
        <div class="main-sidebar-header main-container-1 active">
            <div class="sidemenu-logo" style="overflow: hidden; height: 64px; max-height: 64px; padding: 0 15px; display: flex; align-items: center; justify-content: flex-start; background: #0f141f;">
                <a class="main-logo" href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard" style="display: flex; align-items: center; gap: 10px; text-decoration: none; width: 100%; height: 100%;">
                    <div class="d-flex align-items-center justify-content-center shadow-sm" style="background: #7D0A0A; color: #ffffff; min-width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.2); font-size: 18px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 100 100" fill="none">
                            <polygon points="50,16 16,42 84,42" fill="#ffffff"/>
                            <rect x="22" y="42" width="56" height="44" rx="2" fill="#ffffff"/>
                            <rect x="40" y="56" width="20" height="30" rx="2" fill="#7D0A0A"/>
                        </svg>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-extrabold text-uppercase text-white" style="font-weight: 800; font-size: 14px; line-height: 1.1; letter-spacing: 0.5px;">TOKO MADURA</span>
                        <span class="fw-bold text-uppercase text-muted" style="font-weight: 700; font-size: 8.5px; letter-spacing: 0.8px;">ADMIN PANEL</span>
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