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
    foreach(["add_case", "add_desc", "add_precondition", "add_steps", "add_result", "add_actual_result", "add_notes", "add_status"] as $req) {
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

    /** Get Current DTC id*/
    $SQL_GET_ID = mysqli_query($db, 'SELECT (1 + IFNULL((SELECT MAX(dtc2.DTC_ID) FROM tb_dtc AS dtc2), 0)) AS CURRX');
    if((!$SQL_GET_ID) || $SQL_GET_ID->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Cannot found current dtc id",
            'data'      => []
        ]);
    }
    $RSLT_GETX = $SQL_GET_ID->fetch_assoc();

    $STORED_DATA = [
        "DTC_ID"            => $RSLT_GETX["CURRX"], 
        "DTC_CASE"          => $data["add_case"], 
        "DTC_DESC"          => $data["add_desc"], 
        "DTC_PRECONDITION"  => $data["add_precondition"], 
        "DTC_STEPS"         => $data["add_steps"], 
        "DTC_RESULT"        => $data["add_result"], 
        "DTC_ACTUAL_RESULT" => $data["add_actual_result"], 
        "DTC_NOTES"         => $data["add_notes"], 
        "DTC_STS"           => $data["add_status"] 
    ];

    /** Insert data */
    $insert = Database::insert('tb_dtc', $STORED_DATA);
    if(!$insert){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Cannot insert DTC",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "dtc",
        'message' => "Add DTC",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil menambahkan dtc",
        'data'      => []
    ]);
