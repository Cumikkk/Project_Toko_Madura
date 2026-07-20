<?php

use App\Models\Admin;
use App\Models\Helper;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/master/negara/create")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Denied",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['add-country-name', 'add-country-curr', 'add-country-code', 'add-country-phone-code'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "{$req} field is required",
            'data'      => []
        ]);
    }
}

$countryName = $data['add-country-name'];
$countryCurr = $data['add-country-curr'];
$countryCode = $data['add-country-code'];
$countryPhone = $data['add-country-phone-code'];

/** Check Country Name */
$sqlCheckDuplicate = $db->query("SELECT * FROM tb_country WHERE LOWER(COUNTRY_NAME) = LOWER('$countryName') LIMIT 1");
if($sqlCheckDuplicate->num_rows != 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Country Name already exists",
        'data'      => []
    ]);
} 

/** Check Country Code */
$sqlCheckDuplicate = $db->query("SELECT * FROM tb_country WHERE LOWER(COUNTRY_CODE) = LOWER('$countryCode') LIMIT 1");
if($sqlCheckDuplicate->num_rows != 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Country Name already exists",
        'data'      => []
    ]);
}

/** Insert */
$insert = Database::insert("tb_country", [
    'COUNTRY_NAME'  => $countryName,
    'COUNTRY_CURR'  => $countryCurr,
    'COUNTRY_CODE'  => $countryCode,
    'COUNTRY_PHONE_CODE' => $countryPhone
]);

if(!$insert) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Add country failed, please try again later",
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Add Country Successfull",
    'data'      => []
]);