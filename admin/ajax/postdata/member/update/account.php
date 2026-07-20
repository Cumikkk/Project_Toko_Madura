<?php

use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/member/update/account")) {
    JsonResponse([
        'success' => false,
        'message' => "Permission Denied",
        'data' => []
    ]);
}

/** check user */
$userCode = Helper::form_input($_POST['code'] ?? "");
$userData = User::findByCode($userCode);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Userdata",
        'data' => []
    ]);
}

$maxAccount = Helper::form_input($_POST['max_account']);
if(is_numeric($maxAccount) === FALSE || $maxAccount < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid MaxAccount",
        'data' => []
    ]);
}

$allowallreferral = Helper::form_input($_POST['allowallreferral']);
if($allowallreferral != 1 && $allowallreferral != -1) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid AllowAllReferral",
        'data' => []
    ]);
}


/** account include */
$accountInclude = NULL;
if(!empty($_POST['account_include']) && is_array($_POST['account_include'])) {
    $accountInclude = implode(",", $_POST['account_include']);
}

/** account exclude */
$accountExclude = NULL; 
if(!empty($_POST['account_type']) && is_array($_POST['account_type'])) {
    $accountExclude = implode(",", $_POST['account_type']);
}

/** update */
$updateData = [
    'MBR_ACCMAX' => $maxAccount,
    'MBR_SUFFIX_EXCLUDE' => $accountExclude,
    'MBR_SUFFIX' => $accountInclude,
    'MBR_REFFERALALL' => $allowallreferral
];

$update = Database::update("tb_member", $updateData, ['MBR_ID' => $userData['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui data",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Data berhasil diperbarui",
    'data' => []
]);