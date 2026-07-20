<?php

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
$category_name = trim($data['category_name'] ?? '');

$SQL_CHECK = mysqli_query($db, 'SELECT * FROM tb_symbolcat WHERE tb_symbolcat.SYMCAT_NAME = "'.$category_name.'" LIMIT 1');
if(($SQL_CHECK) && $SQL_CHECK->num_rows != 0){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Category already registered",
        'data'      => []
    ]);
}

$insert = Database::insert("tb_symbolcat", ['SYMCAT_NAME' => $category_name]);
if(!$insert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal insert category " . $category_name,
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "symbol category",
    'message' => "insert symbol category: " . $category_name,
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "symbol category ".$category_name." berhasil diinput",
    'data'      => []
]);