<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
foreach(['account', 'ticket'] as $key) {
    if(empty($data[$key])) {
        JsonResponse([
            'success' => false,
            'message' => "{$key} is required",
            'data' => []
        ]);
    }
}

$isPendingOrder = $data['is_pending'] ?? 0;
$account = Account::findByLogin($data['account']);
if(!$account || $account['ACC_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => 'Invalid Account',
        'data' => []
    ]);
}

if(!in_array($isPendingOrder, [0,1])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Pending Order Status",
        'data' => []
    ]);
}

/** Get Token */
$apiTerminal = MetatraderFactory::apiTerminal($account['RTYPE_SERVER']);
$token = MetatraderFactory::autoConnect($account['ACC_LOGIN']);
if(!$token) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Token",
        'data' => []
    ]);
}

/** Request order close */
$orderClose = $apiTerminal->orderClose([
    'id' => $token,
    'ticket' => $data['ticket'],
    'is_pending' => $isPendingOrder
]);

if($orderClose->success === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to close order, please check your ticket",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Order closed successfully from ticket: {$orderClose->data->ticket}",
    'data' => $orderClose->data
]);