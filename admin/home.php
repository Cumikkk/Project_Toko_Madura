<?php
require_once __DIR__ . "/../config/setting.php";
use App\Models\Helper;
use App\Models\Admin;
use App\Models\CompanyProfile;
use Config\Core\Database;
use App\Factory\AdminPermissionFactory;
use Config\Core\SystemInfo;

$queryParam = Helper::getSafeInput($_GET);
$page = $queryParam['a'] ?? "";
if($page == "logout") {
    Admin::logout();
    die("<script>location.href = '" . SystemInfo::app('ADMIN_URL') . "';</script>");
}

/** Authentication */
$user = Admin::authentication();
if(empty($user)) {
    die("<script>alert('Invalid Session, please re-login'); location.href = '/';</script>");
}

/** update token expired */
$userid = md5(md5($user['ADM_ID']));
$newExpired = date("Y-m-d H:i:s", strtotime("+1 hour"));
Database::update("tb_admin", ['ADM_TOKEN_EXPIRED' => $newExpired], ['ADM_ID' => $user['ADM_ID']]);

/** Permission */
$adminPermissionCore = AdminPermissionFactory::adminPermissionCore();
$getAuthrorizedPermissions = $adminPermissionCore->getAuthrorizedPermissions($user['ID_ADM']);
$filePermission = $adminPermissionCore->hasPermission($getAuthrorizedPermissions);
?>
<!DOCTYPE html>
<html lang="en">
	<head>

        <meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
        <meta name="description" content="<?= CompanyProfile::$name ?>">
        <meta name="author" content="<?= CompanyProfile::$name ?>">
        <meta name="keywords" content="<?= CompanyProfile::$name ?>">
        
        <!-- TITLE -->
        <title> <?= ucwords($page) ?> - <?= CompanyProfile::$name ?></title>

        <!-- FAVICON -->
        <link rel="icon" type="image/svg+xml" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/favicon.svg">
        <link rel="shortcut icon" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/favicon.svg">
        <link rel="manifest" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/favicon/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">

		<!-- BOOTSTRAP CSS -->
		<link  id="style" href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<!-- ICONS CSS -->
		<link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/all.min.css">
		<link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/sharp-solid.min.css">
		<link rel="stylesheet" href="<?= SystemInfo::app('CLIENT_URL') ?>/assets/vendor/css/sharp-regular.min.css">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/icons.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/web-fonts/plugin.css" rel="stylesheet">

		<!-- STYLE CSS -->
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/css/style.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/css/custom.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/css/plugins.css" rel="stylesheet">

		<!-- SWITCHER CSS -->
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/switcher/css/switcher.css" rel="stylesheet">
		<link href="<?= SystemInfo::app('ADMIN_URL') ?>/assets/switcher/demo.css" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

        <!-- JQUERY JS -->
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/vendor/js/jquery-3.6.0.min.js"></script>              
		<!-- BOOTSTRAP JS -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        <script>
            // Ajax & Link path prefix resolver for local subfolder deployment
            (function() {
                var adminUrl = '<?= SystemInfo::app("ADMIN_URL") ?>';
                var pathPrefix = new URL(adminUrl).pathname;
                
                // Prefilter for all jQuery AJAX requests (Datatables, form posts, etc.)
                $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                    if (options.url.indexOf('/ajax/') === 0) {
                        options.url = adminUrl + options.url;
                    }
                });

                // Intercept document clicks to fix absolute links
                $(document).ready(function() {
                    $(document).on('click', 'a', function(e) {
                        var href = $(this).attr('href');
                        if (href) {
                            // Check if href starts with /admin or /developer
                            if (href.indexOf('/admin') === 0 || href.indexOf('/developer') === 0) {
                                e.preventDefault();
                                window.location.href = adminUrl + href;
                            }
                        }
                    });
                });
            })();
        </script>
    </head>
    <body class="ltr main-body leftmenu">
        <!-- LOADEAR -->
		<!-- <div id="global-loader">
			<img src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/img/loader.svg" class="loader-img" alt="Loader">
		</div> -->

        <!-- PAGE -->
        <div class="page">
			<?php require_once __DIR__ . "/template/header.php"; ?>
            <?php require_once __DIR__ . "/template/sidebar.php"; ?>
            <?php require_once __DIR__ . "/template/content.php"; ?>
            <?php require_once __DIR__ . "/template/footer.php"; ?>
        </div>
        <!-- END PAGE -->

        <!-- Toast Container -->
        <div id="toast-container" class="toast-container position-fixed top-0 end-0 py-4 p-3 mt-5"></div>
		<a href="#top" id="back-to-top"><i class="fe fe-arrow-up"></i></a>
        
        <?php if($page == 'dashboard'){ ?>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
        <?php }; ?>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/jquery-ui/ui/widgets/datepicker.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap/js/popper.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/sidemenu/sidemenu.js" id="leftmenu"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/sidebar/sidebar.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/select2/js/select2.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/select2.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/dataTables.buttons.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/buttons.bootstrap5.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/jszip.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/pdfmake/pdfmake.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/pdfmake/vfs_fonts.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/buttons.html5.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/buttons.print.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/js/buttons.colVis.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/dataTables.responsive.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/datatable/responsive.bootstrap5.min.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/table-data.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/jquery.maskedinput/jquery.maskedinput.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/form-elements.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/jquery-steps/jquery.steps.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/spectrum-colorpicker/spectrum.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/amazeui-datetimepicker/js/amazeui.datetimepicker.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/ion-rangeslider/js/ion.rangeSlider.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/accordion-Wizard-Form/jquery.accordion-wizard.min.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/form-wizard.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/form-layouts.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/sticky.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/themeColors.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/custom.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/switcher/js/switcher.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fileuploads/js/fileupload.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fileuploads/js/file-upload.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fancyuploder/jquery.ui.widget.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fancyuploder/jquery.fileupload.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fancyuploder/jquery.iframe-transport.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fancyuploder/jquery.fancy-fileupload.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/fancyuploder/fancy-uploader.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/picturefill.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lightgallery.js"></script>
		<script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lightgallery-1.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lg-pager.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lg-autoplay.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lg-fullscreen.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lg-zoom.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lg-hash.js"></script>
        <script src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/plugins/gallery/lg-share.js"></script>
        <script type="module" src="<?= SystemInfo::app('ADMIN_URL') ?>/assets/js/firebase.js?dt=<?= time(); ?>"></script>

        <script type="text/javascript">
            $.fn.dataTable.ext.errMode = function(e, settings, message) {
                if(e.jqXHR.status == 403) {
                    Swal.fire("Session Expired", "Your session has expired. Please login again.", "warning").then(() => {
                        location.href = '/';
                    });
                    return;
                }

                alert("DataTable Error: " + message)
            };
        </script>

		<script type="text/javascript">
            $(document).ready(function() {
                $('.nav-item').removeClass('active show');
                $('.nav-sub-item').removeClass('active');
                $('.nav-sub-link').removeClass('active');
                
                let currentPath = window.location.pathname;
                let adminUrlPath = new URL('<?= SystemInfo::app("ADMIN_URL") ?>', window.location.origin).pathname;
                let relativePath = currentPath.substring(adminUrlPath.length);
                let segments = relativePath.split('/').filter(Boolean);
                let primaryModule = segments[0] ? segments[0].toLowerCase() : '';
                let secondaryModule = segments[1] ? segments[1].toLowerCase() : '';
                
                $.each($('.menu-nav .nav-link'), (i, el) => {
                    let target = $(el);
                    let href = target.attr('href');
                    if (!href) return;
                    
                    let hrefPath = new URL(href, window.location.origin).pathname;
                    let relativeHref = hrefPath.substring(adminUrlPath.length);
                    let hrefSegments = relativeHref.split('/').filter(Boolean);
                    let hrefPrimary = hrefSegments[0] ? hrefSegments[0].toLowerCase() : '';
                    let hrefSecondary = hrefSegments[1] ? hrefSegments[1].toLowerCase() : '';
                    
                    if (target.hasClass('with-sub')) {
                        let hasActiveSub = false;
                        target.parent().find('.nav-sub-link').each((subIdx, subEl) => {
                            let subHref = $(subEl).attr('href');
                            if (subHref) {
                                let subHrefPath = new URL(subHref, window.location.origin).pathname;
                                let subRelativeHref = subHrefPath.substring(adminUrlPath.length);
                                let subHrefSegments = subRelativeHref.split('/').filter(Boolean);
                                let subHrefPrimary = subHrefSegments[0] ? subHrefSegments[0].toLowerCase() : '';
                                let subHrefSecondary = subHrefSegments[1] ? subHrefSegments[1].toLowerCase() : '';
                                
                                if (subHrefPrimary === primaryModule && subHrefSecondary === secondaryModule) {
                                    $(subEl).addClass('active');
                                    $(subEl).parent().addClass('active');
                                    hasActiveSub = true;
                                }
                            }
                        });
                        
                        if (hasActiveSub) {
                            target.parent().addClass('active show');
                        }
                    } else {
                        if (hrefPrimary === primaryModule) {
                            target.parent().addClass('active');
                        }
                    }
                });

				$('.amount-formatter').on('keyup', function(evt) {
					$(evt.currentTarget).val( formatter( $(evt.currentTarget).val() ) )
				})
            })

			function formatter(angka, prefix = null){
				var number_string = angka.replace(/[^\.\d]/g, '').toString(),
				split   		= number_string.split('.'),
				sisa     		= split[0].length % 3,
				rupiah     		= split[0].substr(0, sisa),
				ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
				// tambahkan titik jika yang di input sudah menjadi angka ribuan
				if(ribuan){
					separator = sisa ? ',' : '';
					rupiah += separator + ribuan.join(',');
				}

				rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
				return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
			}
			

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then((registration) => {
                        console.log('Service Worker berhasil didaftarkan dengan scope:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('Pendaftaran Service Worker gagal:', error);
                    });
                });
            }
        </script>
    </body>
</html>