<?php

use App\Models\Helper;
use App\Models\Logger;
use App\Models\SendEmail;
use App\Models\User;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$required = [
    'current_pass' => "Password Saat ini",
    'new_pass' => "Password Baru",
    'confirm_new_pass' => "Konfirmasi Password Baru"
];

foreach($required as $key => $text) {
    if(empty($data[ $key ])) {
        JsonResponse([
            'success' => false,
            'message' => "Kolom {$text} wajib diisi",
            'data' => []
        ]);
    }
}

/** Check password saat ini */
if(!password_verify($data['current_pass'], $user['MBR_PASS'])) {
    JsonResponse([
        'success' => false,
        'message' => "Password Salah",
        'data' => []
    ]);
}

/** validasi password */
$isValidate = User::validation_password($data['new_pass']);
if($isValidate !== TRUE) {
    JsonResponse([
        'success' => false,
        'message' => $isValidate ?? "Format password salah",
        'data' => []
    ]);
}

/** check password konfirmasi */
if(base64_encode($data['new_pass']) != base64_encode($data['confirm_new_pass'])) {
    JsonResponse([
        'success' => false,
        'message' => "Password konfirmasi salah",
        'data' => []
    ]);
}

/** Update Password */
$passwordHash = password_hash($data['new_pass'], PASSWORD_BCRYPT);
$update = Database::update("tb_member", ['MBR_PASS' => $passwordHash], ['MBR_ID' => $user['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui password",
        'data' => []
    ]);
}

// /** send email */
// $sendEmail = new SendEmail();
// $sendEmail->useDefault()
//     ->useFile("update-password", ['subject' => "Update Password"])
//     ->destination($user['MBR_EMAIL'], $user['MBR_NAME'])
//     ->send();

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "security/update-password",
    'message' => "Memperbarui password",
    'data' => []
]);

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);