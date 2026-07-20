<?php

use App\Factory\FileUploadFactory;
use App\Factory\VerihubFactory;
use App\Models\Account;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\User;

$verihub = VerihubFactory::init();
$accountID = Helper::form_input($_POST['account'] ?? "");
$account = Account::realAccountDetail($accountID);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

if($account['ACC_TYPE_IDT'] != "KTP") {
    JsonResponse([
        'success' => false,
        'message' => "Types of identity are not supported",
        'data' => []
    ]);
}

/** check user */
$userdata = User::findByMemberId($account['ACC_MBR']);
if(!$userdata) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Userdata",
        'data' => []
    ]);
}

$uniqid = uniqid();
$reference_id = md5($userdata['MBR_ID'] . $uniqid);
$fileContentKTP = file_get_contents(FileUploadFactory::aws()->awsFile($account['ACC_F_APP_FILE_ID']));
$fileContentSelfie = file_get_contents(FileUploadFactory::aws()->awsFile($account['ACC_F_APP_FILE_FOTO']));
$sendVerification = $verihub->send_idVerification([
    'mbrid' => $userdata['MBR_ID'],
    'account_id' => md5($account['ID_ACC']),
    'nik'   => $account['ACC_NO_IDT'],
    'name'  => $account['ACC_FULLNAME'],
    'birth_date' => $account['ACC_TANGGAL_LAHIR'],
    'email' => $userdata['MBR_EMAIL'], 
    'phone' => $account['ACC_F_APP_PRIBADI_HP'], 
    'ktp_photo' => ("data:".$account['ACC_F_APP_FILE_ID_MIME'].";base64,".base64_encode($fileContentKTP)), 
    'selfie_photo' => ("data:".$account['ACC_F_APP_FILE_FOTO_MIME'].";base64,".base64_encode($fileContentSelfie)), 
    'reference_id' => $reference_id
]);

if(!$sendVerification['success']) {
    JsonResponse([
        'success' => false,
        'message' => $sendVerification['message'] ?? "Verifikasi Gagal",
        'data' => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "verifikasi_verihub",
    'message' => "Verification Verihub",
    'data'  => ['reference_id' => $reference_id]
]);

JsonResponse([
    'success' => true,
    'message' => "Verifikasi berhasil",
    'data' => []
]);