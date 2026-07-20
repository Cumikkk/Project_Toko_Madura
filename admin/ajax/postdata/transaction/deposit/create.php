<?php

use App\Factory\FileUploadFactory;
use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Admin;
use App\Models\Dpwd;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\User;
use Config\Core\Database;
use Config\Core\SystemInfo;

$dbMetaReal = SystemInfo::app('DB_METALIVE');
if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Permission Denied",
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

$data = Helper::getSafeInput($_POST);
$required = [
    'email' => "Email",
    'account' => "Account",
    'bank-source' => "Bank Source",
    'bank-receiver' => "Bank Receiver",
    'amount' => "Amount",
];

/** Check Email */
$userData = User::findByEmail($data['email']);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Email",
        'data' => []
    ]);
}

/** check deal id */
if(empty($data['deal_type']) || !in_array($data['deal_type'], ['manual', 'auto'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

$dealId = $data['deal_id'] ?? 0;
if(strtolower($data['deal_type']) == "manual") {
    if(empty($dealId)) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Deal Number",
            'data' => []
        ]);
    }

    /** check apakah deal id ada di mt5_trades  */
    $sqlCheckDealId = $db->query("SELECT deal_id FROM {$dbMetaReal}.mt5_deals WHERE deal_id = {$dealId} AND action = 2 LIMIT 1");
    if($sqlCheckDealId->num_rows != 1) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Deal ID",
            'data' => []
        ]);
    }

    /** check apakah deal id sudah digunakan di tb_dpwd */
    $sqlCheckDealDpwd = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE DPWD_TYPE = ".Dpwd::$typeDeposit." AND DPWD_TICKET = {$dealId} LIMIT 1");
    if($sqlCheckDealDpwd->num_rows == 1) {
        JsonResponse([
            'success' => false,
            'message' => "Deal ID Already used by another Transaction",
            'data' => []
        ]);
    }
}

/** Check Account */
$account = Account::findByLogin($data['account']);
if(!$account || $account['ACC_LOGIN'] != $data['account']) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

/** check bank source */
$userBank = User::myBank($userData['MBR_ID'], $data['bank-source']);
if(!$userBank) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Source Bank",
        'data' => []
    ]);
}

/** Check Bank admin */
$adminBank = Admin::getAdminBank($data['bank-receiver']);
if(!$adminBank) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Bank Receiver",
        'data' => []
    ]);
}

/** Check Jumlah */
$jumlah = Helper::stringTonumber($data['amount']);
if($jumlah <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Jumlah deposit tidak valid",
        'data' => []
    ]);
}

$fromCurrency = $account['RTYPE_CURR']; 
$toCurrency = $account['RTYPE_META_CURR'];
$convert = Account::accountConvertation([
    'account_id' => $account['ID_ACC'],
    'amount' => $jumlah,
    'from' => $fromCurrency,
    'to' => $toCurrency ?? ""
]);

if(!is_array($convert)) {
    JsonResponse([
        'success' => false,
        'message' => $convert ?? "Invalid rate",
        'data' => []
    ]);
}


/** Menyimpan rate idr saat ini untuk perhitungan NMI akun usd -> usd*/
$rateIDR = 1;
if($convert['rate'] == 1) {
    $getIDR = Account::accountConvertation([
        'account_id' => $account['ID_ACC'],
        'amount' => $jumlah,
        'from' => $fromCurrency,
        'to' => "IDR"
    ]);

    if(!is_array($getIDR)) {
        JsonResponse([
            'success' => false,
            'message' => $getIDR ?? "Invalid rates",
            'data' => []
        ]);
    }

    $rateIDR = $getIDR['rate'];
}

/** check apakah ini first time deposit */
$sqlCheckDeposit = $db->query("SELECT deal_id FROM {$dbMetaReal}.mt5_deals WHERE `login` = {$account['ACC_LOGIN']} AND `action` IN (2,3) AND profit > 0 LIMIT 1");
$isFirstTimeDeposit = ($sqlCheckDeposit->num_rows == 0)? true : false;

