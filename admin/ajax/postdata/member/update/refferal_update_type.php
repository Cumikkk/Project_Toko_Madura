<?php

use App\Models\Helper;
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


/** check type */
$type = Helper::form_input($_POST['type'] ?? '');
$structure = SalesStructure::findByIdHash($type);
if(!$structure) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

/** check uplinen */
$upline = User::findByMemberId($userData['MBR_IDSPN']);
if($upline) {
    $uplineType = SalesStructure::findById($upline['MBR_TYPE']);
    if(!$uplineType) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Upline Type",
            'data' => []
        ]);
    }

    if($structure['ID_SLSSTRC'] <= $upline['MBR_TYPE']) {
        JsonResponse([
            'success' => false,
            'message' => "Tipe tidak valid karena upline adalah " . $uplineType['SLSSTRC_NAME'],
            'data' => []
        ]);
    }
}

$update = Database::update("tb_member", ['MBR_TYPE' => $structure['ID_SLSSTRC']], ['MBR_ID' => $userData['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);