<?php

use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Account;
use App\Models\Logger;
use App\Models\FileUpload;
use Config\Core\Database;
use App\Factory\MetatraderFactory;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use Config\Core\EmailSender;
use Config\Core\SystemInfo;

$apiManager = MetatraderFactory::apiManager();
$listGrup = $adminPermissionCore->availableGroup();
$adminRoles = Admin::adminRoles();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/transaction/withdrawal/update")) {
    JsonResponse([
        'success' => false,
        'message' => "Authorization Failed",
        'data' => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$data['note'] = $data['note'] ?? "-";
foreach(["acc-dpx", "type"] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add_", "", $req);
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
        'success' => false,
        'message' => "Invalid action",
        'data' => []
    ]);
}

try {
    /** Start Transaction */
    $db->autocommit(false);
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($db);

    /** Lock Transaction Row */
    $sqlGetDpwd = $db->query("
        SELECT 
            td.ID_DPWD,
            td.DPWD_TICKET,
            td.DPWD_AMOUNT,
            td.DPWD_AMOUNT_SOURCE,
            td.DPWD_CURR_FROM,
            tr.ACC_LOGIN,
            tm.MBR_EMAIL,
            tm.MBR_NAME,
            tm.MBR_ID
        FROM tb_dpwd td
        JOIN tb_member tm ON (tm.MBR_ID = td.DPWD_MBR)
        JOIN tb_racc tr ON (tr.ID_ACC = td.DPWD_RACC)
        WHERE MD5(MD5(td.ID_DPWD)) = '".$data["acc-dpx"]."'
        AND DPWD_STS = 0
        AND DPWD_STSVER = -1
        AND DPWD_TYPE = ".Dpwd::$typeWithdrawal."
        FOR UPDATE
    ");

    $dpwd = $sqlGetDpwd->fetch_assoc();
    if($sqlGetDpwd->num_rows == 0) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Withdrawal already processed",
            'data' => []
        ]);
    }

    switch(strtolower($data['type'])) {
        case 'accept':
            /** Update Transaction Status */
            $updateData = [
                "DPWD_STS" => -1,
                "DPWD_NOTE1" => $data["note"],
                "DPWD_DATETIME"=> date("Y-m-d H:i:s")
            ];

            $update = Database::update('tb_dpwd', $updateData, ["ID_DPWD" => $dpwd["ID_DPWD"]]);
            if(!$update){
                $db->rollback();
                JsonResponse([
                    'success' => false,
                    'message' => "Failed to accept withdrawal",
                    'data' => []
                ]);
            }

            /** Notifikasi email withdrawal success */
            $emailData = [
                'subject' => "Konfirmasi Withdrawal Anda Telah Disetujui",
                'jumlah' => $dpwd['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($dpwd['DPWD_AMOUNT_SOURCE'])
            ];

            $emailSender = EmailSender::init(['email' => $dpwd['MBR_EMAIL'], 'name' => $dpwd['MBR_NAME']]);
            $emailSender->useFile("withdrawal-success", $emailData);
            // $emailSender->useInternal();
            $send = $emailSender->send();
            $db->commit();
            break;

        case 'reject':
            /** Pengembalian balance withdrawal yang sudah terpotong */
            $ticket = null;
            if(empty($dpwd['DPWD_TICKET'])) {
                $deposit = $apiManager->deposit([
                    'login' => $dpwd['ACC_LOGIN'],
                    'amount' => $dpwd["DPWD_AMOUNT_SOURCE"],
                    'comment' => "Withdrawal Refund"
                ]);

                if(is_object($deposit) === FALSE || !property_exists($deposit, "ticket")) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Invalid Status Refund Withdrawal",
                        'data' => []
                    ]);
                }

                $ticket = $deposit->ticket;
            }

            /** Update Transaction Status */
            $updateData = [
                "DPWD_STS" => 1,
                "DPWD_NOTE1" => $data["note"],
                "DPWD_TICKET" => $ticket,
                "DPWD_DATETIME"=> date("Y-m-d H:i:s")
            ];

            $update = Database::update('tb_dpwd', $updateData, ["ID_DPWD" => $dpwd["ID_DPWD"]]);
            if(!$update){
                $db->rollback();
                JsonResponse([
                    'success' => false,
                    'message' => "Failed to reject withdrawal",
                    'data' => []
                ]);
            }

            /** Notifikasi email withdrawal reject */
            $emailData = [
                'subject' => "Konfirmasi Withdrawal Anda Telah Ditolak",
                'jumlah' => $dpwd['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($dpwd['DPWD_AMOUNT_SOURCE']),
                'note' => $data['note']
            ];

            $emailSender = EmailSender::init(['email' => $dpwd['MBR_EMAIL'], 'name' => $dpwd['MBR_NAME']]);
            $emailSender->useFile("withdrawal-reject", $emailData);
            // $emailSender->useInternal();
            $send = $emailSender->send();
            $db->commit();
            break;

        default :
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Invalid action",
                'data' => []
            ]);
            break;
    }

} catch (Exception | mysqli_sql_exception $e) {
    $db->rollback();
    $db->autocommit(true);
    JsonResponse([
        'success' => false,
        'message' => (SystemInfo::isDevelopment()) ? $e->getMessage() : "Internal Server Error (500)",
        'data' => []
    ]);
}

$db->autocommit(true);
Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "transaction/withdrawal/accounting",
    'message' => strtoupper($data["type"])." Withdrawal",
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Success ".$data["type"],
    'data'      => []
]);