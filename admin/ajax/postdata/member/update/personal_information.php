<?php

use App\Factory\VerihubFactory;
use App\Models\Country;
use App\Models\Helper;
use App\Models\User;
use App\Models\Wilayah;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/member/update/personal_information")) {
    JsonResponse([
        'success' => false,
        'message' => "Permission Denied",
        'data' => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$verihub = VerihubFactory::init();

/** Initialize empty array update */
$updateData = [];

$required = [
    'code' => "User Code",
    'fullname' => "Nama Lengkap",
    'phone_code' => "Kode Telepon",
    'phone' => "Nomor Telepon",
    // 'province' => "Provinsi",
    // 'regency' => "Kabupaten/Kota",
    // 'district' => "Kecamatan",
    // 'villages' => "Keluarahan/Desa",
    // 'postal_code' => "Kode pos",
    // 'place_of_birth' => "Tempat Lahir",
    // 'date_of_birth' => "Tanggal Lahir",
    // 'gender' => "Jenis Kelamin",
];

// foreach($required as $key => $value) {
//     if(empty($data[ $key ])) {
//         JsonResponse([
//             'success' => false,
//             'message' => "Kolom {$value} wajib diisi",
//             'data' => []
//         ]);
//     }
// }

$userData = User::findByCode($data['code']);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Userdata",
        'data' => []
    ]);
} 

if(!empty($data['fullname'])) {
    /** validasi nama lengkap */
    if(!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ'’`ʼ\-\.\s]{2,100}$/", $data['fullname'])) {
        JsonResponse([
            'success' => false,
            'message' => "Nama Lengkap tidak valid. hanya boleh huruf, spasi, tanda hubung (-), titik (.), atau apostrof (').",
            'data' => []
        ]);
    }

    $updateData['MBR_NAME'] = $data['fullname'];
}


/** Validasi Nomor Telepon */
if(!empty($data['phone_code']) && !empty($data['phone'])) {
    $phone = $verihub->phoneValidation($data['phone_code'], $data['phone']);
    if(!$phone) {
        JsonResponse([
            'success' => false,
            'message' => "Invalid Phone Number",
            'data' => []
        ]);
    }
    
    /** Check pajang nomor telepon */
    $phoneLength = strlen(str_replace($data['phone_code'], "", $phone) ?? 0);
    if($phoneLength < 10 || $phoneLength > 13) {
        JsonResponse([
            'success' => false,
            'message' => "Nomor telepon harus lebih dari 9 digit dan kurang dari 14 digit",
            'data' => []
        ]);
    }
    
    /** Check nomor telepon sudah terdaftar / belum */
    $sqlCheckPhone = $db->query("SELECT ID_MBR FROM tb_member WHERE MBR_PHONE = '{$phone}' AND MBR_ID != ".$userData['MBR_ID']." LIMIT 1");
    if($sqlCheckPhone->num_rows != 0) {
        JsonResponse([
            'success' => false,
            'message' => "Nomor Telepon telah terdaftar",
            'data' => []
        ]);
    }

    $updateData['MBR_PHONE_CODE'] = $data['phone_code'];
    $updateData['MBR_PHONE'] = $phone;
}

/** check kodepos */
if(!empty($data['postal_code'])) {
    if(is_numeric($data['postal_code']) === FALSE) {
        JsonResponse([
            'success' => false,
            'message' => "Nomor kodepos harus berupa angka",
            'data' => []
        ]);
    }
}

/** validasi tempat lahir */
if(!empty($data['place_of_birth'])) {
    $updateData['MBR_TMPTLAHIR'] = $data['place_of_birth'];
}

/** validasi tanggal lahir */
if(!empty($data['date_of_birth'])) {
    $updateData['MBR_TGLLAHIR'] = date("Y-m-d", strtotime($data['date_of_birth']));
}

/** validasi jenis kelamin */
if(!empty($data['gender'])) {
    if(!in_array(strtoupper($data['gender']), ['LAKI-LAKI', 'PEREMPUAN'])) {
        JsonResponse([
            'success' => false,
            'message' => "Jenis kelamin tidak valid",
            'data' => []
        ]);
    }

    $updateData['MBR_JENIS_KELAMIN'] = strtoupper($data['gender']);
}

/** validasi alamat lengkap */
if(!empty($data['address'])) {
    $updateData['MBR_ADDRESS'] = $data['address'] ?? "";
}

/** check kodepos */
if(!empty($data['province']) || !empty($data['regency']) || !empty($data['district']) || !empty($data['villages']) || !empty($data['postal_code'])) {
    $requiredAddressFields = [
        'province' => "Provinsi",
        'regency' => "Kabupaten/Kota",
        'district' => "Kecamatan",
        'villages' => "Keluarahan/Desa",
        'postal_code' => "Kode pos",
    ];

    foreach($requiredAddressFields as $key => $value) {
        if(empty($data[ $key ])) {
            JsonResponse([
                'success' => false,
                'message' => "Kolom {$value} wajib diisi jika ingin memperbarui data alamat",
                'data' => []
            ]);
        }
    }
    
    $kodepos = Wilayah::postalCode($data['province'], $data['regency'], $data['district'], $data['villages'], $data['postal_code']);
    if(!$kodepos) {
        JsonResponse([
            'success' => false,
            'message' => "Nomor Kode Pos tidak valid / terdaftar",
            'data' => []
        ]);
    }

    $updateData['MBR_PROVINCE'] = $data['province'];
    $updateData['MBR_REGENCY'] = $data['regency'];
    $updateData['MBR_DISTRICT'] = $data['district'];
    $updateData['MBR_VILLAGES'] = $data['villages'];
    $updateData['MBR_ZIP'] = $data['postal_code'];
}

if(empty($updateData)) {
    JsonResponse([
        'success' => false,
        'message' => "Tidak ada data yang diperbarui",
        'data' => []
    ]);
}

$update = Database::update("tb_member", $updateData, ['MBR_ID' => $userData['MBR_ID']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Gagal memperbarui data",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);