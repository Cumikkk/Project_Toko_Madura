<?php
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;
    
    if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(["xid"] as $req) {
        if(empty($data[ $req ])) {
            $req = str_replace("add_", "", $req);
            JsonResponse([
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    $SQL_CHECKID = $db->query('SELECT tb_commset.ID_COMMSET FROM tb_commset WHERE MD5(MD5(tb_commset.ID_COMMSET)) = "'.$data["xid"].'" LIMIT 1');
    if((!$SQL_CHECKID) || $SQL_CHECKID->num_rows == 0){
        JsonResponse([
            'success'   => false,
            'message'   => "ID not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECKID = $SQL_CHECKID->fetch_assoc();

    $delete = Database::delete('tb_commset', ["ID_COMMSET" => $RSLT_CHECKID["ID_COMMSET"]]);
    if(!$delete){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to delete data.",
            'data'      => []
        ]);
    }
    
    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "commision/rebatesetting",
        'message' => "Delete rebatesetting",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success delete rebatesetting",
        'data'      => []
    ]);