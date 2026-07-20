<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/profile_perushaaan/delete_kantor")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
        "x"
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
    $SQL_CHECK = mysqli_query($db, 'SELECT tb_office.ID_OFC FROM tb_office WHERE MD5(MD5(tb_office.ID_OFC)) = "'.$data["x"].'"');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Cannot found kantor id",
            'data'      => []
        ]);
    }
    $RSLT_GETX = $SQL_CHECK->fetch_assoc();


    
    /** Delete data */
    $delete = Database::delete('tb_office', ["ID_OFC" => $RSLT_GETX["ID_OFC"]]);
    if(!$delete){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to delete kantor",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/profile_perushaaan/",
        'message' => "Delete kantor",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil delete kantor",
        'data'      => []
    ]);