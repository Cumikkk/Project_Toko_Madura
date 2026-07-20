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
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/edit_document")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = ["sbmt_id"];
    

    $data            = Helper::getSafeInput($_POST);
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

    /**Stored data for update*/
    $UPDATE_DATA = [];
    
    /** Check posted file */
    $POSTED_FILES = [
        "p-dokument-rekkor"            => ["col_name" => "ACC_F_APP_FILE_IMG", "upld_name" => "regol_rekkor"],
        "p-foto-terbaru"               => ["col_name" => "ACC_F_APP_FILE_FOTO", "upld_name" => "regol_selfie"],
        "p-ktp&passport"               => ["col_name" => "ACC_F_APP_FILE_ID", "upld_name" => "regol_ktp"],
        "p-dokument-pendukung"         => ["col_name" => "ACC_F_APP_FILE_IMG3", "upld_name" => "regol_doc3"],
        "p-dokument-pendukung-lainnya" => ["col_name" => "ACC_F_APP_FILE_IMG4", "upld_name" => "regol_doc4"]
    ];
    foreach ($POSTED_FILES as $ky => $VAL) {
        if(isset($_FILES["$ky"]) && $_FILES["$ky"]["error"] == 0){
            /** Upload file*/ 
            $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["$ky"], $VAL["upld_name"]);
            if(!is_array($PRCSF) || !array_key_exists("filename", $PRCSF)){
                JsonResponse([
                    'success'   => false,
                    'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
                    'data'      => []
                ]);
            }
            $UPDATE_DATA = array_merge($UPDATE_DATA, array($VAL["col_name"] => $PRCSF["filename"]));
        }
        
    }

    /**Eksekusi database*/
    $update = Database::update('tb_racc', $UPDATE_DATA, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update data.",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "account/active_real_account/edit",
        'message' => "Edit Dokument",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Data",
        'data'      => []
    ]);