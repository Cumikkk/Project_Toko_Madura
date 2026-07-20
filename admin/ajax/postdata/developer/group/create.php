<?php

use Allmedia\Shared\AdminPermission\Core\AdminPermissionCore;
use App\Factory\PermissionGroupFactory;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$required = [
    'group_name' => "Nama Grup",
    'group_icon' => "Icon",
    'group_type' => "Tipe Grup"
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "{$text} diperlukan",
            'data'      => []
        ]);
    }
}

/** check tipe */
if(!in_array($data['group_type'], ['dropdown', 'single'])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Tipe grup tidak valid",
        'data'      => []
    ]);
}

/** Update */
$maxOrder = PermissionGroupFactory::init()->maxGroupId() ?? 0;
$insert = Database::insert("admin_module_group", [
    'order' => $maxOrder,
    'group' => $data['group_name'],
    'type' => $data['group_type'],
    'icon' => $data['group_icon'],
    'min_level' => 0
]);

if(!$insert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal memperbarui data grup",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "group",
    'message' => "Menambahkan group ".$data['group_name'],
    'data' => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Berhasil membuat grup",
    'data'      => []
]);