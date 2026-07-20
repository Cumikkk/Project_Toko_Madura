<?php

use App\Factory\MetatraderFactory;
use App\Factory\VerihubFactory;
use App\Models\Account;
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Logger;
use App\Models\FileUpload;
use App\Models\MemberBank;
use App\Models\Regol;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;
use Config\Core\SystemInfo;

$apiManager = MetatraderFactory::apiManager();
$apiTerminal = MetatraderFactory::apiTerminal();
$listGrup = $adminPermissionCore->availableGroup();
$adminRoles = Admin::adminRoles();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/wp_verification_acion")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(["sbmt_id", "sbmt_act", "sbmt_note"] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add_", "", $req);
        JsonResponse([
            'code'      => 402,
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
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
if(($ACCOUNT_CHECK["ACC_STS"] != 1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != Regol::$statusWPCheckRegister)){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid Account",
        'data'      => []
    ]);
}

/** Stored note data*/
$INSERT_NOTE = [
    "NOTE_MBR"  => $ACCOUNT_CHECK["ACC_MBR"],
    "NOTE_RACC" => $ACCOUNT_CHECK["ID_ACC"],
    "NOTE_TYPE" => 'WP VER '.strtoupper($data["sbmt_act"]),
    "NOTE_NOTE" => $data["sbmt_note"],
];

/** Update RACC data*/
$UPDATE_RACC = [
    "ACC_WPCHECK_DATE" => date("Y-m-d H:i:s")
];

/**Accept || Reject Processing*/
switch ($data["sbmt_act"]) {
    case 'accept':
        /** Update RACC */
        $UPDATE_RACC['ACC_WPCHECK'] = Regol::$statusWPCheckGoodFund;

        /** Validasi Verihub apakah sudah pernah accept / belum */
        // if(!SystemInfo::isDevelopment() && $ACCOUNT_CHECK['ACC_TYPE_IDT'] == "KTP") {
        //     $logVerihub = VerihubFactory::findAccountLog($ACCOUNT_CHECK['ACC_MBR'], md5($ACCOUNT_CHECK['ID_ACC']));
        //     $verified = false;
        //     foreach($logVerihub as $logVer) {
        //         $response = json_decode($logVer['LOGVER_RESPONSE'], true);
        //         if(($response['data']['status'] ?? "-") === "verified") {
        //             $verified = true;
        //             break;
        //         }
        //     }
    
        //     if(!$verified) {
        //         JsonResponse([
        //             'success' => false,
        //             'message' => "Belum lolos verifikasi verihub",
        //             'data' => []
        //         ]);
        //     }
        // }
        
        /** Check Id Account Condition */
        $ACCOND_CHECK = Account::accoundCondition($ACCOUNT_CHECK["ID_ACC"]);
        if(!$ACCOND_CHECK) {
            Database::insert('tb_acccond', [
                "ACCCND_MBR" => $ACCOUNT_CHECK["ACC_MBR"],
                "ACCCND_ACC" => $ACCOUNT_CHECK["ID_ACC"],
                "ACCCND_AMOUNTMARGIN" => 0,
                "ACCCND_CASH_FOREX" => 0,
                "ACCCND_LOGIN" => 0,
                "ACCCND_DATEMARGIN" => date("Y-m-d H:i:s")
            ]);
        }
        break;

    case 'reject':
        $UPDATE_RACC['ACC_F_CMPLT'] = 0;
        $UPDATE_RACC['ACC_WPCHECK'] = 0;
        $UPDATE_RACC['ACC_DOC_VERIF'] = 0;
        $UPDATE_RACC['ACC_STS'] = 2;

        // $UPDATE_RACC['ACC_WPCHECK'] = 0;
        break;
    
    default:
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Invalid action",
            'data'      => []
        ]);
    break;
}

/**Execute database*/
try {
    global $db;
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($db);

    Database::insert('tb_note', $INSERT_NOTE);

    Database::update('tb_racc', $UPDATE_RACC, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);

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
    'module' => "account/progress_real_account/wp_verification",
    'message' => strtoupper($data["sbmt_act"])." WP Verification",
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