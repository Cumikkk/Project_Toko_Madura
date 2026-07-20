<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
$required = ['account', 'ticket'];
foreach($required as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "invalid request, {$req} is required",
            'data' => []
        ]);
    }
}

/** validasi numeric */
foreach(['tp', 'sl'] as $numField) {
    if(isset($data[ $numField ])) {
        if(is_numeric($data[ $numField ]) === FALSE || $data[ $numField ] < 0) {
            JsonResponse([
                'success' => false,
                'message' => "invalid request, {$numField} must be numeric",
                'data' => []
            ]);
        }
    }
}

$isPendingOrder = $data['is_pending'] ?? 0;
if(!in_array($isPendingOrder, [0, 1])) {
    JsonResponse([
        'success' => false,
        'message' => "invalid request, is_pending must be 0 or 1",
        'data' => []
    ]);
}

/** validasi login */
$login = (int) $data['account'];
$account = Account::findByLogin($login);
if(!$account || $account['ACC_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

$token = MetatraderFactory::autoConnect($login);
if(!$token) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to connect to MetaTrader Terminal",
        'data' => []
    ]);
}

$modifyData = [
    'id' => $token,
    'ticket' => (int) $data['ticket'],
    'tp' => floatval($data['tp'] ?? 0),
    'sl' => floatval($data['sl'] ?? 0),
    'is_pending' => $isPendingOrder
];  

$apiTerminal = MetatraderFactory::apiTerminal($account['RTYPE_SERVER']);
$orderModify = $apiTerminal->orderModify($modifyData);
if(!$orderModify->success) {
    JsonResponse([
        'success' => false,
        'message' => $orderModify->message ?? "Failed to modify order",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => $orderModify->message ?? "Order modified successfully",
    'data' => $orderModify->data ?? []
]);