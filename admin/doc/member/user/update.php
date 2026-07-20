<?php 

use App\Models\Helper;
use App\Models\User;
$userCode = Helper::form_input($_GET['d'] ?? "");
$userData = User::findByCode($userCode);
$currentUrl = $filePermission['link'] . "/{$userCode}";
$content = Helper::form_input($_GET['e'] ?? "personal-information"); 
if(!$userData) {
    die("<script>alert('Invalid Code'); location.href = '/member/user/view';</script>");
}

$menus = [
    'personal-information' => [
        'permission_code' => "view.update.personal_information",
        'title' => "Personal Information"
    ],
    'security' => [
        'permission_code' => "view.update.security",
        'title' => "Security"
    ],
    'change_email' => [
        'permission_code' => "view.update.change_email",
        'title' => "Change Email"
    ],
    'account' => [
        'permission_code' => "view.update.account",
        'title' => "Account"
    ],
    'refferal' => [
        'permission_code' => "view.update.refferal",
        'title' => "Refferal"
    ],
    'transaction' => [
        'permission_code' => "view.update.transaction",
        'title' => "Transaction"
    ],
    // 'report' => [
    //     'permission_code' => "view.update.report",
    //     'title' => "Report"
    // ],
    // 'log' => [
    //     'permission_code' => "view.update.log",
    //     'title' => "Log Aktifitas"
    // ]
];
?>

<style>
    table.no-border td {
        border: none !important;
    }
</style>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">User Update</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Member</a></li>
            <li class="breadcrumb-item"><a href="/member/user/view">User</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="/member/user/update/<?= $userCode ?>">Detail</a></li>
        </ol>
    </div>
</div>


<div class="row row-sm mb-4">
    <div class="col-xl-3 col-lg-12 col-md-3">
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="main-content-label"><?= $userData['MBR_NAME'] ?></h3>
            </div>
            <div class="card-body text-center item-user">
                <div class="d-flex justify-content-center">
                    <div class="custom-avatar-container">
                        <img class="custom-avatar" src="<?= User::avatar($userData['MBR_AVATAR']) ?>" alt="admin">
                        <!-- <label for="avatar" class="edit-icon"><i class="fas fa-camera"></i></label> -->
                    </div>
                    <!-- <input type="file" name="avatar" id="avatar" class="d-none"> -->
                </div>

                <!-- <script type="text/javascript">
                    $(document).ready(function() {
                        $('#avatar').on('change', function() {
                            const file = this.files
                            if(file.length) {
                                let fileReader = new FileReader()
                                fileReader.onload = (event) => {
                                    $('img[alt="admin"]').attr('src', event.target.result)
                                    // $('button[name="update-avatar"]').removeClass('d-none')
                                }
    
                                fileReader.readAsDataURL(file[0])
                            }
                        })
                    })
                </script> -->

                <a href="javascript:void(0);" class="text-dark"><h6 class="mt-3 mb-0 font-weight-semibold"><?= $userData['MBR_EMAIL'] ?></h6></a>
                <span><?= $userData['MBR_NAME'] ?></span>
            </div>

            <ul class="item1-links nav nav-tabs mb-0">
                <?php foreach($menus as $key => $menuu) : ?>
                    <li class="nav-item"><a class="nav-link <?= ($content == $key)? 'active' : '' ?>" href="<?= $currentUrl . "/{$key}" ?>"><?= $menuu['title'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-xl-9 col-lg-12 col-md-9">
        <?php if(in_array($content, array_keys($menus))) : ?>
            <?php if($subPermission = $adminPermissionCore->isHavePermission($moduleId, $menus[ $content ]['permission_code'])) : ?>
                <?php if(file_exists(__DIR__ . "/view_update/{$content}.php")) : ?>
                    <?php require_once __DIR__ . "/view_update/{$content}.php"; ?>
                <?php endif; ?>
            <?php else : ?>
                <h5 class="text-center">[403] Permission Denied</h5>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
