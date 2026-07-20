<?php
    
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/client_deposit_acion")) {
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
    if(($ACCOUNT_CHECK["ACC_STS"] != 1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != 2)){
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
    if(($DEPOSIT_CHECK["DPWD_TYPE"] != 3 || $DEPOSIT_CHECK["DPWD_STS"] != 0)){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Invalid deposit new account",
            'data'      => []
        ]);
    }

    /** Stored note data*/
    $INSERT_NOTE = [
        "NOTE_MBR"  => $ACCOUNT_CHECK["ACC_MBR"],
        "NOTE_RACC" => $ACCOUNT_CHECK["ID_ACC"],
        "NOTE_DPWD" => $DEPOSIT_CHECK["ID_DPWD"],
        "NOTE_TYPE" => 'CL DEPOSIT NEW ACCOUNT '.strtoupper($data["sbmt_act"]),
        "NOTE_NOTE" => $data["sbmt_note"],
    ];

    /** Update RACC data*/
    $UPDATE_RACC = [
        "ACC_WPCHECK_DATE" => date("Y-m-d H:i:s")
    ];

    /** Update deposit data */
    $UPDATE_DPST = [
        "DPWD_TIMESTAMP" => date("Y-m-d H:i:s")
    ];

    /**Accept || Reject Processing*/
    switch ($data["sbmt_act"]) {
        case 'accept':
            $UPDATE_RACC["ACC_WPCHECK"] = 3;
            $UPDATE_DPST["DPWD_STS"]    = -1;
            break;

        case 'reject':
            $UPDATE_RACC["ACC_WPCHECK"] = 1;
            $UPDATE_DPST["DPWD_STS"]    = 1;
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

        Database::update('tb_dpwd', $UPDATE_DPST, ["ID_DPWD" => $DEPOSIT_CHECK["ID_DPWD"]]);

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
        'module' => "account/progress_real_account/client_deposit",
        'message' => strtoupper($data["sbmt_act"])." client deposit",
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