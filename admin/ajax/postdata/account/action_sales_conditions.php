<?php

use App\Models\Helper;
use App\Models\SalesConditions;
use Config\Core\Database;

$actionId = Helper::form_input($_POST['actionId'] ?? "");
$accountConditions = SalesConditions::findByIdHash($actionId);
if(!$accountConditions) {
    JsonResponse([
        "success" => false,
        "message" => "Sales conditions data not found.",
        "data" => []
    ]);
}

$type = Helper::form_input($_POST['type'] ?? "");
if(!in_array($type, ['accept', 'reject'])) {
    JsonResponse([
        "success" => false,
        "message" => "Invalid action type.",
        "data" => []
    ]);
}

$reason = Helper::form_input($_POST['reason'] ?? "");
if(empty($reason) && $type == 'reject') {
    JsonResponse([
        "success" => false,
        "message" => "Reason is required for reject action",
        "data" => []
    ]);
}

$updateData = [
    "SLSCONDITION_STS" => $type == 'accept' ? -1 : 1,
    "SLSCONDITION_RESPONSE_DATETIME" => date("Y-m-d H:i:s"),
    "SLSCONDITION_NOTE" => $reason,
    "SLSCONDITION_WPNAME" => $user['ADM_NAME']
];

$update = Database::update("tb_sales_conditions", $updateData, ["ID_SLSCONDITION" => $accountConditions['ID_SLSCONDITION']]);
if(!$update) {
    JsonResponse([
        "success" => false,
        "message" => "Failed to update sales conditions status.",
        "data" => []
    ]);
}

JsonResponse([
    "success" => true,
    "message" => "Sales conditions status updated successfully.",
    "data" => []
]);