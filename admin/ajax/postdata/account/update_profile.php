<?php
    
    use App\Models\Account;
    use App\Models\Helper;
    use App\Models\Regol;
    use App\Models\Logger;
    use Config\Core\Database;
    if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
        JsonResponse([
            'success' => false,
            'message' => "Authorization Failed",
            'data' => []
        ]);
    }

    $accountId = Helper::form_input($_POST['x'] ?? "");
    $account   = Account::getProgressRealAccount_byID($accountId);
    $nmebu     = $account["ACC_FULLNAME"];
    if(!$account) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Account",
            'data' => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    $REQUIRED = [
        "naleng"            => "Nama Lengkap",
        "smls_tipeidt"      => "Tipe Identitas",
        "smls_nomidt"       => "Nomer Identitas",
        "smls_tgllhr"       => "Tanggal Lahir",
        "smls_tmptlhr"      => "Tempat Lahir",
        "app_no_handphone"  => "Nomer Handphone",
        "app_nama_ibu"      => "Nama Ibu Kandung",
        // "sid_nmbr"          => "Single Investor Identification (SID)"
    ];

    if($account['ACC_CDD'] == 2) {
        $REQUIRED['app_npwp'] = "NPWP";
    }

    foreach($REQUIRED as $kreq => $vreq) {
        if(empty($data[ $kreq ])) {
            JsonResponse([
                'success'   => false,
                'message'   => "{$vreq} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Check nama */
    if(preg_match('/\d+/i', $data['naleng'])){
        JsonResponse([
            'success'   => false,
            'message'   => "Nama lengkap tidak bisa berisi angka",
            'data'  => []
        ]);
    }

    /** Check Tipe identitas */
    if(!in_array($data['smls_tipeidt'], Regol::$typeIdentitas)) {
        JsonResponse([
            'success'   => false,
            'message'   => "Tipe identitas tidak didukung",
            'data'  => []
        ]);
    }

    /** Check No Identitas */
    switch(strtoupper($data['smls_tipeidt'])) {
        case "KTP":
            $noKtp = $data['smls_nomidt'];
            if(is_numeric($noKtp) === FALSE) {
                JsonResponse([
                    'success'   => false,
                    'message'   => "Nomor KTP tidak valid",
                    'data'  => []
                ]);
            }

            if($account['ACC_CDD'] == 2 && strlen($noKtp) < 16) {
                JsonResponse([
                    'success'   => false,
                    'message'   => "Nomor KTP harus 16 digit atau lebih",
                    'data'  => []
                ]);
            }
            break;

        case "PASSPORT":
            $noPsprt = $data['smls_nomidt'];
            if(!preg_match("/^[0-9A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]{7,9}$/i", $noPsprt)) {
                JsonResponse([
                    'success'   => false,
                    'message'   => "Nomor PASSPORT tidak valid",
                    'data'  => []
                ]);
            }
            break;

        case "KITAS":
            if(strlen($data['smls_nomidt']) < 11) {
                JsonResponse([
                    'success'    => false,
                    'message'   => "Nomor KITAS harus 11 digit atau lebih",
                    'data'  => []
                ]);
            }
            break;
    }

    
    /** Check No Identitas apakah sudah digunakan user lain */
    $noIdentitas = $data['smls_nomidt'];
    $sqlCheckIdentitas = $db->query("
        SELECT 
            ID_ACC 
        FROM tb_racc
        JOIN tb_member ON (ACC_MBR = MBR_ID)
        WHERE ACC_NO_IDT = '{$noIdentitas}' 
        AND ACC_MBR != '".$account['ACC_MBR']."' 
        AND (MBR_EMAIL NOT LIKE '%deleted%' OR MBR_STS != 1)
        LIMIT 1
    ");
    
    if($sqlCheckIdentitas->num_rows != 0) {
        JsonResponse([
            'success'    => false,
            'message'   => "Nomor ".strtoupper($data['smls_tipeidt'])." telah terdaftar/digunakan",
            'data'  => []
        ]);
    }

    /**Check NPWP*/
    if($account['ACC_CDD'] == 2 && strlen($data['app_npwp']) != 16) {
        JsonResponse([
            'success' => false,
            'message'   => "Nomor NPWP harus 16 digit",
            'data'  => []
        ]);
    }

    $data['sid_pernyataan'] = 'Tidak';
    if(!empty($data['sid_nmbr'])){
        $data['sid_pernyataan'] = 'Ya';
        
        if(Regol::SidValidation($data['sid_nmbr'], $account["ACC_TANGGAL_LAHIR"]) === false) {
            JsonResponse([
                'success' => false,
                'message' => "Invalid SID Number format",
                'data' => []
            ]);
        }
    }


    $CL_NAME = [
        "ACC_FULLNAME"           => $data["naleng"],
        "ACC_TYPE_IDT"           => $data["smls_tipeidt"],
        "ACC_NO_IDT"             => strtoupper($data['smls_nomidt']),
        "ACC_TANGGAL_LAHIR"      => date("Y-m-d", strtotime($data['smls_tgllhr'])),
        "ACC_TEMPAT_LAHIR"       => $data["smls_tmptlhr"],
        "ACC_F_APP_PRIBADI_HP"   => $data["app_no_handphone"],
        "ACC_F_APP_PRIBADI_NPWP" => $data["app_npwp"],
        "ACC_F_APP_PRIBADI_IBU"  => $data["app_nama_ibu"],
        "ACC_F_SID_PERYT"        => $data["sid_pernyataan"],
        "ACC_F_SID_NOMER"        => $data["sid_nmbr"]
    ];
    
    $update = Database::update('tb_racc', $CL_NAME, ["ID_ACC" => $account["ID_ACC"]]);
    if(!$update) {
        JsonResponse([
            'success'   => false,
            'message'   => "Gagal update category " . $category_name,
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "WP Confirm",
        'message' => "Mengupdate data pribadi $nmebu",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Data pribadi berhasil diupdate",
        'data'      => []
    ]);