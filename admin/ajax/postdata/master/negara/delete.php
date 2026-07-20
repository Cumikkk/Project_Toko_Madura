<?php

use App\Models\Admin;
use App\Models\Helper;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/master/negara/delete")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Denied",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
if(empty($data['code'])) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "code field is required",
        'data'      => []
    ]);
}


$countryId  = $data['code'];

/** Check Id */
$sqlGet = $db->query("SELECT ID_COUNTRY FROM tb_country WHERE MD5(MD5(ID_COUNTRY)) = '{$countryId}' LIMIT 1");
$country = $sqlGet->fetch_assoc();
if($sqlGet->num_rows != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid ID",
        'data'      => []
    ]);
}

/** Delete */
$delete = Database::delete("tb_country", ['ID_COUNTRY' => $country['ID_COUNTRY']]);
if(!$delete) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => $delete ?? "Delete country data failed",
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Delete country successfull",
    'data'      => []
]);