<?php
    use App\Models\Admin;
    use App\Models\Apuppt;
    use App\Models\User;
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/apuppt/evaluasi_calon_nasabah/create")) {
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

    /** Check required value */
    if(!in_array($data["iser_r"], [0, 1, 2])){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Input tidak sesuai",
            'data'      => []
        ]);
    }


    /** Custom Variabel */
    $db      = Database::connect();
    $param   = Apuppt::$evaluasiCalonNasabahProp;
    $RSLT_DT = Apuppt::evaluasiCalonNasabah($data["mrx"]);
    $ARR_NC  = Apuppt::$ACCRCJTEVCNSP;
    $iser    = $data["iser_r"];

    /** Check parameter field */
    $ARR_FIL = array_filter($_POST, function($v, $k){
        global $param, $db;
        if(preg_match("/$param/", $k)){
            (int)$idc = str_replace($param, '', "$k");
            $SQL_CHECK = mysqli_query($db, 'SELECT 1 FROM tb_apuppt_evcannas_type WHERE ID_EVCANNAS_TYPE = "'.$idc.'"');
            if($SQL_CHECK && mysqli_num_rows($SQL_CHECK) > 0){
                return TRUE;
            }else{ return FALSE; }
        }
    }, ARRAY_FILTER_USE_BOTH);
    if(!(count(array_intersect($ARR_FIL, $ARR_NC)) === count($ARR_FIL))){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Data parameter tidak sama",
            'data'      => []
        ]);
    }

    /** Penambahan Query */
    $INSRT_QWER = [];
    foreach($ARR_FIL as $ky => $vl){
        (int)$idc = str_replace($param, '', "$ky");
        $INSRT_QWER[] = "
            INSERT INTO tb_apuppt_evcannas SET 
            tb_apuppt_evcannas.EVCAN_MBR  = ".$RSLT_DT["MBR_ID"].",
            tb_apuppt_evcannas.EVCAN_TYPE = $idc,
            tb_apuppt_evcannas.EVCAN_VAL  = $vl,
            tb_apuppt_evcannas.EVCAN_CONF = '$iser',
            tb_apuppt_evcannas.EVCAN_DATETIME = '".date("Y-m-d H:i:s")."',
            tb_apuppt_evcannas.EVCAN_TIMESTAMP = '".date("Y-m-d H:i:s")."';
        ";
    }

    /** Check data query */
    if(count($INSRT_QWER) == 0){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Data tidak boleh kosong",
            'data'      => []
        ]);
    }

    /** Insert database */
    try {
        
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        $ARR_WRD = array("Ditolak", "Dipertimbangkan", "Dilanjutkan");
        $message    = "Data Nasabah ".$RSLT_DT['ACC_F_APP_PRIBADI_NAMA']." dengan login: ".$RSLT_DT['ACC_LOGIN']." dan demo: ".$RSLT_DT['ACC_DEMO']." ".$ARR_WRD[$iser];   
        // mysqli_multi_query($db, $INSRT_QWER);
        foreach ($INSRT_QWER as $vl) {
            $insert = $db->query($vl);
            if(!$insert) {
                throw new Exception("Data cannot be inserted");
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
        'module' => "apuppt/evaluasi_calon_nasabah/create",
        'message' => $message,
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success ".$ARR_WRD[$iser],
        'data'      => [
            "reloc" => '/apuppt/evaluasi_calon_nasabah/view'
        ]
    ]);