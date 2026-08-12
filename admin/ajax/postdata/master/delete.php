<?php
use App\Models\Helper;
use App\Models\Master;

if (!$adminPermissionCore->hasPermission($authorizedPermission, "/master/delete")) {
    JsonResponse([
        "code"      => 200,
        "success"   => false,
        "message"   => "Anda tidak memiliki hak akses untuk menghapus master",
        "data"      => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$idUsers = intval($data["id_users"] ?? ($data["id"] ?? 0));

$result = Master::deleteMaster($idUsers);

JsonResponse([
    "code"      => 200,
    "success"   => $result["success"],
    "message"   => $result["message"],
    "data"      => []
]);

