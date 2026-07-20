<?php

use App\Factory\UserOtpFactory;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\MemberBank;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use Config\Core\Database;

$required = [
    'user-bank' => "Rekening Penerima",
    'otp' => "Kode OTP" 
];

$data = Helper::getSafeInput($_POST);
foreach($required as $key => $req) {
    if(empty($data[ $key ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} diperlukan",
            'data' => []
        ]);
    }
}

/** Validasi User Bank */
$userBankCode = Helper::form_input($data['user-bank']);
$userBank = MemberBank::findByIdHash($userBankCode);
if(!$userBank) {
    JsonResponse([
        'success' => false,
        'message' => "Mohon pilih rekening penerima",
        'data' => []
    ]);
}


/** Validasi amount */
$amount = Helper::stringTonumber($_POST['amount']);
if(is_numeric($amount) === FALSE || $amount <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Jumlah penarikan tidak valid",
        'data' => []
    ]);
}

/** Validasi OTP */
$isValidOtp = UserOtpFactory::useOtp($data['otp']);
if($isValidOtp !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isValidOtp ?? "Gagal",
        'data' => []
    ]);
}

/** validasi wallet rebate */
$balanceRebate = User::wallet($user['MBR_ID'], [Dpwd::$typeNmiCommission]);
if($balanceRebate < $amount) {
    JsonResponse([
        'success' => false,
        'message' => "Balance tidak mencukupi",
        'data' => []
    ]);
}

/** rate */
$rateWdBonus = ProfilePerusahaan::rateWdBonus();
$amountReceived = $amount / $rateWdBonus;
if($amountReceived <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Amount",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR || MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);


$insertDpwd = Database::insert("tb_dpwd", [
    'DPWD_MBR' => $user['MBR_ID'],
    'DPWD_TYPE' => Dpwd::$typeNmiCommission,
    'DPWD_DEVICE' => "Web",
    'DPWD_BANKSRC' => implode("/", [$userBank['MBANK_NAME'], $userBank['MBANK_HOLDER'], $userBank['MBANK_ACCOUNT']]),
    'DPWD_AMOUNT' => $amountReceived,
    'DPWD_AMOUNT_SOURCE' => ($amount * -1),
    'DPWD_CURR_FROM' => "IDR",
    'DPWD_CURR_TO' => "IDR",
    'DPWD_RATE' => $rateWdBonus,
    'DPWD_RATE_IDR' => $rateWdBonus,
    'DPWD_NOTE' => "Withdrawal NMI Commission",
    'DPWD_NOTE1' => "Withdrawal NMI Commission",
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