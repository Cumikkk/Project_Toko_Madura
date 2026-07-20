<?php use Config\Core\SystemInfo; ?>

<!-- MAIN-CONTENT -->
<div class="main-content side-content pt-0">
    <div class="main-container container-fluid">
        <div class="inner-body">
            <?php if(!$filePermission) : ?>
                <?php require_once __DIR__ . "/403.php";  ?>

            <?php elseif(file_exists(CRM_ROOT . $filePermission['filepath'])) : ?>
                <?php 
                $moduleId = $filePermission['module_id'];
                require_once CRM_ROOT . $filePermission['filepath']; 
                ?>
                
            <?php else : ?>
                <?= SystemInfo::isDevelopment()? ("Unknown Path: " . $filePermission['filepath']) : "";  ?>
                <?php require_once __DIR__ . "/404.php";  ?>
                
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- END MAIN-CONTENT -->