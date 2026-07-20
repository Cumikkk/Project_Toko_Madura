<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/accounting_action")) {
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
    if(($ACCOUNT_CHECK["ACC_STS"] != 1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != 4)){
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

    /** Check Id Account Condition */
    $ACCOND_CHECK = Account::accoundCondition($ACCOUNT_CHECK["ID_ACC"]);
    if(!$ACCOND_CHECK){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Account condition id not found",
            'data'      => []
        ]);
    }

    /** Update RACC data*/
    $UPDATE_RACC = [
        "ACC_WPCHECK_DATE" => date("Y-m-d H:i:s")
    ];

    /**Execute database*/
    try {
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);


        /**Accept || Reject Processing*/
        switch ($data["sbmt_act"]) {
            case 'accept':
                $UPDATE_RACC["ACC_WPCHECK"] = 5;

                /** Upload file*/ 
                $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["mutasi"], 'mts_'.$ACCOUNT_CHECK["ACC_MBR"]);
                if(!is_array($PRCSF)){
                    JsonResponse([
                        'success'   => false,
                        'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
                        'data'      => []
                    ]);
                }

                /** Update Deposit */
                Database::update('tb_dpwd', ["DPWD_STSACC" => -1, "DPWD_PIC_MUTASI" => $PRCSF["filename"]], ["ID_DPWD" => $DEPOSIT_CHECK["ID_DPWD"]]);
                break;

            case 'reject':
                $UPDATE_RACC["ACC_WPCHECK"] = 3;
                
                /** Delete account condition previous record(s) */
                Database::delete('tb_acccond', ["ACCCND_ACC" => $ACCOUNT_CHECK["ID_ACC"], "ACCCND_STS" => 0]);

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

        
        /** Update RACC */
        Database::update('tb_racc', $UPDATE_RACC, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);

        /** Insert note */
        Database::insert('tb_note', [
            "NOTE_MBR"   => $ACCOUNT_CHECK["ACC_MBR"],
            "NOTE_RACC"  => $ACCOUNT_CHECK["ID_ACC"],
            "NOTE_DPWD"  => $DEPOSIT_CHECK["ID_DPWD"],
            "NOTE_ACCDN" => $ACCOND_CHECK["ID_ACCCND"],
            "NOTE_TYPE"  => 'ACCOUNTING '.strtoupper($data["sbmt_act"]),
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
        'module' => "account/progress_real_account/accounting",
        'message' => strtoupper($data["sbmt_act"])." accounting",
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