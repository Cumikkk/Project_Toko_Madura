<?php

use App\Models\Helper;
use App\Models\Wilayah;

$district = Helper::form_input($_POST['district'] ?? "");
$villages = Wilayah::villages($district);
$result = [];
foreach($villages as $vil) {
    $result[] = [
        'name' => $vil['KDP_KELURAHAN'],
        'postalCode' => $vil['KDP_POS'],
        'selected' => (isset($user['MBR_VILLAGES']) && $vil['KDP_KELURAHAN'] == $user['MBR_VILLAGES'])? "selected" : false,
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => $result
]);