<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/support/ticket/send_chats")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(["sbmt_id"] as $req) {
        if(empty($data[ $req ])) {
            $req = str_replace("add_", "", $req);
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Check ticket id */
    $SQL_CHECK = mysqli_query($db, 'SELECT * FROM tb_ticket WHERE MD5(MD5(tb_ticket.ID_TICKET)) = "'.$data["sbmt_id"].'" AND tb_ticket.TICKET_STS = -1');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Ticket not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();

    /**Execute database*/
    try {
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        /** Upload file if exist */
        if(isset($_FILES["mutasi"]) && $_FILES["mutasi"]["error"] == 0){
            $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["mutasi"], 'ticket_'.$user['ADM_ID']);
            if(!is_array($PRCSF)){
                JsonResponse([
                    'success'   => false,
                    'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
                    'data'      => []
                ]);
            }

            /** Insert filename to database */
            Database::insert('tb_ticket_detail', [
                "TDETAIL_TCODE"         => $RSLT_CHECK["TICKET_CODE"],
                "TDETAIL_FROM"          => $user['ADM_ID'],
                "TDETAIL_TYPE"          => 'admin',
                "TDETAIL_CONTENT_TYPE"  => 'image',
                "TDETAIL_CONTENT"       => FileUploadFactory::aws()->awsFile($PRCSF["filename"]),
                "TDETAIL_DATETIME"      => date("Y-m-d H:i:s")
            ]);
        }

        /** Insert text to database */
        Database::insert('tb_ticket_detail', [
            "TDETAIL_TCODE"         => $RSLT_CHECK["TICKET_CODE"],
            "TDETAIL_FROM"          => $user['ADM_ID'],
            "TDETAIL_TYPE"          => 'admin',
            "TDETAIL_CONTENT_TYPE"  => 'message',
            "TDETAIL_CONTENT"       => ((!empty($data["messg"])) ? $data["messg"] : '')
        ]);

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
        'module' => "/support/ticket/detail",
        'message' => "Reply ticket",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success reply",
        'data'      => []
    ]);