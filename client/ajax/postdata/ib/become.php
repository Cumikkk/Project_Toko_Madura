<?php

use App\Models\Helper;
use App\Models\Ib;
use App\Models\Logger;
use App\Models\User;
use Config\Core\Database;

$isAllowToBecomeIb = Ib::isAllowToBecomeIb(md5(md5($user['MBR_ID'])));
$data = Helper::getSafeInput($_POST);

/** check terms & condition */
if(empty($data['terms'])) {
    JsonResponse([
        'success' => false,
        'message' => "Mohon setuju dengan Ketentuan & Kebijakan",
        'data' => []
    ]);
}

if($isAllowToBecomeIb['success'] !== true) {
    JsonResponse([
        'success' => false,
        'message' => "Belum memenuhi persyaratan untuk mengajukan IB",
        'data' => []
    ]);
}

/** Check apakah sudah pernah request berhasil / pending */
$ibData = User::get_ib_data($user['MBR_ID'], [0, -1]);
if(is_array($ibData)) {
    JsonResponse([
        'success' => false,
        'message' => "Pengajuan telah dibuat",
        'data' => []
    ]);
}

/** Insert Become ib */
$jsonData = json_encode([...$data, $isAllowToBecomeIb]);
$insert = Database::insert("tb_become_ib", [
    'BECOME_MBR' => $user['MBR_ID'],
    'BECOME_DATA' => $jsonData,
    'BECOME_DATETIME' => date("Y-m-d H:i:s")
]);

if(!$insert) {
    JsonResponse([
        'success' => false,
        'message' => "Pengajuan telah dibuat",
        'data' => []
    ]);
}

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "request_ib",
    'message' => "Submit become ib",
    'data' => $data
]);

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => []
]);