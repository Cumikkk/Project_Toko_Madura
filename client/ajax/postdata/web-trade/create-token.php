<?php

use App\Factory\ApiServerGateway;
use App\Models\Account;
use App\Models\Helper;

$server = Helper::form_input($_POST['server'] ?? "");
if(!in_array($server, ['real', 'demo'])) {
    JsonResponse([
        'success' => false,
        'message' => 'Invalid server',
    ]);
}

$accountNumber = Helper::form_input($_POST['account'] ?? "");
$account = Account::findByLogin($accountNumber);
if(!$account || $account['ACC_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => 'Account not found',
    ]);
}

$token = ApiServerGateway::gettoken();
if(!$token) {
    JsonResponse([
        'success' => false,
        'message' => 'Failed to get API token',
    ]);
}

JsonResponse([
    'success' => true,
    'message' => 'Token generated successfully',
    'token' => $token,
]);