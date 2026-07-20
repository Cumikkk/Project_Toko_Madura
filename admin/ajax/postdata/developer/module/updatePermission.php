<?php

use App\Factory\PermissionModuleFactory;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

$listGrup = $adminPermissionCore->availableGroup();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/developer/module/update/*")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['code', 'module_id', 'permission_id', 'name', 'url'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Kolom {$req} diperlukan",
            'data' => []
        ]);
    }
}

/** check module */
$module = PermissionModuleFactory::init()->findModuleById($data['module_id']);
if(!$module) {
    JsonResponse([
        'success' => false,
        'message' => "ID Module tidak valid",
        'data' => []
    ]);
}

/** Check Permisson ID */
if(is_numeric($data['permission_id']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Permission ID tidak valid",
        'data' => []
    ]);
}

/** Check ID */
$permission = PermissionModuleFactory::init()->findPermissionById($data['permission_id']);
if(!$permission) {
    JsonResponse([
        'success' => false,
        'message' => "Permission ID tidak ditemukan",
        'data' => []
    ]);
}

/** check code */
if($data['code'] != $permission['code']) {
    if($adminPermissionCore->isHavePermission($module['id'], $data['code']) !== FALSE) {
        JsonResponse([
            'success' => false,
            'message' => "Kode permission sudah terdaftar",
            'data' => []
        ]);
    }
}

/** Insert */
$updateData = [
    'code' => $data['code'],
    'desc' => $data['name'],
    'url' => $data['url']
];

$update = Database::update("admin_permissions", $updateData, ['id' => $permission['id']]);
if(!$update) {
   JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui permission",
        'data' => []
    ]); 
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "module",
    'message' => "Updated permission ".$data['code'].", module ".$module['module'],
    'ip' => Helper::get_ip_address(),
    'data' => $data
]);


JsonResponse([
    'success' => true,
    'message' => "Permission $data[code] berhasil diperbarui",
    'data' => []
]);