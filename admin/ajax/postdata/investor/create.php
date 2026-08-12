<?php
use App\Models\Helper;
use App\Models\Investor;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);
$idInvestor = intval($data['id_investor'] ?? 0);
$isEdit = ($idInvestor > 0);

$requiredPerm = $isEdit ? "/investor/update" : "/investor/create";
if (!$adminPermissionCore->hasPermission($authorizedPermission, $requiredPerm) && !$adminPermissionCore->hasPermission($authorizedPermission, "/investor/create")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
    exit;
}

$currentUserId = intval($user['ADM_ID'] ?? 1);
$result = Investor::saveInvestor($data, $currentUserId);

if ($result['success']) {
    $result['data'] = ['redirect' => SystemInfo::app('ADMIN_URL') . "/investor/view"];
}

JsonResponse([
    'code'      => 200,
    'success'   => $result['success'],
    'message'   => $result['message'],
    'data'      => $result['data'] ?? []
]);
