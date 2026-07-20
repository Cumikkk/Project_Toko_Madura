<?php

use App\Models\Account;
use App\Models\Helper;
use App\Models\ProfilePerusahaan;
use Config\Core\Database;

$id = Helper::form_input($_POST['id'] ?? 0);
$account = Account::realAccountDetail($id);
if(!$account) {
    JsonResponse([
        'success' => false,
        'message' => "Account not found",
        'data' => []
    ]);
}

$selected = Helper::form_input($_POST['selected'] ?? '');
if(empty($selected)) {
    JsonResponse([
        'success' => false,
        'message' => "No wpb selected",
        'data' => []
    ]);
}

/** check wpb name */
$wpb = ProfilePerusahaan::wpb_verifikator_search_bynme($selected);
if(empty($wpb)) {
    JsonResponse([
        'success' => false,
        'message' => "WPB not found",
        'data' => []
    ]);
}

/** Update Racc */
$update = Database::update("tb_racc", ['ACC_F_PERJ_WPB' => $wpb['WPB_NAMA']], ['ID_ACC' => $account['ID_ACC']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to update WPB",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "WPB updated successfully",
    'data' => []
]);