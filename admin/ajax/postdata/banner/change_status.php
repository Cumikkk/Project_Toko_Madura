<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/banner/update")) {
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
            CASE
                WHEN tb_banner.BAN_STS = -1 THEN 0
                WHEN tb_banner.BAN_STS = 0 THEN -1
                ELSE 0
            END AS STS,
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

    $UPDATE_DATA = [
        "BAN_STS" => $RSLT_CHECK["STS"]
    ];

    /** Updat Banner */
    $update = Database::update("tb_banner", $UPDATE_DATA, ["ID_BAN" => $RSLT_CHECK["ID_BAN"]]);
    if(!$update) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Gagal update banner",
            'data'      => []
        ]);
    }

    switch ($RSLT_CHECK["STS"]) {
        case -1:
            $icon = base64_encode('<i class="fas fa-eye"></i>');
            break;
        
        case 0:
            $icon = base64_encode('<i class="fas fa-eye-slash"></i>');
            break;
        
        default:
            $icon = base64_encode('<i class="fas fa-eye-slash"></i>');    
        break;
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "banner",
        'message' => "Merubah status banner",
        'data'  => $data
    ]);

    
    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil update banner",
        'data'      => [
            "icon" => $icon
        ]
    ]);