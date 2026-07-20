<?php

use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Authorization Denied",
        'data' => []
    ]);
}

/** check user */
$userCode = Helper::form_input($_POST['code'] ?? "");
$userData = User::findByCode($userCode);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Userdata",
        'data' => []
    ]);
}

/** new email */
$newEmail = Helper::form_input($_POST['email'] ?? '');
if(empty($newEmail)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Email",
        'data' => []
    ]);
}

/** check email */
$sqlCheckEmail = $db->query("SELECT * FROM tb_member WHERE LOWER(MBR_EMAIL) = LOWER('".$newEmail."') AND MBR_ID != ".$userData['MBR_ID']);
if($sqlCheckEmail->num_rows != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Email sudah terdaftar",
        'data' => []
    ]);
}

if(empty($_FILES['image']) || $_FILES['image']['error'] != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Mohon Upload Dokumen",
        'data' => []
    ]);
}

$uploadFile = FileUploadFactory::aws()->upload_single($_FILES['image'], 'change_email_'.$userData["MBR_ID"]);
if(!is_array($uploadFile) || !array_key_exists("filename", $uploadFile)) {
    JsonResponse([
        'success' => false,
        'message' => $uploadFile ?? "Gagal upload dokumen",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

$insert = Database::insert("tb_chmail_log", [
    'CHML_ADM' => $user['ADM_ID'],
    'CHML_MBR' => $userData['MBR_ID'],
    'CHML_PREV_MAIL' => $userData['MBR_EMAIL'],
    'CHML_NEXT_MAIL' => $newEmail,
    'CHML_FILE' => $uploadFile['filename'],
]);

if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal",
        'data' => []
    ]);
}

/** update email */
$update = Database::update("tb_member", ['MBR_EMAIL' => $newEmail], ['MBR_ID' => $userData['MBR_ID']]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui email",
        'data' => []
    ]);
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Berhasil mengganti email",
    'data' => []
]);