<?php

use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Wilayah;
use Config\Core\Database;

// JsonResponse([
//     'success' => false,
//     'message' => "Maintenance",
//     'data' => []
// ]);

$data = Helper::getSafeInput($_POST);
$required = [
    'zip' => "Kodepos",
    'tempat_lahir' => "Tempat Lahir",
    'tanggal_lahir' => "Tanggal Lahir",
    'province' => "Provinsi",
    'city' => "Kabupaten/Kota",
    'district' => "Kecamatan",
    'villages' => "Kelurahan/Desa",
    // 'identity_type' => "Tipe Identitas",
    // 'identity_number' => "Nomor Identitas",
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

/** check kodepos */
if(is_numeric($data['zip']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Nomor kodepos harus berupa angka",
        'data' => []
    ]);
}

$kodepos = Wilayah::postalCode($data['province'], $data['city'], $data['district'], $data['villages'], $data['zip']);
if(!$kodepos) {
    JsonResponse([
        'success' => false,
        'message' => "Nomor Kode Pos tidak valid / terdaftar",
        'data' => []
    ]);
}

/* Update **/
$gender = empty($data['gender'])? "" : strtoupper($data['gender']);
$address = empty($data['address'])? "" : $data['address'];
$updateData = [
    'MBR_PROVINCE' => $kodepos['KDP_PROV'],
    'MBR_CITY' => $kodepos['KDP_KABKO'],
    'MBR_DISTRICT' => $kodepos['KDP_KECAMATAN'],
    'MBR_VILLAGES' => $kodepos['KDP_KELURAHAN'],
    'MBR_ZIP' => $kodepos['KDP_POS'],
    'MBR_TMPTLAHIR' => $data['tempat_lahir'],
    'MBR_TGLLAHIR' => date("Y-m-d", strtotime($data['tanggal_lahir'])),
    'MBR_ADDRESS' => $address,
    'MBR_JENIS_KELAMIN' => $gender
];

/** check Avatar */
if(!empty($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $uploadFile = FileUploadFactory::aws()->upload_single($_FILES['avatar'], "avatar");
    if(!is_array($uploadFile) || !array_key_exists("filename", $uploadFile)) {
        JsonResponse([
            'success' => false,
            'message' => $uploadFile ?? "Upload avatar gagal",
            'data' => []
        ]);
    }

    $updateData['MBR_AVATAR'] = $uploadFile['filename'];
}

$update = Database::update("tb_member", $updateData, ['MBR_ID' => $user['MBR_ID']]);
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
