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
foreach(['code', 'module_id', 'name', 'url'] as $req) {
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

/** check code */
if($adminPermissionCore->isHavePermission($module['id'], $data['code']) !== FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Kode permission sudah terdaftar",
        'data' => []
    ]);
}

/** Insert */
$insert = Database::insert("admin_permissions", [
    'module_id' => $module['id'],
    'code' => $data['code'],
    'desc' => $data['name'],
    'url' => $data['url']
]);

if(!$insert) {
   JsonResponse([
        'success' => false,
        'message' => "Gagal menambahkan permission",
        'data' => []
    ]); 
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "module",
    'message' => "Membuat permission ".$data['code'].", module ".$module['module'],
    'ip' => Helper::get_ip_address(),
    'data' => $data
]);

JsonResponse([
    'success' => true,
    'message' => "Permission $data[code] berhasil ditambahkan",
    'data' => []
]);