<?php
use App\Models\Admin;
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
foreach(['m_group', 'm_name'] as $req) {
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
$group = false;
foreach($listGrup as $g) {
    if(md5(md5($g['id'])) == $data['m_group']) {
        $groupid = $g['id'];
        $group = $g;
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

/** Check visibility */
if(empty($data['m_visibility']) && is_numeric($data['m_visibility']) === FALSE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "m_visibility field is required",
        'data'      => []
    ]);
}

if(!in_array($data['m_visibility'], [0, -1])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid visible status",
        'data'      => []
    ]);
}

$db = Database::connect();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Insert admin_module */ 
$insert  = Database::insert("admin_module", [
    'group_id' => $groupid,
    'module' => $data['m_name'],
    'status' => -1,
    'visible' => $data['m_visibility']
]);

if(!$insert) {
    $db->rollback();
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal membuat modul baru",
        'data'      => []
    ]);
}

/** Insert Permission */
$moduleId = $db->insert_id;
$permissions = $adminPermissionCore->adminPermission;
foreach($permissions as $perm) {
    $insertPermission = Database::insert("admin_permissions", [
        'module_id' => $moduleId,
        'code' => $perm,
        'desc' => "{$perm} " . $data['m_name'],
        'url' => "/".implode("/", [strtolower($group['group']), strtolower($data['m_name']), $perm]),
        'created_at' => date("Y-m-d H:i:s")
    ]);

    if(!$insertPermission) {
        $db->rollback();
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to create permission {$perm}",
            'data'      => []
        ]);
    }
}

$db->commit();
Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "module",
    'message' => "Create module : ".$data['m_name'],
    'ip' => Helper::get_ip_address(),
    'data' => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Berhasil membuat modul baru",
    'data'      => []
]);