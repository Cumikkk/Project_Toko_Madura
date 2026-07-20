<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/wakil_pialang/update_verificator")) {
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

    /** Check WPB id*/
    $SQL_CHECK = mysqli_query($db, '
        SELECT 
            tb_wpb.ID_WPB 
        FROM tb_wpb 
        WHERE MD5(MD5(tb_wpb.ID_WPB)) = "'.$data["x"].'" 
        AND tb_wpb.WPB_TYPE = 2
    ');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Cannot found WPB id",
            'data'      => []
        ]);
    }
    $RSLT_GETX = $SQL_CHECK->fetch_assoc();
    
    
    /**Execute database*/
    try {
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);


        /** Update verificator yang ditunjuk*/
        $update = Database::update('tb_wpb', ["WPB_VERIFY" => -1], ["ID_WPB" => $RSLT_GETX["ID_WPB"]]);
        if(!$update){
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "Failed to update WPB",
                'data'      => []
            ]);
        }

        /**Update verificator yang tidak ditunjuk*/
        mysqli_stmt_execute(mysqli_prepare($db, 'UPDATE tb_wpb SET WPB_VERIFY = 0 WHERE tb_wpb.ID_WPB != '.$RSLT_GETX["ID_WPB"].' AND tb_wpb.WPB_TYPE = 2'));

        mysqli_commit($db);
    } catch (Exception | mysqli_sql_exception $e) {
        mysqli_rollback($db);
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Exception occured. Please try again!.",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "/tools/wakil_pialang/",
        'message' => "Menunjuk wakil pialang verificator",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil menunjuk wakil pialang verificator",
        'data'      => []
    ]);