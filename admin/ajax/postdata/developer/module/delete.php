<?php

use App\Factory\PermissionModuleFactory;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

$listGrup = $adminPermissionCore->availableGroup();
if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
if(empty($data['delete_m_id'])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID module diperlukan",
        'data'      => []
    ]);
}

if(empty($data['password'])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Password diperlukan",
        'data'      => []
    ]);
}

/** Validasi password */
if(!password_verify($data['password'], $user['ADM_PASS'])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Password Salah",
        'data'      => []
    ]);
}

/** Get Module */
$modul = PermissionModuleFactory::init()->findModuleById($data['delete_m_id']);
if(!$modul) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Modul tidak ditemukan",
        'data'      => []
    ]);
}

/** Delete admin_module */
$deleteModule = Database::delete("admin_module", ['id' => $modul['id']]);
if(!$deleteModule) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Failed to delete module",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "module",
    'message' => "Delete module : ".$modul['module'],
    'ip' => Helper::get_ip_address(),
    'data' => $modul
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Module ".$modul['module']." successfully deleted",
    'data'      => []
]);