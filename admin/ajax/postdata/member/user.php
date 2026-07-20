<?php
    
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use Config\Core\Database;

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
    foreach(['fullname', 'phone', 'country', 'address', 'address', 'zip', 'mbrx',] as $req) {
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
    $SQL_CHECK = $db->query('SELECT tb_member.ID_MBR, tb_member.MBR_EMAIL FROM tb_member WHERE MD5(MD5(ID_MBR)) = "'.$data['mbrx'].'"');
    if($SQL_CHECK->num_rows == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "User not found",
            'data'      => []
        ]);
    }
    $RSLT_CHECK = $SQL_CHECK->fetch_assoc();

    $UPDATE_DATA = [
        "MBR_NAME"    => $data['fullname'],
        "MBR_PHONE"   => $data['phone'],
        "MBR_COUNTRY" => $data['country'],
        "MBR_ADDRESS" => $data['address'],
        "MBR_CITY"    => $data['address'],
        "MBR_ZIP"     => $data['zip']
    ];

    $update = Database::update('tb_member', $UPDATE_DATA, ["ID_MBR" => $RSLT_CHECK["ID_MBR"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update member data!.",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "user/edit",
        'message' => "Update data member :".$RSLT_CHECK["MBR_EMAIL"],
        'data'  => $data
    ]);

    JsonResponse([
        'success'   => true,
        'message'   => "Success Update User Data",
        'data'      => []
    ]);

