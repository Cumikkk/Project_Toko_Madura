<?php
    
    use App\Models\Admin;
    use App\Models\Helper;
    use App\Models\Logger;
    use App\Models\Apuppt;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/apuppt/penilaian-risiko/delete-parameter")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    /** Required */
    $data = Helper::getSafeInput($_POST);
    foreach(["x"] as $req) {
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

    /** Check ID */
    $IXD = Apuppt::checkRangeNsbhId($data["x"]);
    if(!$IXD){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "ID not found",
            'data'      => []
        ]);
    }

    /**Update DB*/
    try {
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        Database::delete('tb_rangensb', ["ID_NSBR" => $IXD["ID_NSBR"]]);

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
        'module' => "apuppt/penilaian-risiko/delete-parameter",
        'message' => "Menghapus ".$IXD["NSBR_TYNAME"],
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Delete Data",
        'data'      => []
    ]);