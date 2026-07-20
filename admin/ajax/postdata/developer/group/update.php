<?php
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
foreach(['edit_group_id', 'edit_group_name', 'edit_group_type', 'edit_group_icon'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}

/** Check Id */
$group = PermissionGroupFactory::init()->getById($data['edit_group_id']);
if(!$group) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "{$req} diperlukan",
        'data'      => []
    ]);
}

/** Update */
$updateData = [
    'group' => $data['edit_group_name'],
    'type' => $data['edit_group_type'],
    'icon' => $data['edit_group_icon']
];

$update = Database::update("admin_module_group", $updateData, ['id' => $group['id']]);
if(!$update) {
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
    'message' => "Memperbarui module group ".$data['edit_group_name'],
    'ip' => Helper::get_ip_address(),
    'data' => json_encode($data)
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Berhasil memperbarui data modul grup",
    'data'      => []
]);