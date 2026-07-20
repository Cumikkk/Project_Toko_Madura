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
foreach(['edit_m_id', 'edit_m_group', 'edit_m_name'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}

/** Group ID */
$groupid = 0;
$order = $data['edit_m_order'] ?? 1;
foreach($listGrup as $g) {
    if(md5(md5($g['id'])) == $data['edit_m_group']) {
        $groupid = $g['id'];
        break;
    }
}

if($groupid == 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Grup tidak ditemukan",
        'data'      => []
    ]);
}

/** Check Id */
$modul = PermissionModuleFactory::init()->findModuleById($data['edit_m_id']);
if(!$modul) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid ID",
        'data'      => []
    ]);
}

/** Check Status */
if(empty($data['edit_m_status']) && is_numeric($data['edit_m_status']) === FALSE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "edit_m_status field is required",
        'data'      => []
    ]);
}

if(!in_array($data['edit_m_status'], [0, -1])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid visible status",
        'data'      => []
    ]);
}

/** Check visibility */
if(empty($data['edit_m_visibility']) && is_numeric($data['edit_m_visibility']) === FALSE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "edit_m_visibility field is required",
        'data'      => []
    ]);
}

if(!in_array($data['edit_m_visibility'], [0, -1])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid visible status",
        'data'      => []
    ]);
}

/** Update */ 
$updateData = [
    'group_id'  => $groupid,
    'module' => $data['edit_m_name'],
    'status' => $data['edit_m_status'],
    'm_order' => $order,
    'visible' => $data['edit_m_visibility']
];

$update = Database::update("admin_module", $updateData, ['id' => $modul['id']]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Failed to update the module data",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "module",
    'message' => "Updated module : ".$data['edit_m_name'],
    'ip' => Helper::get_ip_address(),
    'data' => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Successfully renewed the module data",
    'data'      => []
]);