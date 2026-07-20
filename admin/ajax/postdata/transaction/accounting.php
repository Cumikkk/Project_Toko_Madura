<?php
    
use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Logger;
use App\Models\FileUpload;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;
use Config\Core\SystemInfo;

$dbMetaReal = SystemInfo::app('DB_METALIVE');
$listGrup = $adminPermissionCore->availableGroup();
$adminRoles = Admin::adminRoles();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/transaction/deposit/update")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(["acc-dpx", "type"] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} diperlukan",
            'data' => []
        ]);
    }
}

/** Check accept or reject */
if(!in_array(strtolower($data["type"]), ["accept", "reject"])){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid action",
        'data'      => []
    ]);
}

/** Check deposit id */
$SQL_CHECK = mysqli_query($db, '
    SELECT 
        tb_dpwd.ID_DPWD,
        tb_dpwd.DPWD_CURR_FROM,
        tb_dpwd.DPWD_AMOUNT_SOURCE,
        tb_dpwd.DPWD_AMOUNT,
        tr.ACC_LOGIN
    FROM tb_dpwd 
    JOIN tb_racc tr ON (tr.ID_ACC = tb_dpwd.DPWD_RACC)
    WHERE MD5(MD5(tb_dpwd.ID_DPWD)) = "'.$data["acc-dpx"].'" 
    AND tb_dpwd.DPWD_STS = 0
    AND tb_dpwd.DPWD_TYPE = '.Dpwd::$typeDeposit.'
');

if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Deposit id not found",
        'data'      => []
    ]);
}

$RSLT_CHECK = $SQL_CHECK->fetch_assoc();

/** check akun */
$LOGIN_ACC = Account::realAccountDetail_byLogin($RSLT_CHECK['ACC_LOGIN']);
if(!$LOGIN_ACC) {
    JsonResponse([
        'success' => false,
        'message' => "Akun tidak ditemukan",
        'data' => []
    ]);
}

/** check user */
$userdata = User::findByMemberId($LOGIN_ACC['ACC_MBR']);
if(!$userdata) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid User",
        'data' => []
    ]);
}

/** Check apakah ini first time deposit */
$sqlCheckDeposit = $db->query("SELECT mt4_live_trades.TICKET FROM {$dbMetaReal}.MT4_TRADES mt4_live_trades WHERE `login` = {$LOGIN_ACC['ACC_LOGIN']} AND mt4_live_trades.CMD IN (6) AND mt4_live_trades.profit > 0 LIMIT 1");
$isFirstTimeDeposit = ($sqlCheckDeposit->num_rows == 0)? true : false;
$note = $data['note'] ?? "-";
try {
    switch(strtolower($data["type"])) {
        case 'accept':
            global $db;

            $db->autocommit(false);
            mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
            mysqli_begin_transaction($db);

            /** Lock Row */
            $checkStatus = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE ID_DPWD = '{$RSLT_CHECK["ID_DPWD"]}' AND DPWD_STS = 0 FOR UPDATE");
            if($checkStatus->num_rows == 0) {
                $db->rollback();
                JsonResponse([
                    'success' => false,
                    'message' => "Deposit already processed",
                    'data' => []
                ]);
            }

            /** Update data */
            $updateData = [
                'DPWD_STSACC' => -1,
                'DPWD_STS' => -1,
                'DPWD_NOTE' => $note,
                'DPWD_NOTE1' => $note
            ];

            $update = Database::update('tb_dpwd', $updateData, ["ID_DPWD" => $RSLT_CHECK["ID_DPWD"]]);
            if(!$update){
                $db->rollback();
                JsonResponse([
                    'success' => false,
                    'message' => "Failed to ".$data["auth-act"],
                    'data' => []
                ]);
            }

            if(!empty($RSLT_CHECK['DPWD_TICKET'])) {
                $sqlGet = $db->query("SELECT TICKET FROM {$dbMetaReal}.mt5_balance WHERE TICKET = '{$RSLT_CHECK['DPWD_TICKET']}' LIMIT 1");
                if($sqlGet->num_rows > 0) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Duplicate Deposit Ticket",
                        'data' => []
                    ]);
                }
            }

            /** Proses isi balance MetaTrader */
            $apiManager = MetatraderFactory::apiManager();
            $comment = ($isFirstTimeDeposit) ? "Deposit New Account" : "Deposit";
            $deposit = $apiManager->deposit($dpdt = [
                'login' => $LOGIN_ACC['ACC_LOGIN'],
                'amount' => $RSLT_CHECK["DPWD_AMOUNT"],
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
            Database::update('tb_dpwd', ['DPWD_TICKET' => $deposit->ticket], ['ID_DPWD' => $RSLT_CHECK["ID_DPWD"]]);

            /** Notifikasi email deposit success */
            $emailData = [
                'subject' => "Konfirmasi Deposit Anda Telah Disetujui",
                'jumlah' => $RSLT_CHECK['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($RSLT_CHECK['DPWD_AMOUNT_SOURCE'])
            ];

            // $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
            // $emailSender->useFile("deposit-success", $emailData);
            // $emailSender->useInternal();
            // $send = $emailSender->send();
            $db->commit();
            break;
        
        case 'reject':
            /** Update data */
            $updateData = [
                'DPWD_STSACC' => 1,
                'DPWD_STS' => 1,
                'DPWD_NOTE' => $note,
                'DPWD_NOTE1' => $note
            ];

            $update = Database::update('tb_dpwd', $updateData, ["ID_DPWD" => $RSLT_CHECK["ID_DPWD"]]);
            if(!$update){
                $db->rollback();
                JsonResponse([
                    'success' => false,
                    'message' => "Failed to ".$data["auth-act"],
                    'data' => []
                ]);
            }

            /** Notifikasi email deposit gagal */
            $emailData = [
                'subject' => "Konfirmasi Deposit Anda Telah Ditolak",
                'jumlah' => $RSLT_CHECK['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($RSLT_CHECK['DPWD_AMOUNT_SOURCE']),
                'note' => $data["note"]
            ];

            // $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
            // $emailSender->useFile("deposit-reject", $emailData);
            // $emailSender->useInternal();
            // $send = $emailSender->send();
            break;

        default:
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Invalid action",
                'data' => []
            ]);
            break;

    }

} catch (Exception | mysqli_sql_exception $e) {
    mysqli_rollback($db);
    JsonResponse([
        'success' => false,
        'message' => (SystemInfo::isDevelopment())? $e->getMessage() : "Exception occured. Please try again!.",
        'data' => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "transaction/deposit/accounting",
    'message' => strtoupper($data["type"])." deposit",
    'data'  => $data
]);

JsonResponse([
    'success' => true,
    'message' => "Success ".$data["type"],
    'data' => []
]);