<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/profile_perushaaan/update_kantor")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
        "edit-office",
        "ofc_city",
        "ofc_address",
        "ofc_phone",
        "ofc_email",
        "ofc_fax"
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

    /** Check Kantor id*/
    $SQL_CHECK = mysqli_query($db, 'SELECT tb_office.ID_OFC FROM tb_office WHERE MD5(MD5(tb_office.ID_OFC)) = "'.$data["edit-office"].'"');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Cannot found current dtc id",
            'data'      => []
        ]);
    }
    $RSLT_GETX = $SQL_CHECK->fetch_assoc();

    
    $STORED_DATA = [
        "OFC_CITY"      => $data["ofc_city"],
        "OFC_ADDRESS"   => $data["ofc_address"],
        "OFC_PHONE"     => $data["ofc_phone"],
        "OFC_FAX"     => $data["ofc_fax"],
        "OFC_EMAIL"     => $data["ofc_email"]
    ];

    if(!empty($_POST["content"])){
        $STORED_DATA["OFC_IFRAME"] = $data["content"];
    }

    
    /** Update data */
    $update = Database::update('tb_office', $STORED_DATA, ["ID_OFC" => $RSLT_GETX["ID_OFC"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update kantor",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/profile_perushaaan/",
        'message' => "Update kantor",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil update kantor",
        'data'      => []
    ]);