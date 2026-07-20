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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucwords($authPage) ?> | <?= CompanyProfile::$name ?></title>
    <meta name="description" content="<?= CompanyProfile::$name ?>"/>
    <link rel="icon" type="image/png" sizes="16x16" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/images/favicon/favicon.ico">
    <link rel="manifest" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= SystemInfo::app('CLIENT_URL') ?>/assets/images/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex, nofollow">

    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/all.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/css/custom.css">
    <link rel="stylesheet" id="primaryColor" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/css/gold-color.css">
    <link rel="stylesheet" id="rtlStyle" href="#">
	<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="light-theme">

    <!-- main content start -->
	<?php require_once __DIR__ . "/auth/$authPage.php"; ?>
    <!-- main content end -->
    
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/jquery.overlayScrollbars.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/bootstrap.bundle.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/js/main.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/js/custom.js"></script>
    <!-- for demo purpose -->
    <script>
        var rtlReady = $('html').attr('dir', 'ltr');
        if (rtlReady !== undefined) {
            localStorage.setItem('layoutDirection', 'ltr');
        }
    </script>
    <!-- for demo purpose -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
            navigator.serviceWorker.register('/service-worker.js')
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