<?php 

use App\Models\Helper;
use App\Models\User;

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

/** check password */
$password = Helper::form_input($_POST['password']);
if(!password_verify($password, $userData['MBR_PASS'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Password",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);