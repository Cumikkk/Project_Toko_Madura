<?php

    use App\Factory\FileUploadFactory;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use Config\Core\Database;
    use App\Models\FileUpload;

    global $db;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/member/user/update")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(['email_baru', 'mbrx'] as $req) {
        if(empty($data[ $req ])) {
            // $req = str_replace("add-", "", $req);
            JsonResponse([
                'code'      => 402,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Check user */ 
    $SQL_CHECK = $db->query('
        SELECT 
            tb_member.ID_MBR,
            tb_member.MBR_ID,
            tb_member.MBR_EMAIL,
            IFNULL((
                SELECT
                    0
                FROM tb_member aeml
                WHERE aeml.MBR_EMAIL = "'.$data["email_baru"].'"
                LIMIT 1
            ), 1) AS AEML
        FROM tb_member 
        WHERE MD5(MD5(ID_MBR)) = "'.$data['mbrx'].'"
    ');
    if($SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "User not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();

    /** Check New Email */
    if($RSLT_CHECK["AEML"] == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "New email has been registered",
            'data'      => []
        ]);
    }


    /** Check Files */
    if((!isset($_FILES["bukti_dokument"])) || $_FILES["bukti_dokument"]["error"] != 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "File tidak terdeteksi",
            'data'      => []
        ]);
    }

    /** Upload file*/ 
    $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["bukti_dokument"], 'change_email_'.$RSLT_CHECK["MBR_ID"]);
    if(!is_array($PRCSF)){
        JsonResponse([
            'success'   => false,
            'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
            'data'      => []
        ]);
    }

    $UPDATE_DATA = [
        "MBR_EMAIL" => $data['email_baru']
    ];

    $INSERT_DATA = [
        "CHML_ADM"       => $user['ADM_ID'],
        "CHML_MBR"       => $RSLT_CHECK["MBR_ID"],
        "CHML_PREV_MAIL" => $RSLT_CHECK["MBR_EMAIL"],
        "CHML_NEXT_MAIL" => $data['email_baru'],
        "CHML_FILE"      => $PRCSF["filename"]
    ];

    try {
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        Database::update('tb_member', $UPDATE_DATA, ["ID_MBR" => $RSLT_CHECK["ID_MBR"]]);

        Database::insert('tb_chmail_log', $INSERT_DATA);


        mysqli_commit($db);
    } catch (Exception | mysqli_sql_exception $e) {
        mysqli_rollback($db);
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Exception Occured",
            'data'      => []
        ]);
    }


    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "user/edit",
        'message' => "Update Email",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Success Update User Data",
        'data'      => []
    ]);