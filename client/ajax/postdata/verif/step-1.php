<?php

/** Required fullname */

use App\Models\Country;
use Config\Core\Database;
use App\Models\Helper;
use App\Models\Logger;

$data = Helper::getSafeInput($_POST);
if(empty($user['MBR_NAME'])) {
    if(empty($data['fullname'])) {
        JsonResponse([
            'success' => false,
            'message' => "Fullname field is required",
            'data' => []
        ]);
    }
}

/** Required phone */
if(empty($user['MBR_PHONE'])) {
    if(empty($data['phone'])) {
        JsonResponse([
            'success' => false,
            'message' => "Phone field is required",
            'data' => []
        ]);
    }
}

$fullname   = $data['fullname'] ?? $user['MBR_NAME'];
$phone      = $data['phone'] ?? $user['MBR_PHONE'];
$gender     = $data['gender'] ?? $user['MBR_JENIS_KELAMIN'];
$country    = $data['country'] ?? $user['MBR_COUNTRY'];
$address    = $data['address'] ?? $user['MBR_ADDRESS'];

/** validasi nama lengkap */
if(!preg_match('/^[a-zA-Z\s]+$/', $fullname)) {
    JsonResponse([
        'success' => false,
        'message' => "Nama Lengkap tidak valid",
        'data' => []
    ]);
}

// if(!preg_match("/^[a-zA-Z0-9\s',.\-\/#]+$/", $address)) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Terdapat karakter yang tidak valid pada alamat lengkap",
//         'data' => []
//     ]);
// }

/** check Country */
$d_country = Country::getByName($country);
if(!$d_country) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Country Name",
        'data' => []
    ]);
}

/** Update */
$updateData = [
    'MBR_NAME'  => $fullname,
    'MBR_PHONE' => $phone,
    'MBR_JENIS_KELAMIN' => $gender,
    'MBR_COUNTRY' => $d_country['COUNTRY_NAME'],
    'MBR_ADDRESS' => $address,
    'MBR_VERIF' => -1,
    'MBR_STS' => -1
];

$update = Database::update("tb_member", $updateData, ['MBR_ID' => $user['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to update data",
        'data' => []
    ]);
}

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "kyc",
    'data' => $data,
    'message' => "Success Verification Step 1",
]);

JsonResponse([
    'success' => true,
    'message' => "Verification successfull",
    'data' => [
        'redirect' => '/dashboard'
    ]
]);