<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Logger;
use App\Models\FileUpload;
use Config\Core\Database;
    
$apiManager = MetatraderFactory::apiManager();
$apiTerminal = MetatraderFactory::apiTerminal();
$listGrup = $adminPermissionCore->availableGroup();
$adminRoles = Admin::adminRoles();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/wp_check_action")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(["sbmt_id", "sbmt_act", "sbmt_note", "forex"] as $req) {
    if(in_array($req, ["forex"])){
        if(!isset($data[ $req ])) {
            $req = str_replace("add_", "", $req);
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }else{
        if(empty($data[ $req ])) {
            $req = str_replace("add_", "", $req);
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }
}

/** Check accept or reject */
if(!in_array(strtolower($data["sbmt_act"]), ["accept", "reject"])){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid action",
        'data'      => []
    ]);
}

/** Check Id Account */
$ACCOUNT_CHECK = Account::realAccountDetail($data["sbmt_id"]);
if(!$ACCOUNT_CHECK){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Account id not found",
        'data'      => []
    ]);
}

/** Check Valid Account to proceeds */
if(($ACCOUNT_CHECK["ACC_STS"] != 1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != 3)){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid Account",
        'data'      => []
    ]);
}

/** Check Id Deposit */
$DEPOSIT_CHECK = Dpwd::findByRaccId($ACCOUNT_CHECK["ID_ACC"]);
if(!$DEPOSIT_CHECK){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Deposit id not found",
        'data'      => []
    ]);
}

/** Check Valid Deposit New Account */
if(($DEPOSIT_CHECK["DPWD_TYPE"] != 3 || $DEPOSIT_CHECK["DPWD_STS"] != -1)){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid deposit new account",
        'data'      => []
    ]);
}

$rateInfoData = [
    'account_id' => $ACCOUNT_CHECK["ID_ACC"],
    'amount' => $DEPOSIT_CHECK["DPWD_AMOUNT"],
    'from' => $DEPOSIT_CHECK["DPWD_CURR_FROM"],
    'to' => "USD"
];

$rateInfo = Account::accountConvertation($rateInfoData);
if(!is_array($rateInfo)) {
    JsonResponse([
        'success' => false,
        'message' => $rateInfo ?? "Invalid Account Convert",
        'data' => []
    ]);
}

$rate = $rateInfo['rate'];
$amountMargin = ($DEPOSIT_CHECK['DPWD_CURR_FROM'] == "IDR")? ($DEPOSIT_CHECK['DPWD_AMOUNT'] / $rate) : $DEPOSIT_CHECK['DPWD_AMOUNT_SOURCE'];
if($amountMargin <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Amount Margin",
        'data' => []
    ]);
}

/**Execute database*/
try {
    global $db;
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($db);

    /** Update RACC data*/
    $UPDATE_RACC = [
        "ACC_WPCHECK_DATE" => date("Y-m-d H:i:s")
    ];

    /**Accept || Reject Processing*/
    switch ($data["sbmt_act"]) {
        case 'accept':
            $UPDATE_RACC["ACC_WPCHECK"] = 4;
            $login = 0;

            /** amount margin */
            $fixMargin = 0;
            

            /** Insert account condition */
            Database::insert('tb_acccond', [
                "ACCCND_MBR"            => $ACCOUNT_CHECK["ACC_MBR"],
                "ACCCND_ACC"            => $ACCOUNT_CHECK["ID_ACC"],
                "ACCCND_AMOUNTMARGIN"   => $amountMargin,
                "ACCCND_CASH_FOREX"     => $data["forex"],
                "ACCCND_LOGIN"          => $login,
                "ACCCND_DATEMARGIN"     => date("Y-m-d H:i:s")
            ]);
            break;

        case 'reject':
            $UPDATE_RACC["ACC_WPCHECK"] = 2;

            /** Update Deposit */
            Database::update('tb_dpwd', ["DPWD_STS" => 0], ["ID_DPWD" => $DEPOSIT_CHECK["ID_DPWD"]]);

            /** Delete account condition previous record(s) */
            Database::delete('tb_acccond', ["ACCCND_ACC" => $ACCOUNT_CHECK["ID_ACC"], "ACCCND_STS" => 0]);
            break;
        
        default:
            $db->rollback();
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "Invalid action",
                'data'      => []
            ]);
        break;
    }

    
    /** Update RACC */
    Database::update('tb_racc', $UPDATE_RACC, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);

    /** Insert note */
    Database::insert('tb_note', [
        "NOTE_MBR"   => $ACCOUNT_CHECK["ACC_MBR"],
        "NOTE_RACC"  => $ACCOUNT_CHECK["ID_ACC"],
        "NOTE_DPWD"  => $DEPOSIT_CHECK["ID_DPWD"],
        "NOTE_ACCDN" => ($db->insert_id ?? 0),
        "NOTE_TYPE"  => 'WP CHECK '.strtoupper($data["sbmt_act"]),
        "NOTE_NOTE"  => $data["sbmt_note"],
    ]);

    mysqli_commit($db);

} catch (Exception | mysqli_sql_exception $e) {
    mysqli_rollback($db);
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Exception occured. Please try again!.",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "account/progress_real_account/wp_check",
    'message' => strtoupper($data["sbmt_act"])." wp check",
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Success ".$data["sbmt_act"],
    'data'      => [
        "reloc" => '/account/progress_real_account/view'
    ]
]);