<?php

use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\FileUpload;

$realAccount    = Account::realAccountDetail(($acc ?? ""));
$cdd            = strtolower(App\Models\Regol::cddType($realAccount['ACC_CDD'])['text']);
$accnd          = Account::accoundCondition($realAccount['ID_ACC']);
$depositData    = Dpwd::findByRaccId($realAccount["ID_ACC"]);
$bank           = explode("/", ($depositData['DPWD_BANKSRC'] ?? ''));
$bankName       = $bank[0] ?? "-";
$bankAccount    = $bank[1] ?? "-";
$bankHolder     = $bank[2] ?? "-";

$idAcc = Helper::form_input($_GET['acc'] ?? "");
$account = Account::realAccountDetail($idAcc);
if(!$account) {
    exit('Invalid Request');
}
    
$accountType = filter_var(strtolower($account['RTYPE_TYPE_AS'] ?? ""), FILTER_SANITIZE_URL);
if(file_exists(__DIR__ . "/{$accountType}/{$cdd}/{$filename}.php")) {
    require_once __DIR__ . "/{$accountType}/{$cdd}/{$filename}.php";
}

