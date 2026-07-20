<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/profile_perushaaan/create_bank")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
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
    $SQL_CHECK = mysqli_query($db, 'SELECT * FROM tb_bankadm WHERE tb_bankadm.BKADM_NAME = "'.$data["bkadm-name"].'" AND tb_bankadm.BKADM_ACCOUNT = "'.$data["bkadm-account"].'" LIMIT 1');
    if(($SQL_CHECK) && $SQL_CHECK->num_rows != 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Bank already registered",
            'data'      => []
        ]);
    }

    
    $STORED_DATA = [
        "BKADM_NAME"    => $data["bkadm-name"],
        "BKADM_CURR"    => $data["bkadm-curr"],
        "BKADM_HOLDER"  => $data["bkadm-holder"],
        "BKADM_ACCOUNT" => $data["bkadm-account"]
    ];

    
    /** Insert data */
    $insert = Database::insert('tb_bankadm', $STORED_DATA);
    if(!$insert){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to insert bank",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/profile_perushaaan/",
        'message' => "Insert bank",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil insert bank",
        'data'      => []
    ]);