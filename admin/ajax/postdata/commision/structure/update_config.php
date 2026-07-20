<?php
    
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;
    $CP   = App\Models\CompanyProfile::profilePerusahaan();
    if(!$CP){
        JsonResponse([
            'success' => false,
            'message' => "Invaldi Company Profile",
            'data' => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    $REQ_INPT = [
        "dorman_period" => "Dorman Period",
        "retextp"       => "Retention Extent Period"
    ];
    foreach($REQ_INPT as $req => $text) {
        if(empty($data[ $req ])) {
            JsonResponse([
                'success' => false,
                'message' => "Kolom {$text} wajib diisi",
                'data' => []
            ]);
        }
    }

    $update = Database::update('tb_profile', ["PROF_DORMAN" => $data["dorman_period"], "PROF_DORMAN_EXTEND" => $data["retextp"]], ["ID_PROF" => $CP["ID_PROF"]]);
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
        'module' => "commision/structure configuration",
        'message' => "Update structure configuration",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Structure Configuration",
        'data'      => []
    ]);