<?php 
require_once __DIR__ . "/../config/setting.php";

use App\Models\CompanyProfile;
use App\Models\Helper;
use Config\Core\SystemInfo;

$queryParam = Helper::getSafeInput($_GET);
$authPage = $queryParam['a'] ?? "";
if(empty($authPage)) {
	$authPage = "signin";
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
        <meta name="description" content="<?= CompanyProfile::$name; ?>">
        <meta name="author" content="<?= CompanyProfile::$name; ?>">
        <meta name="keywords" content="<?= CompanyProfile::$name; ?>">
        
        <!-- TITLE -->
        <title><?= ucwords($authPage) ?> - <?= CompanyProfile::$name ?></title>

        <!-- FAVICON -->
        <link rel="icon" type="image/svg+xml" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/favicon.svg">
        <link rel="shortcut icon" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/favicon.svg">
        <link rel="manifest" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">

		<!-- ICONS CSS -->
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/icons.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/plugin.css" rel="stylesheet">
		<link rel="stylesheet" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/vendor/css/all.min.css">
		<link rel="stylesheet" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/vendor/css/sharp-solid.min.css">
		<link rel="stylesheet" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/vendor/css/sharp-regular.min.css">

		<!-- BOOTSTRAP CSS -->
		<link  id="style" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<!-- ICONS CSS -->
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/icons.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/plugin.css" rel="stylesheet">

		<!-- STYLE CSS -->
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/css/style.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/css/plugins.css" rel="stylesheet">

		<!-- SWITCHER CSS -->
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/switcher/css/switcher.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/switcher/demo.css" rel="stylesheet">

        <!-- JQUERY JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
        <!-- sweetalert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </head>

    <body class="ltr main-body leftmenu error-1">
        <!-- LOADEAR -->
		<!-- <div id="global-loader">
			<img src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/loader.svg" class="loader-img" alt="Loader">
		</div> -->
		<!-- END LOADEAR -->

        <!-- END PAGE -->
        <div class="page main-signin-wrapper">
            <?php require_once __DIR__ . "/auth/$authPage.php"; ?>
        </div>
		<!-- END PAGE -->

        <!-- SCRIPTS -->
		<!-- BOOTSTRAP JS -->
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap/js/popper.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

		<!-- PERFECT SCROLLBAR JS -->
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>

		<!-- SELECT2 JS -->
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/select2/js/select2.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/select2.js"></script>
        
        <!-- COLOR THEME JS -->
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/themeColors.js"></script>

        <!-- CUSTOM JS -->
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/custom.js"></script>
		<script>
			
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                navigator.serviceWorker.register('service-worker.js')
                    .then(function(registration) {
                    console.log('Service Worker registered with scope:', registration.scope);
                    }, function(err) {
                    console.error('Service Worker registration failed:', err);
                    });
                });
            } else {
                console.log('Service Worker is not supported in this browser.');
            }
		</script>
    </body>
</html>
