<?php

use App\Factory\MetatraderFactory;
use App\Factory\UserOtpFactory;
use App\Models\Account;
use App\Models\Admin;
use App\Models\Dpwd;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use App\Models\Firebase;
use App\PaymentSystem\BankTransfer;
use Config\Core\Database;
use Config\Core\EmailSender;

/** check status Deposit */
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

$data = Helper::getSafeInput($_POST);
$required = [
    'account' => "Akun",
    'user-bank' => "Bank pengirim",
    'amount' => "Jumlah",
    'otp' => "Kode OTP"
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Kolom {$text} wajib diisi",
            'data' => []
        ]);
    }
}

/** check Kode OTP */
$isValidOtp = UserOtpFactory::useOtp($data['otp']);
if($isValidOtp !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isValidOtp ?? "Gagal",
        'data' => []
    ]);
}

/** Check Account */
$account = Account::realAccountDetail_byLogin($data['account']);
if(!$account || $account['ACC_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

/** check user bank */
$userBank = User::myBank($user['MBR_ID'], $data['user-bank']);
if(!$userBank) {
    JsonResponse([
        'success' => false,
        'message' => "Bank tidak valid",
        'data' => []
    ]);
}

/** check apakah ada withdrawal pending */
$isHavePending = Account::havePendingTransaction($user['MBR_ID'], [2]);
if($isHavePending) {
    JsonResponse([
        'success' => false,
        'message' => "Masih ada transaksi yang belum selesai",
        'data' => []
    ]);
}

/** Check Jumlah */
$jumlah = Helper::stringTonumber($data['amount']);
if($jumlah <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Jumlah withdrawal tidak valid",
        'data' => []
    ]);
}

/** check Balance */
$balance = Account::marginBalance($account['ACC_LOGIN']);
if(!$balance || $balance < $jumlah) {
    JsonResponse([
        'success' => false,
        'message' => "Insufficient Balance",
        'data' => []
    ]);
}

/** check metode pembayaran */
$payment = BankTransfer::detail();
if(!$payment) {
    JsonResponse([
        'success' => false,
        'message' => "Metode pembayaran tidak tersedia",
        'data' => []
    ]);
}

$fromCurrency = $account['RTYPE_META_CURR'];
$toCurrency = $account['RTYPE_CURR']; 
$convert = Account::accountConvertation([
    'account_id' => $account['ID_ACC'],
    'amount' => $jumlah,
    'from' => $fromCurrency,
    'to' => $toCurrency
]);

if(!is_array($convert)) {
    JsonResponse([
        'success' => false,
        'message' => $convert ?? "Invalid rate",
        'data' => []
    ]);
}

/** final amount */
$dpwdAmount = ($jumlah * $convert['rate']) ?? 0;

/** check minimum withdrawal */
$minimumWithdrawal = Helper::stringTonumber($account['RTYPE_MINWITHDRAWAL'] ?? 0);
if($dpwdAmount < $minimumWithdrawal && $minimumWithdrawal != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Minimum Withdrawal " . $account['RTYPE_CURR'] . " " . Helper::formatCurrency($minimumWithdrawal),
        'data' => []
    ]);
}

/** check maximum withdrawal */
$maximumWithdrawal = Helper::stringTonumber($account['RTYPE_MAXWITHDRAWAL'] ?? 0);
if($dpwdAmount > $maximumWithdrawal && $maximumWithdrawal != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Maximum Withdrawal " . $account['RTYPE_CURR'] . " " . Helper::formatCurrency($maximumWithdrawal),
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

/** Menyimpan rate idr saat ini untuk perhitungan NMI akun usd -> usd*/
$rateIDR = 1;
if($convert['rate'] == 1) {
    $getIDR = Account::accountConvertation([
        'account_id' => $account['ID_ACC'],
        'amount' => $jumlah,
        'from' => "USD",
        'to' => "IDR"
    ]);

    if(!$getIDR) {
        JsonResponse([
            'success' => false,
            'message' => $getIDR ?? "Invalid rates",
            'data' => []
        ]);
    }

    $rateIDR = $getIDR['rate'];
}

/** Start Transaction */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Insert Withdrawal */
$insert = Database::insert("tb_dpwd", [
    'DPWD_MBR' => $user['MBR_ID'],
    'DPWD_IDEMPOTENCY_KEY' => $idempotencyKey,
    'DPWD_TYPE' => Dpwd::$typeWithdrawal,
    'DPWD_RACC' => $account['ID_ACC'],
    'DPWD_DEVICE' => "Web",
    'DPWD_BANKSRC' => implode("/", [$userBank['MBANK_NAME'], $userBank['MBANK_HOLDER'], $userBank['MBANK_ACCOUNT']]),
    'DPWD_AMOUNT' => $dpwdAmount,
    'DPWD_AMOUNT_SOURCE' => $jumlah,
    'DPWD_CURR_FROM' => $fromCurrency,
    'DPWD_CURR_TO' => $toCurrency,
    'DPWD_RATE' => $convert['rate'],
    'DPWD_RATE_IDR' => $rateIDR,
    'DPWD_IP' => Helper::get_ip_address(),
    'DPWD_DATETIME' => date("Y-m-d H:i:s"),
]);

if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

/** Proses penarikan balance MetaTrader */
$idDpwd = $db->insert_id;
$apiManager = MetatraderFactory::apiManager();
$deposit = $apiManager->deposit([
    'login' => $account['ACC_LOGIN'],
    'amount' => ($jumlah * -1),
    'comment' => "Withdrawal"
]);

if(is_object($deposit) === FALSE || !property_exists($deposit, "ticket")) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Invalid Withdrawal from {$account['ACC_LOGIN']}",
        'data' => []
    ]);
}

/** Simpan ticket withdrawal */
Database::update("tb_dpwd", ['DPWD_CODE' => $deposit->ticket], ['ID_DPWD' => $idDpwd]);
$db->commit();

/** Notifikasi email withdrawal-pending */
$emailData = [
    'subject' => "Pending Withdrawal",
    'jumlah' => "{$fromCurrency} " . Helper::formatCurrency($jumlah)
];

// $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
// $emailSender->useFile("withdrawal-pending", $emailData);
// $emailSender->useInternal();
// $send = $emailSender->send();

$commentFirebase = "Withdrawal $" . Helper::formatCurrency($jumlah) . " From {$account['ACC_LOGIN']} ({$user['MBR_EMAIL']})";
$pushOpsEvent = Firebase::pushOpsEvent('withdrawal', 0, $commentFirebase, 'Web');

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);
