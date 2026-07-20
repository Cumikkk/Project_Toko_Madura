<?php
    
    use App\Models\Admin;
    use App\Models\Helper;
    use App\Models\Logger;
    use App\Models\Apuppt;
    use Config\Core\Database;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/apuppt/penilaian-risiko/update-tingkat-risiko")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    /** Required */
    $data = Helper::getSafeInput($_POST);
    foreach(["typ", "ixd"] as $req) {
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
    $IXD = Apuppt::checkRangeId($data["ixd"]);
    if(!$IXD){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "ID not found",
            'data'      => []
        ]);
    }
    
    /** Data processing */
    try {
        
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        switch ($data["typ"]) {
            case 1:
                
                foreach(["min", "max"] as $req) {
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

                /** DB Update */
                $update = Database::update('tb_range', ["RNG_MIN" => $data["min"], "RNG_MAX" => $data["max"]], ["ID_RNG" => $IXD["ID_RNG"], "RNG_TYPE" => $data["typ"]]);
                if(!$update){
                    JsonResponse([
                        'code'      => 200,
                        'success'   => false,
                        'message'   => "Cannot update data",
                        'data'      => []
                    ]);
                }

                $message = 'Meng-update Range Nilai Risiko. Level Yang Di Update '.$IXD["RNG_LEVEL"];
                break;

            case 2:
                if(empty($data["lvl"])) {
                    JsonResponse([
                        'code'      => 402,
                        'success'   => false,
                        'message'   => "Level diperlukan",
                        'data'      => []
                    ]);
                }
                switch ($data["lvl"]) {
                    case 'Menengah':
                        foreach(["max", "min"] as $req) {
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
                        $min = $data["min"];
                        $max = $data["max"];
                        break;
                    
                    default:
                        foreach(["max"] as $req) {
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
                        $min = 0;
                        $max = $data["max"];
                    break;
                }

                /** DB Update */
                $lvl = $data["lvl"];
                $update = Database::connect()->query('
                    UPDATE tb_range SET
                        tb_range.RNG_MIN = CASE 
                            WHEN "'.$lvl.'" = "Rendah" THEN tb_range.RNG_MIN
                            WHEN "'.$lvl.'" = "Menengah" THEN '.$min.'
                            WHEN "'.$lvl.'" = "Tinggi" THEN '.$max.'
                            ELSE tb_range.RNG_MIN
                        END,
                        tb_range.RNG_MAX = CASE 
                            WHEN "'.$lvl.'" = "Rendah" THEN '.$max.'
                            WHEN "'.$lvl.'" = "Menengah" THEN '.$max.'
                            WHEN "'.$lvl.'" = "Tinggi" THEN tb_range.RNG_MAX
                            ELSE tb_range.RNG_MAX
                        END
                    WHERE tb_range.ID_RNG = "'.$IXD["ID_RNG"].'"
                    AND tb_range.RNG_TYPE = '.$data["typ"].'
                ');
                if(!$update){
                    JsonResponse([
                        'code'      => 200,
                        'success'   => false,
                        'message'   => "Cannot update data",
                        'data'      => []
                    ]);
                }

                $message = 'Meng-update Klasifikasi Risiko Nasabah Berdasarkan Total Poin. Level Yang Di Update '.$IXD["RNG_LEVEL"];
                break;
            
            default:
                JsonResponse([
                    'code'      => 200,
                    'success'   => false,
                    'message'   => "Unknown action",
                    'data'      => []
                ]);
            break;
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
        'module' => "apuppt/penilaian-risiko/update-tingkat-risiko",
        'message' => $message,
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Data",
        'data'      => []
    ]);