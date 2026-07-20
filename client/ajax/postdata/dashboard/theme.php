<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$theme = $data['theme'];

$update = Database::update("tb_member", ['MBR_THEME' => $theme], ['MBR_ID' => $user['MBR_ID']]);

JsonResponse([
    'success' => true,
    'message' => "Verifikasi successfull",
    'data' => []
]);