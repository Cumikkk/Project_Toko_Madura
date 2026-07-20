<?php

use App\Models\Helper;
use App\Models\User;
use App\Models\Wilayah;

$province = Helper::form_input($_POST['province'] ?? "");
$userCode = Helper::form_input($_POST["user"] ??"");
if(!empty($userCode)) {
    $userdata = User::findByCode($userCode);
}

$regency = Wilayah::regency($province);
$result = [];
foreach($regency as $reg) {
    $result[] = [
        'name' => $reg,
        'selected' => (isset($userdata['MBR_CITY']) && $reg == $userdata['MBR_CITY'])? "selected" : false
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => $result
]);