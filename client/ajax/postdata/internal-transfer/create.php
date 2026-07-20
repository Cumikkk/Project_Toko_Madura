<?php

use App\Factory\MetatraderFactory;
use App\Library\InternalTransfer\InternalTransferFactory;
use App\Library\InternalTransfer\TransferAdapter;
use App\Models\Firebase;
use App\Models\Helper;
use Config\Core\EmailSender;

/** check status internal transfer */
if($user['MBR_IT_STATUS'] != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Fitur Internal Transfer sedang dinonaktifkan",
        'data' => []
    ]);
}

$key = Helper::form_input($_POST['key'] ?? '');
if(empty($key)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Request",
        'data' => []
    ]);
}

$idempotencyKey = base64_decode(trim($key));
if(!$idempotencyKey) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Request Key",
        'data' => []
    ]);
}


$apiManager = MetatraderFactory::apiManager();
$data = Helper::getSafeInput($_POST);
$required = [
    'from-account' => "Akun Pengirim",
    'to-account' => "Akun Penerima",
    'amount' => "Jumlah"
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

/** check jumlah */
$amount = Helper::stringTonumber($data['amount']);
if(is_numeric($amount) === FALSE || $amount <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Jumlah tidak valid",
        'data' => []
    ]);
}

/** Transfer Data From Account */
$from = new TransferAdapter($user['MBR_ID'], $data['from-account']);
if(!$from->isValid()) {
    JsonResponse([
        'success' => false,
        'message' => "Sumber Transfer Tidak Valid",
        'data' => []
    ]);
}

/** Transfer Data To Account */
$to = new TransferAdapter($user['MBR_ID'], $data['to-account']);
if(!$to->isValid()) {
    JsonResponse([
        'success' => false,
        'message' => "Tujuan Transfer Tidak Valid",
        'data' => []
    ]);
}

/** Check Pengirim dan Penerima */
if($from->login == $to->login) {
    JsonResponse([
        'success' => false,
        'message' => "Akun Pengirim dan Penerima tidak boleh sama",
        'data' => []
    ]);
}

/** Check rate account */
if($from->rate != $to->rate) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal, Rate akun tidak diperbolehkan",
        'data' => []
    ]);
}

/** Check Balance */
if($from->balance < $amount) {
    JsonResponse([
        'success' => false,
        'message' => "Saldo tidak mencukupi pada akun pengirim",
        'data' => []
    ]);
}

/** check Idempotency Key */
$checkIdempotency = $db->query("SELECT ID_IT FROM tb_internal_transfer WHERE IT_CODE = '{$db->real_escape_string($idempotencyKey)}'")->fetch_assoc();
if($checkIdempotency) {
    JsonResponse([
        'success' => false,
        'message' => "Duplicate Request",
        'data' => []
    ]);
}

/** Create Internal Transfer */
$internalTransfer = InternalTransferFactory::create($user['MBR_ID'], $from, $to, $amount, $idempotencyKey);
$execute = $internalTransfer->execute();
if(!$execute->success()) {
    /** Notifikasi Email Gagal */
    $emailData = [
        'subject' => "Internal Transfer Failed",
        'accountFrom' => $from->login,
        'accountTo' => $to->login,
        'amount' => "$".Helper::formatCurrency($amount)
    ];
    
    $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
    $emailSender->useFile("internal-transfer-failed", $emailData);
    $emailSender->useInternal();
    $send = $emailSender->send();

    JsonResponse([
        'success' => false,
        'message' => $execute->getError(),
        'data' => []
    ]);
}

/** Notifikasi Email Berhasil */
$emailData = [
    'subject' => "Internal Transfer Successfull",
    'accountFrom' => $from->login,
    'accountTo' => $to->login,
    'amount' => "$".Helper::formatCurrency($amount)
];

$emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
$emailSender->useFile("internal-transfer-success", $emailData);
$emailSender->useInternal();
$send = $emailSender->send();

/** Notifikasi Firebase */
$pushOpsEvent = Firebase::pushOpsEvent('internal_transfer', $user['MBR_ID'], "Internal Transfer From {$from->login} To {$to->login} Amount $".Helper::formatCurrency($amount), 'Web');

JsonResponse([
    'success' => true,
    'message' => "Internal Transfer dari {$from->login} ke {$to->login} berhasil",
    'data' => []
]);