<?php

use App\Models\Admin;
use App\Models\Helper;
use Config\Core\Database;


if(!$adminPermissionCore->hasPermission($authorizedPermission, "/master/negara/update")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Denied",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['country_id', 'edit-country-name', 'edit-country-curr', 'edit-country-code', 'edit-country-phone-code'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "{$req} field is required",
            'data'      => []
        ]);
    }
}


$countryId  = $data['country_id'];
$countryName = $data['edit-country-name'];
$countryCurr = $data['edit-country-curr'];
$countryCode = $data['edit-country-code'];
$countryPhone = $data['edit-country-phone-code'];

/** Check Id */
$sqlGet = $db->query("SELECT ID_COUNTRY FROM tb_country WHERE MD5(MD5(ID_COUNTRY)) = '{$countryId}' LIMIT 1");
$country = $sqlGet->fetch_assoc();
if($sqlGet->num_rows != 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid ID",
        'data'      => []
    ]);
}

/** Check Name */
$sqlCheckDuplicate = $db->query("SELECT * FROM tb_country WHERE LOWER(COUNTRY_NAME) = LOWER('$countryName') AND MD5(MD5(ID_COUNTRY)) != '{$countryId}' LIMIT 1");
if($sqlCheckDuplicate->num_rows != 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Country Name already exists",
        'data'      => []
    ]);
} 

/** Check Country Code */
$sqlCheckDuplicate = $db->query("SELECT * FROM tb_country WHERE LOWER(COUNTRY_CODE) = LOWER('$countryCode') AND MD5(MD5(ID_COUNTRY)) != '{$countryId}' LIMIT 1");
if($sqlCheckDuplicate->num_rows != 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Country Code already exists",
        'data'      => []
    ]);
}

$updateData = [
    'COUNTRY_NAME'  => $countryName,
    'COUNTRY_CURR'  => $countryCurr,
    'COUNTRY_CODE'  => $countryCode,
    'COUNTRY_PHONE_CODE' => $countryPhone
];

$update = Database::update("tb_country", $updateData, ['ID_COUNTRY' => $country['ID_COUNTRY']]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Update country data failed",
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Update country data successfull",
    'data'      => []
]);