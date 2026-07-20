<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/profile_perushaaan/update_bank")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
        "idbk",
        "bkadm-name",
        "bkadm-curr",
        "bkadm-holder",
        "bkadm-account"
    ];
    $data = Helper::getSafeInput($_POST);
    foreach($REQ_POST as $req) {
        if(empty($data[ $req ])) {
            $req = str_replace("edit_", "", $req);
            JsonResponse([
                'code'      => 402,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Check Bank id*/
    $SQL_CHECK = mysqli_query($db, '
        SELECT 
            tb_bankadm.*,
            IFNULL((
                SELECT 
                    1 
                FROM tb_bankadm tb_ckbc
                WHERE tb_ckbc.BKADM_NAME = "'.$data["bkadm-name"].'" 
                AND tb_ckbc.BKADM_ACCOUNT = "'.$data["bkadm-account"].'" 
                AND tb_ckbc.ID_BKADM != tb_bankadm.ID_BKADM
                LIMIT 1
            ), 0) AS CKBK
        FROM tb_bankadm
        WHERE MD5(MD5(tb_bankadm.ID_BKADM)) = "'.$data["idbk"].'"
        LIMIT 1
    ');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Bank id not found!",
            'data'      => []
        ]);
    }
    $RSLT_CKCBK = $SQL_CHECK->fetch_assoc();

    /**Check account already exist*/
    if($RSLT_CKCBK["CKBK"] == 1){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Bank already exist!",
            'data'      => []
        ]);
    }
    
    $STORED_DATA = [
        "BKADM_NAME"    => $data["bkadm-name"],
        "BKADM_CURR"    => $data["bkadm-curr"],
        "BKADM_HOLDER"  => $data["bkadm-holder"],
        "BKADM_ACCOUNT" => $data["bkadm-account"]
    ];

    
    /** Update data */
    $update = Database::update('tb_bankadm', $STORED_DATA, ["ID_BKADM" => $RSLT_CKCBK["ID_BKADM"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update bank",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/profile_perushaaan/",
        'message' => "Update bank",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil update bank",
        'data'      => []
    ]);