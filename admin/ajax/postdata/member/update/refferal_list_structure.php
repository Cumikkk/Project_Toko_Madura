<?php

use App\Models\Helper;
use App\Models\SalesDivision;
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



$division = Helper::form_input($_POST['division'] ?? 0);
if(is_numeric($division) === FALSE ||$division <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid",
        'data' => []
    ]);
}

$result = [];
$structure = SalesDivision::getStructure($division);
foreach($structure as $ar) {
    $result[] = [
        'id' => md5(md5($ar['ID_SLSSTRC'])),
        'name' => $ar['SLSSTRC_NAME'],
        'selected' => $ar['ID_SLSSTRC'] == $userData['MBR_TYPE']
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Success",
    'data' => $result
]);