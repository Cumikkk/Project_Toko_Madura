<?php
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;
    $data = Helper::getSafeInput($_POST);
    
    $UPDATE_DATA = [
        "COMMSET_SYMCAT"    => $data["rebate_symbolcat"] ?? 0,
        "COMMSET_PRODUCT"  => $data["rebate_product"] ?? 0,
        "COMMSET_SALESCAT"  => $data["rebate_structure"] ?? 0,
        "COMMSET_AMOUNT"  => $data["rebate_amount"] ?? 0
    ];

    $res = mysqli_query($db, "
        SELECT ID_COMMSET 
        FROM tb_commset 
        WHERE COMMSET_SYMCAT = ".$data["rebate_symbolcat"]." 
        AND COMMSET_PRODUCT = ".$data["rebate_product"]."
        AND COMMSET_SALESCAT = ".$data["rebate_structure"]."
    ");
    if($res->num_rows > 1) {
        exit(json_encode([
            'success'   => false,
            'alert'     => [
                'title' => "Gagal",
                'text'  => "Rebate Already Exist",
                'icon'  => "error"
            ]
        ]));  
    }

    $insert = Database::insert('tb_commset', $UPDATE_DATA);
    if(!$insert){
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
        'message' => "add rebatesetting",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Insert rebatesetting",
        'data'      => []
    ]);