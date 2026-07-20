<?php
    use App\Models\Admin;
    use App\Models\Helper;
    use App\Models\Logger;
    use App\Models\Apuppt;
    use Config\Core\Database;

    
    /** Required */
    $data = Helper::getSafeInput($_POST);
    foreach(["val2"] as $req) {
        if(!isset($data[ $req ])) {
            $req = str_replace("add_", "", $req);
            JsonResponse([
                'code'      => 402,
                'success'   => false,
                'message'   => "{$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Get Range */
    $db = Database::connect();
    $SQL_VW = mysqli_query($db,'
        SELECT
            tb_range.RNG_MIN,
            tb_range.RNG_MAX,
            tb_range.RNG_LEVEL
        FROM tb_range
        WHERE tb_range.RNG_TYPE = 2
    ');
    if($SQL_VW && mysqli_num_rows($SQL_VW) > 0){
        $val = $data["val2"];
        while($RSLT_VW = mysqli_fetch_assoc($SQL_VW)){
            if((float)$val >= (float)$RSLT_VW["RNG_MIN"] && (float)$val <= (((float)$RSLT_VW["RNG_MAX"] == -1) ? INF : (float)$RSLT_VW["RNG_MAX"])){
                JsonResponse([
                    'code'      => 200,
                    'success'   => true,
                    'message'   => $RSLT_VW["RNG_LEVEL"],
                    'data'      => []
                ]);   
            }
        }
    }

    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => '',
        'data'      => []
    ]);