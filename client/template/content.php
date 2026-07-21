<!-- main content start -->
<div class="main-content">
    <?php
    use App\Library\Sales\SalesMain;
    use App\Models\Helper;
    use Config\Core\SystemInfo;

    switch($pageFile) {
        case "verif": 
            if($user['MBR_STS'] == 0) {
                $otpCode = md5(md5($user['MBR_ID'] . $user['ID_MBR']));
                die("<script>location.href = '/otp/{$otpCode}'</script>");
            }

            if($user['MBR_VERIF'] != -1) {
                $stepPage = Helper::form_input($_GET['b']);
                $user_step  = explode("-", $_GET['b'])[1] ?? $user['MBR_VERIF'];
                if($user_step > $user['MBR_VERIF']) {
                    die("<script>location.href = '/verif/step-".$user['MBR_VERIF']."'</script>");
                }

                $filename   = WEB_ROOT ."/doc/verif/$stepPage.php";
                file_exists($filename) 
                    ? include $filename
                    : include __DIR__ . "/404.php";
            }
            break;

        default: 
            if($user['MBR_STS'] != -1) die("<script>location.href = '/verif/step-1'; </script>");
            $getInput = array_filter($_GET, fn($key) => in_array($key, range('a', 'f'), true), ARRAY_FILTER_USE_KEY);
            $fileUrl = Allmedia\Shared\AdminPermission\Core\UrlParser::urlToPath(Helper::getSafeInput($getInput));
            $filename = WEB_ROOT ."/doc/$fileUrl.php";
            
            /** pengamanan untuk route /sales */
            if($getInput['a'] == "sales") {
                $salesData = SalesMain::getUserType($user['MBR_TYPE']);
                if(!$salesData) {
                    $filename = __DIR__ ."/"."404.php";
                }
            }

            if(!file_exists($filename)) {
                $filename = __DIR__ ."/"."404.php";
            }

            require_once $filename;
            break;
    }
    ?>
    
    <?php require_once __DIR__ . "/footer.php"; ?>
</div>
<!-- main content end -->

<div class="ini-modal-file">
    <style>
        .modal-backdrop {
            z-index: -1 !important;
        }
    </style>

    <?php 
        // create modal file at doc/modal/
        if(!empty($_SESSION['modal']) && is_array($_SESSION['modal'])) {
            foreach($_SESSION['modal'] as $modal) {
                if(file_exists(__DIR__ . "/modal/{$modal}.php")) {
                    require_once __DIR__ . "/modal/{$modal}.php";
                }
            }
        }  
    ?>
</div>

<div class="modal fade" id="dynamicModalDefault" style="background-color: #0000008a;">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<script type="module">
    import CallbackRegistry from '<?= SystemInfo::app('CLIENT_URL') ?>/assets/js/callback-registry.js';
    let dynamicModalDefault = document.getElementById('dynamicModalDefault')
    $(document).ready(function() {
        if(dynamicModalDefault) {
            $(dynamicModalDefault).on('show.bs.modal', async function (event) {
                let button = event.relatedTarget; // Button that triggered the modal
                let url = button.getAttribute('data-url');
                let title = button.getAttribute('data-title');
                let callback = button.getAttribute('data-callback');
                dynamicModalDefault.querySelector('.modal-title').textContent = title;
                dynamicModalDefault.querySelector('.modal-body').innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'; 

                await $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        dynamicModalDefault.querySelector('.modal-body').innerHTML = response;
                        if(callback && typeof CallbackRegistry[callback] === 'function') {
                            CallbackRegistry[callback](button);
                        }
                    },
                    error: function() {
                        dynamicModalDefault.querySelector('.modal-body').innerHTML = '<div class="alert alert-danger" role="alert">Failed to load content. Please try again.</div>';
                    }
                })
            });
        }
    })
</script>