<?php
    
    use App\Models\Account;
    use App\Models\Country;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
use App\Models\Regol;
use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/edit_profile_pribadi")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
        "sbmt_id",
        "pp-nama",
        "pp-tempat-lahir",
        "pp-tanggal-lahir",
        "pp-npwp",
        "pp-type-id",
        "pp-id-number",
        "pp-jenis-kelamin",
        "pp-ibu-kandung",
        "pp-status-perkawinan",
        "pp-alamat",
        "pp-kode-pos",
        "pp-nomor-telepon",
        "pp-nomor-fax",
        "pp-nomor-handphone",
        "pp-status-rumah",
        "pp-pendidikan-terakhir",
        "pp-kewarganegaraan"
    ];
    $data = Helper::getSafeInput($_POST);
    foreach($REQ_POST as $req) {
        if(in_array($req, ["pp-nomor-telepon", "pp-nomor-fax", "pp-nomor-handphone"])){
            if(!isset($data[ $req ])) {
                $req = str_replace("add_", "", $req);
                JsonResponse([
                    'code'      => 402,
                    'success'   => false,
                    'message'   => "{$req} diperlukan",
                    'data'      => []
                ]);
            }
        }else{
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
    }

    /** Check Id Account */
    $ACCOUNT_CHECK = Account::realAccountDetail($data["sbmt_id"]);
    if(!$ACCOUNT_CHECK){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Account id not found",
            'data'      => []
        ]);
    }

    /** Check Valid Account to proceeds */
    if(($ACCOUNT_CHECK["ACC_STS"] != -1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != 6)){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Invalid Account",
            'data'      => []
        ]);
    }

    /** check nama negara */
    if(!Country::getByName($data["pp-kewarganegaraan"])) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Country Name for kewarganegaraan",
            'data' => []
        ]);
    }

    if(strtolower($data['pp-status-perkawinan'] ?? "") == "kawin") {
        if(empty($data["pp-nama-suami-istri"])) {
            JsonResponse([
                'success' => false,
                'message' => "Nama Suami/Istri diperlukan",
                'data' => []
            ]);
        }
    }

    $tglLahir = $data["pp-tanggal-lahir"];
    $tglLahirObj = DateTime::createFromFormat('Y-m-d', $tglLahir);
    if (!$tglLahirObj || $tglLahirObj->format('Y-m-d') !== $tglLahir) {
        JsonResponse([
            'success' => false,
            'message' => "Format tanggal lahir tidak valid",
            'data' => []
        ]);
    }

    /**Stored data for update*/
    $UPDATE_DATA = [
        "ACC_FULLNAME"                   => $data["pp-nama"],
        "ACC_TEMPAT_LAHIR"               => $data["pp-tempat-lahir"],
        "ACC_TANGGAL_LAHIR"              => $data["pp-tanggal-lahir"],
        "ACC_F_APP_PRIBADI_NPWP"         => $data["pp-npwp"],
        "ACC_TYPE_IDT"                   => $data["pp-type-id"],
        "ACC_NO_IDT"                     => $data["pp-id-number"],
        "ACC_F_APP_PRIBADI_KELAMIN"      => $data["pp-jenis-kelamin"],
        "ACC_F_APP_PRIBADI_IBU"          => $data["pp-ibu-kandung"],
        "ACC_F_APP_PRIBADI_STSKAWIN"     => $data["pp-status-perkawinan"],
        "ACC_F_APP_PRIBADI_NAMAISTRI"    => $data["pp-nama-suami-istri"],
        "ACC_ADDRESS"                    => $data["pp-alamat"],
        "ACC_ZIPCODE"                    => $data["pp-kode-pos"],
        "ACC_F_APP_PRIBADI_TLP"          => $data["pp-nomor-telepon"],
        "ACC_F_APP_PRIBADI_FAX"          => $data["pp-nomor-fax"],
        "ACC_F_APP_PRIBADI_HP"           => $data["pp-nomor-handphone"],
        "ACC_F_APP_PRIBADI_STSRMH"       => $data["pp-status-rumah"],
        "ACC_F_APP_PENDIDIKAN"           => $data["pp-pendidikan-terakhir"],
        "ACC_F_APP_KEWARGANEGARAAN"      => $data["pp-kewarganegaraan"],
        "ACC_F_SID_KET" => 2,
        "ACC_F_SID_NOMER" => null
    ];

    $nomorSid = $data['pp-nomor-sid'] ?? null;
    if(!empty($nomorSid)) {
        /** validasi nomor SID */
        if(Regol::SidValidation($nomorSid, $data["pp-tanggal-lahir"]) === false) {
            JsonResponse([
                'success' => false,
                'message' => "Invalid SID Number format",
                'data' => []
            ]);
        }
        
        $UPDATE_DATA["ACC_F_SID_KET"] = 1;
        $UPDATE_DATA["ACC_F_SID_NOMER"] = $nomorSid;
    }

    /**Eksekusi database*/
    $update = Database::update('tb_racc', $UPDATE_DATA, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);
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
        'module' => "account/active_real_account/edit",
        'message' => "Edit profile pribadi",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Data",
        'data'      => []
    ]);