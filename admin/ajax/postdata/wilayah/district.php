<?php

use App\Models\Helper;
use App\Models\User;
use App\Models\Wilayah;

$regency = Helper::form_input($_POST['regency'] ?? "");
$userCode = Helper::form_input($_POST["user"] ??"");
if(!empty($userCode)) {
    $userdata = User::findByCode($userCode);
}

$district = Wilayah::district($regency);
$result = [];
foreach($district as $dis) {
    $result[] = [
        'name' => $dis,
        'selected' => (isset($userdata['MBR_DISTRICT']) && $dis == $userdata['MBR_DISTRICT'])? "selected" : false
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => $result
]);