<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use Config\Core\Database;

$demoAccount = Account::getDemoAccount(md5(md5($user['MBR_ID'])));
if(!empty($demoAccount)) {
    JsonResponse([
        'success' => false,
        'message' => "Sudah memiliki akun demo",
        'data' => []
    ]);
}

/** Check Demo Type */
$sqlGetType = $db->query("SELECT ID_RTYPE FROM tb_racctype WHERE UPPER(RTYPE_TYPE) = 'DEMO' LIMIT 1");
$demoType = $sqlGetType->fetch_assoc()['ID_RTYPE'] ?? 0;
if($sqlGetType->num_rows == 0 || $demoType == 0) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal membuat akun demo, Jenis akun tidak valid",
        'data' => []
    ]);
}

$init_margin = 10000;
$meta_pass = Account::generatePassword();
$meta_investor = Account::generatePassword();
$meta_phone = Account::generatePassword();

/** Create Demo */
$apiManager = MetatraderFactory::apiManager();
$apiData = [
    'master_pass' => $meta_pass, 
    'investor_pass' => $meta_investor, 
    'group' => "demo\MandiriInvestindo\MMUSD", 
    'fullname' => $user['MBR_NAME'], 
    'email' => $user['MBR_EMAIL'], 
    'leverage' => 200,
    'comment' => "metaapi"
];

$createDemo = $apiManager->createAccount($apiData);
if(!is_object($createDemo) || !property_exists($createDemo, "Login")) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal membuat akun demo",
        'data' => []
    ]);
}

/** Insert Balance **/
$deposit = $apiManager->deposit([
    'login' => $createDemo->Login,
    'amount' => $init_margin,
    'comment' => "metaapi"
]);

/** Insert Demo */
$insertDemo = Database::insert("tb_racc", [
    'ACC_MBR' => $user['MBR_ID'],
    'ACC_DERE' => 2,
    'ACC_TYPE' => $demoType,
    'ACC_LOGIN' => $createDemo->Login,
    'ACC_PASS' => $meta_pass,
    'ACC_INVESTOR' => $meta_investor,
    'ACC_PASSPHONE' => $meta_phone,
    'ACC_INITIALMARGIN' => $init_margin,
    'ACC_FULLNAME' => $user['MBR_NAME'],
    'ACC_DATETIME' => date("Y-m-d H:i:s"),
]);

if(!$insertDemo) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to store demo account",
        'data' => []
    ]); 
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil membuat akun demo",
    'data' => [
        'login' => $createDemo->Login,
        'passw' => $meta_pass,
        'invst' => $meta_investor,
        'phone' => $meta_phone,
        'mails' => "Silahkan periksa email anda. Dan jangan serahkan password, investor ataupun phone yang tertera kepada siapapun!"
    ]
]);