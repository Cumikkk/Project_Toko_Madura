<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
$required = [
    'account' => "Account",
    'symbol' => "Symbol",
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} diperlukan",
            'data' => []
        ]);
    }
}

/** check Account */
$account = Account::findByLogin($data['account']);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

/** Get Price History */
$apiTerminal = MetatraderFactory::apiTerminal($account['RTYPE_SERVER']);
$getToken = $apiTerminal->connect(['login' => $account['ACC_LOGIN'], 'password' => $account['ACC_PASS']]);
if(!$getToken) {
    JsonResponse([
        'success' => false,
        'message' => "Connection failed",
        'data' => []
    ]);
}

$priceHistoryData = [
    'id' => $getToken,
    'symbol' => $data['symbol'],
    'date_from' => date("Y-m-d", strtotime("-7 day")),
    'date_to' => date("Y-m-d"),
    'timeframe' => "M1"
];

$result = [];
$priceHistory = $apiTerminal->priceHistory($priceHistoryData);
if(!$priceHistory->success) {
    JsonResponse([
        'success' => false,
        'message' => $priceHistory->message,
        'data' => []
    ]);
}

foreach($priceHistory->data as $price) {
    $result[] = [
        'time' => $price->time,
        'openPrice' => $price->openPrice,
        'highPrice' => $price->highPrice,
        'lowPrice' => $price->lowPrice,
        'closePrice' => $price->closePrice,
        'tickVolume' => $price->tickVolume,
        'digits' => $price->digits  
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => $result
]);