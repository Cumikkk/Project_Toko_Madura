<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/banner/delete")) {
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
            $req = str_replace("add-", "", $req);
            JsonResponse([
                'code'      => 402,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Check Banner ID */
    $SQL_CHECK = $db->query('
        SELECT
            tb_banner.BAN_FILE,
            tb_banner.ID_BAN
        FROM tb_banner
        WHERE MD5(MD5(tb_banner.ID_BAN)) = "'.$data['x'].'"
    ');
    if((!$SQL_CHECK) || $SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Banner not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();

    /** Delete Banner */
    $delete = Database::delete("tb_banner", ["ID_BAN" => $RSLT_CHECK["ID_BAN"]]);
    if(!$delete) {
        JsonResponse([
            'success'   => false,
            'message'   => "Gagal hapus banner",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "banner",
        'message' => "Menghapus banner",
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Berhasil hapus banner",
        'data'      => []
    ]);