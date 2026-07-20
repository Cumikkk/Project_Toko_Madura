<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Ib;
use App\Models\Logger;
    use App\Models\ProfilePerusahaan;
    use Config\Core\Database;
    use Config\Core\EmailSender;
use Config\Core\SystemInfo;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/member/delete_user_action")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(['xid', 'val'] as $req) {
        if(empty($data[ $req ])) {
            // $req = str_replace("add-", "", $req);
            JsonResponse([
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Check accept or reject */
    if(!in_array(strtolower($data["val"]), ["accept", "reject"])){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Invalid action",
            'data'      => []
        ]);
    }

    /** Check ID */ 
    $SQL_CHECK = $db->query('
        SELECT 
            tb_dlt_account.ID_DLTACC, 
            tb_dlt_account.DLTACC_MBR, 
            tb_member.MBR_ID,
            tb_member.MBR_IDSPN,
            tb_member.MBR_EMAIL,
            tb_member.MBR_NAME
        FROM tb_dlt_account 
        JOIN tb_member
        ON(tb_dlt_account.DLTACC_MBR = tb_member.MBR_ID)
        WHERE MD5(MD5(tb_dlt_account.ID_DLTACC)) = "'.$data['xid'].'"
    ');
    if($SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "ID not found",
            'data'      => []
        ]);
    }
    
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();
    $uplineId = $RSLT_CHECK['MBR_IDSPN'] ?? 1000000000;
    $sqlGetDirectDownlines = $db->query("SELECT MBR_ID, MBR_IDSPN, MBR_EMAIL FROM tb_member WHERE MBR_IDSPN = {$RSLT_CHECK['MBR_ID']}");
    $directDownlines = $sqlGetDirectDownlines->fetch_all(MYSQLI_ASSOC) ?? [];

    /**Execute database*/
    try {
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        if($data["val"] == 'accept'){
            /**Update tabel member*/
            $db->query('
                UPDATE tb_member SET
                    tb_member.MBR_ID    = (tb_member.MBR_ID * 10),
                    tb_member.MBR_EMAIL = CONCAT(tb_member.MBR_EMAIL, "_'.uniqid().'_deleted"),
                    tb_member.MBR_PHONE = CONCAT(tb_member.MBR_PHONE, "_'.uniqid().'_deleted"),
                    tb_member.MBR_STS   = 1
                WHERE tb_member.MBR_ID = '.$RSLT_CHECK["DLTACC_MBR"].'
            ');

            /** Update racc */
            $update_racc_identity_number = $db->query("
                UPDATE tb_racc SET 
                    ACC_NO_IDT = CONCAT(ACC_NO_IDT, '0') 
                WHERE ACC_MBR = {$RSLT_CHECK['MBR_ID']}
            ");

            /** Update downline to upline */
            if(!empty($directDownlines)){
                $data['update_downline'] = [];
                foreach($directDownlines as $downline) {
                    $data['update_downline'][] = [
                        'user_id' => $downline['MBR_ID'],
                        'user_upline_before' => $downline['MBR_IDSPN'],
                        'user_upline_after' => $uplineId,
                    ];
                }
                
                $updateDowline = Database::update("tb_member", ["MBR_IDSPN" => $uplineId], ["MBR_IDSPN" => $RSLT_CHECK['MBR_ID']]);
                if(!$updateDowline) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Failed to update upline ID for downlines",
                        'data' => []
                    ]);
                }
            }
        }

        /**Update delete account tabel*/
        $UPDATE_DATA = [
            "DLTACC_STS"    => ($data["val"] == 'accept') ? -1 : (($data["val"] == 'reject') ? 1 : 0)
        ];
        Database::update('tb_dlt_account', $UPDATE_DATA, ["ID_DLTACC" => $RSLT_CHECK["ID_DLTACC"], "DLTACC_STS" => 0]);

        switch ($data["val"]) {
            case 'accept':
                $wrd = 'disetujui';
                $fml = 'otp-delete-success';
                break;
            case 'reject':
                $wrd = 'ditolak';
                $fml = 'otp-delete-reject';
                break;
            
            default:
                $wrd = '';
                $fml = '';
            break;
        }

        /** Notifikasi email untuk admin dan client */
        if(!empty($fml)){
            $emailData = [
                'subject' => "Penghapusan akun anda telah ".$wrd,
                'email'   => $RSLT_CHECK['MBR_EMAIL']
            ];
    
            $emailSender = EmailSender::init(['email' => $RSLT_CHECK['MBR_EMAIL'], 'name' => $RSLT_CHECK['MBR_NAME']]);
            $emailSender->useFile($fml, $emailData);
            $emailSender->useInternal();
            $send = $emailSender->send();
        }

        mysqli_commit($db);
    } catch (Exception | mysqli_sql_exception $e) {
        mysqli_rollback($db);
        if(SystemInfo::isDevelopment()) {
            throw $e;
        }

        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Exception occured. Please try again!.",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "delete_user",
        'message' => ucfirst($data["val"])." delete user request",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Success ".$data["val"]." delete user request",
        'data'      => []
    ]);

