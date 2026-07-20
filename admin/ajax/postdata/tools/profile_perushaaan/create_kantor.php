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
        "ofc_city",
        "ofc_address",
        "ofc_phone",
        "ofc_fax",
        "ofc_email"
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
    
    /** Insert data */
    $insert = Database::insert('tb_office', $STORED_DATA);
    if(!$insert){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to insert kantor",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/profile_perushaaan/",
        'message' => "Insert kantor",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil insert kantor",
        'data'      => []
    ]);