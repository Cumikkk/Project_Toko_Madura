<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/dtc/create")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(["edit_case", "edit_desc", "edit_precondition", "edit_steps", "edit_result", "edit_actual_result", "edit_notes", "edit_status", "submit-edit-test"] as $req) {
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

    /** Check DTC id*/
    $SQL_CHECK = mysqli_query($db, 'SELECT tb_dtc.ID_DTC FROM tb_dtc WHERE MD5(MD5(ID_DTC)) = "'.$data["submit-edit-test"].'"');
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
        "DTC_CASE"          => $data["edit_case"], 
        "DTC_DESC"          => $data["edit_desc"], 
        "DTC_PRECONDITION"  => $data["edit_precondition"], 
        "DTC_STEPS"         => $data["edit_steps"], 
        "DTC_RESULT"        => $data["edit_result"], 
        "DTC_ACTUAL_RESULT" => $data["edit_actual_result"], 
        "DTC_NOTES"         => $data["edit_notes"], 
        "DTC_STS"           => $data["edit_status"] 
    ];

    
    /** Update data */
    $update = Database::update('tb_dtc', $STORED_DATA, ["ID_DTC" => $RSLT_GETX["ID_DTC"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update dtc",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "dtc",
        'message' => "Update DTC",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil update dtc",
        'data'      => []
    ]);