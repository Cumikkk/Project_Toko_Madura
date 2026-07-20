<?php

use App\Models\Helper;
use App\Models\Wilayah;

$regency = Helper::form_input($_POST['regency'] ?? "");
$district = Wilayah::district($regency);
$result = [];
foreach($district as $dis) {
    $result[] = [
        'name' => $dis,
        'selected' => (isset($user['MBR_DISTRICT']) && $dis == $user['MBR_DISTRICT'])? "selected" : false
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => $result
]);