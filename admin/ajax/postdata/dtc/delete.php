<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/dtc/delete")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(['x'] as $req) {
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

    /** Check DTC ID */
    $SQL_CHECK = $db->query('
        SELECT
            tb_dtc.ID_DTC
        FROM tb_dtc
        WHERE MD5(MD5(ID_DTC)) = "'.$data['x'].'"
    ');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "DTC not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();

    /** Delete Banner */
    $delete = Database::delete("tb_dtc", ["ID_DTC" => $RSLT_CHECK["ID_DTC"]]);
    if(!$delete) {
        JsonResponse([
            'success'   => false,
            'message'   => "Gagal hapus DTC",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "dtc",
        'message' => "Menghapus DTC",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil hapus DTC",
        'data'      => []
    ]);