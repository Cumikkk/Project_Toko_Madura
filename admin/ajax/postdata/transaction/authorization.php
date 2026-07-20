<?php
    
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
use Config\Core\SystemInfo
;
JsonResponse([
    'success'   => false,
    'message'   => "Authorization Failed",
    'data'      => []
]);

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
    // $REQ_POST = ["voucher", "note", "auth-dpx", "auth-act"]; //Old req pos
    $REQ_POST = ["note", "auth-dpx", "auth-act"];
    foreach($REQ_POST as $req) {
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
    if(!in_array(strtolower($data["auth-act"]), ["accept", "reject"])){
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
            CASE
                WHEN tb_dpwd.DPWD_CURR_FROM = "USD" THEN tb_dpwd.DPWD_AMOUNT_SOURCE
                WHEN tb_dpwd.DPWD_CURR_FROM = "IDR" THEN tb_dpwd.DPWD_AMOUNT
                ELSE tb_dpwd.DPWD_AMOUNT_SOURCE
            END AS JMLH,
            IFNULL((
                SELECT
                    tb_racc.ACC_LOGIN
                FROM tb_racc
                WHERE tb_racc.ID_ACC = tb_dpwd.DPWD_RACC
                LIMIT 1
            ), 0) AS ACC_LOGIN
        FROM tb_dpwd 
        WHERE MD5(MD5(tb_dpwd.ID_DPWD)) = "'.$data["auth-dpx"].'" 
        AND tb_dpwd.DPWD_STS = 0
        AND tb_dpwd.DPWD_STSACC = -1
        AND tb_dpwd.DPWD_STSVER IN (0, -1)
        AND tb_dpwd.DPWD_TYPE = 1
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

    $UPDATE_DATA = [
        "DPWD_NOTE1"     => $data["note"],
        "DPWD_TIMESTAMP" => date("Y-m-d H:i:s")
    ];

    switch (strtolower($data["auth-act"])) {
        case 'accept':
            $UPDATE_DATA["DPWD_STS"]  = -1;
            break;
        
        case 'reject':
            $UPDATE_DATA["DPWD_STS"]    = 1;
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


        /** Update data */
        $update = Database::update('tb_dpwd', $UPDATE_DATA, ["ID_DPWD" => $RSLT_CHECK["ID_DPWD"]]);
        if(!$update){
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "Failed to ".$data["auth-act"],
                'data'      => []
            ]);
        } 

        /** MetaTrader action if accept */
        if($UPDATE_DATA['DPWD_STS'] == -1){
            $comment = "deposit_".$RSLT_CHECK["ID_DPWD"];
            sleep(3);

            /** Check apakah deposit sudah masuk */
            $sqlGet = $db->query("SELECT TICKET FROM mt5_balance WHERE COMMENT = '{$comment}' LIMIT 1");
            if($sqlGet->num_rows == 0) {
                /** Proses isi balance MetaTrader */
                $apiManager = MetatraderFactory::apiManager();
                $deposit = $apiManager->deposit($dpdt = [
                    'login' => $LOGIN_ACC['ACC_LOGIN'],
                    'amount' => $RSLT_CHECK["JMLH"],
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
            }

            /** Notifikasi email deposit success */
            $emailData = [
                'subject' => "Konfirmasi Deposit Anda Telah Disetujui",
                'jumlah' => $RSLT_CHECK['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($RSLT_CHECK['DPWD_AMOUNT_SOURCE'])
            ];

            $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
            $emailSender->useFile("deposit-success", $emailData);
            $emailSender->useInternal();
            $send = $emailSender->send();
        
            
        } elseif ($UPDATE_DATA['DPWD_STS'] == 1) {

            /** Notifikasi email deposit gagal */
            $emailData = [
                'subject' => "Konfirmasi Deposit Anda Telah Ditolak",
                'jumlah' => $RSLT_CHECK['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($RSLT_CHECK['DPWD_AMOUNT_SOURCE']),
                'note' => $data["note"]
            ];

            $emailSender = EmailSender::init(['email' => $userdata['MBR_EMAIL'], 'name' => $userdata['MBR_NAME']]);
            $emailSender->useFile("deposit-reject", $emailData);
            $emailSender->useInternal();
            $send = $emailSender->send();
        }


        mysqli_commit($db);
    } catch (Exception | mysqli_sql_exception $e) {
        mysqli_rollback($db);
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => (SystemInfo::isDevelopment())? $e->getMessage() : "Exception occured. Please try again!.",
            'data'      => []
        ]);
    }
    
    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "transaction/deposit/authorization",
        'message' => strtoupper($data["auth-act"])." deposit",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success ".$data["auth-act"],
        'data'      => []
    ]);