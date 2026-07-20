<?php

use Config\Core\Database;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\MemberPasscode;
use App\Models\User;

$data = Helper::getSafeInput($_POST);
$required = [
    'code' => "Code",
    'passcode' => "New Passcode",
    'passcode_confirm' => "Passcode Confirmation"
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "$text field is required",
            'data' => []
        ]);
    }
}

/** Validasi Code */
$passcode = MemberPasscode::findResetCode($data['code']);
$isDeleted = !empty($isValidCode['PASSCODE_DELETED']);
if(!$passcode || $isDeleted) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** validasi passcode baru */
if(is_numeric($data['passcode']) === FALSE || strlen($data['passcode']) < 6) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Passcode",
        'data' => []
    ]);
}

/** check expired */
if(strtotime($passcode['PASSCODE_RESET_EXPIRED']) < time()) {
    JsonResponse([
        'success' => false,
        'message' => "Reset Code Expired",
        'data' => []
    ]);
}

/** validasi passcode confirm */
if($data['passcode'] !== $data['passcode_confirm']) {
    JsonResponse([
        'success' => false,
        'message' => "Wrong confirmation passcode",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Disable old passcode */
$update = Database::update("tb_member_passcode", ['PASSCODE_DELETED' => date("Y-m-d H:i:s")], ['ID_PASSCODE' => $passcode['ID_PASSCODE']]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed reset passcode",
        'data' => []
    ]);
}

/** create new passcode */
$insert = Database::insert("tb_member_passcode", [
    'PASSCODE_MBR' => $passcode['PASSCODE_MBR'],
    'PASSCODE_NUMBER' => password_hash($data['passcode'], PASSWORD_BCRYPT),
    'PASSCODE_DATETIME' => date("Y-m-d H:i:s"),
]);

$newPasscodeId = $db->insert_id;
if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed",
        'data' => []
    ]);
}

$db->commit();

Logger::client_log([
    'mbrid' => $passcode['PASSCODE_MBR'],
    'module' => "reset-passcode",
    'data' => [
        'old_passcode_id' => $passcode['ID_PASSCODE'],
        'new_passcode_id' => $newPasscodeId
    ],
    'message' => "Reset passcode Successfull"
]);

JsonResponse([
    'success' => true,
    'message' => "Passcode reset was successful",
    'data' => []
]);