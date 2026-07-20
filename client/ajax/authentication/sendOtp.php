<?php

use App\Factory\ApiWapanelFactory;
use App\Models\Helper;
use App\Factory\VerihubFactory;
use Config\Core\ApiWapanel;
use Config\Core\Database;

JsonResponse([
    'success' => false,
    'message' => "Invalid Request",
    'data' => []
]);

$verihub = VerihubFactory::init();
$data = Helper::getSafeInput($_POST);
$required = [
    'phone_code' => "Phone Code",
    'phone' => "Phone Number"
];

foreach($required as $req => $text) {
    if(empty($data[$req])) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} is required",
            'data' => []
        ]);
    }
}

/** Check Phone Number */
$phone = $verihub->phoneValidation($data['phone_code'], $data['phone']);
if(!$phone){
    JsonResponse([
        'success' => false,
        'message' => "Invalid Phone Number",
        'data' => []
    ]);
}

$sqlCheckPhone = $db->query("SELECT ID_MBR FROM tb_member WHERE MBR_PHONE = '{$phone}' LIMIT 1");
if($sqlCheckPhone->num_rows != 0) {
    JsonResponse([
        'success' => false,
        'message' => "Nomor Telepon telah terdaftar",
        'data' => []
    ]);
}

/** Check Terakhir Mengirim */
$sqlCheckOtp = $db->query("SELECT * FROM tb_member_pending WHERE MBR_PENDING_PHONE = {$phone} ORDER BY ID_MBR_PENDING DESC LIMIT 1");
if($sqlCheckOtp->num_rows == 1) {
    $memberPending = $sqlCheckOtp->fetch_assoc();
    $timenow = time();
    $timeExpired = strtotime($memberPending['MBR_PENDING_OTP_EXPIRED']);
    if($timeExpired > $timenow) {
        JsonResponse([
            'success' => false,
            'message' => "You have to wait a few minutes to send again",
            'data' => []
        ]);
    }
}

// $type = Helper::form_input($_POST['type'] ?? "");
// $type = strtolower($type);
$type = "sms";
$otp = random_int(1000, 9999);
$expiredSeconds = 0;
$sendOtp = false;
if(empty($type) || !in_array($type, ['whatsapp', 'sms'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

switch($type) {
    case "whatsapp":
        $expiredSeconds = 300; // 5 Menit
        $apiWapanel = ApiWapanelFactory::init();
        $sendOtp = $apiWapanel->sendMessage(['phone' => $phone, 'message' => "Kode OTP {$otp}"]);
        if(!is_array($sendOtp) || !array_key_exists("status", $sendOtp)) {
            JsonResponse([
                'success' => false,
                'message' => $sendOtp['message'] ?? "Invalid Message Status",
                'data' => []
            ]);
        }

        $sendOtp = [
            'success' => $sendOtp['status'],
            'message' => $sendOtp['message'] ?? "",
            'data' => $sendOtp['data']
        ];
        break;

    case "sms":
        $expiredSeconds = 90; // 1 menit 30 detik
        $phone = $verihub->phoneValidation($data['phone_code'], $data['phone']);
        if(!$phone){
            JsonResponse([
                'success' => false,
                'message' => "Invalid Phone Number",
                'data' => []
            ]);
        }
        
        $sendVerihub = $verihub->sendOtp_sms(['phone' => $phone, 'otp' => $otp, 'template' => 'RRFX KMNA OTP: $OTP']);
        $sendOtp = [
            'success' => $sendVerihub['success'],
            'message' => $sendVerihub['message'] ?? "",
            'data' => $sendVerihub['data']
        ];
        break;
}

if(!$sendOtp) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status",
        'data' => []
    ]);
}

if(!$sendOtp['success']) {
    JsonResponse([
        'success' => false,
        'message' => $sendOtp['message'],
        'data' => []
    ]);
}

/** Insert member pending */
$datetime = date("Y-m-d H:i:s");
$dateExpired = date("Y-m-d H:i:s", strtotime("+ {$expiredSeconds} second"));
$sqlInsert = $db->prepare("INSERT INTO tb_member_pending (MBR_PENDING_PHONE, MBR_PENDING_OTP, MBR_PENDING_OTP_SEND, MBR_PENDING_OTP_EXPIRED, MBR_PENDING_OTP_METHOD) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE MBR_PENDING_OTP = ?, MBR_PENDING_OTP_SEND = ?, MBR_PENDING_OTP_EXPIRED = ?, MBR_PENDING_OTP_METHOD = ?");
$sqlInsert->bind_param("sssssssss", $phone, $otp, $datetime, $dateExpired, $type, $otp, $datetime, $dateExpired, $type);
$sqlInsert->execute();

JsonResponse([
    'success' => true,
    'message' => "OTP code was successfully sent",
    'data' => [
        'delay' => $expiredSeconds
    ]
]);
