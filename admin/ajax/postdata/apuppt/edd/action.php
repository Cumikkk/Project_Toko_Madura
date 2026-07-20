<?php
    
    use App\Models\Admin;
    use App\Models\User;
    use App\Models\Helper;
    use App\Models\Logger;
    use App\Models\Apuppt;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/apuppt/edd/action")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    /** Required */
    $data = Helper::getSafeInput($_POST);
    foreach(["iser", "anf", "rkm"] as $req) {
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

    /** Custom Variabels */
    $db    = Database::connect();
    $param = Apuppt::$eddParam;
    $x     = $data["iser"];

    /** Check Account ID */
    $CUSER = Apuppt::dataEvaluasiNasabah($data["iser"]);
    if(!$CUSER){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Account ID Not Found!.",
            'data'      => []
        ]);
    }

    /** Check Parameter Value */
    $ARR_FIL = array_filter($_POST, function($v, $k){
        global $param, $db;
        if(preg_match("/$param/", $k)){
            (int)$idc = str_replace($param, '', "$k");
            $SQL_CHECK = mysqli_query($db, 'SELECT 1 FROM tb_range_edtype WHERE ID_EDTYPE = "'.$idc.'"');
            if($SQL_CHECK && mysqli_num_rows($SQL_CHECK) > 0){
                return TRUE;
            }else{ return FALSE; }
        }
    }, ARRAY_FILTER_USE_BOTH);
    $ARR_NC  = array_filter($ARR_FIL, function($v, $k){
        global $param, $db;
        if(!empty($v)){
            if(preg_match("/$param/", $k)){
                (int)$idc = str_replace($param, '', "$k");
                $quer     = 'SELECT 1 FROM tb_range_edd WHERE EDD_TYPE = '.$idc.' AND ID_EDD = '.base64_decode($v).'';
                $SQL_CHECK = mysqli_query($db, $quer);
                if($SQL_CHECK && mysqli_num_rows($SQL_CHECK) > 0){
                    return TRUE;
                }else{ return FALSE; }
            }
        }else{ return TRUE; }
    }, ARRAY_FILTER_USE_BOTH);
    if(count($ARR_NC) != count($ARR_FIL)){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Parameter not matches",
            'data'      => []
        ]);
    }

    /** Query data */
    $INSRT_QUER = [];
    $hstrid     = Apuppt::getHstrId();
    foreach($ARR_FIL as $ky => $vl){
        (int)$idc = str_replace($param, '', "$ky");
        $rnsb_val = (empty($vl)) ? 'NULL' : base64_decode($vl);
        $INSRT_QUER[] = "
            INSERT INTO tb_apuppt_edd SET 
            tb_apuppt_edd.ADD_HSTRID    = $hstrid,
            tb_apuppt_edd.ADD_MBR       = ".$CUSER["ACC_MBR"].",
            tb_apuppt_edd.ADD_ADM       = ".$user["ADM_ID"].",
            tb_apuppt_edd.ADD_TYP       = $idc,
            tb_apuppt_edd.ADD_VAL       = $rnsb_val,
            tb_apuppt_edd.ADD_ARF       = '".$data["anf"]."',
            tb_apuppt_edd.ADD_RKM       = '".$data["rkm"]."',
            tb_apuppt_edd.ADD_DATTIME   = '".date("Y-m-d H:i:s")."',
            tb_apuppt_edd.ADD_TIMESTAMP = '".date("Y-m-d H:i:s")."'
        ";
    }

    /** Check data query */
    if(count($INSRT_QUER) == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Data tidak boleh kosong",
            'data'      => []
        ]);
    }

    /** Store to database */
    try {
        
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);


        $message    = "";   
        foreach ($INSRT_QUER as $vl) {
            $insert = $db->query($vl);
            if(!$insert) {
                throw new Exception("Data cannot be inserted");
            }
        }
        $message    .= "Insert Data evaluasi EDD. ";   

        if(count($_SESSION["EQT"]) != 0){
            foreach($_SESSION["EQT"] as $VL){
                // $eqt = Database::insert('tb_equity', ["EQTY_LOGIN" => $VL["lgn"], "EQTY_VAL" => $VL["vl"], "EQTY_APU_ID" => $hstrid]);
                $eqt = $db->query('INSERT INTO tb_equity SET EQTY_LOGIN = "'.$VL["lgn"].'", EQTY_VAL = "'.$VL["vl"].'", EQTY_APU_ID = "'.$hstrid.'"');
                if(!$eqt) {
                    throw new Exception("Data equity cannot be inserted");
                }
            }
        }

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
        'module' => "apuppt/edd/action",
        'message' => $message,
        'data'  => $data
    ]);

    unset($_SESSION["EQT"]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success",
        'data'      => [
            "reloc" => '/apuppt/edd/view'
        ]
    ]);