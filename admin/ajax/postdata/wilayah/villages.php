<?php

use App\Models\Helper;
use App\Models\User;
use App\Models\Wilayah;

$district = Helper::form_input($_POST['district'] ?? "");
$userCode = Helper::form_input($_POST["user"] ??"");
if(!empty($userCode)) {
    $userdata = User::findByCode($userCode);
}

$villages = Wilayah::villages($district);
$result = [];
foreach($villages as $vil) {
    $result[] = [
        'name' => $vil['KDP_KELURAHAN'],
        'postalCode' => $vil['KDP_POS'],
        'selected' => (isset($userdata['MBR_VILLAGES']) && $vil['KDP_KELURAHAN'] == $userdata['MBR_VILLAGES'])? "selected" : false,
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => $result
]);