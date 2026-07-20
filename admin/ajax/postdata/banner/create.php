<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/banner/create")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(['desc'] as $req) {
        if(empty($data[ $req ])) {
            $req = str_replace("add-", "", $req);
            JsonResponse([
                'code'      => 402,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    $STORED_DATA = [
        "BAN_DESC" => $data["desc"]
    ];

    /** Check gambar */
    if((!isset($_FILES["file"])) || $_FILES["file"]["error"] != 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "File tidak terdeteksi",
            'data'      => []
        ]);
    }

    /** Upload file */
    $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["file"], 'admin_tckt_');
    if(!is_array($PRCSF)){
        JsonResponse([
            'success'   => false,
            'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
            'data'      => []
        ]);
    }

    $STORED_DATA["BAN_FILE"] = $PRCSF["filename"];

    
    /** Insert Banner */
    $insert = Database::insert("tb_banner", $STORED_DATA);
    if(!$insert) {
        JsonResponse([
            'success'   => false,
            'message'   => "Gagal upload banner",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "banner",
        'message' => "Menambah banner",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil upload banner",
        'data'      => []
    ]);


