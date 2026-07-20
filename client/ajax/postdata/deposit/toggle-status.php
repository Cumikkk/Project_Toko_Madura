<?php

use App\Models\Helper;
use Config\Core\Database;

if($user['MBR_EMAIL'] != "kholidhikam1@gmail.com") {
    JsonResponse([
        'success' => false,
        'message' => "Unauthorized Access",
        'data' => []
    ]);
}

$type = Helper::form_input($_POST['type'] ?? '');
if(!in_array($type, [1, 0])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status Type",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Update */
$sqlUpdate = $db->prepare("UPDATE tb_member SET MBR_DEPOSIT_STATUS = ?");
$sqlUpdate->bind_param("i", $type);
if(!$sqlUpdate->execute()) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui status Deposit",
        'data' => []
    ]);
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Berhasil memperbarui status Deposit",
    'data' => []
]);