<?php
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;
    $data = Helper::getSafeInput($_POST);
    
    /**Stored data for update*/
    $UPDATE_DATA = [
        "SLSSTRC_UP"    => $data["struc_upline"],
        "SLSSTRC_NAME"  => $data["struc_name"]
    ];

    $insert = Database::insert('tb_sales_structure', $UPDATE_DATA);
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
        'module' => "commision/structure",
        'message' => "add structure",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Insert Structure",
        'data'      => []
    ]);