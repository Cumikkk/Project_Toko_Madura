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
foreach(["category_name", "xid"] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add_", "", $req);
        JsonResponse([
            'code'      => 402,
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}
$category_name = trim($data['category_name'] ?? '');

$SQL_CHECK = mysqli_query($db, '
    SELECT 
        IFNULL((
            SELECT 
                1
            FROM tb_symbolcat snc
            WHERE snc.SYMCAT_NAME = "'.$category_name.'" 
            LIMIT 1
        ), 0) AS SNC, 
        ID_SYMCAT 
    FROM tb_symbolcat 
    WHERE MD5(MD5(ID_SYMCAT)) = "'.$data["xid"].'" 
    LIMIT 1
');

if(($SQL_CHECK) && $SQL_CHECK->num_rows == 0){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Category ID not found",
        'data'      => []
    ]);
}
$RSLT_CHECK = $SQL_CHECK->fetch_assoc();

if($RSLT_CHECK["SNC"] != 0){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Category already registered",
        'data'      => []
    ]);
}

$update = Database::update("tb_symbolcat", ['SYMCAT_NAME' => $category_name], ["ID_SYMCAT" => $RSLT_CHECK["ID_SYMCAT"]]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal update category " . $category_name,
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "symbol category",
    'message' => "update symbol category: " . $category_name,
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "symbol category ".$category_name." berhasil diupdate",
    'data'      => []
]);