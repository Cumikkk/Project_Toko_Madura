<?php


use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\FileUpload;
use App\Models\CompanyProfile;
use App\Models\ProfilePerusahaan;

$realAccount     = Account::realAccountDetail(($acc ?? ""));
$accnd           = Account::accoundCondition($realAccount['ID_ACC']);
$depositData     = Dpwd::findByRaccId($realAccount["ID_ACC"]);
$COMPANY_PRF     = CompanyProfile::profilePerusahaan();
$COMPANY_MOF     = CompanyProfile::getMainOffice();
$company         = CompanyProfile::$name;
$list_wpb_satu   = ProfilePerusahaan::list_wpb(-1, 2);
$list_wpb_dua    = ProfilePerusahaan::list_wpb(2, 2);
$profile         = array_merge(($COMPANY_PRF ?? []), ["OFFICE" => ($COMPANY_MOF ?? [])]);
$bank            = explode("/", ($depositData['DPWD_BANKSRC'] ?? ''));
$bankName        = $bank[0] ?? "-";
$bankAccount     = $bank[1] ?? "-";
$bankHolder      = $bank[2] ?? "-";

$bapakatauibu = (!empty($realAccount['ACC_F_APP_PRIBADI_KELAMIN']) && $realAccount['ACC_F_APP_PRIBADI_KELAMIN'] == "Laki-laki")
? 'Bapak/<strike>Ibu</strike>'
: '<strike>Bapak</strike>/Ibu';

$idAcc = Helper::form_input($_GET['acc'] ?? "");
$account = Account::realAccountDetail($idAcc);
if(!$account) {
    exit('Invalid Request');
}

$accountType = filter_var(strtolower($account['RTYPE_TYPE_AS'] ?? ""), FILTER_SANITIZE_URL);
if(file_exists(__DIR__ . "/{$accountType}/{$filename}.php")) {
    require_once __DIR__ . "/{$accountType}/{$filename}.php";
}
