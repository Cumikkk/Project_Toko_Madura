<?php

use App\Models\Helper;
use App\Models\Ib;
use App\Models\Logger;
use App\Models\SalesStructure;
use App\Models\User;
use App\Library\Sales\SalesMain;
use Config\Core\Database;

$permissionAction = $adminPermissionCore->hasPermission($authorizedPermission, $url);
if(!$permissionAction) {
    JsonResponse([
        'success' => false,
        'message' => "Permission Denied",
        'data' => []
    ]);
}

$becomeId = Helper::form_input($_POST['id'] ?? "");
$ibData = Ib::findById($becomeId);
if(!$ibData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Data",
        'data' => []
    ]);
}

/** check Type */
$type = strtolower($_POST['type'] ?? "");
if(!in_array($type, ['accept', 'reject'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

/** check user */
$userData = User::findByMemberId($ibData['BECOME_MBR']);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid User",
        'data' => []
    ]);
}

/** Check Upline */
$uplineCode = Helper::form_input($_POST['upline'] ?? "");
$upline = User::findByCode($uplineCode);

/** check type */
$salesCode = Helper::form_input($_POST['sales_type']);
$sales = SalesStructure::findByCode($salesCode);

/** Update */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

$status = ($type == "accept")? -1 : 1;
$note = $data['note'] ?? "";
$uplineId = $userData['MBR_ID'];
$userType = 0; // Default Trader

/** Jika memilih upline */
if($upline) {
    $uplineId = $upline['MBR_ID'];
}

/** jika memilih Type  */
if($sales) {
    $userType = $sales['ID_SLSSTRC'];
}

/** update becomeib */
$update = Database::update("tb_become_ib", ['BECOME_STS' => $status, 'BECOME_NOTE' => $note], ['ID_BECOME' => $ibData['ID_BECOME']]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed to update status",
        'data' => []
    ]);
}

/** Update User */
$updateUser = Database::update("tb_member", ['MBR_IDSPN' => $uplineId, 'MBR_TYPE' => $userType], ['MBR_ID' => $userData['MBR_ID']]);
if(!$updateUser) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed to update user info",
        'data' => []
    ]);
}

$db->commit();
Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => $permissionAction['link'],
    'message' => "{$type} request ib for id: {$becomeId}",
    'data' => $_POST
]);

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => []
]);