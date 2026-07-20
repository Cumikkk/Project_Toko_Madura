<?php

use App\Factory\FileUploadFactory;
use App\Factory\VerihubFactory;
use Config\Core\Database;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\User;

$verihub = VerihubFactory::init();
$data = Helper::getSafeInput($_POST);
$arRequired = [
    'fullname'  => "Fullname",
    'place_of_birth' => "Place of Birth",
    'date_of_birth' => "Date of Birth",
    'type_idt' => "Identity Type",
    'no_idt' => "Identity Number"
];

foreach($arRequired as $key => $req) {
    if(empty($data[ $key ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$req} is required",
            'data' => []
        ]);
    }
}

/** Validasi sudah verifikasi / belum */
$isVerified = User::isVerified($user['MBR_ID']);
if($isVerified !== FALSE) {
    // if($isVerified['MBRFILE_STS'] == -1) {
        JsonResponse([
            'success' => false,
            'message' => "Documents have been verified",
            'data' => []
        ]);
    // }
}

/** Validate identity type */
if(!in_array(strtoupper($data['type_idt']), ['KTP'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Identity Type",
        'data' => []
    ]);
}

/** Validate no_idt */
if(is_numeric($data['no_idt']) === FALSE || strlen($data['no_idt']) < 16) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Identity Number",
        'data' => []
    ]);
}

/** Validasi NIK */
$nik = $data['no_idt'];
$typeIdt = $data['type_idt'];
$sqlCheckNik = $db->query("SELECT MBRFILE_MBR FROM tb_member_file WHERE MBRFILE_NUMBER = '{$nik}' AND MBRFILE_TYPE = '{$typeIdt}' LIMIT 1");
if($sqlCheckNik->num_rows != 0) {
    $pemilikNIK = $sqlCheckNik->fetch_assoc()['MBRFILE_MBR'] ?? 0;
    if($pemilikNIK == 0 || $pemilikNIK != $user['MBR_ID']) {
        JsonResponse([
            'success' => false,
            'message' => "Identity number has been used",
            'data' => []
        ]);
    }
}


/** Validate File KTP */
if(empty($_FILES['ktp_photo']) || $_FILES['ktp_photo']['error'] != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Please upload KTP photo",
        'data' => []
    ]);
}

$fotoKtp = $verihub->validate_photoKtp($_FILES['ktp_photo']);
if(!is_array($fotoKtp)) {
    JsonResponse([
        'success' => false,
        'message' => $fotoKtp,
        'data' => []
    ]);
}

/** Validasi File Selfie */
if(empty($_FILES['selfie_photo']) || $_FILES['selfie_photo']['error'] != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Please upload Selfie photo",
        'data' => []
    ]);
}

$fotoSelfie = $verihub->validate_photoSelfie($_FILES['selfie_photo']);
if(!is_array($fotoSelfie)) {
    JsonResponse([
        'success' => false,
        'message' => $fotoSelfie,
        'data' => []
    ]);
}

/** Upload File KTP */
$fileKtp = FileUploadFactory::aws()->upload_single($fotoKtp);
if(!is_array($fileKtp) || !array_key_exists("filename", $fileKtp)) {
    JsonResponse([
        'success' => false,
        'message' => $fileKtp ?? "Failed when uploading a KTP photo",
        'data' => []
    ]);
}

/** Upload File Selfie */
$fileSelfie = FileUploadFactory::aws()->upload_single($fotoSelfie);
if(!is_array($fileSelfie) || !array_key_exists("filename", $fileSelfie)) {
    JsonResponse([
        'success' => false,
        'message' => $fileSelfie ?? "Failed when uploading a Selfie photo",
        'data' => []
    ]);
}

/** Start Transaction */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);
        
/** Save File */
$typeIdt = strtoupper($data['type_idt']);
$nik = $data['no_idt'];
$datetime = date("Y-m-d H:i:s");
$sqlSaveFile = $db->prepare("INSERT INTO tb_member_file (MBRFILE_MBR, MBRFILE_TYPE, MBRFILE_NUMBER, MBRFILE_PHOTO1, MBRFILE_PHOTO2, MBRFILE_DATETIME) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE MBRFILE_TYPE = ?, MBRFILE_NUMBER = ?, MBRFILE_PHOTO1 = ?, MBRFILE_PHOTO2 = ?");
$sqlSaveFile->bind_param("isissssiss", $user['MBR_ID'], $typeIdt, $nik, $fileKtp['filename'], $fileSelfie['filename'], $datetime, $typeIdt, $nik, $fileKtp['filename'], $fileSelfie['filename']);
$execute = $sqlSaveFile->execute();
$sqlSaveFile->close();
if(!$execute) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed when saving photo",
        'data' => []
    ]);
}

/** Update Member */
$updateData = [
    'MBR_VERIF' => -1,
    'MBR_STS' => -1,
    'MBR_NAME' => $data['fullname'],
    'MBR_TMPTLAHIR' => $data['place_of_birth'],
    'MBR_TGLLAHIR' => date('Y-m-d H:i:s', strtotime($data['date_of_birth']))
];

$updateMember = Database::update("tb_member", $updateData, ['MBR_ID' => $user['MBR_ID']]);
if(!$updateMember) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Invalid State",
        'data' => []
    ]);
}

/** Send Verification Verihub */
$fileContentKTP = file_get_contents(FileUploadFactory::aws()->awsFile($fileKtp['filename']));
$fileContentSelfie = file_get_contents(FileUploadFactory::aws()->awsFile($fileSelfie['filename']));
$uniqid = uniqid();
$reference_id = md5($user['MBR_ID'] . $user['MBR_CODE'] . $uniqid);
$sendVerification = $verihub->send_idVerification([
    'mbrid' => $user['MBR_ID'],
    'nik'   => $data['no_idt'],
    'name'  => $data['fullname'],
    'birth_date' => $data['date_of_birth'],
    'email' => $user['MBR_EMAIL'], 
    'phone' => $user['MBR_PHONE'], 
    'selfie_photo' => ("data:".$fotoSelfie['type'].";base64,".base64_encode($fileContentSelfie)), 
    'ktp_photo' => ("data:".$fotoKtp['type'].";base64,".base64_encode($fileContentKTP)), 
    'reference_id' => $reference_id
]);

if(!$sendVerification['success']) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => $sendVerification['message'] ,
        'data' => []
    ]);
}

$data['uniqid'] = $uniqid;
$data['reference_id'] = $reference_id;
Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "verification",
    'message' => "Verification KTP & Selfie",
    'data'  => $data
]);

$db->commit();
JsonResponse([
    'success' => true,
    'message' => $sendVerification['message'] ?? "Successfull",
    'data' => [
        'redirect'  => "/dashboard" 
    ]
]);