$db->autocommit(false);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Check Key */
$sqlGetKey = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE DPWD_IDEMPOTENCY_KEY = '{$idempotencyKey}' AND DPWD_MBR = '{$userData['MBR_ID']}' FOR UPDATE");
if($sqlGetKey->num_rows > 0) { 
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Duplicate Request",
        'data' => []
    ]);
}

$date = date("Y-m-d", strtotime($data['date'] ?? ""));
$time = date("H:i:s", strtotime($data['time'] ?? ""));
$note = $data['note'] ?? null;
$comment = ($isFirstTimeDeposit) ? "Deposit New Account" : "Deposit";
$dpwdAmount = ($jumlah / $convert['rate']) ?? 0;
$dpwdData = [
    'DPWD_MBR' => $userData['MBR_ID'],
    'DPWD_IDEMPOTENCY_KEY' => $idempotencyKey,
    'DPWD_TICKET' => $dealId,
    'DPWD_CODE' => Helper::generate_unique('MANUAL'),
    'DPWD_TYPE' => Dpwd::$typeDeposit,
    'DPWD_RACC' => $account['ID_ACC'],
    'DPWD_DEVICE' => "Web",
    'DPWD_BANKSRC' => implode("/", [$userBank['MBANK_NAME'], $userBank['MBANK_HOLDER'], $userBank['MBANK_ACCOUNT']]),
    'DPWD_BANK' => implode("/", [$adminBank['BKADM_NAME'], $adminBank['BKADM_HOLDER'], $adminBank['BKADM_ACCOUNT']]),
    'DPWD_AMOUNT' => $dpwdAmount,
    'DPWD_AMOUNT_SOURCE' => $jumlah,
    'DPWD_CURR_FROM' => $fromCurrency,
    'DPWD_CURR_TO' => $toCurrency,
    'DPWD_RATE' => $convert['rate'],
    'DPWD_RATE_IDR' => $rateIDR,
    'DPWD_IP' => Helper::get_ip_address(),
    'DPWD_REQUESTBY' => $user['ADM_ID'],
    'DPWD_NOTE' => $note,
    'DPWD_NOTE1' => $note,
    'DPWD_DATETIME' => sprintf('%s %s ', $date, $time),
    'DPWD_STSACC' => -1,
    'DPWD_STS' => -1
];

/** check apakah upload bukti transfer */
if((!empty($_FILES['image'])) && $_FILES['image']['error'] == 0){
    $uploadImage = FileUploadFactory::aws()->upload_single($_FILES['image']);
    if(!is_array($uploadImage) || !array_key_exists("filename", $uploadImage)) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => $uploadImage ?? "Uploading proof of transfer failed",
            'data' => []
        ]);
    }

    $dpwdData['DPWD_PIC'] = $uploadImage['filename'];
}

/** Insert Deposit */
$insert = Database::insert("tb_dpwd", $dpwdData);
if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed",
        'data' => []
    ]);
}

/** Proses isi balance MetaTrader */
$dpwdId = $db->insert_id;
if(strtolower($data['deal_type']) == "auto") {
    $apiManager = MetatraderFactory::apiManager();
    $deposit = $apiManager->deposit([
        'login' => $account['ACC_LOGIN'],
        'amount' => $dpwdAmount,
        'comment' => $comment
    ]);

    if(!is_object($deposit) || !property_exists($deposit, "ticket")) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Invalid Status Deposit",
            'data' => [$dpdt]
        ]);
    }

    /** Update Ticket */
    Database::update('tb_dpwd', ['DPWD_TICKET' => $deposit->ticket], ['ID_DPWD' => $dpwdId]);
}

$db->commit();
Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "manual_deposit",
    'message' => sprintf("Create Manual Deposit on %s account %s $%s", $userData['MBR_EMAIL'], $account['ACC_LOGIN'], Helper::formatCurrency($dpwdAmount)),
    'data' => array_merge($_POST, $_FILES)
]);

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => []
]);