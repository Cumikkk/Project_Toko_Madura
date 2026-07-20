<?php

use App\Factory\FileUploadFactory;
use App\Library\MT5\MT5Balance;
use App\Models\Account;
use App\Models\Admin;
use App\Models\Dpwd;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\User;
use App\Models\Firebase;
use App\Models\Regol;
use App\PaymentSystem\BankTransfer;
use Config\Core\Database;
use Config\Core\EmailSender;

/** check status Deposit */
if($user['MBR_DEPOSIT_STATUS'] != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Fitur Deposit sedang dalam pemeliharaan",
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
    'sender-bank' => "Bank pengirim",
    'receive-bank' => "Bank Penerima",
    'amount' => "Jumlah",
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

/** Check Account */
$account = Account::realAccountDetail($data['account']);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Akun tidak valid",
        'data' => []
    ]);
}

/** check bank pengirim */
$userBank = User::myBank($user['MBR_ID'], $data['sender-bank']);
if(!$userBank) {
    JsonResponse([
        'success' => false,
        'message' => "Bank Pengirim tidak valid",
        'data' => []
    ]);
}

/** Check Bank admin */
$adminBank = Admin::getAdminBank($data['receive-bank']);
if(!$adminBank) {
    JsonResponse([
        'success' => false,
        'message' => "Bank Penerima tidak valid",
        'data' => []
    ]);
}

/** check apakah ada deposit pending */
$isHavePending = Account::havePendingTransaction($user['MBR_ID']);
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
        'message' => "Jumlah deposit tidak valid",
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

/** check first time deposit */
$sqlGetDpwd = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE DPWD_RACC = {$account['ID_ACC']} AND DPWD_MBR = {$user['MBR_ID']} AND DPWD_TYPE = ".Dpwd::$typeDeposit." AND DPWD_STS = -1 LIMIT 1");
if($sqlGetDpwd->num_rows <= 0) {
    $minimumDepositAwal = Helper::stringTonumber($account['RTYPE_MINDEPOSIT'] ?? 0);
    if($jumlah < $minimumDepositAwal && $minimumDepositAwal != 0) {
        JsonResponse([
            'success' => false,
            'message' => sprintf("Minimum First Time Deposit %s%s ", Helper::currencyToSymbol($account['RTYPE_CURR']), Helper::formatCurrency($minimumDepositAwal)),
            'data' => []
        ]);
    }
}

/** check minimum deposit */
$minimumTopup = Helper::stringTonumber($account['RTYPE_MINTOPUP'] ?? 0);
if($jumlah < $minimumTopup && $minimumTopup != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Minimum Deposit " . $account['RTYPE_CURR'] . " " . Helper::formatCurrency($minimumTopup),
        'data' => []
    ]);
}

/** check maximum deposit */
$maximumTopup = Helper::stringTonumber($account['RTYPE_MAXTOPUP'] ?? 0);
if($jumlah > $maximumTopup && $maximumTopup != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Maximum Deposit " . $account['RTYPE_CURR'] . " " . Helper::formatCurrency($maximumTopup),
        'data' => []
    ]);
}

/** check minimum margin deposit akun cdds */
if($account['ACC_CDD'] == Regol::$cddTypeSederhana) {
    $accountEquity = MT5Balance::getEquity($account['ACC_LOGIN']);
    if($accountEquity === false) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Equity",
            'data' => []
        ]);
    }

    $residualLimit = Account::calcResidualMargin($accountEquity, $account['ACC_LIMIT_MARGIN']);
    if($residualLimit <= 0) {
        JsonResponse([
            'success' => false,
            'message' => sprintf("Akun anda sudah melebihi batas maksimal margin yang diperbolehkan (%s%s)", Helper::currencyToSymbol($account['RTYPE_META_CURR']), Helper::formatCurrency($account['ACC_LIMIT_MARGIN'])),
            'data' => []
        ]);
    }
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

/** Check Key */
$sqlGetKey = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE DPWD_IDEMPOTENCY_KEY = '{$idempotencyKey}' AND DPWD_MBR = '{$user['MBR_ID']}' LIMIT 1");
if($sqlGetKey->num_rows > 0) {
    JsonResponse([
        'success' => false,
        'message' => "Duplicate Request",
        'data' => []
    ]);
}

/** check Image */
// if(empty($_FILES['image']) || $_FILES['image']['error'] != 0) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Mohon upload bukti transfer",
//         'data' => []
//     ]);
// }

$dpwdAmount = ($jumlah / $convert['rate']) ?? 0;
$STORED_DATA = [
    'DPWD_MBR' => $user['MBR_ID'],
    'DPWD_IDEMPOTENCY_KEY' => $idempotencyKey,
    'DPWD_CODE' => "deposit_" . uniqid(),
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
    'DPWD_DATETIME' => date("Y-m-d H:i:s"),
];

/** check apakah upload bukti transfer */
if((!empty($_FILES['image'])) && $_FILES['image']['error'] == 0){
    $uploadImage = FileUploadFactory::aws()->upload_single($_FILES['image']);
    if(!is_array($uploadImage) || !array_key_exists("filename", $uploadImage)) {
        JsonResponse([
            'success' => false,
            'message' => $uploadImage ?? "Upload bukti transfer gagal",
            'data' => []
        ]);
    }

    $STORED_DATA['DPWD_PIC'] = $uploadImage['filename'];
}

/** Insert Deposit */
$insert = Database::insert("tb_dpwd", $STORED_DATA);
if(!$insert) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

/** Notifikasi email deposit success */
$emailData = [
    'subject' => "Pending Deposit",
    'jumlah' => $fromCurrency . " " . Helper::formatCurrency($jumlah)
];

// $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
// $emailSender->useFile("deposit-pending", $emailData);
// $emailSender->useInternal();
// $send = $emailSender->send();

$commentFirebase = "Deposit " . $fromCurrency . " " . Helper::formatCurrency($jumlah) . " From {$account['ACC_LOGIN']} ({$user['MBR_EMAIL']})";
$pushOpsEvent = Firebase::pushOpsEvent('deposit', $user['MBR_ID'], $commentFirebase, 'Web');

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);
