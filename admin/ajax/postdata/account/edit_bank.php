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
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/edit_bank")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }
    $REQ_POST        = ["sbmt_id"];
    $data            = Helper::getSafeInput($_POST);
    $progressAccount = Account::realAccountDetail($data["sbmt_id"]);
    $userBanks       = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];

    /** count bank field */
    $nmbr = 1;
    foreach($userBanks as $bankmbr){
        $REQ_POST[] = "b-nama".($nmbr != 1 ? $nmbr : '' );
        $REQ_POST[] = "b-nama-bank".($nmbr != 1 ? $nmbr : '' );
        $REQ_POST[] = "b-nomor-rekening".($nmbr != 1 ? $nmbr : '' );
        if($nmbr ==  2){ break; }
        $nmbr++;
    }

    foreach($REQ_POST as $req) {
        if(in_array($req, ["k-fax-kantor"])){
            if(!isset($data[ $req ])) {
                $req = str_replace("add_", "", $req);
                JsonResponse([
                    'code'      => 402,
                    'success'   => false,
                    'message'   => "{$req} diperlukan",
                    'data'      => []
                ]);
            }
        }else{
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
    if(($ACCOUNT_CHECK["ACC_STS"] != -1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != 6)){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Invalid Account",
            'data'      => []
        ]);
    }

    /** looping update data */
    $nmbrn = 1;
    foreach($userBanks as $bankmbr){

        /**Stored data for update*/
        $UPDATE_DATA = [
            "MBANK_HOLDER"      => $data["b-nama".($nmbrn != 1 ? $nmbrn : '' )],
            "MBANK_NAME"        => $data["b-nama-bank".($nmbrn != 1 ? $nmbrn : '' )],
            "MBANK_ACCOUNT"     => $data["b-nomor-rekening".($nmbrn != 1 ? $nmbrn : '' )]
        ];

        /**Eksekusi database*/
        $update = Database::update('tb_member_bank', $UPDATE_DATA, ["ID_MBANK" => $bankmbr["ID_MBANK"]]);
        if(!$update){
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "Failed to update data.",
                'data'      => []
            ]);
        }
        
        if($nmbrn ==  2){ break; }
        $nmbrn++;
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "account/active_real_account/edit",
        'message' => "Edit Bank",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Data",
        'data'      => []
    ]);