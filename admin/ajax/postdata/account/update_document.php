<?php

use App\Factory\FileUploadFactory;
use App\Factory\VerihubFactory;
use App\Models\Account;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Authorization Failed",
        'data' => []
    ]);
}

$verihub = VerihubFactory::init();
$accountId = Helper::form_input($_POST['account'] ?? "");
$account = Account::getProgressRealAccount_byID($accountId);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

$imageList = [
    'app_image_1' => [
        'name' => "Rekening Koran Bank / Tagihan Kartu Kredit",
        'column' => 'ACC_F_APP_FILE_IMG',
    ],
    'app_image_2' => [
        'name' => "Rekening Listrik / Telepon",
        'column' => 'ACC_F_APP_FILE_IMG2',
    ],
    'app_image_3' => [
        'name' => "Dokumen Pendukung Lainnya (1)",
        'column' => 'ACC_F_APP_FILE_IMG3',
    ],
    'app_image_4' => [
        'name' => "Dokumen Pendukung Lainnya (2)",
        'column' => 'ACC_F_APP_FILE_IMG4',
    ],
    'app_image_npwp' => [
        'name' => "NPWP",
        'column' => "ACC_F_APP_FILE_NPWP",
    ],
    'app_image_selfie' => [
        'name' => "Foto Terbaru (Selfie)",
        'column' => "ACC_F_APP_FILE_FOTO",
        'columnType' => "ACC_F_APP_FILE_FOTO_MIME",
        'validateFunction' => "validate_photoSelfie"
    ],
    'app_image_identitas' => [
        'name' => "Foto Identitas",
        'column' => "ACC_F_APP_FILE_ID",
        'columnType' => "ACC_F_APP_FILE_ID_MIME",
        'validateFunction' => "validate_photoKtp"
    ],
];

/** check harus ada image yang di upload */
$totalUpload = [];
foreach(array_keys($imageList) as $img) {
    if(!empty($_FILES[ $img ]) && $_FILES[ $img ]['error'] == 0) {
        $totalUpload[] = $img;
    }
}

if(count($totalUpload) <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Mohon upload setidaknya 1 dokumen",
        'data' => []
    ]);
}

$uploaded = [];
foreach($imageList as $post_key => $imageInfo) {
    switch(in_array($post_key, ['app_image_selfie', 'app_image_identitas'])) {
        case false:
            /** check is valid document */
            if(!empty($_FILES[ $post_key ]) && $_FILES[ $post_key ]['error'] == 0) {
                $uploadDokumen = FileUploadFactory::aws()->upload_single($_FILES[ $post_key ], "regol_wp_update");
                if(!is_array($uploadDokumen) || !array_key_exists("filename", $uploadDokumen)) {
                    JsonResponse([
                        'success' => false,
                        'message' => $uploadDokumen ?? "Gagal mengunggah file " . $imageInfo['name'],
                        'data' => []
                    ]);
                }
            
                Database::update("tb_racc", [$imageInfo['column'] => $uploadDokumen['filename']], ['ID_ACC' => $account['ID_ACC']]);
                $uploaded[] = $imageInfo['name'];
            }
            break;

        case true:
            if(!empty($_FILES[ $post_key ]) && $_FILES[ $post_key ]['error'] == 0) {
                $uploadDokumen = FileUploadFactory::aws()->upload_single($_FILES[ $post_key ], "regol_wp_update");
                if(!is_array($uploadDokumen) || !array_key_exists("filename", $uploadDokumen)) {
                    JsonResponse([
                        'success' => false,
                        'message' => $uploadDokumen ?? "Gagal mengunggah file " . $imageInfo['name'],
                        'data' => []
                    ]);
                }
            
                $updateData = [
                    $imageInfo['column'] => $uploadDokumen['filename'],
                    $imageInfo['columnType'] => $uploadDokumen['mime']
                ];

                Database::update("tb_racc", $updateData, ['ID_ACC' => $account['ID_ACC']]);
                $uploaded[] = $imageInfo['name'];
            }

            /** validasi file untuk verihub sebelum di upload */
            // if(!empty($_FILES[ $post_key ]) && $_FILES[ $post_key ]['error'] == 0) {
            //     $checkDokumen = call_user_func_array([$verihub, $imageInfo['validateFunction']], [$_FILES[ $post_key ]]);
            //     if(!is_array($checkDokumen)) {
            //         JsonResponse([
            //             'success' => false,
            //             'message' => "Gagal upload dokumen ". $imageInfo['name'],
            //             'data' => []
            //         ]);
            //     }

            //     if(empty($checkDokumen['image_scaling'])) {
            //         JsonResponse([
            //             'success' => false,
            //             'message' => "Invalid ". $imageInfo['name'],
            //             'data' => []
            //         ]);
            //     }

            //     $newFileName = "regol_wp_update".time().rand(1000000, 9999999).".jpeg";
            //     $target_dir = WEB_ROOT . "/assets/uploads/{$newFileName}";
            //     $upload_local = file_put_contents($target_dir, $checkDokumen['image_scaling']);
            //     if(!$upload_local) {
            //         JsonResponse([
            //             'success' => false,
            //             'message' => "Gagal mengunggah foto ". $imageInfo['name'],
            //             'data' => []
            //         ]);
            //     }
            
            //     /** Upload to AWS */
            //     $fileUploadFactory = FileUploadFactory::aws();
            //     $s3 = new Aws\S3\S3Client([
            //         'region'  => $fileUploadFactory->getRegion(),
            //         'version' => 'latest',
            //         'credentials' => [
            //             'key'    => $fileUploadFactory->getKey(),
            //             'secret' => $fileUploadFactory->getSecretKey(),
            //         ]
            //     ]);

            //     try {
            //         /** Upload to AWS */
            //         $result = $s3->putObject([
            //             'Bucket' => $fileUploadFactory->getBucket(),
            //             'Key'    => $fileUploadFactory->getFolder() ."/".$newFileName,
            //             'Body'   => fopen($target_dir, 'r'),
            //             'ACL'    => 'public-read', // make file 'public'
            //         ]);

            //         /** Delete file from local disk */
            //         unlink($target_dir);

                    
            //     } catch (Aws\S3\Exception\S3Exception $e) {
            //         JsonResponse([
            //             'success' => false,
            //             'message' => "Gagal mengunggah foto ".$imageInfo['name']." (402)",
            //             'data' => []
            //         ]);
            //     }
                
            //     $data = [
            //         $imageInfo['column'] => $newFileName,
            //         $imageInfo['columnType'] => "image/jpeg"
            //     ];

            //     Database::update("tb_racc", $data, ['ID_ACC' => $account['ID_ACC']]);
            //     $uploaded[] = $imageInfo['name'];
            // }

            break;
    }
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "account/update_document",
    'message' => " Update Account Document",
    'data'  => $totalUpload
]);

JsonResponse([
    'success' => true,
    'message' => "Berhasil memperbarui dokumen " . implode(", ", $uploaded),
    'data' => []
]);