<?php
    
    use App\Models\Admin;
    use App\Models\User;
    use App\Models\Helper;
    use App\Models\Logger;
    use App\Models\Apuppt;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/apuppt/evaluasi_nasabah/action")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }
    
    /** Required */
    $data = Helper::getSafeInput($_POST);
    foreach(["mrx"] as $req) {
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

    /** Check ID */
    $MRX = Apuppt::dataEvaluasiNasabah($data["mrx"]);
    if(!$MRX){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Unknwon ID",
            'data'      => []
        ]);
    }

    
    /** Custom Variabel */
    $db      = Database::connect();
    $param   = Apuppt::$evNasParamPrp;
    $RSLT_DT = $MRX;

    /** Insert or Update Qwery */
    $ARR_FIL = array_filter($data, function($v, $k){
        global $param, $db;
        if(preg_match("/$param/", $k)){
            (int)$idc = str_replace($param, '', "$k");
            $SQL_CHECK = mysqli_query($db, 'SELECT 1 FROM tb_rangetype WHERE ID_RATYP = "'.$idc.'"');
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
                $quer     = 'SELECT 1 FROM tb_rangensb WHERE NSBR_TYPE = '.$idc.' AND ID_NSBR = '.base64_decode($v).'';
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
            'message'   => "Unknwon Param",
            'data'      => []
        ]);
    }

    
    $INSRT_QUER = [];
    $UPDTE_QUER = [];
    $APU_DB     = json_decode(((!empty($RSLT_DT["JSN_ID_APU"])) ? $RSLT_DT["JSN_ID_APU"] : '[]'), true);
    foreach($ARR_FIL as $ky => $vl){
        (int)$idc = str_replace($param, '', "$ky");
        $rnsb_val = (empty($vl)) ? 'NULL' : base64_decode($vl);

        if(isset($APU_DB["$idc"])){
            $UPDTE_QUER[$APU_DB["$idc"]] = $rnsb_val;
        }else{
            $INSRT_QUER[] = "
                INSERT INTO tb_apuppt SET 
                tb_apuppt.APU_ADM         = ".$user["ADM_ID"].",
                tb_apuppt.APU_MBR         = ".$RSLT_DT["MBR_ID"].",
                tb_apuppt.APU_ACC         = ".$RSLT_DT["ID_ACC"].",
                tb_apuppt.APU_RNGNSB      = $idc,
                tb_apuppt.APU_RNGNSB_VAL  = ".$rnsb_val.",
                tb_apuppt.APU_DATETIME    = '".date("Y-m-d H:i:s")."',
                tb_apuppt.APU_TIMESTAMP   = '".date("Y-m-d H:i:s")."'
            ";
        }
    }

    /** Check data query */
    if(count($INSRT_QUER) == 0 && count($UPDTE_QUER) == 0){
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
        if(count($INSRT_QUER) != 0){
            foreach ($INSRT_QUER as $vl) {
                $insert = $db->query($vl);
                if(!$insert) {
                    throw new Exception("Data cannot be inserted");
                }
            }
            $message    .= "Insert Data evaluasi. ";   
        }else if(count($UPDTE_QUER) != 0){
            foreach ($UPDTE_QUER as $id => $val) {
                $update = Database::update('tb_apuppt', ["APU_RNGNSB_VAL" => $val], ["ID_APU" => $id]);
                if(!$update) {
                    throw new Exception("Data cannot be updated");
                }
            }
            $message    .= "Update Data evaluasi. ";   
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
        'module' => "apuppt/evaluasi_nasabah/action",
        'message' => $message,
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success",
        'data'      => [
            "reloc" => '/apuppt/evaluasi_nasabah/view'
        ]
    ]);