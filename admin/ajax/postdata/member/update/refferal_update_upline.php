<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\SalesStructure;
use App\Models\User;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/member/update/refferal")) {
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

/** check upline */
$uplineCode = Helper::form_input($_POST['upline'] ?? "");
$upline = User::findByCode($uplineCode);
if(!$upline) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Upline",
        'data' => []
    ]);
}

/** check sales level */
$mySales = SalesMain::getUserType($userData['MBR_TYPE']);
$uplineSales = SalesMain::getUserType($upline['MBR_TYPE']);
if($mySales && !$uplineSales) {
    JsonResponse([
        'success' => false,
        'message' => "Anda tidak bisa mendaftarkan " . ($mySales->salesDetail['SLSSTRC_NAME'] ?? "-") . " dibawah Trader",
        'data' => []
    ]);
}

/** check sales level */
if($mySales && $uplineSales) {
    if($mySales->level() <= $uplineSales->level() || $mySales->salesDetail['SLSSTRC_DIV'] != $uplineSales->salesDetail['SLSSTRC_DIV']) {
        JsonResponse([
            'success' => false,
            'message' => "Anda tidak bisa mendaftarkan " . ($mySales->salesDetail['SLSSTRC_NAME'] ?? "-") . " dibawah " . ($uplineSales->salesDetail['SLSSTRC_NAME'] ?? "-"),
            'data' => []
        ]);
    }
}

$logMessage = sprintf('Change upline user %s from %s to %s', 
    $userData['MBR_EMAIL'], 
    $userData['MBR_IDSPN'], 
    $upline['MBR_ID']
);

/** update upline */
$update = Database::update("tb_member", ['MBR_IDSPN' => $upline['MBR_ID']], ['MBR_ID' => $userData['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui upline",
        'data' => []
    ]);
}

Logger::admin_log([
    'mbrid' => $userData['MBR_ID'],
    'admid' => $user['ADM_ID'],
    'module' => "refferal_update_upline",
    'message' => $logMessage,
    'data' => [
        'before' => [
            'upline_id' => $userData['MBR_IDSPN'],
        ],
        'after' => [
            'upline_id' => $upline['MBR_ID'],
        ]
    ]
]);

JsonResponse([
    'success' => true,
    'message' => "Berhasil memperbarui upline",
    'data' => []
]);