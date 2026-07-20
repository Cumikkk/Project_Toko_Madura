<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;
use Config\Core\Database;

$required = [
    'account' => "Account Login",
    'type' => 'type',
    'comment' => "Comment"
];

$data = Helper::getSafeInput($_POST);
foreach($required as $field => $desc) {
    if(empty($data[$field])) {
        JsonResponse([
            'success' => false,
            'message' => "$desc is required.",
            'data' => []
        ]);
    }
}

/** Check Account */
$account = Account::realAccountDetail_byLogin($data['account']);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Account not found.",
        'data' => []
    ]);
}

/** check comment */
if(!in_array($data['comment'], App\Models\AdjustmentAccount::comment())) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid comment.",
        'data' => []
    ]);
}

/** Check Type */
$arrayType = [
    App\Models\AdjustmentAccount::$typeDeposit,
    App\Models\AdjustmentAccount::$typeWithdrawal
];

if(!in_array($data['type'], $arrayType)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid type.",
        'data' => []
    ]);
}

/** Check Amount */
$amount = Helper::stringTonumber($data['amount']);
if($amount <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Amount must be greater than 0.",
        'data' => []
    ]);
}

/** Create Adjustment Account */
if($data['type'] == App\Models\AdjustmentAccount::$typeWithdrawal) {
    $accountBalance = Account::marginBalance($account['ACC_LOGIN']);
    if(!$accountBalance || $accountBalance < $amount) {
        JsonResponse([
            'success' => false,
            'message' => "Insufficient balance for withdrawal.",
            'data' => []
        ]);
    }

    $amount = -$amount;
}

/** Start Transaction */
$apiManager = MetatraderFactory::apiManager();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Manager Hit */
$deposit = $apiManager->deposit([
    'login' => $account['ACC_LOGIN'],
    'amount' => $amount,
    'comment' => $data['comment'],
]);

if(is_object($deposit) === FALSE || !property_exists($deposit, 'ticket')) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed to adjust account: {$account['ACC_LOGIN']}.",
        'data' => []
    ]);
}

/** insert history */
$insert = Database::insert("tb_adjustment_account", [
    'ADJUST_ACC_DATETIME' => date("Y-m-d H:i:s"),
    'ADJUST_ACC_LOGIN' => $account['ACC_LOGIN'],
    'ADJUST_ACC_TYPE' => $data['type'],
    'ADJUST_ACC_TICKET' => $deposit->ticket,
    'ADJUST_ACC_AMOUNT' => $amount,
    'ADJUST_ACC_COMMENT' => $data['comment'],
]);

if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed to record adjustment history.",
        'data' => []
    ]);
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Adjustment account created successfully.",
    'data' => []
]);