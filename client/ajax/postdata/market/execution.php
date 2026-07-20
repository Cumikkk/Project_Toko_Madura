<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
foreach(['account', 'symbol', 'operation', 'volume'] as $key) {
    if(empty($data[$key])) {
        JsonResponse([
            'success' => false,
            'message' => "{$key} is required",
            'data' => []
        ]);
    }
}

if(is_numeric($data['account']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "account must be numeric",
        'data' => []
    ]);
}

/** Check Account */
$account = Account::findByLogin($data['account']);
if(empty($account) || $account['ACC_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

$apiTerminal = MetatraderFactory::apiTerminal($account['RTYPE_SERVER']);
$token = MetatraderFactory::autoConnect($account["ACC_LOGIN"]);
if(!$token) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Token Connection",
        'data' => []
    ]);
}

/** Check Operation */
if(in_array($data['operation'], Allmedia\Shared\Metatrader\ApiVariable::operations()) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Operation",
        'data' => []
    ]);
}

/** Check Volume */
if(is_numeric($data['volume']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Volume must be numeric",
        'data' => []
    ]);
}

if($data['volume'] <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Volume must be greater than 0",
        'data' => []
    ]);
}

/** Request Order Send */
$orderData = [
    'id' => $token,
    'symbol' => $data['symbol'],
    'operation' => $data['operation'],
    'volume' => $data['volume'],
    'sl' => $data['sl'] ?? 0,
    'tp' => $data['tp'] ?? 0,
    'price' => $data['price'] ?? 0
];

$orderSend = $apiTerminal->orderSend($orderData);
if($orderSend->success === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => $orderSend->message,
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Order request successful with ticket: #{$orderSend->data->ticket}",
    'data' => $orderSend->data
]);