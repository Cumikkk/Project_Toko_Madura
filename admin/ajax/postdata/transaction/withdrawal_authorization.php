<?php

use App\Factory\MetatraderFactory;
use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\ProfilePerusahaan;
    use App\Models\User;
    use Config\Core\Database;
    use Config\Core\EmailSender;

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
    foreach(["auth-dpx", "type"] as $req) {
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

    /** Check withdrawal id */
    $SQL_CHECK = mysqli_query($db, '
        SELECT 
            tb_dpwd.ID_DPWD,
            DPWD_MBR,
            DPWD_CODE,
            DPWD_CURR_FROM,
            ACC_LOGIN,
            DPWD_AMOUNT_SOURCE
        FROM tb_dpwd 
        JOIN tb_racc tr ON (tr.ID_ACC = DPWD_RACC)
        WHERE MD5(MD5(tb_dpwd.ID_DPWD)) = "'.$data["auth-dpx"].'" 
        AND tb_dpwd.DPWD_STS = 0
        AND tb_dpwd.DPWD_STSVER = 0
        AND tb_dpwd.DPWD_TYPE = 2
    ');

    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Withdrawal id not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();

    /** check user */
    $userdata = User::findByMemberId($RSLT_CHECK['DPWD_MBR']);
    if(!$userdata) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid User",
            'data' => []
        ]);
    }


    /** Start Transaction */
    $db->autocommit(false);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($db);

    /** Lock Row For Update */
    $sqlCheckStatus = $db->query("SELECT ID_DPWD, DPWD_TICKET FROM tb_dpwd WHERE ID_DPWD = '{$RSLT_CHECK['ID_DPWD']}' AND DPWD_STS = 0 AND DPWD_STSVER = 0 FOR UPDATE");
    if($sqlCheckStatus->num_rows == 0) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Withdrawal already processed",
            'data' => []
        ]);
    }

    switch(strtolower($data["type"])) {
        case 'accept':
            $updateData = [
                "DPWD_STSVER" => -1,
                "DPWD_NOTE1" => $data["note"],
                "DPWD_DATETIME2" => date("Y-m-d H:i:s")
            ];

            $update = Database::update('tb_dpwd', $updateData, ["ID_DPWD" => $RSLT_CHECK["ID_DPWD"]]);
            if(!$update){
                $db->rollback();
                JsonResponse([
                    'success' => false,
                    'message' => "Failed to accept",
                    'data' => []
                ]);
            }

            $db->commit();
            break;

        case 'reject':
            /** Pengembalian balance withdrawal yang sudah terpotong */
            $ticket = null;
            if(empty($sqlCheckStatus->fetch_assoc()['DPWD_TICKET'])) {
                $deposit = $apiManager->deposit([
                    'login' => $RSLT_CHECK['ACC_LOGIN'],
                    'amount' => $RSLT_CHECK["DPWD_AMOUNT_SOURCE"],
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

            /** Update Row */
            $updateData = [
                'DPWD_TICKET' => $ticket,
                'DPWD_STSVER' => 1,
                'DPWD_STS' => 1,
                'DPWD_NOTE1' => $data['note'],
            ];

            Database::update("tb_dpwd", $updateData, ['ID_DPWD' => $RSLT_CHECK['ID_DPWD']]);

            /** Prepare email data */
            $emailData = [
                'file' => "withdrawal-reject",
                'subject' => "Konfirmasi Withdrawal Anda Telah Ditolak",
                'jumlah' => $RSLT_CHECK['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($RSLT_CHECK['DPWD_AMOUNT_SOURCE']),
                'note' => $data['note']
            ];

            /** Notifikasi email */
            $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
            $emailSender->useFile($emailData['file'], $emailData);
            $emailSender->useInternal();
            $send = $emailSender->send();

            $db->commit();
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

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "transaction/withdrawal/authorization",
        'message' => strtoupper($data["type"])." Withdrawal",
        'data'  => $data
    ]);

    JsonResponse([
        'success' => true,
        'message' => "Success ".$data["type"],
        'data' => []
    ]);