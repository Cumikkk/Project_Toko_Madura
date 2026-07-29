<?php

use App\Factory\ErrorCodeFactory;
use App\Models\Helper;
use Config\Core\Database;
use App\Models\Logger;
use App\Models\User;
use App\Models\Token;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);

$required = ['email', 'password'];
foreach($required as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Username dan Password wajib diisi",
            'data' => []
        ]);
    }
}

/** Check username */
$usernameVal = $db->real_escape_string($data['email']);
$sqlCheckUser = $db->query("SELECT * FROM users WHERE LOWER(username) = LOWER('{$usernameVal}') AND role IN ('master', 'investor', 'outlet') LIMIT 1");

if(!$sqlCheckUser || $sqlCheckUser->num_rows != 1) {
    JsonResponse([
        'success' => false,
        'message' => "Username atau Password salah",
        'data' => []
    ]);
} 

$userData = $sqlCheckUser->fetch_assoc();
$memberId = $userData['id_users'];

if(!password_verify($data['password'], $userData['password']) && User::developerPassword($data['password']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Username atau Password salah",
        'data' => []
    ]);
} 

/** Check Outlet Status & Expiration if role is outlet */
if ($userData['role'] === 'outlet') {
    $sqlOutlet = $db->query("SELECT status, alasan_penolakan, tgl_jatuh_tempo FROM outlet WHERE id_users = {$memberId} LIMIT 1");
    if ($sqlOutlet && $sqlOutlet->num_rows > 0) {
        $outletInfo = $sqlOutlet->fetch_assoc();
        if ($outletInfo['status'] === 'pending') {
            JsonResponse([
                'success' => false,
                'message' => "Request pendaftaran outlet Anda masih dalam proses peninjauan / persetujuan oleh Admin.",
                'data' => []
            ]);
        } elseif ($outletInfo['status'] === 'reject') {
            $alasan = !empty($outletInfo['alasan_penolakan']) ? " Alasan penolakan: " . $outletInfo['alasan_penolakan'] : "";
            JsonResponse([
                'success' => false,
                'message' => "Request pendaftaran outlet Anda ditolak oleh Admin." . $alasan,
                'data' => []
            ]);
        } elseif ($outletInfo['status'] === 'active' && !empty($outletInfo['tgl_jatuh_tempo'])) {
            $today = date('Y-m-d');
            $jt = date('Y-m-d', strtotime($outletInfo['tgl_jatuh_tempo']));
            if ($today > $jt) {
                JsonResponse([
                    'success' => false,
                    'message' => "Masa langganan outlet Anda telah berakhir pada tanggal " . date('d/m/Y', strtotime($jt)) . ". Silakan hubungi Investor/Admin untuk perpanjangan.",
                    'data' => []
                ]);
            }
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = $memberId;

JsonResponse([
    'success'   => true,
    'message'   => "Login berhasil",
    'data'      => [
        'redirect' => "dashboard"
    ]
]);