<?php

use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Authorization Failed",
        'data' => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['type', 'id'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} is required",
            'data' => []
        ]);
    }
}

/** check type */
if(!in_array(strtolower($data['type']), ['accept', 'release'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

$query = $db->query("SELECT ID_DRMCONF, DRMCONFIRM_STS FROM tb_dormanconfirm WHERE MD5(MD5(ID_DRMCONF)) = '".$data['id']."' LIMIT 1");
$row = $query->fetch_assoc();

switch($data['type']) {
    case "accept":
        $update = Database::update("tb_dormanconfirm", ['DRMCONFIRM_STS' => -1], ['ID_DRMCONF' => $row['ID_DRMCONF']]);
        if(!$update) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Failed to update data",
                'data' => []
            ]);
        }
        break;
    case "release":
        $delete = Database::delete("tb_dormanconfirm", ['ID_DRMCONF' => $row['ID_DRMCONF']]);
        if(!$delete) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Failed to delete data",
                'data' => []
            ]);
        }
        break;
    default:
        JsonResponse([
            'success' => false,
            'message' => "Invalid Type",
            'data' => []
        ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil " . ucwords($data['type']),
    'data' => []
]);