<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/wakil_pialang/create")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
        "nama_wpb",
        "type"
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
        "WPB_NAMA"      => $data["nama_wpb"],
        "WPB_TYPE"      => $data["type"],
        "WPB_NO1"       => $data["wpb_no1"],
        "WPB_NO2"       => $data["wpb_no2"],
        "WPB_STS"       => ((!empty($_POST["wpb_sts"])) ? -1 : 0)
    ];

    /** Cek file post */
    if((!isset($_FILES["files"])) || $_FILES["files"]["error"] != 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Image Required!.",
            'data'      => []
        ]);
    }
    
    /** Upload file*/ 
    $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["files"], 'wpttd');
    if(!is_array($PRCSF)){
        JsonResponse([
            'success'   => false,
            'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
            'data'      => []
        ]);
    }
    
    $STORED_DATA["WPB_TANDA_TANGAN"] = $PRCSF["filename"];
    
    /** Insert data */
    $insert = Database::insert('tb_wpb', $STORED_DATA);
    if(!$insert){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to insert WPB",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/wakil_pialang/",
        'message' => "Insert kantor",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil insert wakil pialang",
        'data'      => []
    ]);