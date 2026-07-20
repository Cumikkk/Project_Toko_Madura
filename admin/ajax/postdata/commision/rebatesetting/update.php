<?php
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;
    $data = Helper::getSafeInput($_POST);
    foreach(["rebate_amount", "xedt"] as $req) {
        if(empty($data[ $req ])) {
            // $req = str_replace("add_", "", $req);
            JsonResponse([
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    $idComset = $data['xedt'];
    $sqlGetRebateSetting = $db->query("SELECT * FROM tb_commset WHERE MD5(MD5(ID_COMMSET)) = '{$idComset}' LIMIT 1");
    if($sqlGetRebateSetting->num_rows != 1) {
        JsonResponse([
            'success'   => false,
            'message'   => "Invalid Rebate Commission",
            'data'      => []
        ]);
    }

    $rebateSetting = $sqlGetRebateSetting->fetch_assoc();
    $UPDATE_DATA = [
        "COMMSET_AMOUNT"  => $data["rebate_amount"] ?? 0
    ];

    $update = Database::update('tb_commset', $UPDATE_DATA, ["ID_COMMSET" => $rebateSetting["ID_COMMSET"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update data.",
            'data'      => []
        ]);
    }
    
    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "commision/rebatesetting",
        'message' => "Edit rebatesetting",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success edit rebatesetting",
        'data'      => []
    ]);