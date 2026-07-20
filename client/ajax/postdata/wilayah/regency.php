<?php

use App\Models\Helper;
use App\Models\Wilayah;

$province = Helper::form_input($_POST['province'] ?? "");
$regency = Wilayah::regency($province);
$result = [];
foreach($regency as $reg) {
    $result[] = [
        'name' => $reg,
        'selected' => (isset($user['MBR_CITY']) && $reg == $user['MBR_CITY'])? "selected" : false
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => $result
]);