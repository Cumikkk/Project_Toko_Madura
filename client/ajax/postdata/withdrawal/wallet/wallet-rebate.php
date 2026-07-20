<?php

use App\Factory\UserOtpFactory;
use App\Models\Country;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\MemberBank;
use App\Models\Rate;
use App\Models\User;
use Config\Core\Database;

/** check status Withdrawal */
if($user['MBR_WITHDRAWAL_STATUS'] != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Fitur Withdrawal sedang dalam pemeliharaan",
        'data' => []
    ]);
}

$idempotencyKey = Helper::form_input($_POST['key'] ?? '');
if(empty($idempotencyKey)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Request",
        'data' => []
    ]);
}

$required = [
    'client_bank' => "Destination Bank Account",
    'otp' => "OTP Code" 
];

$data = Helper::getSafeInput($_POST);
foreach($required as $key => $req) {
    if(empty($data[ $key ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} required",
            'data' => []
        ]);
    }
}

/** Validasi User Bank */
$userBankCode = Helper::form_input($data['client_bank']);
$userBank = MemberBank::findByIdHash($userBankCode);
if(!$userBank) {
    JsonResponse([
        'success' => false,
        'message' => "Please select a destination bank account",
        'data' => []
    ]);
}

/** Validasi amount */
$amount = Helper::stringTonumber($_POST['amount']);
if(is_numeric($amount) === FALSE || $amount <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid withdrawal amount",
        'data' => []
    ]);
}

/** validasi wallet rebate */
$balanceRebate = User::wallet($user['MBR_ID']);
if($balanceRebate < $amount) {
    JsonResponse([
        'success' => false,
        'message' => "Insufficient balance",
        'data' => []
    ]);
}

/** rate */
$userCountry = Country::getByName($user['MBR_COUNTRY']);
if(!$userCountry) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid currency",
        'data' => []
    ]);
}

$rate = Rate::getFloatingRate_jisdor("USD", $userCountry['COUNTRY_CURR']);
if(!$rate || $rate <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid rate",
        'data' => []
    ]);
}

/** Check Key */
$sqlGetKey = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE DPWD_IDEMPOTENCY_KEY = '{$idempotencyKey}' AND DPWD_MBR = '{$user['MBR_ID']}' LIMIT 1");
if($sqlGetKey->num_rows > 0) {
    JsonResponse([
        'success' => false,
        'message' => "Duplicate Request",
        'data' => []
    ]);
}

$amountReceived = $amount * $rate;
if($amountReceived <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Amount",
        'data' => []
    ]);
}

// $rateWdBonus = ProfilePerusahaan::rateWdBonus();
// $amountReceived = $amount / $rateWdBonus;
// if($amountReceived <= 0) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Invalid Amount",
//         'data' => []
//     ]);
// }

/** Validasi OTP */
$isValidOtp = UserOtpFactory::useOtp($data['otp']);
if($isValidOtp !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isValidOtp ?? "Failed to validate OTP",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR || MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

$insertDpwd = Database::insert("tb_dpwd", [
    'DPWD_MBR' => $user['MBR_ID'],
    'DPWD_IDEMPOTENCY_KEY' => $idempotencyKey,
    'DPWD_CODE' => Helper::generate_unique("WD"),
    'DPWD_TYPE' => Dpwd::$typeWithdrawalCommission,
    'DPWD_DEVICE' => "Web",
    'DPWD_TICKET' => "Wallet",
    'DPWD_BANKSRC' => implode("/", [$userBank['MBANK_NAME'], $userBank['MBANK_HOLDER'], $userBank['MBANK_ACCOUNT']]),
    'DPWD_AMOUNT' => $amountReceived,
    'DPWD_AMOUNT_SOURCE' => $amount,
    'DPWD_CURR_FROM' => "USD",
    'DPWD_CURR_TO' => $userCountry['COUNTRY_CURR'],
    'DPWD_RATE' => $rate,
    'DPWD_RATE_IDR' => $rate,
    'DPWD_NOTE' => "Withdrawal Rebate Commission",
    'DPWD_NOTE1' => "Withdrawal Rebate Commission",
    'DPWD_STS' => 0, 
    'DPWD_IP' => Helper::get_ip_address(),
    'DPWD_DATETIME' => date("Y-m-d H:i:s")
]);

if(!$insertDpwd) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);