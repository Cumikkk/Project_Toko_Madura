<?php
    
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/edit_kekayaan")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }
    $REQ_POST = [
        "sbmt_id",
        "ky-lokasi-rumah",
        "ky-nilai-njop",
        "ky-deposit-bank",
        "ky-jumlah",
        "ky-jumlah-kekayaan-lainya",
        "ky-sumber-penghasilan"
    ];
    $data = Helper::getSafeInput($_POST);
    foreach($REQ_POST as $req) {
        if(in_array($req, ["k-fax-kantor"])){
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

    /** Validasi sumber penghasilan */
    if(!in_array($data['ky-sumber-penghasilan'], App\Models\Regol::$sumberPenghasilan)) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid sumber penghasilan value",
            'data' => []
        ]);
    }

    /**Stored data for update*/
    $UPDATE_DATA = [
        "ACC_F_APP_KEKYAN_RMHLKS" => $data["ky-lokasi-rumah"],
        "ACC_F_APP_KEKYAN_NJOP" => $data["ky-nilai-njop"],
        "ACC_F_APP_KEKYAN_DPST" => $data["ky-deposit-bank"],
        "ACC_F_APP_KEKYAN_NILAI" => $data["ky-jumlah"],
        "ACC_F_APP_KEKYAN_LAIN" => $data["ky-jumlah-kekayaan-lainya"],
        "ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN"  => $data["ky-sumber-penghasilan"],
    ];

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
        'message' => "Edit Kekayaan",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Data",
        'data'      => []
    ]);