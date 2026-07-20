<?php

use App\Factory\FileUploadFactory;
    use App\Factory\MetatraderFactory;
use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\Admin;
    use App\Models\Logger;
    use App\Models\FileUpload;
    use App\Models\ProfilePerusahaan;
    use App\Models\Regol;
    use Config\Core\Database;
    use Config\Core\EmailSender;
    use Config\Core\SystemInfo;
    
    $apiManager = MetatraderFactory::apiManager();
    $apiTerminal = MetatraderFactory::apiTerminal();
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/account/dealer_action")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    $forceUpdate = (bool) ($data["force_update"] ?? false);
    foreach(["sbmt_id", "sbmt_act", "sbmt_note"] as $req) {
        if(empty($data[ $req ])) {
            $req = str_replace("add_", "", $req);
            JsonResponse([
                'success' => false,
                'message' => "{$req} diperlukan",
                'data' => []
            ]);
        }
    }

    /** Check accept or reject */
    if(!in_array(strtolower($data["sbmt_act"]), ["accept", "reject"])){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Invalid action",
            'data'      => []
        ]);
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
    if(($ACCOUNT_CHECK["ACC_STS"] != 1 || $ACCOUNT_CHECK["ACC_WPCHECK"] != 5)){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Account has been processed",
            'data'      => []
        ]);
    }

    /** Check Id Account Condition */
    $ACCOND_CHECK = Account::accoundCondition($ACCOUNT_CHECK["ID_ACC"]);
    if(!$ACCOND_CHECK){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Account condition id not found",
            'data'      => []
        ]);
    }

    /** Update Account condition data*/
    $UPDATE_ACCND = [
        "ACCCND_DATEMARGIN" => date("Y-m-d H:i:s")
    ];

    $logMessage = "";

    /**Execute database*/
    try {
        global $db;
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        /**Accept || Reject Processing*/
        $sendEmail = false;
        switch ($data["sbmt_act"]) {
            case 'accept':                
                /** check nomor login */
                if(empty($data['login'])) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Invalid Account Login",
                        'data' => []
                    ]);
                }

                /** check password master */
                if(empty($data['password'])) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Invalid Password Master",
                        'data' => []
                    ]);
                }

                /** check password investor */
                if(empty($data['investor'])) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Invalid Password Investor",
                        'data' => []
                    ]);
                }

                $login = $data['login'];
                if(is_numeric($login) === false) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Invalid Account Login",
                        'data' => [] 
                    ]);
                }

                /** Menampilkan konfirmasi force update nomor login jika nomor login yang diinputkan tidak sama dengan yang lama */
                $isDifferentLogin = $ACCOUNT_CHECK['ACC_LOGIN'] != $login;
                $isUpgradeFromCdds = ($ACCOUNT_CHECK['ACC_NEEDS_UPGRADE'] == 1);
                if($isUpgradeFromCdds && $isDifferentLogin) {
                    if($forceUpdate == false) {
                        $db->rollback();
                        JsonResponse([
                            'success' => false,
                            'message' => "Akun sudah memiliki nomor login karena merupakan upgrade dari CDDS ke CDD Standar. Apakah Anda ingin memaksa update dengan nomor login baru?",
                            'data' => [
                                'login' => $ACCOUNT_CHECK['ACC_LOGIN'],
                                'require_confirmation' => true
                            ] 
                        ]);
                    }
                }

                /** Update RACC */
                $logMessage = "Accept dealer, login: {$login}";
                $updateRaccData = [
                    'ACC_STS' => -1,
                    'ACC_LOGIN' => $login,
                    'ACC_PASS' => $data['password'],
                    'ACC_INVESTOR' => $data['investor'],
                    'ACC_WPCHECK' => 6,
                    'ACC_WPCHECK_DATE' => date("Y-m-d H:i:s")
                ];

                if($isUpgradeFromCdds) {
                    $updateRaccData['ACC_NEEDS_UPGRADE'] = 0;
                    $updateRaccData['ACC_UPGRADE_AT'] = date("Y-m-d H:i:s");
                }

                $updateRacc = Database::update('tb_racc', $updateRaccData, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);
                if(!$updateRacc) {
                    $db->rollback();
                    JsonResponse([
                        'success'   => false,
                        'message'   => "Gagal memperbarui data akun",
                        'data'      => []
                    ]);
                }

                /** Update Account condition */
                $updateAccnd = Database::update('tb_acccond', ['ACCCND_STS' => -1, 'ACCCND_LOGIN' => $login], ["ID_ACCCND" => $ACCOND_CHECK["ID_ACCCND"]]);
                if(!$updateAccnd) {
                    $db->rollback();
                    JsonResponse([
                        'success'   => false,
                        'message'   => "Gagal memperbarui account condition ",
                        'data'      => []
                    ]);
                }

                /**
                 * Create account metatrader jika
                 * - ACCCND_LOGIN is empty (kondisi standar untuk akun yang bukan merupakan upgrade dari CDDS ke CDD Standar)
                 * - ACC_LOGIN berbeda dengan yang diinputkan dan akun ini merupakan upgrade dari CDDS ke CDD Standar & mendapatkan konfirmasi untuk force update
                 */
                $isEmptyAccountCondition = empty($ACCOND_CHECK['ACCCND_LOGIN']);
                if($isEmptyAccountCondition) {
                    if(!$isUpgradeFromCdds || ($isDifferentLogin && $isUpgradeFromCdds && $forceUpdate)) {
                        /** create account metatrader  */
                        $accountData = [
                            "master_pass" => $data['password'], 
                            "investor_pass" => $data['investor'], 
                            "group" => $ACCOUNT_CHECK['RTYPE_GROUP'], 
                            "fullname" => $ACCOUNT_CHECK['ACC_FULLNAME'], 
                            "email" => $ACCOUNT_CHECK['MBR_EMAIL'], 
                            "leverage" => $ACCOUNT_CHECK['RTYPE_LEVERAGE'], 
                            "comment" => "metaapi"
                        ];
        
                        if($login > 0) {
                            $accountData['login'] = $login;
                        }
    
                        $accountCreate = $apiManager->createAccount($accountData);
                        if(!is_object($accountCreate) || !property_exists($accountCreate, "Login")) {
                            $db->rollback();
                            JsonResponse([
                                'success'   => false,
                                'message'   => "Gagal membuat akun metatrader",
                                'data'      => []
                            ]);
                        }
                    }
                }

                /** Test Connection Account Metatrader */
                $connect = $apiTerminal->connect(['login' => $login, 'password' => $data['password']]);
                if($connect) {
                    Database::update("tb_racc", ['ACC_TOKEN' => $connect], ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);
                }
                break;

            case 'reject':
                $updateRaccData = [
                    'ACC_WPCHECK' => Regol::$statusWPCheckRegister,
                    'ACC_WPCHECK_DATE' => date("Y-m-d H:i:s"),
                ];

                $updateRacc = Database::update('tb_racc', $updateRaccData, ["ID_ACC" => $ACCOUNT_CHECK["ID_ACC"]]);
                if(!$updateRacc) {
                    $db->rollback();
                    JsonResponse([
                        'success' => false,
                        'message' => "Gagal memperbarui data akun",
                        'data' => []
                    ]);
                }
                break;
            
            default:
                $db->rollback();
                JsonResponse([
                    'success'   => false,
                    'message'   => "Invalid action",
                    'data'      => []
                ]);
            break;
        }
        
        /** Insert note */
        Database::insert('tb_note', [
            "NOTE_MBR"   => $ACCOUNT_CHECK["ACC_MBR"],
            "NOTE_RACC"  => $ACCOUNT_CHECK["ID_ACC"],
            "NOTE_ACCDN" => $ACCOND_CHECK["ID_ACCCND"],
            "NOTE_TYPE"  => 'DEALER '.strtoupper($data["sbmt_act"]),
            "NOTE_NOTE"  => $data["sbmt_note"],
        ]);

        mysqli_commit($db);
        
    } catch (Exception | mysqli_sql_exception | Throwable $e) {
        mysqli_rollback($db);
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Exception occured. Please try again!. Exception : ".str_replace("'", "", $e->getMessage()),
            'data'      => []
        ]);
    }

    if($data["sbmt_act"] == "accept") {
        /** Email notifikasi dokumen telah disetujui */
        $emailData = [
            'subject' => "Dokumen Anda telah disetujui - " . (ProfilePerusahaan::get()['COMPANY_NAME'] ?? "?"),
            'name'=> $ACCOUNT_CHECK["ACC_FULLNAME"],
            'login' => $login,
            'metaPassword' => $data['password'],
            'metaInvestor' => $data['investor']
        ];

        $idAcc = md5(md5($ACCOUNT_CHECK['ID_ACC']));
        $sendEmail = EmailSender::init(['email' => $ACCOUNT_CHECK['MBR_EMAIL'], 'name' => $ACCOUNT_CHECK['MBR_NAME']]);
        $sendEmail->useFile("progress-real-account-success", $emailData);
        $sendEmail->addStringAttachment("PERJANJIAN NASABAH.pdf", SystemInfo::app('ADMIN_URL') . "/export/all?acc={$idAcc}");
        $sendEmail->addStringAttachment("BUKTI KONFIRMASI NASABAH.pdf", SystemInfo::app('ADMIN_URL') . "/export/bukti-konfirmasi-penerimaan-nasabah?acc={$idAcc}");

        /** Add document trading rules if exists */
        if($ACCOUNT_CHECK['RTYPE_FILE']) {
            $sendEmail->addStringAttachment("TRADING RULES.pdf", FileUploadFactory::aws()->awsFile($ACCOUNT_CHECK['RTYPE_FILE']));
        }

        $sendEmail->useInternal();
        $sendEmail->send();
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "account/progress_real_account/dealer",
        'message' => $logMessage,
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success ".$data["sbmt_act"],
        'data'      => (strtoupper($data["sbmt_act"] ?? "") == "ACCEPT") 
            ? ["reloc" => '/account/active_real_account/document/'.md5(md5($ACCOUNT_CHECK['ID_ACC']))]
            : ["reloc" => '/account/progress_real_account/view']
    ]);