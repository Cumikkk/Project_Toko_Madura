<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;

$login = Helper::form_input($_POST['account'] ?? "");
$account = Account::findByLogin($login);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Mohon pilih akun",
        'data' => []
    ]);
}

/** Connect */
$apiTerminal = MetatraderFactory::apiTerminal($account['RTYPE_SERVER']);
$token = $apiTerminal->connect(['login' => $account['ACC_LOGIN'], 'password' => $account['ACC_PASS']]);
if(!$token) {
    JsonResponse([
        'success' => false,
        'message' => "Connection Failed",
        'data' => []
    ]);
}

$symbols = $apiTerminal->symbols(['id' => $token]);
if(!$symbols || !$symbols->success) {
    JsonResponse([
        'success' => false,
        'message' => $symbols->message ?? "Gagal",
        'data' => []
    ]);
}

$result = [];
foreach($symbols->data as $symbol) {
    $result[] = [
        'currency' => $symbol->currency,
        'description' => $symbol->description,
        'digits' => $symbol->digits,
        'volumeMin' => $symbol->volumeMin,
        'volumeMax' => $symbol->volumeMax
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => $result
]);