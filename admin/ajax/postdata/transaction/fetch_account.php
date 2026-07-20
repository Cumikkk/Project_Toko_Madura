<?php

use App\Models\Account;
use App\Models\Helper;
use App\Models\MemberBank;
use App\Models\User;

$email = Helper::form_input($_GET['email'] ?? "");
if(empty($email)) {
    JsonResponse([
        'success' => false,
        'message' => "Email is required",
        'data' => []
    ]);
}

/** Find Email */
$userData = User::findByEmail($email);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid User",
        'data' => []
    ]);
}

/** Find Account */
$account = Account::myAccount($userData['MBR_ID']);
if(!is_array($account)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

/** mapping response */
$result = [];
foreach($account as $acc) {
    $rate = sprintf("%s %s", $acc['RTYPE_CURR'], Helper::formatCurrency($acc['RTYPE_RATE']));
    if($acc['RTYPE_ISFLOATING']) {
        $rate = "Floating";
    }

    $result[] = [
        'login' => $acc['ACC_LOGIN'],
        'free_margin' => $acc['MARGIN_FREE'],
        'currency' => $acc['RTYPE_CURR'],
        'rate' => $rate,
        'warning' => !in_array($acc['rights'], Account::$allowedRights)
    ];
}

/** find user banks */
$banks = [];
$userBanks = MemberBank::activeBanks($userData['MBR_ID']);
if(!empty($userBanks)) {
    foreach($userBanks as $bank) {
        $banks[] = [
            'id' => md5(md5($bank['ID_MBANK'])),
            'account' => $bank['MBANK_ACCOUNT'],
            'holder' => $bank['MBANK_HOLDER'],
            'provider' => $bank['MBANK_NAME']
        ];
    }
}

JsonResponse([
    'success' => true,
    'message' => "OK",
    'data' => [
        'accounts' => $result,
        'banks' => $banks
    ]
]);