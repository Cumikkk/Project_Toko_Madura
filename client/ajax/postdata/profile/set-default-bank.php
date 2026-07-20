<?php

use App\Models\Helper;
use App\Models\Logger;
use App\Models\MemberBank;
use Config\Core\Database;

$code = Helper::form_input($_POST['code'] ?? "");
if(empty($code)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Request",
        'data' => []
    ]);
}

/** check bank */
$bank = MemberBank::findByIdHash($code);
if(!$bank || $bank['MBANK_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => "Bank tidak valid",
        'data' => []
    ]);
}

$db->autocommit(false);
$db->query('SET innodb_lock_wait_timeout = 3;');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Lock Row */
$db->query("SELECT ID_MBANK FROM tb_member_bank WHERE MBANK_MBR = ".$user['MBR_ID']." FOR UPDATE");

/** Reset all default bank */
$reset = Database::update("tb_member_bank", ['MBANK_SORT' => 1], ['MBANK_MBR' => $user['MBR_ID']]);
if(!$reset) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal mengatur bank sebagai default",
        'data' => []
    ]);
}

/** set default bank */
$update = Database::update("tb_member_bank", ['MBANK_SORT' => 0], ['ID_MBANK' => $bank['ID_MBANK']]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal mengatur bank sebagai default",
        'data' => []
    ]);
}

$db->commit();
$db->autocommit(true);

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "Set Default Bank",
    'message' => "{$user['MBR_EMAIL']} mengatur bank ID ".$bank['ID_MBANK']." sebagai bank default",
    'data' => [
        'bank_id' => $code,
    ]
]);

JsonResponse([
    'success' => true,
    'message' => "Berhasil mengatur bank sebagai default",
    'data' => []
]);