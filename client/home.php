<?php

use Config\Core\SystemInfo;

require_once(__DIR__ . "/../config/setting.php");

use App\Models\CompanyProfile;
use App\Models\Logger;
use App\Models\User;

$pageFile = "404";
$pageTitle = "404";
if (!empty($_GET["a"])) {
    $pageFile = htmlentities(str_replace('%', '', str_replace(' ', '_', stripslashes(($_GET['a'])))), ENT_QUOTES, 'WINDOWS-1252');
    $pageTitle = ucwords(strtolower(str_replace('-', ' ', $pageFile)));
}

if ($pageFile == "logout") {
    User::logout();
    die("<script>location.href = '" . SystemInfo::app('CLIENT_URL') . "';</script>");
}

$user = User::user();
if (!$user) {
    die("<script>alert('Session Expired, please re-login'); location.href = '" . SystemInfo::app('CLIENT_URL') . "';</script>");
}

$userid = md5(md5($user['MBR_ID'])) ?? "";

$GETT = $_GET ?? [];
$POSTT = $_POST ?? [];
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => $pageFile,
    'message' => "Access Page {$pageFile}",
    'data' => [
        ...$GETT,
        ...$POSTT,
    ]
]);
?>

<!DOCTYPE html>
<html lang="en" data-menu="vertical" data-nav-size="nav-default">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= "{$pageTitle} - " . CompanyProfile::$name; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/images/favicon/favicon.ico">
    <link rel="manifest" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= SystemInfo::app('CLIENT_URL') ?>/assets/images/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex, nofollow">

    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/all.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/sharp-solid.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/sharp-regular.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/jquery-ui.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/jquery.uploader.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/dropzone.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/css/custom.css">
    <link rel="stylesheet" id="primaryColor" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/css/<?= ($user['MBR_THEME'] == '1') ? 'gold' : 'gold'; ?>-color.css">
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.css" integrity="sha512-In/+MILhf6UMDJU4ZhDL0R0fEpsp4D3Le23m6+ujDWXwl3whwpucJG1PEmI3B07nyJx+875ccs+yX2CqQJUxUw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" integrity="sha512-EZSUkJWTjzDlspOoPSpUFR0o0Xy7jdzW//6qhUkoZ9c4StFkVsp9fbbd0O06p9ELS3H486m4wmrCELjza4JEog==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js" integrity="sha512-8QFTrG0oeOiyWo/VM9Y8kgxdlCryqhIxVeRpWSezdRRAvarxVtwLnGroJgnVW9/XBRduxO/z1GblzPrMQoeuew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://sdk.amazonaws.com/js/aws-sdk-2.179.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
    <!-- Flag Icon -->

    <!--Date Range picker-->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style rel="stylesheet">
        <?php if ($user['MBR_THEME'] == '1') { ?>.dataTables_length select {
            color: white !important;
            background-color: #242526 !important;
        }

        <?php } else { ?>.dataTables_length select {
            background-color: white !important;
            color: #242526 !important;
        }

        <?php }; ?>
    </style>
</head>

<body class="body-padding body-p-top <?= ($user['MBR_THEME'] == '1') ? 'dark-theme' : 'light-theme'; ?>">
    <?php require_once __DIR__ . "/template/header.php"; ?>
    <?php require_once __DIR__ . "/template/sidebar.php"; ?>
    <?php require_once __DIR__ . "/template/content.php"; ?>

    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/jquery-ui.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/jquery.overlayScrollbars.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/jquery.dataTables.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/jquery.uploader.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/dropzone.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/sweetalert2.all.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/js/bootstrap.bundle.min.js"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/js/main.js?v=<?= time(); ?>"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/js/custom.js?v=<?= time(); ?>"></script>
    <script src="<?= SystemInfo::app('CLIENT_URL') ?>/assets/js/global.js?v=<?= filemtime(__DIR__ . "/assets/js/global.js") ?>"></script>
    <script>
        function format(item, state) {
            if (!item.id) {
                return item.text;
            }
            var countryUrl = "https://hatscripts.github.io/circle-flags/flags/";
            var stateUrl = "https://oxguy3.github.io/flags/svg/us/";
            var url = state ? stateUrl : countryUrl;
            var ctr = item.element?.dataset.content;
            var img = $("<img>", {
                class: "img-flag",
                width: 30,
                src: url + ctr.toLowerCase() + ".svg"
            });
            var span = $("<span>", {
                text: " " + item.text
            });
            span.prepend(img);
            return span;
        }
        $(document).ready(function() {
            $('.select2').select2({
                height: "resolve"
            });


            $("#cntry").select2({
                templateResult: function(item) {
                    return format(item, false);
                }
            });

            let datePickerInputs = $('.datepicker');
            $.each(datePickerInputs, (i, el) => {
                $(el).datepicker({
                    showOtherMonths: true,
                    selectOtherMonths: true,
                    dateFormat: 'yy-mm-dd',
                    maxDate: $(el).data('max')
                });
            })
        })

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= SystemInfo::app('CLIENT_URL') ?>/service-worker.js')
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