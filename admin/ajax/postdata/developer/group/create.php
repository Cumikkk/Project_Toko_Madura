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
    'min_level' => 1
]);

if(!$insert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal memperbarui data grup",
        'data'      => []
    ]);
}

$db = Database::connect();
$newGroupId = $db->insert_id;

if ($data['group_type'] === 'single' && $newGroupId) {
    // Automatically create default module for single group
    $modName = strtolower(str_replace(' ', '-', trim($data['group_name'])));
    $insertModule = Database::insert("admin_module", [
        'group_id' => $newGroupId,
        'module'   => $modName,
        'status'   => -1,
        'visible'  => -1
    ]);

    if ($insertModule) {
        $newModId = $db->insert_id;
        $permissions = ['view', 'create', 'update', 'delete'];
        
        $adminUsersRes = $db->query("SELECT id_users FROM users WHERE role IN ('programmer', 'admin', 'master')");
        $adminIds = [(int)$user['ADM_ID']];
        if ($adminUsersRes && $adminUsersRes->num_rows > 0) {
            while ($aur = $adminUsersRes->fetch_assoc()) {
                $adminIds[] = (int)$aur['id_users'];
            }
        }
        $adminIds = array_unique($adminIds);

        foreach ($permissions as $perm) {
            Database::insert("admin_permissions", [
                'module_id'  => $newModId,
                'code'       => $perm,
                'desc'       => "{$perm} {$modName}",
                'url'        => "/{$modName}/{$perm}",
                'created_at' => date("Y-m-d H:i:s")
            ]);
            $pId = $db->insert_id;

            foreach ($adminIds as $aid) {
                Database::insert("admin_authorize", [
                    'admin_id'      => $aid,
                    'permission_id' => $pId,
                    'status'        => -1
                ]);
            }
        }
    }
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