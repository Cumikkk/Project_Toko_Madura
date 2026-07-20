<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;
use Config\Core\Database;

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
        'data' => [
            'required_password' => true
        ]
    ]);
}

Database::update("tb_racc", ['ACC_TOKEN' => $token], ['ID_ACC' => $account['ID_ACC']]);
JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);